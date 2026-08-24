<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Terima_uang_supplier extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Terima_uang_supplier.View';
    protected $addPermission    = 'Terima_uang_supplier.Add';
    protected $managePermission = 'Terima_uang_supplier.Manage';
    protected $deletePermission = 'Terima_uang_supplier.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Terima_uang_supplier/Terima_uang_supplier_model',
        ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    /**
     * Halaman Index - List Retur yang masih ada sisa
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Terima Uang dari Supplier');
        $this->template->page_icon('fa fa-money');
        $this->template->render('index');
    }

    /**
     * DataTable server-side AJAX
     */
    public function data()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Terima_uang_supplier_model->data_serverside();
    }

    /**
     * Form Terima Uang (Edit/Input)
     */
    public function receive($id_retur = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id_retur) {
            show_404();
        }

        $retur = $this->Terima_uang_supplier_model->get_retur_by_id($id_retur);

        if (!$retur || $retur['header']['sisa_retur'] <= 0) {
            show_error('Data tidak ditemukan atau sisa retur sudah 0.', 404);
            return;
        }

        $detail = $this->Terima_uang_supplier_model->get_retur_detail($id_retur);
        $history = $this->Terima_uang_supplier_model->get_history_penerimaan($id_retur);

        $data = [
            'retur'   => $retur,
            'detail'  => $detail,
            'history' => $history,
        ];

        $this->template->title('Terima Uang dari Supplier');
        $this->template->page_icon('fa fa-money');
        $this->template->render('form', $data);
    }

    /**
     * AJAX: Simpan Penerimaan Uang
     */
    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $post = $this->input->post();

        if (empty($post['id_retur'])) {
            echo json_encode(['status' => 0, 'pesan' => 'ID Retur tidak valid.']);
            return;
        }

        $result = $this->Terima_uang_supplier_model->save_penerimaan($post);

        if ($result['status']) {
            history("Terima Uang dari Supplier - Retur: " . $result['no_retur'] . " - Jumlah: " . number_format($result['jumlah']));
        }

        echo json_encode($result);
    }

    /**
     * AJAX: Get detail item retur untuk form
     */
    public function get_detail_retur()
    {
        $id_retur = $this->input->post('id_retur');

        if (empty($id_retur)) {
            echo json_encode([]);
            return;
        }

        $detail = $this->Terima_uang_supplier_model->get_retur_detail($id_retur);
        echo json_encode($detail);
    }
}
