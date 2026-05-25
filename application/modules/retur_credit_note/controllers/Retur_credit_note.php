<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_credit_note extends Admin_Controller
{
    protected $viewPermission   = 'Retur_credit_note.View';
    protected $addPermission    = 'Retur_credit_note.Add';
    protected $managePermission = 'Retur_credit_note.Manage';
    protected $deletePermission = 'Retur_credit_note.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array('Retur_credit_note/Retur_credit_note_model'));
        date_default_timezone_set('Asia/Bangkok');
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index()
    {
        $this->template->title('Retur Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('index');
    }

    public function data_side_inv()
    {
        $this->Retur_credit_note_model->data_side_inv();
    }

    // =========================================================
    // STEP 1: Request Retur (dari tombol di invoice_produk)
    // Hanya simpan request ke tr_retur, belum cancel invoice
    // =========================================================
    public function add($id_invoice)
    {
        // Cek apakah sudah ada request retur untuk invoice ini
        $existing = $this->db->get_where('tr_retur', ['id_invoice' => $id_invoice])->row();
        if ($existing) {
            // Sudah ada, redirect ke index dengan pesan
            $this->session->set_flashdata('warning', 'Invoice ini sudah memiliki request retur (No. ' . $existing->no_retur . ').');
            redirect('retur_credit_note');
        }

        $sql = "
            SELECT i.id_invoice, i.id_billing, i.id_so, sj.pengiriman,
                   i.id_customer, i.nm_customer
            FROM tr_invoice_sales i
            JOIN surat_jalan sj ON i.id_billing = sj.no_surat_jalan
            WHERE i.id_invoice = ?
        ";
        $inv = $this->db->query($sql, [$id_invoice])->row_array();

        $sql2 = "
            SELECT dt.id_so, sjd.id_so_det, dt.id_penawaran, dt.id_delivery,
                   dt.id_produk, dt.nm_produk,
                   round(dt.qty) as qty,
                   dt.harga,
                   sod.harga_beli,
                   round(dt.qty * dt.harga) as total
            FROM tr_invoice_sales_detail dt
            JOIN surat_jalan_detail sjd
                ON dt.id_delivery = sjd.no_surat_jalan
                AND dt.id_produk  = sjd.id_product
            JOIN sales_order_detail sod 
                ON sjd.id_so_det = sod.id
            WHERE dt.id_invoice = ?
            ORDER BY dt.id_invoice
        ";
        $detail = $this->db->query($sql2, [$id_invoice])->result_array();

        $data = ['inv' => $inv, 'detail' => $detail];

        $this->template->title('Request Retur');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('form_request', $data);
    }

    // =========================================================
    // STEP 1 SAVE: Simpan request retur (status=0)
    // =========================================================
    public function save_request()
    {
        $post        = $this->input->post();
        $id_invoice  = $post['id_invoice'];
        $tipe        = $post['pengiriman'];

        // Generate nomor retur
        $Ym = date('ym');
        $prefix = ($tipe == 'Pabrik') ? "CN/P/{$Ym}" : "CN/G/{$Ym}";
        $SQL    = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE '{$prefix}/%'";
        $result = $this->db->query($SQL)->row_array();
        $urutan = 0;
        if ($result['maxM']) {
            $parts  = explode('/', $result['maxM']);
            $urutan = isset($parts[3]) ? (int)$parts[3] : 0;
        }
        $urutan++;
        $no_retur = $prefix . '/' . sprintf('%04d', $urutan);

        $ArrHeader = [
            'no_retur'       => $no_retur,
            'no_surat_jalan' => $post['id_billing'],
            'no_so'          => $post['id_so'],
            'id_invoice'     => $id_invoice,
            'id_customer'    => $post['id_customer'],
            'nm_customer'    => $post['nm_customer'],
            'alasan'         => $post['alasan'],
            'tipe'           => $tipe,
            'total_harga'    => 0,
            'tgl_retur'      => date('Y-m-d', strtotime($post['tgl_retur'])),
            'created_by'     => $this->auth->user_id(),
            'created_date'   => date('Y-m-d H:i:s'),
            'status'         => 0,   // 0 = request masuk, belum ada SJ retur
            'jenis_retur'    => 2
        ];

        // Simpan detail item (qty masih dari invoice, belum final)
        $ArrDetail = [];
        foreach ($post['detail'] as $key => $value) {
            $ArrDetail[] = [
                'no_retur'       => $no_retur,
                'no_surat_jalan' => $post['id_billing'],
                'id_so_det'      => $value['id_so_det'],
                'id_product'     => $value['id_produk'],
                'nm_product'     => $value['nm_produk'],
                'qty_retur'      => (float)$value['qty'],
                'harga'          => (float)str_replace(',', '', $value['harga_raw']),
                'harga_beli'     => (float)str_replace(',', '', $value['harga_beli']),
                'total'          => (float)str_replace(',', '', $value['total_raw']),
                'created_by'     => $this->auth->user_id(),
                'created_date'   => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->trans_begin();
        $this->db->insert('tr_retur', $ArrHeader);
        if (!empty($ArrDetail)) {
            $this->db->insert_batch('tr_retur_detail', $ArrDetail);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan request retur.']);
        } else {
            $this->db->trans_commit();
            history("Request Retur: " . $no_retur);
            echo json_encode(['status' => 1, 'pesan' => 'Request retur berhasil disimpan. No. Retur: ' . $no_retur]);
        }
    }

    // =========================================================
    // STEP 2: Form Buat Surat Jalan Retur (Gudang, dept=2)
    // =========================================================
    public function form_sjr($id_retur)
    {
        // Cek department user — hanya gudang (dept_id=2) atau admin (user_id=7)
        $user_dept = $this->_get_user_dept();
        $user_id   = $this->auth->user_id();
        if ($user_dept != 2 && $user_id != 7) {
            show_error('Akses ditolak. Hanya departemen Gudang yang dapat membuat Surat Jalan Retur.', 403);
        }

        $retur = $this->db
            ->select('r.*, i.id_billing as no_sj_asal')
            ->from('tr_retur r')
            ->join('tr_invoice_sales i', 'i.id_invoice = r.id_invoice', 'left')
            ->where('r.id', $id_retur)
            ->get()->row_array();

        if (!$retur || $retur['status'] != 0) {
            show_error('Data tidak ditemukan atau sudah diproses.', 404);
        }

        $detail = $this->db
            ->get_where('tr_retur_detail', ['no_retur' => $retur['no_retur']])
            ->result_array();

        $data = ['retur' => $retur, 'detail' => $detail];

        $this->template->title('Buat Surat Jalan Retur');
        $this->template->page_icon('fa fa-truck');
        $this->template->render('form_sjr', $data);
    }

    // =========================================================
    // STEP 2 SAVE: Simpan Surat Jalan Retur (status tr_retur → 1)
    // =========================================================
    public function save_sjr()
    {
        $user_dept = $this->_get_user_dept();
        $user_id   = $this->auth->user_id();
        if ($user_dept != 2 && $user_id != 7) {
            echo json_encode(['status' => 0, 'pesan' => 'Akses ditolak.']);
            return;
        }

        $post       = $this->input->post();
        $no_retur   = $post['no_retur'];
        $no_sj_asal = $post['no_sj_asal'];
        $no_sjr     = $no_sj_asal . 'R';
        $no_invoice = $post['no_invoice'];

        // Cek duplikat no_sjr
        $cek = $this->db->get_where('surat_jalan_retur', ['no_sjr' => $no_sjr])->num_rows();
        if ($cek > 0) {
            // Tambah suffix angka jika sudah ada
            $cek2 = $this->db->like('no_sjr', $no_sj_asal . 'R', 'after')->count_all_results('surat_jalan_retur');
            $no_sjr = $no_sj_asal . 'R' . ($cek2 + 1);
        }

        $ArrHeader = [
            'no_sjr'       => $no_sjr,
            'no_sj_asal'   => $no_sj_asal,
            'no_invoice'   => $no_invoice,
            'no_so'        => $post['no_so'],
            'id_customer'  => $post['id_customer'],
            'nm_customer'  => $post['nm_customer'],
            'tgl_sjr'      => date('Y-m-d', strtotime($post['tgl_sjr'])),
            'keterangan'   => $post['keterangan'],
            'created_by'   => $this->auth->user_id(),
            'created_date' => date('Y-m-d H:i:s'),
        ];

        $ArrDetail = [];
        $total_harga_beli = 0;
        foreach ($post['detail'] as $value) {
            $qty_retur = (float)$value['qty_retur'];
            if ($qty_retur <= 0) continue;
            $harga      = (float)str_replace(',', '', $value['harga_raw']);
            $harga_beli = (float)str_replace(',', '', $value['harga_beli']);
            $ArrDetail[] = [
                'no_sjr'       => $no_sjr,
                'id_product'   => $value['id_product'],
                'nm_product'   => $value['nm_product'],
                'qty_retur'    => $qty_retur,
                'harga'        => $harga,
                'total'        => $qty_retur * $harga,
                'id_so_det'    => $value['id_so_det'],
                'created_by'   => $this->auth->user_id(),
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $total_harga_beli += $qty_retur * $harga_beli;
        }

        if (empty($ArrDetail)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada item dengan qty retur > 0.']);
            return;
        }

        $this->db->trans_begin();
        $this->db->insert('surat_jalan_retur', $ArrHeader);
        $this->db->insert_batch('surat_jalan_retur_detail', $ArrDetail);
        // Update status tr_retur → 1 (SJ Retur sudah dibuat), simpan no_sjr
        $this->db->update('tr_retur', ['status' => 1, 'no_sjr' => $no_sjr], ['no_retur' => $no_retur]);

        // =========================================================
        // JURNAL: Saat buat Surat Jalan Retur
        // Kredit : 1104-01-01 Persediaan Barang Warehouse (qty * harga_beli)
        // Debit  : 5101-01-01 HPP                         (qty * harga_beli)
        // =========================================================
        if ($total_harga_beli > 0) {
            $this->load->model('jurnal_nomor/Jurnal_model');
            $tgl_sjr    = date('Y-m-d', strtotime($post['tgl_sjr']));
            $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_sjr);
            $keterangan = "SJ Retur {$no_sjr} asal SJ {$no_sj_asal} atas INV {$no_invoice}";

            $this->db->insert(DBACC . '.javh', [
                'nomor'         => $Nomor_JV,
                'tgl'           => $tgl_sjr,
                'jml'           => $total_harga_beli,
                'koreksi_no'    => '-',
                'kdcab'         => '101',
                'jenis'         => 'JV',
                'keterangan'    => $keterangan,
                'bulan'         => date('m', strtotime($tgl_sjr)),
                'tahun'         => date('Y', strtotime($tgl_sjr)),
                'user_id'       => $this->auth->user_id(),
                'memo'          => '',
                'tgl_jvkoreksi' => $tgl_sjr,
                'ho_valid'      => ''
            ]);

            $this->db->insert_batch(DBACC . '.jurnal', [
                [
                    'tipe'         => 'JV',
                    'nomor'        => $Nomor_JV,
                    'tanggal'      => $tgl_sjr,
                    'no_perkiraan' => '1104-01-01',
                    'keterangan'   => $keterangan,
                    'no_reff'      => $no_sjr,
                    'debet'        => 0,
                    'kredit'       => $total_harga_beli,
                    'created_by'   => $this->auth->user_id(),
                    'created_on'   => date('Y-m-d H:i:s'),
                ],
                [
                    'tipe'         => 'JV',
                    'nomor'        => $Nomor_JV,
                    'tanggal'      => $tgl_sjr,
                    'no_perkiraan' => '5101-01-01',
                    'keterangan'   => $keterangan,
                    'no_reff'      => $no_sjr,
                    'debet'        => $total_harga_beli,
                    'kredit'       => 0,
                    'created_by'   => $this->auth->user_id(),
                    'created_on'   => date('Y-m-d H:i:s'),
                ],
            ]);

            $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC+1 WHERE nocab='101'");
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan Surat Jalan Retur.']);
        } else {
            $this->db->trans_commit();
            history("Buat SJ Retur: " . $no_sjr);
            echo json_encode(['status' => 1, 'pesan' => 'Surat Jalan Retur ' . $no_sjr . ' berhasil disimpan.']);
        }
    }

    // =========================================================
    // STEP 3: Form Credit Note (Finance, dept=3)
    // Qty diambil dari SJ Retur yang sudah dibuat gudang
    // =========================================================
    public function form_cn($id_retur)
    {
        $user_dept = $this->_get_user_dept();
        $user_id   = $this->auth->user_id();
        if ($user_dept != 3 && $user_id != 7) {
            show_error('Akses ditolak. Hanya departemen Finance yang dapat membuat Credit Note.', 403);
        }

        $retur = $this->db
            ->select('r.*, i.id_billing as no_sj_asal')
            ->from('tr_retur r')
            ->join('tr_invoice_sales i', 'i.id_invoice = r.id_invoice', 'left')
            ->where('r.id', $id_retur)
            ->get()->row_array();

        if (!$retur || $retur['status'] != 1) {
            show_error('Data tidak ditemukan atau belum ada Surat Jalan Retur.', 404);
        }

        // Ambil detail dari SJ Retur (qty sudah final dari gudang)
        // Join ke tr_retur_detail untuk ambil harga_beli
        $no_retur_escaped = $this->db->escape($retur['no_retur']);
        $no_sjr_escaped   = $this->db->escape($retur['no_sjr']);
        $detail_query = $this->db->query("
            SELECT sjrd.*, trd.harga_beli
            FROM surat_jalan_retur_detail sjrd
            LEFT JOIN tr_retur_detail trd
                ON trd.no_retur = {$no_retur_escaped}
                AND trd.id_product = sjrd.id_product
            WHERE sjrd.no_sjr = {$no_sjr_escaped}
        ");
        $detail = $detail_query ? $detail_query->result_array() : [];

        // Ambil data invoice untuk hitung total sudah bayar
        $inv = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $retur['id_invoice']])->row_array();

        $bayar_query = $this->db
            ->select('COALESCE(SUM(total_bayar_idr), 0) AS total', false)
            ->from('tr_invoice_payment_detail')
            ->where('no_invoice', $retur['id_invoice'])
            ->get();
        $total_sudah_bayar = ($bayar_query && $bayar_query->row()) ? (float)$bayar_query->row()->total : 0.0;

        $grand_total_inv = (float)($inv['grand_total'] ?? 0);

        $data = [
            'retur'             => $retur,
            'inv'               => $inv,
            'detail'            => $detail,
            'total_sudah_bayar' => $total_sudah_bayar,
            'grand_total_inv'   => $grand_total_inv,
        ];

        $this->template->title('Buat Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('form_cn', $data);
    }

    // =========================================================
    // STEP 3 SAVE: Simpan Credit Note (status tr_retur → 2)
    // =========================================================
    public function save_cn()
    {
        $user_dept = $this->_get_user_dept();
        $user_id   = $this->auth->user_id();
        if ($user_dept != 3 && $user_id != 7) {
            echo json_encode(['status' => 0, 'pesan' => 'Akses ditolak.']);
            return;
        }

        $post            = $this->input->post();
        $no_retur        = $post['no_retur'];
        $id_invoice_lama = $post['id_invoice'];
        $grand_total_retur = (float)str_replace(',', '', $post['grand_total']);
        $nilai_inv_baru    = (float)str_replace(',', '', $post['nilai_inv_baru']);

        // Ambil total harga_beli dari tr_retur_detail untuk jurnal
        $retur_detail = $this->db
            ->select('qty_retur, harga_beli')
            ->from('tr_retur_detail')
            ->where('no_retur', $no_retur)
            ->get()->result_array();

        $total_retur_penjualan = 0;
        foreach ($retur_detail as $rd) {
            $total_retur_penjualan += (float)$rd['qty_retur'] * (float)$rd['harga_beli'];
        }

        $inv_lama = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice_lama])->row();
        if (!$inv_lama) {
            echo json_encode(['status' => 0, 'pesan' => 'Invoice tidak ditemukan.']);
            return;
        }

        $total_sudah_bayar = (float)$this->db
            ->select('COALESCE(SUM(total_bayar_idr), 0) AS total', false)
            ->from('tr_invoice_payment_detail')
            ->where('no_invoice', $id_invoice_lama)
            ->get()->row()->total;

        $piutang_baru = max(0, $nilai_inv_baru - $total_sudah_bayar);
        $sts_baru     = ($piutang_baru <= 0) ? 0 : 1;
        $flag_cancel  = ($nilai_inv_baru <= 0) ? 1 : 2;

        $this->db->trans_begin();

        try {
            // Update tr_retur: simpan total_harga dan status=2
            $this->db->update('tr_retur', [
                'total_harga'    => $grand_total_retur,
                'nilai_inv_baru' => $nilai_inv_baru,
                'tgl_retur'      => date('Y-m-d', strtotime($post['tgl_retur'])),
                'status'         => 2,
            ], ['no_retur' => $no_retur]);

            // Update invoice
            $this->db->update('tr_invoice_sales', [
                'is_cancel'  => $flag_cancel,
                'grand_total' => $nilai_inv_baru,
                'piutang'    => $piutang_baru,
                'sts'        => $sts_baru,
                'updated_by' => $this->auth->user_id(),
                'updated_on' => date('Y-m-d H:i:s'),
            ], ['id_invoice' => $id_invoice_lama]);

            // Jurnal koreksi
            $this->_buat_jurnal_credit_note(
                $no_retur,
                $post['tgl_retur'],
                $id_invoice_lama,
                $inv_lama->id_customer,
                $inv_lama->nm_customer,
                $grand_total_retur,
                $total_retur_penjualan
            );

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('DB Error.');
            }

            $this->db->trans_commit();
            history("Credit Note: " . $no_retur);

            $pesan = 'Credit Note berhasil disimpan.';
            if ($nilai_inv_baru > 0) {
                $pesan .= ' Nilai invoice baru: Rp ' . number_format($nilai_inv_baru, 0, ',', '.');
            } else {
                $pesan .= ' Invoice telah di-cancel penuh.';
            }
            echo json_encode(['status' => 1, 'pesan' => $pesan]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    // =========================================================
    // VIEW detail retur
    // =========================================================
    public function view($id)
    {
        $inv = $this->db->query("SELECT * FROM tr_retur WHERE id = ?", [$id])->row_array();
        $detail = $this->db->query("SELECT * FROM tr_retur_detail WHERE no_retur = ?", [$inv['no_retur']])->result_array();

        // Ambil detail SJ Retur jika sudah ada
        $detail_sjr = [];
        if (!empty($inv['no_sjr'])) {
            $detail_sjr = $this->db->get_where('surat_jalan_retur_detail', ['no_sjr' => $inv['no_sjr']])->result_array();
        }

        $data = ['inv' => $inv, 'detail' => $detail, 'detail_sjr' => $detail_sjr];

        $this->template->title('View Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('view', $data);
    }

    // =========================================================
    // AJAX: History CN untuk penerimaan
    // =========================================================
    public function get_cn_history()
    {
        $id_invoice = $this->input->get('id_invoice', TRUE);
        if (!$id_invoice) {
            echo json_encode([]);
            return;
        }

        $data = $this->db
            ->select('r.no_retur, r.tgl_retur, r.total_harga as nilai_retur, r.nilai_inv_baru, r.nm_customer, r.alasan, r.status')
            ->from('tr_retur r')
            ->where('r.id_invoice', $id_invoice)
            ->order_by('r.tgl_retur', 'ASC')
            ->get()->result_array();

        // Ambil nilai invoice asal dari kolom nilai_asli
        $inv = $this->db
            ->select('nilai_asli')
            ->from('tr_invoice_sales')
            ->where('id_invoice', $id_invoice)
            ->get()->row_array();

        echo json_encode([
            'nilai_inv_asal' => (float)($inv['nilai_asli'] ?? 0),
            'rows'           => $data,
        ]);
    }

    // =========================================================
    // PRIVATE: Jurnal koreksi credit note
    // Debit  : 1102-01-01 Piutang Dagang     = grand_total_retur (qty * harga include PPN)
    // Kredit : 4102-01-01 Retur Penjualan    = total_retur_penjualan (qty * harga_beli)
    // Kredit : 2103-01-01 PPN Keluaran       = grand_total_retur - total_retur_penjualan
    // =========================================================
    private function _buat_jurnal_credit_note($no_retur, $tgl_retur, $id_invoice, $id_customer, $nm_customer, $nilai_retur, $total_retur_penjualan = 0)
    {
        $this->load->model('jurnal_nomor/Jurnal_model');
        $tgl        = date('Y-m-d', strtotime($tgl_retur));
        $Nomor_JV   = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl);
        $keterangan = "Credit Note {$no_retur} atas Invoice {$id_invoice} A/n {$nm_customer}";

        // Hitung komponen jurnal
        $nilai_piutang      = $nilai_retur;                                  // Debit Piutang Dagang
        $nilai_retur_penj   = $total_retur_penjualan;                        // Kredit Retur Penjualan (harga_beli * qty)
        $nilai_ppn_keluaran = $nilai_piutang - $nilai_retur_penj;            // Kredit PPN Keluaran (selisih)

        $this->db->insert(DBACC . '.javh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl,
            'jml'           => $nilai_piutang,
            'koreksi_no'    => '-',
            'kdcab'         => '101',
            'jenis'         => 'JV',
            'keterangan'    => $keterangan,
            'bulan'         => date('m', strtotime($tgl)),
            'tahun'         => date('Y', strtotime($tgl)),
            'user_id'       => $this->auth->user_id(),
            'memo'          => '',
            'tgl_jvkoreksi' => $tgl,
            'ho_valid'      => ''
        ]);

        $this->db->insert_batch(DBACC . '.jurnal', [
            // Debit: Piutang Dagang
            [
                'tipe'         => 'JV',
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl,
                'no_perkiraan' => '1102-01-01',
                'keterangan'   => $keterangan,
                'no_reff'      => $no_retur,
                'debet'        => $nilai_piutang,
                'kredit'       => 0,
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ],
            // Kredit: Retur Penjualan
            [
                'tipe'         => 'JV',
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl,
                'no_perkiraan' => '4102-01-01',
                'keterangan'   => $keterangan,
                'no_reff'      => $no_retur,
                'debet'        => 0,
                'kredit'       => $nilai_retur_penj,
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ],
            // Kredit: PPN Keluaran
            [
                'tipe'         => 'JV',
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl,
                'no_perkiraan' => '2103-01-01',
                'keterangan'   => $keterangan,
                'no_reff'      => $no_retur,
                'debet'        => 0,
                'kredit'       => $nilai_ppn_keluaran,
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ],
        ]);

        $this->db->insert('tr_kartu_piutang', [
            'tipe'          => 'JV',
            'nomor'         => $Nomor_JV,
            'tanggal'       => $tgl,
            'no_perkiraan'  => '1102-01-01',
            'keterangan'    => $keterangan,
            'no_reff'       => $id_invoice,
            'debet'         => 0,
            'kredit'        => $nilai_piutang,
            'id_supplier'   => $id_customer,
            'nama_supplier' => $nm_customer,
        ]);

        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC+1 WHERE nocab='101'");
    }

    // =========================================================
    // PRIVATE: Ambil department_id user yang login
    // =========================================================
    private function _get_user_dept()
    {
        $user_id = $this->auth->user_id();
        $row = $this->db
            ->select('e.department')
            ->from('users u')
            ->join('employee e', 'e.id = u.employee_id', 'left')
            ->where('u.id_user', $user_id)
            ->get()->row();
        return $row ? (int)$row->department : 0;
    }
}
