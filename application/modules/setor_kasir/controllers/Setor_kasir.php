<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setor_kasir extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Setor_Kasir.View';
    protected $addPermission    = 'Setor_Kasir.Add';
    protected $managePermission = 'Setor_Kasir.Manage';
    protected $deletePermission = 'Setor_Kasir.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Setor_kasir/setor_kasir_model',
            'Setor_bank/setor_bank_model',
            'Penerimaan_cash/All_model',
            'Penerimaan_cash/Jurnal_model',
            'Penerimaan_cash/Acc_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Setor Kas Keuangan');

        $this->template->render('index');
    }

    public function data_side_setoran_kasir()
    {
        $this->setor_kasir_model->get_json_setoran_kasir();
    }

    public function create()
    {
        $this->template->page_icon('fa fa-sign-in');
        $this->template->title('Input Setoran');

        $sales = $this->db
            ->where('department_id', 2)
            ->get('users')
            ->result();

        $data = [
            'sales' => $sales,
        ];

        $this->template->render('form', $data);
    }

    public function save()
    {
        try {

            $post = $this->input->post();

            $tgl_setor        = $post['tgl_setor'];
            $id_sales         = $post['id_sales'];
            $sales            = $post['nama'];

            $nilai_setor      = str_replace(",", "", $post['nilai_setor']);
            $total_penerimaan = str_replace(",", "", $post['total_penerimaan']);
            $sisa_piutang     = str_replace(",", "", $post['sisa_piutang_sesudah']);

            if (empty($post['detail'])) {
                echo json_encode(['status' => false, 'message' => 'Data penerimaan kosong']);
                return;
            }

            $id_setoran = $this->setor_kasir_model->generateKodeSetoran($tgl_setor);

            $header = [
                'id'                => $id_setoran,
                'tgl_setor'         => $tgl_setor,
                'id_sales'          => $id_sales,
                'sales'             => $sales,
                'total_penerimaan'  => $total_penerimaan,
                'total_setoran'     => $nilai_setor,
                'sisa_piutang'      => $sisa_piutang,
                'created_by'        => $this->auth->user_id(),
                'created_at'        => date('Y-m-d H:i:s')
            ];

            $this->db->trans_begin();

            // =========================
            // HEADER
            // =========================
            $this->db->insert('tr_setor_kasir', $header);

            // =========================
            // DETAIL
            // =========================
            foreach ($post['detail'] as $item) {

                $this->db->insert('tr_setor_kasir_detail', [
                    'id_setor_kasir'    => $id_setoran,
                    'kd_pembayaran'     => $item['kd_pembayaran'],
                    'id_customer'       => $item['id_customer'],
                    'name_customer'     => $item['name_customer'],
                    'no_invoice'        => $item['no_invoice'],
                    'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                    'total_penerimaan'  => str_replace(",", "", $item['total_invoiced']),
                ]);

                // update flag setor
                $this->db->where('kd_pembayaran', $item['kd_pembayaran'])
                    ->update('tr_invoice_payment', ['status_setor' => 1]);
            }

            // =========================
            // JURNAL (PAKAI POST 🔥)
            // =========================
            $this->appr_jurnal($id_setoran, $post);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("Gagal simpan");
            }

            $this->db->trans_commit();

            echo json_encode(['status' => true, 'message' => 'Berhasil disimpan']);
        } catch (Throwable $e) {

            $this->db->trans_rollback();

            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    function appr_jurnal($kd_bayar, $post)
    {
        $session = $this->session->userdata('app_session');

        $tgl = $post['tgl_setor'];

        $Nomor_BUM = $this->Jurnal_model
            ->get_Nomor_Jurnal_BUM('101', $tgl);

        // =========================
        // VALIDASI BALANCE 🔥
        // =========================
        if (array_sum($post['debet']) != array_sum($post['kredit'])) {
            throw new Exception("Jurnal tidak balance");
        }

        // =========================
        // HEADER
        // =========================
        $dataJARH = [
            'nomor'         => $Nomor_BUM,
            'kd_pembayaran' => $kd_bayar,
            'tgl'           => $tgl,
            'jml'           => array_sum($post['debet']),
            'kdcab'         => '101',
            'jenis_reff'    => $kd_bayar,
            'no_reff'       => $kd_bayar,
            'customer'      => $post['nama'],
            'note'          => 'SETORAN SALES NO. ' . $kd_bayar . ' KE KAS PENJUALAN CIREBON',
            'jenis_ar'      => 'V',
            'terima_dari'   => '-',
            'valid'         => $session['id_user'],
            'tgl_valid'     => $tgl,
            'user_id'       => $session['id_user'],
            'tgl_invoice'   => $tgl,
            'batal'         => 0
        ];

        $this->db->insert(DBACC . '.jarh', $dataJARH);

        // =========================
        // DETAIL JURNAL 🔥 (DARI POST)
        // =========================
        $arrJurnal = [];

        for ($i = 0; $i < count($post['no_coa']); $i++) {

            $arrJurnal[] = [
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $post['tgl_jurnal'][$i],
                'tipe'          => $post['type'][$i],
                'no_perkiraan'  => $post['no_coa'][$i],
                'keterangan'    => $post['keterangan'][$i],
                'no_reff'       => $kd_bayar,
                'debet'         => floatval($post['debet'][$i]),
                'kredit'        => floatval($post['kredit'][$i]),
                'created_by'    => $this->auth->user_id(),
                'created_on'    => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->insert_batch(DBACC . '.jurnal', $arrJurnal);

        // =========================
        // KARTU PIUTANG 🔥 (FILTER KREDIT AJA)
        // =========================
        for ($i = 0; $i < count($post['no_coa']); $i++) {

            if ($post['kredit'][$i] > 0) {

                $this->db->insert('tr_kartu_piutang', [
                    'tipe'          => 'BUM',
                    'nomor'         => $Nomor_BUM,
                    'tanggal'       => $tgl,
                    'no_perkiraan'  => $post['no_coa'][$i],
                    'keterangan'    => $post['keterangan'][$i],
                    'no_reff'       => $kd_bayar,
                    'debet'         => 0,
                    'kredit'        => $post['kredit'][$i],
                    'id_supplier'   => $post['id_sales'],
                    'nama_supplier' => $post['nama'],
                ]);
            }
        }

        // =========================
        // COUNTER
        // =========================
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang 
        SET nobum = nobum + 1 WHERE nocab='101'");
    }

    public function add_from_kasir()
    {
        $ids = $this->input->get('ids');
        $ids_array = explode(',', $ids);

        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $data['bank']  = $this->db->get()->result();

        $data['setor_kasir'] = $this->db
            ->where_in('id', $ids_array)
            ->get('tr_setor_kasir')
            ->result();

        // Ambil detailnya
        $details = $this->db
            ->where_in('id_setor_kasir', $ids_array)
            ->get('tr_setor_kasir_detail')
            ->result();

        $grouped_details = [];
        foreach ($details as $row) {
            $grouped_details[$row->id_setor_kasir][] = $row;
        }

        $data['detail_kasir'] = $grouped_details;

        $this->template->page_icon('fa fa-bank');
        $this->template->title('Setor Bank dari Kasir');
        $this->template->render('form_kasir', $data);
    }

    public function view($id)
    {
        // 1. Ambil data Header Transaksi (yang sudah disimpan sebelumnya)
        // Misal di tabel tr_setor_bank atau tr_setor_kasir (tergantung nama tabel Anda)
        // Di sini saya asumsikan Anda ingin melihat detail dari 'id' yang dilempar
        $header = $this->db->get_where('tr_setor_kasir', ['id' => $id])->row();

        if (!$header) {
            show_404();
        }

        // 2. Ambil List Bank untuk label
        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $data['bank'] = $this->db->get()->result();

        // 3. Ambil data transaksi kasir yang terkait (Header-nya)
        // Jika id_setor_kasir disimpan dalam bentuk string dipisah koma atau relasi lain:
        // (Asumsi: Anda menyimpan list ID kasir di kolom tertentu, atau ini adalah view untuk list ID yang dikirim)
        // Jika ini adalah view hasil gabungan beberapa ID kasir:
        $ids_array = explode(',', $header->id_setor_kasir_list ?? $id);

        $data['setor_kasir'] = $this->db
            ->where_in('id', $ids_array)
            ->get('tr_setor_kasir')
            ->result();

        // 4. Ambil Detail Kasir
        $details = $this->db
            ->where_in('id_setor_kasir', $ids_array)
            ->get('tr_setor_kasir_detail')
            ->result();

        $grouped_details = [];
        foreach ($details as $row) {
            $grouped_details[$row->id_setor_kasir][] = $row;
        }

        $data['header'] = $header;
        $data['detail_kasir'] = $grouped_details;

        $this->template->page_icon('fa fa-eye');
        $this->template->title('View Setor Bank dari Kasir');
        $this->template->render('view', $data);
    }

    public function save_bank()
    {
        $post = $this->input->post();

        $tgl_setor        = $post['tgl_setor'];
        $bank             = $post['bank'];
        $norek            = $post['norek'];
        $nilai_setor      = str_replace(",", "", $post['nilai_setor']);
        $total_penerimaan = str_replace(",", "", $post['total_penerimaan']);
        $sisa_piutang     = str_replace(",", "", ($post['sisa_piutang_sesudah'] ?? 0));

        if (empty($post['detail'])) {
            echo json_encode(['status' => false, 'message' => 'Data penerimaan tidak boleh kosong.']);
            return;
        }

        $id_setoran = $this->setor_bank_model->generateKodeSetoran($tgl_setor);

        $header = [
            'id'                => $id_setoran,
            'tgl_setor'         => $tgl_setor,
            'bank_id'           => $bank,
            'norek'             => $norek,
            'total_penerimaan'  => $total_penerimaan,
            'total_setoran'     => $nilai_setor,
            'sisa_piutang'      => $sisa_piutang,
            'created_by'        => $this->auth->user_id(),
            'created_at'        => date('Y-m-d H:i:s'),
            'tipe_setor'        => 'KASIR',
        ];

        $this->db->trans_begin();

        // ================= HEADER =================
        $this->db->insert('tr_setor_bank', $header);

        // ================= DETAIL =================
        foreach ($post['detail'] as $item) {

            $nominal = str_replace(",", "", $item['total_invoiced']);

            $detail = [
                'id_setor_bank'     => $id_setoran,
                'kd_pembayaran'     => $item['kd_pembayaran'],
                'id_setor_kasir'    => $item['id_setor_kasir'],
                'id_sales'          => $item['id_sales'],
                'sales'             => $item['sales'],
                'tgl_setor_kasir'   => $item['tgl_setor_kasir'],
                'id_customer'       => $item['id_customer'],
                'name_customer'     => $item['name_customer'],
                'no_invoice'        => $item['no_invoice'],
                'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                'total_penerimaan'  => $nominal,
            ];

            $this->db->insert('tr_setor_bank_detail', $detail);

            // update status invoice payment
            $this->db->where('kd_pembayaran', $item['kd_pembayaran'])
                ->update('tr_invoice_payment', ['status_setor' => 1]);

            // update status kasir
            $this->db->where('id', $item['id_setor_kasir'])
                ->update('tr_setor_kasir', ['status' => 1]);
        }

        // ================= JURNAL =================
        $this->appr_jurnal_bank($id_setoran, $post);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan data setoran.']);
        } else {
            $this->db->trans_commit();
            echo json_encode([
                'status' => true,
                'message' => 'Data setoran berhasil disimpan.',
                'id_setoran' => $id_setoran
            ]);
        }
    }

    function appr_jurnal_bank($kd_bayar, $post)
    {
        $session = $this->session->userdata('app_session');

        $data = $this->db->get_where('tr_setor_bank', ['id' => $kd_bayar])->row();

        $Nomor_BUM = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $data->tgl_setor);

        $keterangan_header = 'SETOR BANK DARI KASIR NO. ' . $kd_bayar;

        // ================= HEADER =================
        $dataJARH = [
            'nomor'         => $Nomor_BUM,
            'kd_pembayaran' => $kd_bayar,
            'tgl'           => $data->tgl_setor,
            'jml'           => $data->total_setoran,
            'kdcab'         => '101',
            'jenis_reff'    => $kd_bayar,
            'no_reff'       => $kd_bayar,
            'customer'      => $session['nm_lengkap'],
            'note'          => $keterangan_header,
            'jenis_ar'      => 'V',
            'terima_dari'   => '-',
            'valid'         => $session['id_user'],
            'tgl_valid'     => $data->tgl_setor,
            'user_id'       => $session['id_user'],
            'tgl_invoice'   => $data->tgl_setor,
            'batal'         => 0
        ];

        $this->db->insert(DBACC . '.jarh', $dataJARH);

        // ================= DETAIL JURNAL =================
        $det = [];

        foreach ($post['no_coa'] as $i => $coa) {

            $det[] = [
                'nomor'        => $Nomor_BUM,
                'tanggal'      => $post['tgl_jurnal'][$i],
                'tipe'         => $post['type'][$i],
                'no_perkiraan' => $coa,
                'keterangan'   => $post['keterangan'][$i],
                'no_reff'      => $kd_bayar,
                'debet'        => str_replace(",", "", $post['debet'][$i]),
                'kredit'       => str_replace(",", "", $post['kredit'][$i]),
                'created_by'   => $this->auth->user_id(),
                'created_on'   => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->insert_batch(DBACC . '.jurnal', $det);

        // ================= KARTU PIUTANG =================
        foreach ($post['detail'] as $item) {

            $this->db->insert('tr_kartu_piutang', [
                'tipe'           => 'BUM',
                'nomor'          => $Nomor_BUM,
                'tanggal'        => $data->tgl_setor,
                'no_perkiraan'   => '1102-01-04',
                'keterangan'     => 'SETOR BANK DARI KASIR NO. - ' . $kd_bayar . ' ' . $item['kd_pembayaran'],
                'no_reff'        => $item['kd_pembayaran'],
                'debet'          => 0,
                'kredit'         => str_replace(",", "", $item['total_invoiced']),
                'id_supplier'    => $session['id_user'],
                'nama_supplier'  => $session['nm_lengkap'],
            ]);
        }

        // update nomor jurnal
        $this->db->query("UPDATE " . DBACC . ".pastibisa_tb_cabang 
                      SET nobum=nobum+1 WHERE nocab='101'");
    }

    // fungsi get untuk ajax
    public function get_penerimaan()
    {
        $user_id = $this->input->post('user_id'); // ambil dari POST

        if (!$user_id) {
            echo json_encode([]);
            return;
        }

        $data = $this->db
            ->select('
            a.kd_pembayaran,
            a.created_on,
            a.created_by,
            a.id_customer,
            m.name_customer,
            c.invoiced,
            c.totalinvoiced,
            c.total_invoice
        ')
            ->from('tr_invoice_payment a')
            ->join("
            (
                SELECT 
                    kd_pembayaran, 
                    GROUP_CONCAT(no_invoice SEPARATOR ',') AS invoiced, 
                    SUM(total_bayar_idr) AS totalinvoiced, 
                    SUM(total_invoice_idr) AS total_invoice 
                FROM tr_invoice_payment_detail 
                GROUP BY kd_pembayaran
            ) c", 'a.kd_pembayaran = c.kd_pembayaran', 'left')
            ->join('master_customers m', 'm.id_customer = a.id_customer', 'left')
            ->where('a.tipe_bayar', 'CASH')
            ->where('a.status_setor', 0)
            ->where('a.created_by', $user_id)
            ->order_by('a.created_on', 'DESC')
            ->get()
            ->result_array(); // 🔥 penting pakai array

        // =========================
        // TAMBAHKAN DETAIL INVOICE 🔥
        // =========================
        foreach ($data as &$row) {

            $detail = $this->db
                ->select('no_invoice, total_bayar_idr')
                ->from('tr_invoice_payment_detail')
                ->where('kd_pembayaran', $row['kd_pembayaran'])
                ->get()
                ->result_array();

            $row['detail'] = array_map(function ($d) {
                return [
                    'invoice' => $d['no_invoice'],
                    'nominal' => (float) $d['total_bayar_idr']
                ];
            }, $detail);
        }

        echo json_encode($data);
    }

    public function get_sisa_piutang_sebelumnya()
    {
        $user_id = $this->input->post('user_id'); // ambil dari POST

        if (!$user_id) {
            echo json_encode([]);
            return;
        }

        $last = $this->db->select('sisa_piutang')
            ->from('tr_setor_kasir')
            ->where('created_by', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $total = ($last && $last->sisa_piutang > 0) ? $last->sisa_piutang : 0;

        echo json_encode([
            'status' => true,
            'total'  => $total
        ]);
    }
}
