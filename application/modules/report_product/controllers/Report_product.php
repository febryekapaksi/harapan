<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_product extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Report_Product.View';
    protected $addPermission    = 'Report_Product.Add';
    protected $managePermission = 'Report_Product.Manage';
    protected $deletePermission = 'Report_Product.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_product/Report_product_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        // Ambil tahun dari input (default tahun sekarang)
        $tahun = $this->input->get('tahun') ? $this->input->get('tahun') : date('Y');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data_qty = array_fill_keys($months, 0);
        $data_produk = array_fill_keys($months, []);

        $this->db->select('a.product_name, a.modified_at, b.qty_stock');
        $this->db->from('product_costing a');
        $this->db->join('warehouse_stock b', 'a.code_lv4 = b.id_material', 'left');
        $this->db->where('a.status', 'A');
        $this->db->where('YEAR(a.modified_at)', $tahun); // Filter berdasarkan tahun
        $query = $this->db->get()->result();

        foreach ($query as $row) {
            $m_index = date('n', strtotime($row->modified_at)) - 1;
            if (isset($months[$m_index])) {
                $m_name = $months[$m_index];
                $data_qty[$m_name] += $row->qty_stock;
                $data_produk[$m_name][] = $row->product_name . " (" . number_format($row->qty_stock) . ")";
            }
        }

        $max_rows = 1;
        foreach ($data_produk as $p) {
            if (count($p) > $max_rows) $max_rows = count($p);
        }

        // Kirim data ke View
        $data = [
            'months'      => $months,
            'data_qty'    => $data_qty,
            'data_produk' => $data_produk,
            'max_rows'    => $max_rows,
            'tahun_pilih' => $tahun
        ];

        $this->template->page_icon('fa fa-cubes');
        $this->template->title('Report New Produk');
        $this->template->set($data); // Mengirimkan array data ke template
        $this->template->render('index_new_product');
    }
}
