<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_pembelian extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Report_Pembelian.View';
    protected $addPermission    = 'Report_Pembelian.Add';
    protected $managePermission = 'Report_Pembelian.Manage';
    protected $deletePermission = 'Report_Pembelian.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_pembelian/Report_pembelian_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    // =============================
    // Report Seluruh Pembelian
    // =============================
    public function index()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Daftar Faktur Pembelian');
        $this->template->render('index');
    }

    public function data_side_report()
    {
        $this->Report_pembelian_model->data_side_report();
    }

    // =============================
    // Report Seluruh Pembelian
    // =============================




    // =============================
    // Report Seluruh Pembelian
    // =============================
}
