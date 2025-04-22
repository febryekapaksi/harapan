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

    public function save()
    {
        $data = $this->input->post();

        $id = $data['product_id'];
        $id_product_costing = $this->product_costing_model->generate_id();

        $inven = $this->db->get_where('new_inventory_4', array('id' => $id))->row();

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
            'created_by'        => $this->auth->user_id(),
            'created_at'        => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('product_costing', $header);

        $no = 0;
        if (isset($_POST['kompetitor']) && is_array($_POST['kompetitor'])) {
            foreach ($_POST['kompetitor'] as $komp) {
                $no++;
                $data = array(
                    'id_product_costing'    => $id_product_costing,
                    'nama'                  => $komp['nama'],
                    'harga'                 => $komp['harga'],
                );
                $this->db->insert('product_costing_kompetitor', $data);
            }
        }

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
