<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Surat_jalan extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Surat_Jalan.View';
    protected $addPermission    = 'Surat_Jalan.Add';
    protected $managePermission = 'Surat_Jalan.Manage';
    protected $deletePermission = 'Surat_Jalan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Surat_jalan/surat_jalan_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Surat Jalan');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('index');
    }

    public function data_side_surat_jalan() {}

    public function add()
    {
        $data = [
            'loading' => $this->db->get('loading_delivery')->result_array(),
        ];

        $this->template->title('Add Surat Jalan');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('form', $data);
    }
}
