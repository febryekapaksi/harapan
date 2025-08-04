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
            'Setor_bank/setor_bank_model'
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
        $this->template->title('Input Setoran');

        $bank = $this->db->get('master_bank')->result();

        $data = [
            'bank' => $bank,
        ];

        $this->template->render('form', $data);
    }

    public function save()
    {
        $post = $this->input->post();

        $tgl_setor = $post['tgl_setor'];
        $bank = $post['bank'];
        $norek = $post['norek'];
        $nilai_setor = str_replace(",", "", $post['nilai_setor']);
        $total_penerimaan = str_replace(",", "", $post['total_penerimaan']);
        $sisa_piutang = str_replace(",", "", $post['sisa_piutang_sesudah']);

        $id_setoran = $this->setor_bank_model->generateKodeSetoran($tgl_setor);

        $header = [
            'id'                => $id_setoran,
            'tgl_setor'         => $tgl_setor,
            'bank_id'           => $bank,
            'norek'             => $norek,
            'total_penerimaan'  => $total_penerimaan,
            'total_setoran'     => $nilai_setor,
            'sisa_piutang'      => $sisa_piutang,
            'tipe_setor'        => 'BANK',
            'created_by'        => $this->auth->user_id(),
            'created_at'        => date('Y-m-d H:i:s')
        ];

        if (empty($post['detail'])) {
            echo json_encode(['status' => false, 'message' => 'Data penerimaan tidak boleh kosong.']);
            return;
        }

        $this->db->trans_begin();

        $this->db->insert('tr_setor', $header);

        foreach ($post['detail'] as $kd_penerimaan => $item) {
            $detail = [
                'id_setor'          => $id_setoran,
                'kd_pembayaran'     => $item['kd_pembayaran'],
                'id_customer'       => $item['id_customer'],
                'name_customer'     => $item['name_customer'],
                'no_invoice'        => $item['no_invoice'],
                'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                'total_penerimaan'  => str_replace(",", "", $item['total_invoiced']),
            ];

            $this->db->insert('tr_setor_detail', $detail);

            $this->db->where('kd_pembayaran', $item['kd_pembayaran'])
                ->update('tr_invoice_payment', ['status_setor' => 1]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan data setoran.']);
        } else {
            $this->db->trans_commit();
            echo json_encode(['status' => true, 'message' => 'Data setoran berhasil disimpan.']);
        }
    }

    // fungsi get untuk ajax
    public function get_penerimaan()
    {
        $user_id = $this->auth->user_id(); // ambil user login

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
            ->result();

        echo json_encode($data);
    }

    public function get_sisa_piutang_sebelumnya()
    {
        $user_id = $this->auth->user_id();

        $last = $this->db->select('sisa_piutang')
            ->from('tr_setor')
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
