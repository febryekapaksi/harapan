<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Retur_pembelian_model extends BF_Model
{
    protected $table_name = 'tr_retur_pembelian';
    protected $key        = 'id';

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Retur_pembelian.Add');
        $this->ENABLE_MANAGE  = has_permission('Retur_pembelian.Manage');
        $this->ENABLE_VIEW    = has_permission('Retur_pembelian.View');
        $this->ENABLE_DELETE  = has_permission('Retur_pembelian.Delete');
    }

    // ============================
    // GENERATE NOMOR RETUR
    // ============================
    public function generate_no_retur()
    {
        $year = date('Y');
        $sql  = "SELECT MAX(no_retur) as max_no FROM tr_retur_pembelian WHERE no_retur LIKE 'RTR-{$year}-%'";
        $row  = $this->db->query($sql)->row_array();

        $urut = 1;
        if (!empty($row['max_no'])) {
            $last  = explode('-', $row['max_no']);
            $urut  = (int) end($last) + 1;
        }

        return 'RTR-' . $year . '-' . sprintf('%05d', $urut);
    }

    // ============================
    // GET INVOICE BY SUPPLIER
    // ============================
    public function get_invoice_by_supplier($id_supplier)
    {
        // id_suplier di tr_incoming berisi kode_supplier dari new_supplier
        $kode_supplier = $this->db->select('kode_supplier')->get_where('new_supplier', ['id' => $id_supplier])->row();
        if (!$kode_supplier) return [];

        $sql = "SELECT ti.id_data, ti.id_incoming, ti.no_invoice, ti.tanggal as tgl_invoice, ti.hutang_idr as total
                FROM tr_incoming ti
                WHERE ti.id_suplier = ?
                ORDER BY ti.tanggal DESC";
        return $this->db->query($sql, [$kode_supplier->kode_supplier])->result_array();
    }

    // ============================
    // GET DETAIL INVOICE
    // ============================
    public function get_detail_invoice($id_data, $no_po = null)
    {
        $sql = "SELECT di.id_material as id_product, di.kode_barang, di.nama_material as nama_barang,
                       '' as satuan, di.qty_recive as qty_beli, di.harga_satuan_usd as harga_satuan,
                       di.harga_total_idr as total_nilai
                FROM dt_incoming di
                WHERE di.id_data = ?";
        $params = [$id_data];

        if (!empty($no_po)) {
            $sql .= " AND SUBSTRING(di.id_dt_po, 1, 8) = ?";
            $params[] = $no_po;
        }

        $sql .= " ORDER BY di.id ASC";
        return $this->db->query($sql, $params)->result_array();
    }

    // ============================
    // GET PO LIST BY INCOMING
    // ============================
    public function get_po_by_incoming($id_data)
    {
        $sql = "SELECT DISTINCT SUBSTRING(di.id_dt_po, 1, 8) as no_po
                FROM dt_incoming di
                WHERE di.id_data = ?
                ORDER BY no_po ASC";
        return $this->db->query($sql, [$id_data])->result_array();
    }

    // ============================
    // GET BY ID (LENGKAP)
    // ============================
    public function get_by_id($id)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header) return null;

        $detail = $this->db->get_where('tr_retur_pembelian_detail', ['id_retur' => $id])->result_array();
        $pinalti = $this->db->get_where('tr_retur_pembelian_pinalti', ['id_retur' => $id])->result_array();

        return [
            'header'  => $header,
            'detail'  => $detail,
            'pinalti' => $pinalti,
        ];
    }

    // ============================
    // GET SETTLEMENT
    // ============================
    public function get_settlement($id)
    {
        return $this->db->order_by('tgl_terima', 'asc')
            ->get_where('tr_retur_pembelian_settlement', ['id_retur' => $id])
            ->result_array();
    }

    // ============================
    // SAVE RETUR (DRAFT)
    // ============================
    public function save_retur($post, $file_ba = null)
    {
        $no_retur = $this->generate_no_retur();
        $detail   = isset($post['detail']) ? $post['detail'] : [];
        $pinalti  = isset($post['pinalti']) ? $post['pinalti'] : [];

        // Hitung totals
        $nilai_retur = 0;
        foreach ($detail as $d) {
            $qty_retur    = (float) str_replace(',', '', $d['qty_retur']);
            $harga        = (float) str_replace(',', '', $d['harga_satuan']);
            $nilai_retur += ($qty_retur * $harga);
        }

        $total_pinalti = 0;
        foreach ($pinalti as $p) {
            $total_pinalti += (float) str_replace(',', '', $p['nilai']);
        }

        $ppn         = $nilai_retur * 0.11;
        $total_retur = $nilai_retur + $ppn + $total_pinalti;

        $header = [
            'no_retur'          => $no_retur,
            'no_invoice'        => $post['no_invoice'],
            'id_supplier'       => $post['id_supplier'],
            'nama_supplier'     => $post['nama_supplier'],
            'tgl_pembelian'     => !empty($post['tgl_pembelian']) ? date('Y-m-d', strtotime($post['tgl_pembelian'])) : null,
            'tgl_retur'         => date('Y-m-d', strtotime($post['tgl_retur'])),
            'nilai_retur'       => $nilai_retur,
            'ppn'               => $ppn,
            'total_retur'       => $total_retur,
            'pinalti'           => $total_pinalti,
            'settlement'        => 0,
            'sisa_retur'        => $total_retur,
            'kembalikan_barang' => isset($post['kembalikan_barang']) ? $post['kembalikan_barang'] : 'Tidak',
            'nota_retur'        => isset($post['nota_retur']) ? $post['nota_retur'] : 'Tidak',
            'kategori_alasan'   => isset($post['kategori_alasan']) ? $post['kategori_alasan'] : null,
            'keterangan_alasan' => isset($post['keterangan_alasan']) ? $post['keterangan_alasan'] : null,
            'file_ba'           => $file_ba,
            'status'            => 1, // Draft
            'created_by'        => $this->auth->user_id(),
            'created_date'      => date('Y-m-d H:i:s'),
        ];

        $this->db->trans_start();

        $this->db->insert('tr_retur_pembelian', $header);
        $id_retur = $this->db->insert_id();

        // Insert detail
        $arr_detail = [];
        foreach ($detail as $d) {
            $qty_retur = (float) str_replace(',', '', $d['qty_retur']);
            if ($qty_retur <= 0) continue;

            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            $arr_detail[] = [
                'id_retur'     => $id_retur,
                'no_retur'     => $no_retur,
                'id_product'   => $d['id_product'],
                'kode_barang'  => $d['kode_barang'],
                'nama_barang'  => $d['nama_barang'],
                'satuan'       => isset($d['satuan']) ? $d['satuan'] : '',
                'qty_beli'     => (float) str_replace(',', '', $d['qty_beli']),
                'qty_retur'    => $qty_retur,
                'harga_satuan' => $harga,
                'total_nilai'  => $qty_retur * $harga,
            ];
        }
        if (!empty($arr_detail)) {
            $this->db->insert_batch('tr_retur_pembelian_detail', $arr_detail);
        }

        // Insert pinalti
        $arr_pinalti = [];
        foreach ($pinalti as $p) {
            $nilai = (float) str_replace(',', '', $p['nilai']);
            if ($nilai <= 0) continue;

            $arr_pinalti[] = [
                'id_retur'   => $id_retur,
                'no_retur'   => $no_retur,
                'nilai'      => $nilai,
                'keterangan' => isset($p['keterangan']) ? $p['keterangan'] : '',
            ];
        }
        if (!empty($arr_pinalti)) {
            $this->db->insert_batch('tr_retur_pembelian_pinalti', $arr_pinalti);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal menyimpan data.'];
        }

        return ['status' => 1, 'pesan' => 'Data berhasil disimpan.', 'no_retur' => $no_retur];
    }

    // ============================
    // UPDATE RETUR (DRAFT ONLY)
    // ============================
    public function update_retur($id, $post, $file_ba = null)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header || $header['status'] != 1) {
            return ['status' => 0, 'pesan' => 'Data tidak bisa diedit.'];
        }

        $no_retur = $header['no_retur'];
        $detail   = isset($post['detail']) ? $post['detail'] : [];
        $pinalti  = isset($post['pinalti']) ? $post['pinalti'] : [];

        // Hitung totals
        $nilai_retur = 0;
        foreach ($detail as $d) {
            $qty_retur    = (float) str_replace(',', '', $d['qty_retur']);
            $harga        = (float) str_replace(',', '', $d['harga_satuan']);
            $nilai_retur += ($qty_retur * $harga);
        }

        $total_pinalti = 0;
        foreach ($pinalti as $p) {
            $total_pinalti += (float) str_replace(',', '', $p['nilai']);
        }

        $ppn         = $nilai_retur * 0.11;
        $total_retur = $nilai_retur + $ppn + $total_pinalti;

        $update = [
            'no_invoice'        => $post['no_invoice'],
            'id_supplier'       => $post['id_supplier'],
            'nama_supplier'     => $post['nama_supplier'],
            'tgl_pembelian'     => !empty($post['tgl_pembelian']) ? date('Y-m-d', strtotime($post['tgl_pembelian'])) : null,
            'tgl_retur'         => date('Y-m-d', strtotime($post['tgl_retur'])),
            'nilai_retur'       => $nilai_retur,
            'ppn'               => $ppn,
            'total_retur'       => $total_retur,
            'pinalti'           => $total_pinalti,
            'sisa_retur'        => $total_retur,
            'kembalikan_barang' => isset($post['kembalikan_barang']) ? $post['kembalikan_barang'] : 'Tidak',
            'nota_retur'        => isset($post['nota_retur']) ? $post['nota_retur'] : 'Tidak',
            'kategori_alasan'   => isset($post['kategori_alasan']) ? $post['kategori_alasan'] : null,
            'keterangan_alasan' => isset($post['keterangan_alasan']) ? $post['keterangan_alasan'] : null,
            'updated_by'        => $this->auth->user_id(),
            'updated_date'      => date('Y-m-d H:i:s'),
        ];

        if ($file_ba) {
            $update['file_ba'] = $file_ba;
        }

        $this->db->trans_start();

        $this->db->update('tr_retur_pembelian', $update, ['id' => $id]);

        // Delete & re-insert detail
        $this->db->delete('tr_retur_pembelian_detail', ['id_retur' => $id]);
        $arr_detail = [];
        foreach ($detail as $d) {
            $qty_retur = (float) str_replace(',', '', $d['qty_retur']);
            if ($qty_retur <= 0) continue;

            $harga = (float) str_replace(',', '', $d['harga_satuan']);
            $arr_detail[] = [
                'id_retur'     => $id,
                'no_retur'     => $no_retur,
                'id_product'   => $d['id_product'],
                'kode_barang'  => $d['kode_barang'],
                'nama_barang'  => $d['nama_barang'],
                'satuan'       => isset($d['satuan']) ? $d['satuan'] : '',
                'qty_beli'     => (float) str_replace(',', '', $d['qty_beli']),
                'qty_retur'    => $qty_retur,
                'harga_satuan' => $harga,
                'total_nilai'  => $qty_retur * $harga,
            ];
        }
        if (!empty($arr_detail)) {
            $this->db->insert_batch('tr_retur_pembelian_detail', $arr_detail);
        }

        // Delete & re-insert pinalti
        $this->db->delete('tr_retur_pembelian_pinalti', ['id_retur' => $id]);
        $arr_pinalti = [];
        foreach ($pinalti as $p) {
            $nilai = (float) str_replace(',', '', $p['nilai']);
            if ($nilai <= 0) continue;

            $arr_pinalti[] = [
                'id_retur'   => $id,
                'no_retur'   => $no_retur,
                'nilai'      => $nilai,
                'keterangan' => isset($p['keterangan']) ? $p['keterangan'] : '',
            ];
        }
        if (!empty($arr_pinalti)) {
            $this->db->insert_batch('tr_retur_pembelian_pinalti', $arr_pinalti);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal update data.'];
        }

        return ['status' => 1, 'pesan' => 'Data berhasil diupdate.'];
    }

    // ============================
    // AJUKAN (Draft -> Process)
    // ============================
    public function ajukan($id)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header || $header['status'] != 1) {
            return ['status' => 0, 'pesan' => 'Data tidak bisa diajukan.'];
        }

        $detail = $this->db->get_where('tr_retur_pembelian_detail', ['id_retur' => $id])->result_array();

        $this->db->trans_start();

        // Update status
        $this->db->update('tr_retur_pembelian', [
            'status'       => 2,
            'updated_by'   => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        // Buat jurnal via gl_interface
        $this->load->model('Retur_pembelian/Jurnal_retur_model');
        $this->Jurnal_retur_model->create_jurnal_retur($header, $detail);

        // Jika ada pinalti
        if ($header['pinalti'] > 0) {
            $this->Jurnal_retur_model->create_jurnal_pinalti($header);
        }

        // Update stok (Out)
        foreach ($detail as $d) {
            $this->db->set('qty_stock', 'qty_stock - ' . (float)$d['qty_retur'], false);
            $this->db->where('id_material', $d['id_product']);
            $this->db->update('warehouse_stock');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal mengajukan retur.'];
        }

        return ['status' => 1, 'pesan' => 'Retur berhasil diajukan.', 'no_retur' => $header['no_retur']];
    }

    // ============================
    // CANCEL RETUR
    // ============================
    public function cancel($id)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header || !in_array($header['status'], [1, 2])) {
            return ['status' => 0, 'pesan' => 'Data tidak bisa dibatalkan.'];
        }

        $this->db->trans_start();

        // Jika sudah Process, buat jurnal balik + kembalikan stok
        if ($header['status'] == 2) {
            $detail = $this->db->get_where('tr_retur_pembelian_detail', ['id_retur' => $id])->result_array();

            $this->load->model('Retur_pembelian/Jurnal_retur_model');
            $this->Jurnal_retur_model->create_jurnal_balik($header, $detail);

            // Kembalikan stok (In)
            foreach ($detail as $d) {
                $this->db->set('qty_stock', 'qty_stock + ' . (float)$d['qty_retur'], false);
                $this->db->where('id_material', $d['id_product']);
                $this->db->update('warehouse_stock');
            }
        }

        $this->db->update('tr_retur_pembelian', [
            'status'       => 0,
            'updated_by'   => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal membatalkan retur.'];
        }

        return ['status' => 1, 'pesan' => 'Retur berhasil dibatalkan.'];
    }

    // ============================
    // SAVE SETTLEMENT (Terima Uang)
    // ============================
    public function save_settlement($id, $post)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header || $header['sisa_retur'] <= 0) {
            return ['status' => 0, 'pesan' => 'Sisa retur sudah 0.'];
        }

        $jumlah = (float) str_replace(',', '', $post['jumlah']);
        if ($jumlah <= 0 || $jumlah > $header['sisa_retur']) {
            return ['status' => 0, 'pesan' => 'Jumlah tidak valid (max: ' . number_format($header['sisa_retur']) . ').'];
        }

        $this->db->trans_start();

        // Insert settlement
        $this->db->insert('tr_retur_pembelian_settlement', [
            'id_retur'     => $id,
            'no_retur'     => $header['no_retur'],
            'tgl_terima'   => date('Y-m-d', strtotime($post['tgl_terima'])),
            'jumlah'       => $jumlah,
            'metode'       => $post['metode'],
            'no_referensi' => isset($post['no_referensi']) ? $post['no_referensi'] : null,
            'keterangan'   => isset($post['keterangan']) ? $post['keterangan'] : null,
            'created_by'   => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s'),
        ]);

        // Update header
        $new_settlement = $header['settlement'] + $jumlah;
        $new_sisa       = $header['total_retur'] - $new_settlement;
        $new_status     = ($new_sisa <= 0) ? 3 : $header['status']; // Selesai jika sisa = 0

        $this->db->update('tr_retur_pembelian', [
            'settlement'   => $new_settlement,
            'sisa_retur'   => $new_sisa,
            'status'       => $new_status,
            'updated_by'   => $this->auth->user_id(),
            'updated_date' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        // Jurnal settlement
        $this->load->model('Retur_pembelian/Jurnal_retur_model');
        $this->Jurnal_retur_model->create_jurnal_settlement($header, $jumlah, $post['metode']);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => 0, 'pesan' => 'Gagal menyimpan settlement.'];
        }

        $msg = ($new_sisa <= 0) ? 'Settlement berhasil. Retur telah selesai.' : 'Settlement berhasil disimpan.';
        return ['status' => 1, 'pesan' => $msg];
    }

    // ============================
    // TERIMA NOTA RETUR
    // ============================
    public function terima_nota($id, $tgl_terima)
    {
        $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
        if (!$header || $header['nota_retur'] != 'Ya') {
            return ['status' => 0, 'pesan' => 'Data tidak valid.'];
        }

        $this->db->update('tr_retur_pembelian', [
            'status_nota_retur' => 1,
            'tgl_terima_nota'   => date('Y-m-d', strtotime($tgl_terima)),
            'updated_by'        => $this->auth->user_id(),
            'updated_date'      => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        return ['status' => 1, 'pesan' => 'Nota retur berhasil dikonfirmasi.'];
    }

    // ============================
    // DATA SERVER-SIDE (Index)
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
            6 => 'rp.settlement',
            7 => 'rp.sisa_retur',
            8 => 'rp.status',
        ];

        // Total data
        $totalData = $this->db->count_all('tr_retur_pembelian');

        // Filtered
        $this->db->from('tr_retur_pembelian rp');
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
            $status_badge = '';
            switch ($row['status']) {
                case 0: $status_badge = "<span class='badge bg-red'>Cancel</span>"; break;
                case 1: $status_badge = "<span class='badge bg-yellow'>Draft</span>"; break;
                case 2: $status_badge = "<span class='badge bg-blue'>Process</span>"; break;
                case 3: $status_badge = "<span class='badge bg-green'>Selesai</span>"; break;
            }

            // Action buttons
            $btn_view = "<a href='" . site_url('retur_pembelian/view/' . $row['id']) . "' class='btn btn-xs btn-warning' title='View'><i class='fa fa-eye'></i></a>";
            $btn_edit = "";
            $btn_cancel = "";
            $btn_ajukan = "";
            $btn_print_sj = "";
            $btn_terima_uang = "";

            if ($row['status'] == 1) { // Draft
                $btn_edit   = "<a href='" . site_url('retur_pembelian/edit/' . $row['id']) . "' class='btn btn-xs btn-info' title='Edit'><i class='fa fa-edit'></i></a>";
                $btn_ajukan = "<button onclick=\"ajukanRetur('{$row['id']}')\" class='btn btn-xs btn-success' title='Ajukan'><i class='fa fa-check'></i></button>";
                $btn_cancel = "<button onclick=\"cancelRetur('{$row['id']}')\" class='btn btn-xs btn-danger' title='Cancel'><i class='fa fa-times'></i></button>";
            }
            if ($row['status'] == 2) { // Process
                $btn_cancel = "<button onclick=\"cancelRetur('{$row['id']}')\" class='btn btn-xs btn-danger' title='Cancel'><i class='fa fa-times'></i></button>";
                if ($row['kembalikan_barang'] == 'Ya') {
                    $btn_print_sj = "<a href='" . site_url('retur_pembelian/print_sj/' . $row['id']) . "' class='btn btn-xs btn-default' title='Print SJ' target='_blank'><i class='fa fa-print'></i></a>";
                }
                if ($row['sisa_retur'] > 0) {
                    $btn_terima_uang = "<a href='" . site_url('retur_pembelian/settlement/' . $row['id']) . "' class='btn btn-xs btn-primary' title='Terima Uang'><i class='fa fa-money'></i></a>";
                }
            }

            $action = "<div class='text-center'>{$btn_view} {$btn_edit} {$btn_ajukan} {$btn_print_sj} {$btn_terima_uang} {$btn_cancel}</div>";

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['no_invoice'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = date('d/m/Y', strtotime($row['tgl_retur']));
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_retur'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['settlement'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['sisa_retur'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-center'>{$status_badge}</div>";
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
    // DATA NOTA RETUR SERVER-SIDE
    // ============================
    public function data_nota_retur_serverside()
    {
        $requestData = $_REQUEST;
        $search = $requestData['search']['value'];
        $start  = $requestData['start'];
        $length = $requestData['length'];

        $this->db->from('tr_retur_pembelian');
        $this->db->where('nota_retur', 'Ya');
        $this->db->where('status', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('no_retur', $search);
            $this->db->or_like('nama_supplier', $search);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        $this->db->select('*');
        $this->db->from('tr_retur_pembelian');
        $this->db->where('nota_retur', 'Ya');
        $this->db->where('status', 2);
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('no_retur', $search);
            $this->db->or_like('nama_supplier', $search);
            $this->db->group_end();
        }
        $this->db->order_by('created_date', 'desc');
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        $data = [];
        $urut = $start + 1;
        foreach ($query->result_array() as $row) {
            $status_nota = $row['status_nota_retur'] == 1
                ? "<span class='badge bg-green'>Sudah Diterima</span>"
                : "<span class='badge bg-yellow'>Belum Diterima</span>";

            $btn_terima = '';
            if ($row['status_nota_retur'] == 0) {
                $btn_terima = "<button onclick=\"terimaNota('{$row['id']}')\" class='btn btn-xs btn-success' title='Konfirmasi Terima'><i class='fa fa-check'></i> Terima</button>";
            }

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = date('d/m/Y', strtotime($row['tgl_retur']));
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_retur'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-center'>{$status_nota}</div>";
            $nestedData[] = "<div class='text-center'>{$btn_terima}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalFiltered),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data,
        ]);
    }
}
