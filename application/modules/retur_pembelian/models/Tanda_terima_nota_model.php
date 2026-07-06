<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Tanda_terima_nota_model extends BF_Model
{
    protected $table_name = 'tr_tanda_terima_nota_retur';
    protected $key        = 'id';

    public function __construct()
    {
        parent::__construct();
    }

    // ============================
    // GET BY RETUR ID
    // ============================
    public function get_by_retur_id($id_retur)
    {
        $header = $this->db->get_where('tr_tanda_terima_nota_retur', ['id_retur' => $id_retur])->row_array();
        if (!$header) return null;

        $detail = $this->db->order_by('id', 'asc')
            ->get_where('tr_tanda_terima_nota_retur_detail', ['id_tanda_terima' => $header['id']])
            ->result_array();

        return [
            'header' => $header,
            'detail' => $detail,
        ];
    }

    // ============================
    // GET BY ID
    // ============================
    public function get_by_id($id)
    {
        $header = $this->db->get_where('tr_tanda_terima_nota_retur', ['id' => $id])->row_array();
        if (!$header) return null;

        $detail = $this->db->order_by('id', 'asc')
            ->get_where('tr_tanda_terima_nota_retur_detail', ['id_tanda_terima' => $id])
            ->result_array();

        return [
            'header' => $header,
            'detail' => $detail,
        ];
    }

    // ============================
    // SAVE TANDA TERIMA
    // ============================
    public function save_tanda_terima($id_retur, $post)
    {
        // Validasi: retur harus ada dan status Process (2), nota_retur = Ya
        $retur = $this->db->get_where('tr_retur_pembelian', ['id' => $id_retur])->row_array();
        if (!$retur) {
            return ['status' => 0, 'pesan' => 'Data retur tidak ditemukan.'];
        }
        if ($retur['nota_retur'] != 'Ya') {
            return ['status' => 0, 'pesan' => 'Retur ini tidak memerlukan Nota Retur.'];
        }

        // Cek apakah sudah ada tanda terima
        $existing = $this->db->get_where('tr_tanda_terima_nota_retur', ['id_retur' => $id_retur])->row();
        if ($existing) {
            return ['status' => 0, 'pesan' => 'Tanda Terima sudah dibuat sebelumnya. Gunakan menu Edit.'];
        }

        $details = isset($post['detail']) ? $post['detail'] : [];

        // Hitung total
        $nilai_barang = 0;
        foreach ($details as $d) {
            $qty   = (float) str_replace(',', '', $d['qty']);
            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            $nilai_barang += ($qty * $harga);
        }

        $ppn   = round($nilai_barang * 0.11, 2);
        $total = $nilai_barang + $ppn;

        $header_data = [
            'id_retur'              => $id_retur,
            'no_retur'              => $retur['no_retur'],
            'no_invoice'            => $retur['no_invoice'],
            'id_supplier'           => $retur['id_supplier'],
            'nama_supplier'         => $retur['nama_supplier'],
            'tgl_retur'             => $retur['tgl_retur'],
            'no_sj_retur'           => isset($post['no_sj_retur']) ? $post['no_sj_retur'] : null,
            'no_faktur_pajak_retur' => isset($post['no_faktur_pajak_retur']) ? $post['no_faktur_pajak_retur'] : null,
            'no_nota_retur_supplier'=> isset($post['no_nota_retur_supplier']) ? $post['no_nota_retur_supplier'] : null,
            'nilai_barang'          => $nilai_barang,
            'ppn'                   => $ppn,
            'total'                 => $total,
            'metode_retur'          => isset($post['metode_retur']) ? $post['metode_retur'] : 'Potong Tagihan',
            'status'                => 1, // 1=Draft/Created
            'created_by'            => $this->auth->user_id(),
            'created_date'          => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();

        $this->db->insert('tr_tanda_terima_nota_retur', $header_data);
        $id_tanda_terima = $this->db->insert_id();

        // Insert detail
        $arr_detail = [];
        foreach ($details as $d) {
            $qty   = (float) str_replace(',', '', $d['qty']);
            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            if ($qty <= 0) continue;

            $arr_detail[] = [
                'id_tanda_terima' => $id_tanda_terima,
                'keterangan'      => isset($d['keterangan']) ? $d['keterangan'] : '',
                'qty'             => $qty,
                'harga_satuan'    => $harga,
                'total_nilai'     => $qty * $harga,
            ];
        }
        if (!empty($arr_detail)) {
            $this->db->insert_batch('tr_tanda_terima_nota_retur_detail', $arr_detail);
        }

        // Update status nota_retur di tr_retur_pembelian
        $this->db->update('tr_retur_pembelian', [
            'status_nota_retur' => 1,
            'tgl_terima_nota'   => date('Y-m-d'),
            'updated_by'        => $this->auth->user_id(),
            'updated_date'      => date('Y-m-d H:i:s'),
        ], ['id' => $id_retur]);

        // Buat jurnal
        $this->_create_jurnal_nota_retur($header_data, $retur);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal menyimpan data.'];
        }

        return ['status' => 1, 'pesan' => 'Tanda Terima Nota Retur berhasil dibuat.', 'id' => $id_tanda_terima];
    }

    // ============================
    // UPDATE TANDA TERIMA
    // ============================
    public function update_tanda_terima($id, $post)
    {
        $header = $this->db->get_where('tr_tanda_terima_nota_retur', ['id' => $id])->row_array();
        if (!$header) {
            return ['status' => 0, 'pesan' => 'Data tidak ditemukan.'];
        }
        if ($header['status'] != 1) {
            return ['status' => 0, 'pesan' => 'Data tidak bisa diedit (sudah final).'];
        }

        $details = isset($post['detail']) ? $post['detail'] : [];

        // Hitung total
        $nilai_barang = 0;
        foreach ($details as $d) {
            $qty   = (float) str_replace(',', '', $d['qty']);
            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            $nilai_barang += ($qty * $harga);
        }

        $ppn   = round($nilai_barang * 0.11, 2);
        $total = $nilai_barang + $ppn;

        $update_data = [
            'no_sj_retur'           => isset($post['no_sj_retur']) ? $post['no_sj_retur'] : null,
            'no_faktur_pajak_retur' => isset($post['no_faktur_pajak_retur']) ? $post['no_faktur_pajak_retur'] : null,
            'no_nota_retur_supplier'=> isset($post['no_nota_retur_supplier']) ? $post['no_nota_retur_supplier'] : null,
            'nilai_barang'          => $nilai_barang,
            'ppn'                   => $ppn,
            'total'                 => $total,
            'metode_retur'          => isset($post['metode_retur']) ? $post['metode_retur'] : 'Potong Tagihan',
            'updated_by'            => $this->auth->user_id(),
            'updated_date'          => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();

        $this->db->update('tr_tanda_terima_nota_retur', $update_data, ['id' => $id]);

        // Delete & re-insert detail
        $this->db->delete('tr_tanda_terima_nota_retur_detail', ['id_tanda_terima' => $id]);
        $arr_detail = [];
        foreach ($details as $d) {
            $qty   = (float) str_replace(',', '', $d['qty']);
            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            if ($qty <= 0) continue;

            $arr_detail[] = [
                'id_tanda_terima' => $id,
                'keterangan'      => isset($d['keterangan']) ? $d['keterangan'] : '',
                'qty'             => $qty,
                'harga_satuan'    => $harga,
                'total_nilai'     => $qty * $harga,
            ];
        }
        if (!empty($arr_detail)) {
            $this->db->insert_batch('tr_tanda_terima_nota_retur_detail', $arr_detail);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal update data.'];
        }

        return ['status' => 1, 'pesan' => 'Tanda Terima berhasil diupdate.'];
    }

    // ============================
    // DATA SERVER-SIDE
    // ============================
    public function data_tanda_terima_serverside()
    {
        $requestData = $_REQUEST;
        $search = $requestData['search']['value'];
        $start  = $requestData['start'];
        $length = $requestData['length'];
        $col_order = $requestData['order'][0]['column'];
        $col_dir   = $requestData['order'][0]['dir'];

        $columns_order = [
            0 => 'tt.id',
            1 => 'tt.no_retur',
            2 => 'tt.no_invoice',
            3 => 'tt.nama_supplier',
            4 => 'tt.tgl_retur',
            5 => 'tt.metode_retur',
            6 => 'tt.total',
        ];

        // Total
        $this->db->from('tr_tanda_terima_nota_retur tt');
        $totalData = $this->db->count_all_results();

        // Filtered
        $this->db->from('tr_tanda_terima_nota_retur tt');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tt.no_retur', $search);
            $this->db->or_like('tt.no_invoice', $search);
            $this->db->or_like('tt.nama_supplier', $search);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Get data
        $this->db->select('tt.*');
        $this->db->from('tr_tanda_terima_nota_retur tt');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tt.no_retur', $search);
            $this->db->or_like('tt.no_invoice', $search);
            $this->db->or_like('tt.nama_supplier', $search);
            $this->db->group_end();
        }
        if (isset($columns_order[$col_order])) {
            $this->db->order_by($columns_order[$col_order], $col_dir);
        } else {
            $this->db->order_by('tt.created_date', 'desc');
        }
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        $data = [];
        $urut = $start + 1;
        foreach ($query->result_array() as $row) {
            // Metode badge
            $metode_badge = '';
            if ($row['metode_retur'] == 'Terima Uang') {
                $metode_badge = "<span class='badge bg-green'>Terima Uang</span>";
            } else {
                $metode_badge = "<span class='badge bg-blue'>Potong Tagihan</span>";
            }

            // Action buttons
            $btn_view = "<a href='" . site_url('retur_pembelian/view_tanda_terima/' . $row['id']) . "' class='btn btn-xs btn-warning' title='View'><i class='fa fa-eye'></i></a>";
            $btn_edit = "";
            if ($row['status'] == 1) {
                $btn_edit = "<a href='" . site_url('retur_pembelian/edit_tanda_terima/' . $row['id']) . "' class='btn btn-xs btn-info' title='Edit'><i class='fa fa-edit'></i></a>";
            }

            $action = "<div class='text-center'>{$btn_edit} {$btn_view}</div>";

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['no_invoice'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = date('d/m/Y', strtotime($row['tgl_retur']));
            $nestedData[] = "<div class='text-center'>{$metode_badge}</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['total'], 2, ',', '.') . "</div>";
            $nestedData[] = $action;

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        ]);
    }

    // ============================
    // GET RETUR FOR TANDA TERIMA (yang belum dibuat)
    // ============================
    public function get_retur_available()
    {
        $sql = "SELECT rp.id, rp.no_retur, rp.no_invoice, rp.nama_supplier, rp.tgl_retur, rp.total_retur
                FROM tr_retur_pembelian rp
                WHERE rp.nota_retur = 'Ya'
                AND rp.status IN (2, 3)
                AND NOT EXISTS (
                    SELECT 1 FROM tr_tanda_terima_nota_retur tt WHERE tt.id_retur = rp.id
                )
                ORDER BY rp.created_date DESC";
        return $this->db->query($sql)->result_array();
    }

    // ============================
    // DATA RETUR NOTA SERVER-SIDE (semua retur dengan nota_retur = Ya)
    // ============================
    public function data_retur_nota_serverside()
    {
        $requestData = $_REQUEST;
        $search = $requestData['search']['value'];
        $start  = $requestData['start'];
        $length = $requestData['length'];
        $col_order = $requestData['order'][0]['column'];
        $col_dir   = $requestData['order'][0]['dir'];

        $columns_order = [
            0 => 'rp.id',
            1 => 'rp.no_retur',
            2 => 'rp.no_invoice',
            3 => 'rp.nama_supplier',
            4 => 'rp.tgl_retur',
            5 => 'rp.total_retur',
        ];

        // Total
        $this->db->from('tr_retur_pembelian rp');
        $this->db->where('rp.nota_retur', 'Ya');
        $this->db->where_in('rp.status', [2, 3]);
        $totalData = $this->db->count_all_results();

        // Filtered
        $this->db->from('tr_retur_pembelian rp');
        $this->db->where('rp.nota_retur', 'Ya');
        $this->db->where_in('rp.status', [2, 3]);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('rp.no_retur', $search);
            $this->db->or_like('rp.no_invoice', $search);
            $this->db->or_like('rp.nama_supplier', $search);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Get data
        $this->db->select('rp.id, rp.no_retur, rp.no_invoice, rp.nama_supplier, rp.tgl_retur, rp.total_retur');
        $this->db->from('tr_retur_pembelian rp');
        $this->db->where('rp.nota_retur', 'Ya');
        $this->db->where_in('rp.status', [2, 3]);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('rp.no_retur', $search);
            $this->db->or_like('rp.no_invoice', $search);
            $this->db->or_like('rp.nama_supplier', $search);
            $this->db->group_end();
        }
        if (isset($columns_order[$col_order])) {
            $this->db->order_by($columns_order[$col_order], $col_dir);
        } else {
            $this->db->order_by('rp.created_date', 'desc');
        }
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        $data = [];
        $urut = $start + 1;
        foreach ($query->result_array() as $row) {
            // Cek apakah sudah ada tanda terima
            $tanda_terima = $this->db->get_where('tr_tanda_terima_nota_retur', ['id_retur' => $row['id']])->row_array();

            // Action buttons
            if ($tanda_terima) {
                $action = "<a href='" . site_url('retur_pembelian/view_tanda_terima/' . $tanda_terima['id']) . "' class='btn btn-xs btn-primary' title='View Tanda Terima'><i class='fa fa-eye'></i> View</a>";
            } else {
                $action = "<a href='" . site_url('retur_pembelian/create_tanda_terima/' . $row['id']) . "' class='btn btn-xs btn-success' title='Buat Tanda Terima'><i class='fa fa-plus'></i> Create</a>";
            }

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['no_invoice'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = date('d/m/Y', strtotime($row['tgl_retur']));
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_retur'], 2, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-center'>{$action}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        ]);
    }

    // ============================
    // PRIVATE: Buat Jurnal Nota Retur
    // Jurnal:
    //   D: 2101-01-01 Hutang Dagang = Nilai Barang + PPn
    //   K: 1107-01-06 PPN Dibayar Dimuka = PPn
    //   K: 1104-01-02 Persediaan Barang In Transit = Nilai Barang
    // ============================
    private function _create_jurnal_nota_retur($header_data, $retur)
    {
        $tgl_jurnal  = date('Y-m-d');
        $keterangan  = 'Tanda Terima Nota Retur - ' . $header_data['no_retur'] . ' - ' . $header_data['nama_supplier'];
        $no_retur    = $header_data['no_retur'];

        $COA_HUTANG_DAGANG       = '2101-01-01';
        $COA_PPN_DIBAYAR_DIMUKA  = '1107-01-06';
        $COA_PERSEDIAAN_TRANSIT  = '1104-01-02';

        $nilai_barang = floatval($header_data['nilai_barang']);
        $ppn          = floatval($header_data['ppn']);
        $total        = $nilai_barang + $ppn;

        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $total,
            'kdcab'            => '101',
            'jenis'            => 'JV',
            'jenis_transaksi'  => 'tanda_terima_nota_retur',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'memo'             => json_encode([
                'no_retur'              => $no_retur,
                'no_invoice'            => $header_data['no_invoice'],
                'no_faktur_pajak_retur' => $header_data['no_faktur_pajak_retur'],
                'no_nota_retur_supplier'=> $header_data['no_nota_retur_supplier'],
                'nilai_barang'          => $nilai_barang,
                'ppn'                   => $ppn,
                'total'                 => $total,
            ]),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            // Debit: Hutang Dagang (Nilai Barang + PPn)
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => $total,
                'kredit'          => 0,
            ]);

            // Kredit: PPN Dibayar Dimuka
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_PPN_DIBAYAR_DIMUKA,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => 0,
                'kredit'          => $ppn,
            ]);

            // Kredit: Persediaan Barang In Transit
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_PERSEDIAAN_TRANSIT,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => 0,
                'kredit'          => $nilai_barang,
            ]);
        }
    }
}
