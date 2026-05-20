<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_credit_note extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Retur_credit_note.View';
    protected $addPermission    = 'Retur_credit_note.Add';
    protected $managePermission = 'Retur_credit_note.Manage';
    protected $deletePermission = 'Retur_credit_note.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Retur_credit_note/Retur_credit_note_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

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

    public function add($id)
    {
        $sql = "
                SELECT 
                    i.id_invoice, 
                    i.id_billing, 
                    i.id_so, 
                    sj.pengiriman, 
                    i.id_customer, 
                    i.nm_customer
                FROM tr_invoice_sales i
                JOIN surat_jalan sj ON i.id_billing = sj.no_surat_jalan
                WHERE i.id_invoice = ?
                ORDER BY i.id_invoice DESC
            ";
        $inv = $this->db->query($sql, [$id])->row_array();

        $sql2 = "
            SELECT 
                dt.id_so, 
                sjd.id_so_det, 
                dt.id_penawaran, 
                dt.id_delivery, 
                dt.id_produk, 
                dt.nm_produk, 
                round(dt.qty) as qty, 
                dt.harga, 
                round(dt.qty * dt.harga) as total
            FROM tr_invoice_sales_detail dt
            JOIN surat_jalan_detail sjd 
                ON dt.id_delivery   = sjd.no_surat_jalan
                AND dt.id_produk    = sjd.id_product
            WHERE dt.id_invoice = ?
            ORDER BY dt.id_invoice;
        ";
        $detail = $this->db->query($sql2, [$id])->result_array();

        $data = [
            'inv' => $inv,
            'detail' => $detail,
        ];

        $this->template->title('Request Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post = $this->input->post();

        $detail          = $post['detail'];
        $tipe            = $post['pengiriman'];
        $id_invoice_lama = $post['id_invoice'];
        $no_surat_jalan  = $post['id_billing'];
        $grand_total_retur = (float) str_replace(',', '', $post['grand_total']);
        $nilai_inv_baru    = (float) str_replace(',', '', $post['nilai_inv_baru']);

        // =============================================
        // GENERATE NOMOR RETUR
        // =============================================
        $Ym = date('ym');
        if ($tipe == 'Pabrik') {
            $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'CN/P/{$Ym}/%'";
        } else {
            $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'CN/G/{$Ym}/%'";
        }
        $result    = $this->db->query($SQL)->result_array();
        $angkaUrut = $result[0]['maxM'];

        if ($angkaUrut) {
            $parts   = explode('/', $angkaUrut);
            $urutan  = isset($parts[3]) ? (int)$parts[3] : 0;
        } else {
            $urutan = 0;
        }
        $urutan++;
        $formatUrut = sprintf('%04d', $urutan);
        $no_retur   = ($tipe == 'Pabrik') ? "CN/P/{$Ym}/{$formatUrut}" : "CN/G/{$Ym}/{$formatUrut}";

        // =============================================
        // AMBIL DATA INVOICE LAMA
        // =============================================
        $inv_lama = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $id_invoice_lama])->row();
        if (!$inv_lama) {
            echo json_encode(['status' => 0, 'pesan' => 'Invoice tidak ditemukan.']);
            return;
        }

        // Cek total yang sudah dibayar
        $total_sudah_bayar = (float) $this->db
            ->select('COALESCE(SUM(total_bayar_idr), 0) AS total', false)
            ->from('tr_invoice_payment_detail')
            ->where('no_invoice', $id_invoice_lama)
            ->get()->row()->total;

        // =============================================
        // SIAPKAN DETAIL RETUR (hanya item qty_retur > 0)
        // =============================================
        $ArrDetail = [];
        foreach ($detail as $key => $value) {
            $qty_retur = (float) $value['qty_retur'];
            if ($qty_retur <= 0) continue;

            $harga_raw = (float) str_replace(',', '', $value['harga_raw']);
            $total_raw = $qty_retur * $harga_raw;

            $ArrDetail[] = [
                'no_retur'       => $no_retur,
                'no_surat_jalan' => $no_surat_jalan,
                'id_so_det'      => $value['id_so_det'],
                'id_product'     => $value['id_produk'],
                'nm_product'     => $value['nm_produk'],
                'qty_retur'      => $qty_retur,
                'alasan'         => $value['alasan_retur'],
                'harga'          => $harga_raw,
                'total'          => $total_raw,
                'created_by'     => $this->auth->user_id(),
                'created_date'   => date('Y-m-d H:i:s'),
            ];
        }

        if (empty($ArrDetail)) {
            echo json_encode(['status' => 0, 'pesan' => 'Tidak ada item yang diretur.']);
            return;
        }

        // =============================================
        // HITUNG PIUTANG BARU
        // Piutang baru = nilai_inv_baru - total_sudah_bayar
        // Jika sudah bayar lebih dari nilai baru, piutang = 0
        // =============================================
        $piutang_baru = max(0, $nilai_inv_baru - $total_sudah_bayar);
        $sts_baru     = ($piutang_baru <= 0) ? 0 : 1;

        // =============================================
        // TRANSAKSI DB
        // =============================================
        $this->db->trans_begin();

        try {
            // 1. Simpan header retur
            $ArrHeader = [
                'no_retur'       => $no_retur,
                'no_surat_jalan' => $no_surat_jalan,
                'no_so'          => $post['id_so'],
                'id_invoice'     => $id_invoice_lama,
                'id_customer'    => $post['id_customer'],
                'nm_customer'    => $post['nm_customer'],
                'alasan'         => $post['alasan'],
                'tipe'           => $tipe,
                'total_harga'    => $grand_total_retur,
                'nilai_inv_baru' => $nilai_inv_baru,
                'tgl_retur'      => date('Y-m-d', strtotime($post['tgl_retur'])),
                'created_by'     => $this->auth->user_id(),
                'created_date'   => date('Y-m-d H:i:s'),
                'status'         => 1,
                'jenis_retur'    => 2
            ];
            $this->db->insert('tr_retur', $ArrHeader);

            // 2. Simpan detail retur
            $this->db->insert_batch('tr_retur_detail', $ArrDetail);

            // 3. Update invoice lama: is_cancel=1, grand_total & piutang disesuaikan
            $this->db->update('tr_invoice_sales', [
                'is_cancel'   => 1,
                'grand_total' => $nilai_inv_baru,
                'piutang'     => $piutang_baru,
                'sts'         => $sts_baru,
                'updated_by'  => $this->auth->user_id(),
                'updated_on'  => date('Y-m-d H:i:s'),
            ], ['id_invoice' => $id_invoice_lama]);

            // 4. Jurnal koreksi (membalik sebagian nilai piutang yang di-credit note)
            $this->_buat_jurnal_credit_note(
                $no_retur,
                $post['tgl_retur'],
                $id_invoice_lama,
                $post['id_customer'],
                $post['nm_customer'],
                $grand_total_retur
            );

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('DB Error saat menyimpan.');
            }

            $this->db->trans_commit();

            history("Create Credit Note : " . $no_retur);

            $pesan = 'Credit note berhasil disimpan.';
            if ($nilai_inv_baru > 0) {
                $pesan .= ' Nilai invoice diperbarui menjadi Rp ' . number_format($nilai_inv_baru, 0, ',', '.');
            } else {
                $pesan .= ' Invoice telah di-cancel penuh.';
            }

            echo json_encode(['status' => 1, 'pesan' => $pesan]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    /**
     * Buat jurnal koreksi untuk credit note.
     * Membalik piutang sebesar nilai retur: Kredit Piutang Dagang, Debit Retur Penjualan.
     */
    private function _buat_jurnal_credit_note($no_retur, $tgl_retur, $id_invoice, $id_customer, $nm_customer, $nilai_retur)
    {
        $this->load->model('jurnal_nomor/Jurnal_model');

        $tgl = date('Y-m-d', strtotime($tgl_retur));
        $Nomor_JV = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl);

        $keterangan = "Credit Note {$no_retur} atas Invoice {$id_invoice} A/n {$nm_customer}";

        // Header jurnal
        $this->db->insert(DBACC . '.javh', [
            'nomor'         => $Nomor_JV,
            'tgl'           => $tgl,
            'jml'           => $nilai_retur,
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

        // Detail jurnal:
        // Debit  : Retur Penjualan (4xxx atau sesuai COA retur penjualan)
        // Kredit : Piutang Dagang
        $det_jurnal = [
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl,
                'tipe'         => 'JV',
                'no_perkiraan' => '4101-01-02', // Retur Penjualan — sesuaikan dengan COA perusahaan
                'keterangan'   => $keterangan,
                'no_reff'      => $no_retur,
                'debet'        => $nilai_retur,
                'kredit'       => 0,
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ],
            [
                'nomor'        => $Nomor_JV,
                'tanggal'      => $tgl,
                'tipe'         => 'JV',
                'no_perkiraan' => '1102-01-01', // Piutang Dagang
                'keterangan'   => $keterangan,
                'no_reff'      => $no_retur,
                'debet'        => 0,
                'kredit'       => $nilai_retur,
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ],
        ];
        $this->db->insert_batch(DBACC . '.jurnal', $det_jurnal);

        // Kartu piutang: kurangi piutang customer
        $this->db->insert('tr_kartu_piutang', [
            'tipe'          => 'JV',
            'nomor'         => $Nomor_JV,
            'tanggal'       => $tgl,
            'no_perkiraan'  => '1102-01-01',
            'keterangan'    => $keterangan,
            'no_reff'       => $id_invoice,
            'debet'         => 0,
            'kredit'        => $nilai_retur,
            'id_supplier'   => $id_customer,
            'nama_supplier' => $nm_customer,
        ]);

        // Update counter nomor jurnal
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC+1 WHERE nocab='101'");
    }

    public function req_spk($id_retur = null)
    {
        if (!$id_retur) {
            show_404();
        }

        $retur = $this->db
            ->select('r.id as id_retur, r.no_retur, r.no_so, r.id_customer, r.nm_customer, r.tipe, c.address_office')
            ->from('tr_retur r')
            ->join('master_customers c', 'c.id_customer = r.id_customer', 'left')
            ->where('r.id', $id_retur)
            ->get()
            ->row_array();

        if (!$retur) {
            show_error("Data Retur dengan nomor {$retur['no_retur']} tidak ditemukan.", 404);
        }

        $detail = $this->db
            ->select('rd.*')
            ->from('tr_retur_detail rd')
            ->where('rd.no_retur', $retur['no_retur'])
            ->get()
            ->result_array();

        $data = [
            'retur'     => $retur,
            'detail'    => $detail
        ];

        $this->template->page_icon('fa fa-truck');
        $this->template->title("Request SPK Delivery Retur {$retur['no_retur']}");
        $this->template->render('req_spk', $data);
    }

    public function save_spk()
    {
        $data = $this->input->post();

        $no_retur         = $data['no_retur'];
        $id_customer      = $data['id_customer'];
        $no_so            = $data['no_so'];
        $tanggal_spk      = !empty($data['tanggal_spk']) ? date('Y-m-d', strtotime($data['tanggal_spk'])) : NULL;
        $tanggal_kirim    = !empty($data['tanggal_kirim']) ? date('Y-m-d', strtotime($data['tanggal_kirim'])) : NULL;
        $delivery_address = $data['delivery_address'];
        $notes            = $data['notes'];
        $pengiriman       = $data['tipe'];
        $detail           = $data['detail'];

        // Generate nomor SPK baru
        $Ym             = date('ym');
        $SQL            = "SELECT MAX(no_delivery) as maxP FROM spk_delivery WHERE no_delivery LIKE 'SPK" . $Ym . "%'";
        $result         = $this->db->query($SQL)->row_array();
        $angkaUrut      = isset($result['maxP']) ? $result['maxP'] : null;
        $lastNum        = ($angkaUrut) ? (int)substr($angkaUrut, 7, 4) : 0;
        $no_delivery    = 'SPK' . $Ym . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        // Header insert
        $ArrHeader = [
            'no_delivery'      => $no_delivery,
            'id_customer'      => $id_customer,
            'no_so'            => $no_so,
            'tanggal_spk'      => $tanggal_spk,
            'tanggal_kirim'    => $tanggal_kirim,
            'delivery_address' => $delivery_address,
            'pengiriman'       => $pengiriman,
            'created_by'       => $this->auth->user_id(),
            'created_date'     => date('Y-m-d H:i:s'),
            'notes'            => $notes,
        ];

        $ArrDetail = [];

        $this->db->trans_start();

        foreach ($detail as $key => $value) {
            $id_so_det      = $value['id_so_det'];
            $id_product     = $value['id_product'];
            $qty_spk        = (float)str_replace(',', '', $value['qty_spk']);
            $qty_retur      = (float)str_replace(',', '', $value['qty_retur']);

            $ArrDetail[] = [
                'no_delivery'     => $no_delivery,
                'no_so'           => $no_so,
                'id_so_det'       => $id_so_det,
                'id_product'      => $id_product,
                'qty_so'          => $qty_retur,
                'qty_spk'         => $qty_spk,
                'qty_belum_muat'  => $qty_spk
            ];
        }

        // Insert detail SPK
        if (!empty($ArrDetail)) {
            $this->db->insert_batch('spk_delivery_detail', $ArrDetail);
        }

        $this->db->insert('spk_delivery', $ArrHeader);
        $this->db->update('tr_retur', ['status' => 2], ['no_retur' => $no_retur]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'pesan'  => 'Save gagal disimpan ...',
                'status' => 0
            ]);
        } else {
            $this->db->trans_commit();
            history("Create SPK Delivery: " . $no_delivery);
            echo json_encode([
                'pesan'  => 'Save berhasil disimpan. Thanks ...',
                'status' => 1
            ]);
        }
    }

    /**
     * Endpoint AJAX: ambil history credit note untuk satu invoice.
     * Dipanggil dari form penerimaan cash/bank.
     * GET: ?id_invoice=INV-OM-xx-xx-xxx
     */
    public function get_cn_history()
    {
        $id_invoice = $this->input->get('id_invoice', TRUE);
        if (!$id_invoice) {
            echo json_encode([]);
            return;
        }

        $data = $this->db
            ->select('r.no_retur, r.tgl_retur, r.total_harga as nilai_retur, r.nilai_inv_baru, r.nm_customer, r.alasan')
            ->from('tr_retur r')
            ->where('r.id_invoice', $id_invoice)
            ->order_by('r.tgl_retur', 'ASC')
            ->get()->result();

        echo json_encode($data);
    }

    public function view($id)
    {
        $sql = "
                SELECT *
                FROM tr_retur r
                WHERE r.id = ?
                ORDER BY r.id DESC
            ";
        $inv = $this->db->query($sql, [$id])->row_array();

        $sql2 = "
            SELECT *
            FROM tr_retur_detail dt
            WHERE dt.no_retur = ?
            ORDER BY dt.no_retur;
        ";
        $detail = $this->db->query($sql2, [$inv['no_retur']])->result_array();

        $data = [
            'inv' => $inv,
            'detail' => $detail,
        ];

        $this->template->title('View Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('view', $data);
    }
}
