<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_pembelian extends Admin_Controller
{
    // Permission
    protected $viewPermission   = 'Retur_pembelian.View';
    protected $addPermission    = 'Retur_pembelian.Add';
    protected $managePermission = 'Retur_pembelian.Manage';
    protected $deletePermission = 'Retur_pembelian.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Retur_pembelian/Retur_pembelian_model',
            'Retur_pembelian/Jurnal_retur_model',
        ));

        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    /**
     * Halaman Index - List Retur Pembelian
     */
    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Retur Pembelian');
        $this->template->page_icon('fa fa-undo');
        $this->template->render('index');
    }

    /**
     * DataTable server-side AJAX
     */
    public function data()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Retur_pembelian_model->data_serverside();
    }

    /**
     * Form Tambah Retur Baru
     */
    public function add()
    {
        $this->auth->restrict($this->addPermission);

        $suppliers = $this->db->order_by('nama', 'asc')
            ->get_where('new_supplier', ['deleted_date' => NULL])
            ->result_array();

        $data = [
            'suppliers' => $suppliers,
        ];

        $this->template->title('Form Retur Pembelian');
        $this->template->page_icon('fa fa-undo');
        $this->template->render('form', $data);
    }

    /**
     * AJAX: Get list invoice by supplier
     */
    public function get_invoice_by_supplier()
    {
        $id_supplier = $this->input->post('id_supplier');

        if (empty($id_supplier)) {
            echo json_encode([]);
            return;
        }

        $invoices = $this->Retur_pembelian_model->get_invoice_by_supplier($id_supplier);
        echo json_encode($invoices);
    }

    /**
     * AJAX: Get detail produk dari invoice
     */
    public function get_detail_invoice()
    {
        $no_invoice = $this->input->post('no_invoice');
        $no_po      = $this->input->post('no_po');

        if (empty($no_invoice)) {
            echo json_encode([]);
            return;
        }

        $details = $this->Retur_pembelian_model->get_detail_invoice($no_invoice, $no_po);
        echo json_encode($details);
    }

    /**
     * AJAX: Get list PO dari incoming
     */
    public function get_po_by_incoming()
    {
        $id_data = $this->input->post('id_data');

        if (empty($id_data)) {
            echo json_encode([]);
            return;
        }

        $po_list = $this->Retur_pembelian_model->get_po_by_incoming($id_data);
        echo json_encode($po_list);
    }

    /**
     * Simpan Retur (Draft)
     */
    public function save()
    {
        $this->auth->restrict($this->addPermission);
        $post = $this->input->post();

        // Upload file BA jika ada
        $file_ba = null;
        if (!empty($_FILES['file_ba']['name'])) {
            $file_ba = $this->_upload_ba();
            if ($file_ba === false) {
                echo json_encode(['status' => 0, 'pesan' => 'Upload file gagal: ' . $this->upload->display_errors('', '')]);
                return;
            }
        }

        $result = $this->Retur_pembelian_model->save_retur($post, $file_ba);

        if ($result['status']) {
            history("Create Retur Pembelian: " . $result['no_retur']);
        }

        echo json_encode($result);
    }

    /**
     * Form Edit Retur (Draft only)
     */
    public function edit($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            show_404();
        }

        $retur = $this->Retur_pembelian_model->get_by_id($id);

        if (!$retur || $retur['header']['status'] != 1) {
            show_error('Data tidak ditemukan atau tidak bisa diedit.', 403);
            return;
        }

        $suppliers = $this->db->order_by('nama', 'asc')
            ->get_where('new_supplier', ['deleted_date' => NULL])
            ->result_array();

        $data = [
            'retur'     => $retur,
            'suppliers' => $suppliers,
        ];

        $this->template->title('Edit Retur Pembelian');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('form_edit', $data);
    }

    /**
     * Update Retur (Draft only)
     */
    public function update($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $post = $this->input->post();

        // Upload file BA jika ada
        $file_ba = null;
        if (!empty($_FILES['file_ba']['name'])) {
            $file_ba = $this->_upload_ba();
            if ($file_ba === false) {
                echo json_encode(['status' => 0, 'pesan' => 'Upload file gagal: ' . $this->upload->display_errors('', '')]);
                return;
            }
        }

        $result = $this->Retur_pembelian_model->update_retur($id, $post, $file_ba);

        if ($result['status']) {
            history("Update Retur Pembelian ID: " . $id);
        }

        echo json_encode($result);
    }

    /**
     * Ajukan Retur (Draft -> Process)
     */
    public function ajukan($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $result = $this->Retur_pembelian_model->ajukan($id);

        if ($result['status']) {
            history("Ajukan Retur Pembelian: " . $result['no_retur']);
        }

        echo json_encode($result);
    }

    /**
     * View Detail Retur
     */
    public function view($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            show_404();
        }

        $retur = $this->Retur_pembelian_model->get_by_id($id);

        if (!$retur) {
            show_error('Data Retur tidak ditemukan.', 404);
            return;
        }

        $settlements = $this->Retur_pembelian_model->get_settlement($id);

        $data = [
            'retur'       => $retur,
            'settlements' => $settlements,
        ];

        $this->template->title('Detail Retur ' . $retur['header']['no_retur']);
        $this->template->page_icon('fa fa-eye');
        $this->template->render('view', $data);
    }

    /**
     * Print Surat Jalan Pengembalian Barang
     */
    public function print_sj($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            show_404();
        }

        $retur = $this->Retur_pembelian_model->get_by_id($id);

        if (!$retur || $retur['header']['kembalikan_barang'] != 'Ya') {
            show_error('Data tidak ditemukan atau tidak memerlukan pengembalian barang.', 404);
            return;
        }

        $data = [
            'retur' => $retur,
        ];

        $this->template->set_layout('print');
        $this->template->render('print_sj', $data);
    }

    /**
     * Index Tanda Terima Nota Retur
     */
    public function nota_retur()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-file-text-o');
        $this->template->render('nota_retur');
    }

    /**
     * DataTable Nota Retur server-side
     */
    public function data_nota_retur()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Retur_pembelian_model->data_nota_retur_serverside();
    }

    /**
     * Konfirmasi Terima Nota Retur
     */
    public function terima_nota($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $tgl_terima = $this->input->post('tgl_terima') ?: date('Y-m-d');

        $result = $this->Retur_pembelian_model->terima_nota($id, $tgl_terima);

        if ($result['status']) {
            history("Terima Nota Retur ID: " . $id);
        }

        echo json_encode($result);
    }

    /**
     * Form Settlement (Terima Uang)
     */
    public function settlement($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            show_404();
        }

        $retur = $this->Retur_pembelian_model->get_by_id($id);

        if (!$retur || $retur['header']['sisa_retur'] <= 0) {
            show_error('Data tidak ditemukan atau sisa retur sudah 0.', 404);
            return;
        }

        $settlements = $this->Retur_pembelian_model->get_settlement($id);

        $data = [
            'retur'       => $retur,
            'settlements' => $settlements,
        ];

        $this->template->title('Penerimaan Uang - ' . $retur['header']['no_retur']);
        $this->template->page_icon('fa fa-money');
        $this->template->render('settlement', $data);
    }

    /**
     * Simpan Settlement (Terima Uang)
     */
    public function save_settlement($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $post = $this->input->post();
        $result = $this->Retur_pembelian_model->save_settlement($id, $post);

        if ($result['status']) {
            history("Settlement Retur Pembelian ID: " . $id . " - Jumlah: " . $post['jumlah']);
        }

        echo json_encode($result);
    }

    /**
     * Cancel Retur
     */
    public function cancel($id = null)
    {
        $this->auth->restrict($this->deletePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $result = $this->Retur_pembelian_model->cancel($id);

        if ($result['status']) {
            history("Cancel Retur Pembelian ID: " . $id);
        }

        echo json_encode($result);
    }

    /**
     * Private: Upload file Berita Acara
     */
    private function _upload_ba()
    {
        $config = [
            'upload_path'   => './uploads/retur_pembelian/',
            'allowed_types' => 'pdf|jpg|jpeg|png',
            'max_size'      => 2048, // 2MB
            'file_name'     => 'BA_' . date('YmdHis') . '_' . rand(100, 999),
        ];

        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->upload->initialize($config);

        if ($this->upload->do_upload('file_ba')) {
            $upload_data = $this->upload->data();
            return 'uploads/retur_pembelian/' . $upload_data['file_name'];
        }

        return false;
    }
}
