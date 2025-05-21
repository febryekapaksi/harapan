<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is controller for Master Warehouse
 */
$status = array();
class Warehouse extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Warehouse.View';
    protected $addPermission        = 'Warehouse.Add';
    protected $managePermission     = 'Warehouse.Manage';
    protected $deletePermission     = 'Warehouse.Delete';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('Warehouse/Warehouse_model', 'All/All_model'));
        $this->template->title('Gudang');
        $this->template->page_icon('fa fa-dollar');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Stock Product');
        $this->template->page_icon('fa fa-cubes');
        $this->template->render('index');
    }

    // SERVER SIDE
    public function data_side_warehouse_stock()
    {
        $this->Warehouse_model->get_json_warehouse_stock();
    }
}
