<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penawaran extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Penawaran.View';
    protected $addPermission        = 'Penawaran.Add';
    protected $managePermission     = 'Penawaran.Manage';
    protected $deletePermission     = 'Penawaran.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Penawaran/penawaran_model',
            'Price_list/price_list_model',
            'Product_costing/product_costing_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->render('index');
    }

    public function add()
    {
        $data['products'] = $this->db->get_where('new_inventory_4', ['price_ref IS NOT NULL'])->result_array();



        $this->template->render('form', $data);
    }
}
