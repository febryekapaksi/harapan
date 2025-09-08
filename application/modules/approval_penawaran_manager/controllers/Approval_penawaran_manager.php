<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Approval_penawaran_manager extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Approval_Penawaran_Manager.Add';
    protected $addPermission        = 'Approval_Penawaran_Manager.Manage';
    protected $managePermission     = 'Approval_Penawaran_Manager.View';
    protected $deletePermission     = 'Approval_Penawaran_Manager.Delete';


    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Approval_penawaran_manager/approval_penawaran_manager_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function data_side_approval_manager()
    {
        $this->approval_penawaran_manager_model->get_json_approval_manager();
    }

    public function index()
    {
        $this->template->page_icon('fa fa-check-square-o');
        $this->template->title('Approval Penawaran Manager');
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
        $data['mode'] = 'approval_manager';

        // View form edit
        $this->template->title("Approval Penawaran Manager");
        $this->template->page_icon("fa fa-check-square-o");
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post = $this->input->post();
        $id_penawaran = $post['id_penawaran'];

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            echo json_encode(['status' => 0, 'pesan' => 'Data penawaran tidak ditemukan']);
            return;
        }

        // Siapkan data header update
        $update = [
            'approved_by_manager' => $this->auth->user_id(),
            'approved_at_manager' => date('Y-m-d H:i:s')
        ];


        // Cek apakah level approval butuh direksi
        if ($penawaran['level_approval'] == 'D') {
            $update['status'] = 'WA'; // Tunggu approval Direksi
        } else {
            $update['status'] = 'A'; // Final approval dari Manager
        }

        // Simpan update ke penawaran
        $this->db->where('id_penawaran', $id_penawaran);
        $this->db->update('penawaran', $update);

        // Proses revisi data produk (penawaran_detail)
        if (isset($post['product']) && is_array($post['product'])) {
            $product_data = [];

            foreach ($post['product'] as $pro) {
                $product_data[] = [
                    'id_penawaran'      => $id_penawaran,
                    'id_product'        => $pro['id_product'],
                    'product_name'      => $pro['product_name'],
                    'harga_beli'        => str_replace(',', '', $pro['harga_beli']),
                    'qty'               => $pro['qty'],
                    'price_list'        => str_replace(',', '', $pro['price_list']),
                    'harga_penawaran'   => str_replace(',', '', $pro['harga_penawaran']),
                    'diskon'            => $pro['diskon'],
                    'diskon_nilai'      => $pro['diskon_nilai'],
                    'total'             => str_replace(',', '', $pro['total']),
                    'total_pl'          => str_replace(',', '', $pro['total_pl']),
                ];
            }

            if (!empty($product_data)) {
                $this->db->where('id_penawaran', $id_penawaran)->delete('penawaran_detail');

                $this->db->insert_batch('penawaran_detail', $product_data);
            }
        }

        echo json_encode([
            'status' => 1,
            'pesan' => 'Penawaran berhasil diapprove oleh Manager.'
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
