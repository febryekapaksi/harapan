<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setor_bank extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Setor_Bank.View';
    protected $addPermission    = 'Setor_Bank.Add';
    protected $managePermission = 'Setor_Bank.Manage';
    protected $deletePermission = 'Setor_Bank.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
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
        $this->template->title('Setor Bank');

        $this->template->render('index');
    }

    public function data_side_setoran_bank()
    {
        $this->setor_bank_model->get_json_setoran_bank();
    }

    public function create()
    {
        $this->template->page_icon('fa fa-sign-in');
        $this->template->title('Input Setoran Bank');

        // Ambil data bank dari GL
        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $bank = $this->db->get()->result();

        $data = [
            'bank' => $bank,
        ];

        $this->template->render('form', $data);
    }

    public function view($id)
    {
        $this->template->page_icon('fa fa-eye');
        $this->template->title('View Setoran Bank');

        // Ambil data header (tr_setor_bank)
        $header = $this->db->get_where('tr_setor_bank', ['id' => $id])->row();

        // Ambil data detail (tr_setor_bank_detail)
        $detail = $this->db->get_where('tr_setor_bank_detail', ['id_setor_bank' => $id])->result();

        // Ambil data bank untuk label
        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $bank = $this->db->get()->result();

        $data = [
            'header' => $header,
            'detail' => $detail,
            'bank'   => $bank,
        ];

        $this->template->render('view', $data);
    }

    public function save()
    {
        try {
            $post = $this->input->post();

            $tgl_setor          = $post['tgl_setor'];
            $bank               = $post['bank'];
            $nilai_setor        = floatval(str_replace(",", "", $post['nilai_setor']));
            $total_penerimaan   = floatval(str_replace(",", "", $post['total_penerimaan']));
            $sisa_piutang       = floatval(str_replace(",", "", $post['sisa_piutang_sesudah']));

            $jurnal = [
                'tgl'       => $post['tgl_jurnal'],
                'type'      => $post['type'],
                'coa'       => $post['no_coa'],
                'ket'       => $post['keterangan'],
                'debet'     => $post['debet'],
                'kredit'    => $post['kredit'],
            ];

            if (empty($post['detail'])) {
                echo json_encode(['status' => false, 'message' => 'Data penerimaan kosong']);
                return;
            }

            $id_setoran = $this->setor_bank_model->generateKodeSetoran($tgl_setor);

            $this->db->trans_begin();

            // =========================
            // HEADER
            // =========================
            $this->db->insert('tr_setor_bank', [
                'id'               => $id_setoran,
                'tgl_setor'        => $tgl_setor,
                'bank_id'          => $bank,
                'total_penerimaan' => $total_penerimaan,
                'total_setoran'    => $nilai_setor,
                'sisa_piutang'     => $sisa_piutang,
                'created_by'       => $this->auth->user_id(),
                'created_at'       => date('Y-m-d H:i:s'),
                'tipe_setor'       => 'LANGSUNG'
            ]);

            // =========================
            // DETAIL (PER INVOICE 🔥)
            // =========================
            foreach ($post['detail'] as $pn) {

                $detail_invoice = json_decode($pn['detail_json'], true);

                foreach ($detail_invoice as $inv) {

                    $nominal = floatval(str_replace(",", "", $inv['nominal']));

                    $this->db->insert('tr_setor_bank_detail', [
                        'id_setor_bank'     => $id_setoran,
                        'kd_pembayaran'     => $pn['kd_pembayaran'],
                        'id_customer'       => $pn['id_customer'],
                        'name_customer'     => $pn['name_customer'],
                        'no_invoice'        => $inv['invoice'], // 🔥 per invoice
                        'total_invoice'     => $nominal,
                        'total_penerimaan'  => $nominal
                    ]);
                }

                // update status setor
                $this->db->where('kd_pembayaran', $pn['kd_pembayaran'])
                    ->update('tr_invoice_payment', ['status_setor' => 1]);
            }

            // =========================
            // JURNAL (PAKAI FRONTEND)
            // =========================
            $this->appr_jurnal($id_setoran, $jurnal, $post);

            if ($this->db->trans_status() === FALSE) {
                throw new Exception("DB Error");
            }

            $this->db->trans_commit();

            echo json_encode([
                'status' => true,
                'message' => 'Setoran berhasil disimpan'
            ]);
        } catch (\Throwable $th) {

            $this->db->trans_rollback();

            echo json_encode([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    function appr_jurnal($id_setoran, $jurnal, $post)
    {
        $session = $this->session->userdata('app_session');

        $tgl = $post['tgl_setor'];

        $Nomor_BUM = $this->Jurnal_model
            ->get_Nomor_Jurnal_BUM('101', $tgl);

        // =========================
        // VALIDASI BALANCE
        // =========================
        if (array_sum($jurnal['debet']) != array_sum($jurnal['kredit'])) {
            throw new Exception("Jurnal tidak balance");
        }

        // =========================
        // HEADER JURNAL
        // =========================
        $this->db->insert(DBACC . '.jarh', [
            'nomor'         => $Nomor_BUM,
            'kd_pembayaran' => $id_setoran,
            'tgl'           => $tgl,
            'jml'           => array_sum($jurnal['debet']),
            'kdcab'         => '101',
            'jenis_reff'    => $id_setoran,
            'no_reff'       => $id_setoran,
            'customer'      => $session['nm_lengkap'],
            'note'          => 'SETOR BANK DARI SALES NO. ' . $id_setoran,
            'jenis_ar'      => 'V',
            'terima_dari'   => '-',
            'valid'         => $session['id_user'],
            'tgl_valid'     => $tgl,
            'user_id'       => $session['id_user'],
            'tgl_invoice'   => $tgl,
            'batal'         => 0
        ]);

        // =========================
        // DETAIL JURNAL
        // =========================
        $arrJurnal = [];

        for ($i = 0; $i < count($jurnal['coa']); $i++) {

            $arrJurnal[] = [
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $jurnal['tgl'][$i],
                'tipe'          => $jurnal['type'][$i],
                'no_perkiraan'  => $jurnal['coa'][$i],
                'keterangan'    => $jurnal['ket'][$i],
                'no_reff'       => $id_setoran,
                'debet'         => floatval($jurnal['debet'][$i]),
                'kredit'        => floatval($jurnal['kredit'][$i]),
                'created_by'    => $this->auth->user_id(),
                'created_on'    => date('Y-m-d H:i:s'),
            ];
        }

        $this->db->insert_batch(DBACC . '.jurnal', $arrJurnal);

        // =========================
        // KARTU PIUTANG (PER INVOICE 🔥)
        // =========================
        foreach ($post['detail'] as $pn) {

            $detail_invoice = json_decode($pn['detail_json'], true);

            foreach ($detail_invoice as $inv) {

                $nominal = floatval(str_replace(",", "", $inv['nominal']));

                $customer = $this->db
                    ->select('c.name_customer, c.id_karyawan, e.nm_karyawan')
                    ->from('master_customers c')
                    ->join('employee e', 'e.id = c.id_karyawan', 'left')
                    ->where('c.id_customer', $pn['id_customer'])
                    ->get()
                    ->row();

                if ($nominal > 0) {

                    $this->db->insert('tr_kartu_piutang_sales', [
                        'tipe'          => 'BUM',
                        'nomor'         => $Nomor_BUM,
                        'tanggal'       => $tgl,
                        'no_perkiraan'  => '1102-01-04',
                        'keterangan'    => 'SETOR PIUTANG INV ' . $inv['invoice'] . ' A/n ' . $pn['name_customer'] . ' dari Sales',
                        'no_reff'       => $inv['invoice'],
                        'debet'         => 0,
                        'kredit'        => $nominal,
                        'id_sales'      => $customer->id_karyawan,
                        'nama_sales'    => $customer->nm_karyawan,
                    ]);
                }
            }
        }

        // =========================
        // COUNTER
        // =========================
        $this->db->query("
        UPDATE " . DBACC . ".pastibisa_tb_cabang 
        SET nobum = nobum + 1 
        WHERE nocab = '101'
    ");
    }

    // fungsi get untuk ajax
    public function get_penerimaan()
    {
        $user_id = $this->auth->user_id();

        // Ambil kd_pembayaran yang sudah pernah disetor (ada di tr_setor_bank_detail)
        $sudah_setor = $this->db
            ->select('kd_pembayaran')
            ->from('tr_setor_bank_detail')
            ->group_by('kd_pembayaran')
            ->get()
            ->result_array();

        $kd_sudah_setor = array_column($sudah_setor, 'kd_pembayaran');

        $this->db
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
            ->where('a.created_by', $user_id);

        // Exclude penerimaan yang sudah ada di detail setoran bank
        if (!empty($kd_sudah_setor)) {
            $this->db->where_not_in('a.kd_pembayaran', $kd_sudah_setor);
        }

        $data = $this->db
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
        $user_id = $this->auth->user_id();

        $last = $this->db->select('sisa_piutang')
            ->from('tr_setor_bank')
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
