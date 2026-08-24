<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_piutang_sales extends Admin_Controller
{
    protected $viewPermission   = 'Report_Piutang_Sales.View';
    protected $addPermission    = 'Report_Piutang_Sales.Add';
    protected $managePermission = 'Report_Piutang_Sales.Manage';
    protected $deletePermission = 'Report_Piutang_Sales.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_piutang_sales/Report_piutang_sales_model');
        date_default_timezone_set('Asia/Bangkok');
    }

    // ─────────────────────────────────────────────────────────────
    // Halaman Utama: Ringkasan Piutang per Sales
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $tanggal = $this->input->get('tanggal', true); // opsional cut-off

        $piutang_sales = $this->Report_piutang_sales_model->get_piutang_per_sales($tanggal);
        $total_penerimaan  = array_sum(array_column($piutang_sales, 'total_penerimaan'));
        $total_setor_kasir = array_sum(array_column($piutang_sales, 'total_setor_kasir'));
        $total_setor_bank  = array_sum(array_column($piutang_sales, 'total_setor_bank'));
        $total_piutang     = array_sum(array_column($piutang_sales, 'saldo_piutang'));

        $data = [
            'tanggal'           => $tanggal,
            'piutang_sales'     => $piutang_sales,
            'total_penerimaan'  => $total_penerimaan,
            'total_setor_kasir' => $total_setor_kasir,
            'total_setor_bank'  => $total_setor_bank,
            'total_piutang'     => $total_piutang,
        ];

        $this->template->page_icon('fa fa-money');
        $this->template->title('Report Piutang Sales');
        $this->template->render('index', $data);
    }

    // ─────────────────────────────────────────────────────────────
    // Detail Piutang per Sales
    // ─────────────────────────────────────────────────────────────
    public function detail()
    {
        $id_user = $this->input->get('id_user', true);
        $tanggal = $this->input->get('tanggal', true);

        $sales = $this->db
            ->select('id_user, nm_lengkap')
            ->where('id_user', $id_user)
            ->get('users')
            ->row_array();

        if (empty($sales)) {
            show_404();
        }

        $rows          = $this->Report_piutang_sales_model->get_detail_piutang($id_user, $tanggal);
        $saldo_piutang = array_sum(array_column($rows, 'saldo'));

        $data = [
            'sales'         => $sales,
            'tanggal'       => $tanggal,
            'rows'          => $rows,
            'saldo_piutang' => $saldo_piutang,
        ];

        $this->template->page_icon('fa fa-list');
        $this->template->title('Detail Piutang - ' . ucfirst($sales['nm_lengkap']));
        $this->template->render('detail', $data);
    }
}
