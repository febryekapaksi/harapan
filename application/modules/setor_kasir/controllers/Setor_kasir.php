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
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Setor Kasir');

        $this->template->render('index');
    }

    public function data_side_setoran_kasir()
    {
        $this->setor_kasir_model->get_json_setoran_kasir();
    }

    public function create()
    {
        $this->template->page_icon('fa fa-sign-in');
        $this->template->title('Input Setoran Kasir');

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
        $post               = $this->input->post();

        $tgl_setor          = $post['tgl_setor'];
        $id_sales           = $post['id_sales'];
        $sales              = $post['nama'];
        $nilai_setor        = str_replace(",", "", $post['nilai_setor']);
        $total_penerimaan   = str_replace(",", "", $post['total_penerimaan']);
        $sisa_piutang       = str_replace(",", "", $post['sisa_piutang_sesudah']);

        $id_setoran         = $this->setor_kasir_model->generateKodeSetoran($tgl_setor);

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

        if (empty($post['detail'])) {
            echo json_encode(['status' => false, 'message' => 'Data penerimaan tidak boleh kosong.']);
            return;
        }

        $this->db->trans_begin();

        $this->db->insert('tr_setor_kasir', $header);

        foreach ($post['detail'] as $kd_penerimaan => $item) {
            $detail = [
                'id_setor_kasir'    => $id_setoran,
                'kd_pembayaran'     => $item['kd_pembayaran'],
                'id_customer'       => $item['id_customer'],
                'name_customer'     => $item['name_customer'],
                'no_invoice'        => $item['no_invoice'],
                'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                'total_penerimaan'  => str_replace(",", "", $item['total_invoiced']),
            ];

            $this->db->insert('tr_setor_kasir_detail', $detail);

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

    public function add_from_kasir()
    {
        $ids = $this->input->get('ids');
        $ids_array = explode(',', $ids);

        $data['bank'] = $this->db->get('master_bank')->result();

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

    public function save_bank()
    {
        $post = $this->input->post();

        // Ambil & format data header
        $tgl_setor = $post['tgl_setor'];
        $bank = $post['bank'];
        $norek = $post['norek'];
        $nilai_setor = str_replace(",", "", $post['nilai_setor']);
        $total_penerimaan = str_replace(",", "", $post['total_penerimaan']);
        $sisa_piutang = str_replace(",", "", $post['sisa_piutang_sesudah']);

        // Generate ID Setoran
        $id_setoran = $this->setor_bank_model->generateKodeSetoran($tgl_setor);

        // Cek ada detail atau tidak
        if (empty($post['detail'])) {
            echo json_encode(['status' => false, 'message' => 'Data penerimaan tidak boleh kosong.']);
            return;
        }

        // Siapkan header
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
        $this->db->insert('tr_setor_bank', $header);

        // Proses detail
        foreach ($post['detail'] as $kd_penerimaan => $item) {
            $detail = [
                'id_setor_bank'     => $id_setoran,
                'kd_pembayaran'     => $item['kd_pembayaran'],
                'id_setor_kasir'    => $item['id_setor_kasir'],
                'id_sales'          => $item['id_sales'],
                'sales'             => $item['sales'],
                'tgl_setor_kasir'   => $item['tgl_setor_kasir'],
                'id_customer'       => $item['id_customer'], // fallback jika tidak ada
                'name_customer'     => $item['name_customer'],
                'no_invoice'        => $item['no_invoice'],
                'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                'total_penerimaan'  => str_replace(",", "", $item['total_invoiced']),
            ];

            $this->db->insert('tr_setor_bank_detail', $detail);

            // Update status di tr_invoice_payment
            $this->db->where('kd_pembayaran', $item['kd_pembayaran'])
                ->update('tr_invoice_payment', ['status_setor' => 1]);

            // ✅ Tambahan: update status setor kasir
            $this->db->where('id', $item['id_setor_kasir'])
                ->update('tr_setor_kasir', ['status' => 1]);
        }

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
            ->result();

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
