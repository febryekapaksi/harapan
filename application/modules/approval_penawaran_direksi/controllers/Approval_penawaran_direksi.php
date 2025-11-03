<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_penawaran_direksi extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Approval_Penawaran_Direksi.Add';
    protected $addPermission        = 'Approval_Penawaran_Direksi.Manage';
    protected $managePermission     = 'Approval_Penawaran_Direksi.View';
    protected $deletePermission     = 'Approval_Penawaran_Direksi.Delete';


    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Approval_penawaran_direksi/approval_penawaran_direksi_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function data_side_approval_direksi()
    {
        $this->approval_penawaran_direksi_model->get_json_approval_direksi();
    }

    public function index()
    {
        $this->template->page_icon('fa fa-check-square-o');
        $this->template->title('Approval Penawaran Direksi');
        $this->template->render('index');
    }

    public function approval($id_penawaran)
    {
        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            show_404();
        }

        $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

        $data['customers'] = $this->db->get('master_customers')->result_array();
        $data['products'] = $this->db->get('product_costing')->result_array();
        $data['payment_terms'] = $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array();

        // Kirim data ke view
        $data['penawaran'] = $penawaran;
        $data['penawaran_detail'] = $penawaran_detail;
        $data['mode'] = 'approval_direksi';

        // View form edit
        $this->template->title("Approval Penawaran Direksi");
        $this->template->page_icon("fa fa-check-square-o");
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post = $this->input->post();
        $id_penawaran = $post['id_penawaran'];

        if (empty($id_penawaran)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID penawaran tidak ditemukan']);
            return;
        }

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            echo json_encode(['status' => 0, 'pesan' => 'Data penawaran tidak ditemukan']);
            return;
        }

        $this->db->where('id_penawaran', $id_penawaran);
        $this->db->update('penawaran', [
            'status' => 'A', // FINAL Approved
            'approved_by_direksi' => $this->auth->user_id(),
            'approved_at_direksi' => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'status' => 1,
            'pesan' => 'Approval direksi berhasil diproses.'
        ]);
    }

    public function reject($id = null)
    {
        if (!$id) {
            echo json_encode(['save' => 0, 'message' => 'ID tidak ditemukan']);
            return;
        }

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id])->row();
        if (!$penawaran) {
            echo json_encode(['save' => 0, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $reason = $this->input->post('reason');
        if (!$reason) {
            echo json_encode(['save' => 0, 'message' => 'Alasan harus diisi']);
            return;
        }

        $data = [
            'status' => "R",
            'status_draft' => 0,
            'reject_reason' => $reason,
            'modified_by' => $this->auth->user_id(),
            'modified_at' => date('Y-m-d H:i:s')
        ];

        if ($penawaran->level_approval == "D" && $penawaran->approved_by_manager !== null) {
            $data['status'] = "WA";
            $data['approved_by_manager'] = null;
            $data['approved_at_manager'] = null;
        }

        $this->db->where('id_penawaran', $id);
        $update = $this->db->update('penawaran', $data);

        if ($update) {
            echo json_encode(['save' => 1]);
        } else {
            echo json_encode(['save' => 0, 'message' => 'Gagal menyimpan alasan penolakan']);
        }
    }
}
