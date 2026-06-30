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
            'Retur_pembelian/Tanda_terima_nota_model',
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

        $suppliers = $this->db->select('id, kode_supplier, nama')
            ->order_by('nama', 'asc')
            ->get_where('new_supplier', ['deleted_by' => NULL])
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
        $kode_trans = $this->input->post('id_data');

        if (empty($kode_trans)) {
            echo json_encode([]);
            return;
        }

        $po_list = $this->Retur_pembelian_model->get_po_by_incoming($kode_trans);
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

        $suppliers = $this->db->select('id, kode_supplier, nama')
            ->order_by('nama', 'asc')
            ->get_where('new_supplier', ['deleted_by' => NULL])
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

        $this->load->view('retur_pembelian/print_sj', $data);
    }

    /**
     * Index Tanda Terima Nota Retur (Legacy - redirect to new page)
     */
    public function nota_retur()
    {
        redirect('retur_pembelian/tanda_terima');
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

    // ============================================================
    // TANDA TERIMA NOTA RETUR
    // ============================================================

    /**
     * Index Tanda Terima Nota Retur (List)
     */
    public function tanda_terima()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->title('Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-file-text-o');
        $this->template->render('tanda_terima/index');
    }

    /**
     * DataTable Tanda Terima server-side
     */
    public function data_tanda_terima()
    {
        $this->auth->restrict($this->viewPermission);
        $this->Tanda_terima_nota_model->data_tanda_terima_serverside();
    }

    /**
     * Form Create Tanda Terima Nota Retur
     */
    public function create_tanda_terima($id_retur = null)
    {
        $this->auth->restrict($this->addPermission);

        if (!$id_retur) {
            show_404();
        }

        $retur = $this->Retur_pembelian_model->get_by_id($id_retur);
        if (!$retur || $retur['header']['nota_retur'] != 'Ya') {
            show_error('Data retur tidak ditemukan atau tidak memerlukan Nota Retur.', 404);
            return;
        }

        // Cek apakah sudah ada tanda terima
        $existing = $this->Tanda_terima_nota_model->get_by_retur_id($id_retur);
        if ($existing) {
            redirect('retur_pembelian/edit_tanda_terima/' . $existing['header']['id']);
            return;
        }

        $data = [
            'retur' => $retur,
        ];

        $this->template->title('Buat Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-file-text-o');
        $this->template->render('tanda_terima/form', $data);
    }

    /**
     * Simpan Tanda Terima Nota Retur
     */
    public function save_tanda_terima()
    {
        $this->auth->restrict($this->addPermission);

        $post     = $this->input->post();
        $id_retur = $post['id_retur'];

        $result = $this->Tanda_terima_nota_model->save_tanda_terima($id_retur, $post);

        if ($result['status']) {
            history("Create Tanda Terima Nota Retur - Retur ID: " . $id_retur);
        }

        echo json_encode($result);
    }

    /**
     * Form Edit Tanda Terima Nota Retur
     */
    public function edit_tanda_terima($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            show_404();
        }

        $tanda_terima = $this->Tanda_terima_nota_model->get_by_id($id);
        if (!$tanda_terima) {
            show_error('Data Tanda Terima tidak ditemukan.', 404);
            return;
        }

        if ($tanda_terima['header']['status'] != 1) {
            redirect('retur_pembelian/view_tanda_terima/' . $id);
            return;
        }

        $retur = $this->Retur_pembelian_model->get_by_id($tanda_terima['header']['id_retur']);

        $data = [
            'tanda_terima' => $tanda_terima,
            'retur'        => $retur,
        ];

        $this->template->title('Edit Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('tanda_terima/form_edit', $data);
    }

    /**
     * Update Tanda Terima Nota Retur
     */
    public function update_tanda_terima($id = null)
    {
        $this->auth->restrict($this->managePermission);

        if (!$id) {
            echo json_encode(['status' => 0, 'pesan' => 'ID tidak valid.']);
            return;
        }

        $post   = $this->input->post();
        $result = $this->Tanda_terima_nota_model->update_tanda_terima($id, $post);

        if ($result['status']) {
            history("Update Tanda Terima Nota Retur ID: " . $id);
        }

        echo json_encode($result);
    }

    /**
     * View Tanda Terima Nota Retur
     */
    public function view_tanda_terima($id = null)
    {
        $this->auth->restrict($this->viewPermission);

        if (!$id) {
            show_404();
        }

        $tanda_terima = $this->Tanda_terima_nota_model->get_by_id($id);
        if (!$tanda_terima) {
            show_error('Data Tanda Terima tidak ditemukan.', 404);
            return;
        }

        $retur = $this->Retur_pembelian_model->get_by_id($tanda_terima['header']['id_retur']);

        $data = [
            'tanda_terima' => $tanda_terima,
            'retur'        => $retur,
        ];

        $this->template->title('Detail Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-eye');
        $this->template->render('tanda_terima/view', $data);
    }

    /**
     * Buat Retur - Halaman list retur yang bisa dibuat tanda terima
     */
    public function buat_tanda_terima()
    {
        $this->auth->restrict($this->addPermission);

        $retur_list = $this->Tanda_terima_nota_model->get_retur_available();

        $data = [
            'retur_list' => $retur_list,
        ];

        $this->template->title('Buat Tanda Terima Nota Retur');
        $this->template->page_icon('fa fa-plus');
        $this->template->render('tanda_terima/pilih_retur', $data);
    }

    /**
     * DataTable Penerimaan Uang server-side (retur dengan metode Terima Uang)
     */
    public function data_penerimaan_uang()
    {
        $this->auth->restrict($this->viewPermission);

        $requestData = $_REQUEST;
        $search = $requestData['search']['value'];
        $start  = $requestData['start'];
        $length = $requestData['length'];

        // Filter: retur yang sudah Process/Selesai dan metode = Terima Uang
        $this->db->from('tr_retur_pembelian rp');
        $this->db->join('tr_tanda_terima_nota_retur tt', 'tt.id_retur = rp.id', 'inner');
        $this->db->where('tt.metode_retur', 'Terima Uang');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('rp.no_retur', $search);
            $this->db->or_like('rp.nama_supplier', $search);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        $this->db->select('rp.id, rp.no_retur, rp.nama_supplier, rp.total_retur, rp.settlement, rp.sisa_retur, rp.status');
        $this->db->from('tr_retur_pembelian rp');
        $this->db->join('tr_tanda_terima_nota_retur tt', 'tt.id_retur = rp.id', 'inner');
        $this->db->where('tt.metode_retur', 'Terima Uang');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('rp.no_retur', $search);
            $this->db->or_like('rp.nama_supplier', $search);
            $this->db->group_end();
        }
        $this->db->order_by('rp.created_date', 'desc');
        if ($length != -1) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        $data = [];
        $urut = $start + 1;
        foreach ($query->result_array() as $row) {
            $status_badge = '';
            switch ($row['status']) {
                case 2: $status_badge = "<span class='badge bg-blue'>Process</span>"; break;
                case 3: $status_badge = "<span class='badge bg-green'>Selesai</span>"; break;
                default: $status_badge = "<span class='badge bg-yellow'>-</span>"; break;
            }

            $btn_settlement = '';
            if ($row['sisa_retur'] > 0 && $row['status'] == 2) {
                $btn_settlement = "<a href='" . site_url('retur_pembelian/settlement/' . $row['id']) . "' class='btn btn-xs btn-primary' title='Terima Uang'><i class='fa fa-money'></i> Terima Uang</a>";
            }

            $nestedData   = [];
            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = $row['no_retur'];
            $nestedData[] = $row['nama_supplier'];
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_retur'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['settlement'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['sisa_retur'], 0, ',', '.') . "</div>";
            $nestedData[] = "<div class='text-center'>{$status_badge}</div>";
            $nestedData[] = "<div class='text-center'>{$btn_settlement}</div>";

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
