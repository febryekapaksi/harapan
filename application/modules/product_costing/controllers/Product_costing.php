<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_costing extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Product_Price.View';
    protected $addPermission      = 'Product_Price.Add';
    protected $managePermission   = 'Product_Price.Manage';
    protected $deletePermission   = 'Product_Price.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Product_costing/product_costing_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $session = $this->session->userdata('app_session');

        $product_price   = $this->db->select('MAX(update_date) AS updated_date')->get('product_price')->result();
        $last_update     = "Last Update: " . date('d-M-Y H:i:s', strtotime($product_price[0]->updated_date));
        $data = [
            'product_lv1' => array(),
            'last_update' => $last_update
        ];

        history("View index product costing");
        $this->template->title('Costing / Product Costing');
        $this->template->render('index', $data);
    }

    public function add()
    {
        $product = $this->db->get_where('new_inventory_4', array('price_ref !=' => NULL))->result_array();
        $costing = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

        $data = [
            'product'   => $product,
            'costing'   => $costing,
        ];
        $this->template->title('Add Costing');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add', $data);
    }

    public function edit()
    {
        $id = $this->input->post('id');
        if (!$id) {
            show_error("ID tidak ditemukan", 400);
        }
        $procost = $this->db->get_where('product_costing', ['id' => $id])->row();
        $kompetitor = $this->db->get_where('product_costing_kompetitor', ['id_product_costing' => $procost->id])->result();

        $product = $this->db->get_where('new_inventory_4', array('price_ref !=' => NULL))->result_array();
        $costing = $this->db->get_where('costing_rate', array('deleted_date' => NULL))->result_array();

        $data = [
            'product'       => $product,
            'costing'       => $costing,
            'procost'       => $procost,
            'kompetitor'    => $kompetitor,
        ];
        $this->template->title('Edit Costing');
        $this->template->page_icon('fa fa-edit');
        $this->template->render('add', $data);
    }

    public function save()
    {
        $data = $this->input->post();
        $id = $data['id']; // <-- id untuk update, bisa null/kosong saat insert

        // Ambil data inventory
        $id_product = $data['product_id'];
        $inven = $this->db->get_where('new_inventory_4', ['code_lv4' => $id_product])->row();
        if (!$inven) {
            show_error('Produk tidak ditemukan.');
        }

        $is_update = !empty($id);
        $id_product_costing = $is_update ? $id : $this->product_costing_model->generate_id();

        $header = [
            'id'                => $id_product_costing,
            'code_lv1'          => $inven->code_lv1,
            'code_lv2'          => $inven->code_lv2,
            'code_lv3'          => $inven->code_lv3,
            'code_lv4'          => $inven->code_lv4,
            'product_name'      => $inven->nama,
            'harga_beli'        => $data['harga_beli'],
            'biaya_import'      => $data['biaya_import'],
            'biaya_cabang'      => $data['biaya_cabang'],
            'biaya_logistik'    => $data['biaya_logistik'],
            'biaya_ho'          => $data['biaya_ho'],
            'biaya_marketing'   => $data['biaya_marketing'],
            'price'             => $data['price'],
            'propose_price'     => $data['propose_price'],
            'status'            => "WA",
        ];

        if ($is_update) {
            $header['modified_by'] = $this->auth->user_id();
            $header['modified_at'] = date('Y-m-d H:i:s');
        } else {
            $header['created_by'] = $this->auth->user_id();
            $header['created_at'] = date('Y-m-d H:i:s');
        }

        // Insert/update product_costing
        $this->db->trans_start();
        if ($is_update) {
            $this->db->where('id', $id);
            $this->db->update('product_costing', $header);
            $id_product_costing = $id;
        } else {
            $this->db->insert('product_costing', $header);
            $id_product_costing = $header['id']; // pakai ID yang baru dibuat
        }
        // Hapus dan simpan ulang kompetitor
        if ($is_update) {
            $this->db->delete('product_costing_kompetitor', ['id_product_costing' => $id_product_costing]);
        }
        if (isset($_POST['kompetitor']) && is_array($_POST['kompetitor'])) {
            $kompetitor_data = [];
            foreach ($_POST['kompetitor'] as $komp) {
                $kompetitor_data[] = [
                    'id_product_costing' => $id_product_costing,
                    'nama'               => $komp['nama'],
                    'harga'              => str_replace(',', '', $komp['harga']),
                ];
            }
            if (!empty($kompetitor_data)) {
                $this->db->insert_batch('product_costing_kompetitor', $kompetitor_data);
            }
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'        => 'Gagal Save. Try Again Later ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'        => 'Success Save. Thanks ...',
                'status'    => 1
            );
        }

        echo json_encode($status);
    }

    public function data_side_product_costing()
    {
        $this->product_costing_model->get_json_product_costing();
    }
}
