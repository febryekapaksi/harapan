<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Terima_uang_supplier_model extends BF_Model
{
    protected $table_name = 'tr_terima_uang_supplier';
    protected $key        = 'id';

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Terima_uang_supplier.Add');
        $this->ENABLE_MANAGE  = has_permission('Terima_uang_supplier.Manage');
        $this->ENABLE_VIEW    = has_permission('Terima_uang_supplier.View');
        $this->ENABLE_DELETE  = has_permission('Terima_uang_supplier.Delete');
    }

    // ============================
    // GET RETUR BY ID (LENGKAP)
    // ============================
    public function get_retur_by_id($id)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header) return null;

        return [
            'header' => $header,
        ];
    }

    // ============================
    // GET RETUR DETAIL ITEMS
    // ============================
    public function get_retur_detail($id_retur)
    {
        return $this->db->select('*')
            ->from('tr_retur_pembelian_detail')
            ->where('id_retur', $id_retur)
            ->order_by('id', 'asc')
            ->get()
            ->result_array();
    }

    // ============================
    // GET HISTORY PENERIMAAN
    // ============================
    public function get_history_penerimaan($id_retur)
    {
        return $this->db->select('*')
            ->from('tr_terima_uang_supplier')
            ->where('id_retur', $id_retur)
            ->order_by('tgl_terima', 'asc')
            ->get()
            ->result_array();
    }

    // ============================
    // SAVE PENERIMAAN UANG
    // ============================
    public function save_penerimaan($post)
    {
        $id_retur = $post['id_retur'];

        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id_retur])->row_array();
        if (!$header || $header['sisa_retur'] <= 0) {
            return ['status' => 0, 'pesan' => 'Sisa retur sudah 0 atau data tidak valid.'];
        }

        // Hitung total dari detail items yang diinput
        $items  = isset($post['items']) ? $post['items'] : [];
        $nilai  = 0;
        foreach ($items as $item) {
            $qty   = (float) str_replace(',', '', $item['qty']);
            $harga = (float) str_replace(',', '', $item['harga_satuan']);
            $nilai += ($qty * $harga);
        }

        $ppn   = (float) str_replace(',', '', $post['ppn']);
        $total = $nilai + $ppn;

        if ($total <= 0) {
            return ['status' => 0, 'pesan' => 'Total penerimaan harus lebih dari 0.'];
        }

        if ($total > $header['sisa_retur']) {
            return ['status' => 0, 'pesan' => 'Total penerimaan melebihi sisa retur (max: ' . number_format($header['sisa_retur']) . ').'];
        }

        $this->db->trans_start();

        // Insert header penerimaan
        $insert_header = [
            'id_retur'              => $id_retur,
            'no_retur'              => $header['no_retur'],
            'no_invoice'            => $header['no_invoice'],
            'id_supplier'           => $header['id_supplier'],
            'nama_supplier'         => $header['nama_supplier'],
            'no_sj_retur'           => isset($post['no_sj_retur']) ? $post['no_sj_retur'] : null,
            'no_faktur_pajak_retur' => isset($post['no_faktur_pajak_retur']) ? $post['no_faktur_pajak_retur'] : null,
            'no_nota_retur_supplier'=> isset($post['no_nota_retur_supplier']) ? $post['no_nota_retur_supplier'] : null,
            'tgl_terima'            => date('Y-m-d', strtotime($post['tgl_terima'])),
            'nilai'                 => $nilai,
            'ppn'                   => $ppn,
            'total'                 => $total,
            'created_by'            => $this->auth->user_id(),
            'created_date'          => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tr_terima_uang_supplier', $insert_header);
        $id_penerimaan = $this->db->insert_id();

        // Insert detail items
        if (!empty($items) && $id_penerimaan) {
            $arr_detail = [];
            foreach ($items as $item) {
                $qty   = (float) str_replace(',', '', $item['qty']);
                $harga = (float) str_replace(',', '', $item['harga_satuan']);
                if ($qty <= 0) continue;

                $arr_detail[] = [
                    'id_terima_uang' => $id_penerimaan,
                    'id_retur'       => $id_retur,
                    'keterangan'     => isset($item['keterangan']) ? $item['keterangan'] : '',
                    'qty'            => $qty,
                    'harga_satuan'   => $harga,
                    'total_nilai'    => $qty * $harga,
                ];
            }
            if (!empty($arr_detail)) {
                $this->db->insert_batch('tr_terima_uang_supplier_detail', $arr_detail);
            }
        }

        // Update settlement di tr_retur_pembelian
        $new_settlement = $header['settlement'] + $total;
        $new_sisa       = $header['total_retur'] - $new_settlement;
        $new_status     = ($new_sisa <= 0) ? 3 : $header['status']; // 3 = Selesai

        $this->db->update('tr_retur_pembelian', [
            'settlement'   => $new_settlement,
            'sisa_retur'   => $new_sisa,
            'status'       => $new_status,
            'updated_by'   => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s'),
        ], ['id' => $id_retur]);

        // Buat Jurnal: D: Bank, K: Hutang Dagang
        $this->_create_jurnal($header, $total);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal menyimpan data.'];
        }

        $msg = ($new_sisa <= 0) ? 'Penerimaan berhasil. Retur telah selesai.' : 'Penerimaan uang berhasil disimpan.';
        return [
            'status'  => 1,
            'pesan'   => $msg,
            'no_retur' => $header['no_retur'],
            'jumlah'  => $total,
        ];
    }

    // ============================
    // CREATE JURNAL
    // D: Bank (1102-01-01)
    // K: Hutang Dagang (2101-01-01)
    // ============================
    private function _create_jurnal($header, $jumlah)
    {
        $tgl_jurnal = date('Y-m-d');
        $keterangan = 'Terima Uang Supplier - ' . $header['no_retur'] . ' - ' . $header['nama_supplier'];

        $COA_BANK          = '1102-01-01'; // Bank
        $COA_HUTANG_DAGANG = '2101-01-01'; // Hutang Dagang

        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $jumlah,
            'kdcab'            => '101',
            'jenis'            => 'BM',
            'jenis_transaksi'  => 'terima_uang_supplier',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            // Debit: Bank
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'BM',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_BANK,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => $jumlah,
                'kredit'          => 0,
            ]);

            // Kredit: Hutang Dagang
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'BM',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => 0,
                'kredit'          => $jumlah,
            ]);
        }
    }

    // ============================
    // DATA SERVER-SIDE (Index)
    // Tampilkan retur yang masih ada sisa
    // ============================
    public function data_serverside()
    {
        $requestData = $_REQUEST;
        $search = $requestData['search']['value'];
        $col_order = $requestData['order'][0]['column'];
        $col_dir   = $requestData['order'][0]['dir'];
        $start     = $requestData['start'];
        $length    = $requestData['length'];

        $columns_order = [
            0 => 'rp.no_retur',
            1 => 'rp.no_retur',
            2 => 'rp.no_invoice',
            3 => 'rp.nama_supplier',
            4 => 'rp.tgl_retur',
            5 => 'rp.total_retur',
        ];

        // Total data (retur status Process & sisa > 0)
        $this->db->from('tr_retur_pembelian');
        $this->db->where('status', 2);
        $this->db->where('sisa_retur >', 0);
        $totalData = $this->db->count_all_results();

        // Filtered
        $this->db->from('tr_retur_pembelian rp');
        $this->db->where('rp.status', 2);
        $this->db->where('rp.sisa_retur >', 0);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('rp.no_retur', $search);
            $this->db->or_like('rp.no_invoice', $search);
            $this->db->or_like('rp.nama_supplier', $search);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Get data
        $this->db->select('rp.*');
        $this->db->from('tr_retur_pembelian rp');
        $this->db->where('rp.status', 2);
        $this->db->where('rp.sisa_retur >', 0);
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
            $this->db->order_by('rp.tgl_retur', 'desc');
        }
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        $data = [];
        $urut = $start + 1;
        foreach ($query->result_array() as $row) {
            $btn_view = "<a href='" . site_url('terima_uang_supplier/receive/' . $row['id']) . "' class='btn btn-xs btn-primary' title='Terima Uang'><i class='fa fa-money'></i> View</a>";

            $action = "<div class='text-center'>{$btn_view}</div>";

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['no_invoice'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = date('d/m/Y', strtotime($row['tgl_retur']));
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_retur'], 2, ',', '.') . "</div>";
            $nestedData[] = $action;

            $data[] = $nestedData;
            $urut++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        ];

        echo json_encode($json_data);
    }
}
