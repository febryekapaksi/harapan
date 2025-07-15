<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pr_product extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'PR_Product.View';
    protected $addPermission    = 'PR_Product.Add';
    protected $managePermission = 'PR_Product.Manage';
    protected $deletePermission = 'PR_Product.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Pr_product/pr_product_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-cubes');
        $this->template->title('PR Product');
        $this->template->render('index');
    }

    public function add()
    {
        $this->template->page_icon('fa fa-cubes');
        $this->template->title('Re-order Point Product');

        $this->template->render('add');
    }

    public function data_side_material_planning()
    {
        $this->pr_product_model->get_data_json_material_planning();
    }

    public function server_side_reorder_point()
    {
        $this->pr_product_model->get_data_json_reorder_point();
    }

    public function save_reorder_change()
    {
        $data = $this->input->post();

        $id_material    = $data['id_material'];
        $purchase       = str_replace(',', '', $data['purchase']);
        $tanggal        = $data['tanggal'];
        $keterangan     = $data['keterangan'];

        $ArrHeader = array(
            'request'           => $purchase,
            'tgl_dibutuhkan'    => $tanggal,
            'keterangan'        => $keterangan,
        );

        $this->db->trans_start();
        $this->db->where('code_lv4', $id_material);
        $this->db->update('new_inventory_4', $ArrHeader);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Change propose request material ' . $id_material . ' / ' . $purchase . ' / ' . $tanggal);
        }
        echo json_encode($Arr_Data);
    }

    public function save_reorder_all()
    {
        $data = $this->input->post();

        $Ym       = date('ym'); // contoh: 2407
        $prefix   = "SPR";

        $qIPP = "SELECT MAX(so_number) AS maxP 
         FROM material_planning_base_on_produksi 
         WHERE so_number LIKE '{$prefix}{$Ym}%'";

        $resultIPP  = $this->db->query($qIPP)->row_array();
        $lastNumber = $resultIPP['maxP'];

        $urutan = ($lastNumber) ? (int)substr($lastNumber, strlen($prefix . $Ym), 5) : 0;
        $urutan++;

        $urutFormatted = sprintf('%05d', $urutan);
        $so_number     = $prefix . $Ym . $urutFormatted;

        $getraw_materials   = $this->db->get_where('new_inventory_4', array('request >' => 0))->result_array();

        $ArrSaveDetail = [];
        $SUM = 0;
        foreach ($getraw_materials as $key => $value) {
            $SUM += $value['request'];
            $ArrSaveDetail[$key]['so_number']           = $so_number;
            $ArrSaveDetail[$key]['id_material']         = $value['code_lv4'];
            $ArrSaveDetail[$key]['propose_purchase']    = $value['request'];
            $ArrSaveDetail[$key]['note']                = $value['keterangan'];
        }

        $ArrSaveHeader = array(
            'so_number'         => $so_number,
            'no_pr'             => generateNoPR(),
            'category'          => 'pr product',
            'tgl_so'            => date('Y-m-d'),
            'id_customer'       => 'C100-2401002',
            'project'           => 'Pengisian Stok Product',
            'qty_propose'       => $SUM,
            'tgl_dibutuhkan'    => $value['tgl_dibutuhkan'],
            'created_by'        => $this->auth->user_id(),
            'created_date'      => date('Y-m-d H:i:s'),
            'booking_by'        => $this->auth->user_id(),
            'booking_date'      => date('Y-m-d H:i:s'),
            'tingkat_pr'        => $data['tingkat_pr'],
            'app_post'          => '3',
            'app_1'             => '1',
            'app_2'             => '1',
        );

        $this->db->trans_start();
        $this->db->insert('material_planning_base_on_produksi', $ArrSaveHeader);
        if (!empty($ArrSaveDetail)) {
            $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrSaveDetail);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Save pengajuan propose material all');
        }
        echo json_encode($Arr_Data);
    }

    public function save_reorder_change_date()
    {
        $data = $this->input->post();

        $tanggal        = $data['tanggal'];
        $get_materials  = $this->db->get_where('new_inventory_4', array('category' => 'product'))->result_array();

        foreach ($get_materials as $key => $value) {
            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tanggal;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'    => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'    => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Change propose request material tgl dibutuhkan all ' . $tanggal);
        }
        echo json_encode($Arr_Data);
    }

    public function set_update_propose_reorder()
    {
        $data = $this->input->post();
        $tgl_now = date('Y-m-d');
        $GET_OUTANDING_PR   = get_pr_on_progress();
        $tgl_next_month     = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
        $get_materials      = $this->db
            ->select('a.*, b.qty_stock')
            ->join('warehouse_stock b', 'a.code_lv4 = b.code_lv4 AND b.id_gudang = 1', 'left')
            ->get_where('new_inventory_4 a', array('a.category' => 'product'))
            ->result_array();

        foreach ($get_materials as $key => $value) {
            $outanding_pr   = (!empty($GET_OUTANDING_PR[$value['code_lv4']]) and $GET_OUTANDING_PR[$value['code_lv4']] > 0) ? $GET_OUTANDING_PR[$value['code_lv4']] : 0;

            $QTY_PR = NULL;
            if ($value['qty_stock'] < $value['min_stok']) {
                $QTY_PR = ($value['max_stok'] - ($value['qty_stock'] + $outanding_pr));
                $QTY_PR = ($QTY_PR < 0) ? NULL : $QTY_PR;
            }

            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['request']         = $QTY_PR;
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tgl_next_month;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'   => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'   => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Set propose request material');
        }
        echo json_encode($Arr_Data);
    }

    public function clear_update_reorder()
    {
        $data               = $this->input->post();
        $tgl_now            = date('Y-m-d');
        $tgl_next_month     = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
        $get_materials      = $this->db->get_where('new_inventory_4', array('category' => 'product'))->result_array();

        foreach ($get_materials as $key => $value) {
            $ArrUpdate[$key]['code_lv4']        = $value['code_lv4'];
            $ArrUpdate[$key]['request']         = 0;
            $ArrUpdate[$key]['tgl_dibutuhkan']  = $tgl_next_month;
        }

        $this->db->trans_start();
        $this->db->update_batch('new_inventory_4', $ArrUpdate, 'code_lv4');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = array(
                'pesan'   => 'Save process failed. Please try again later ...',
                'status'  => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data  = array(
                'pesan'   => 'Save process success. Thanks ...',
                'status'  => 1
            );
            history('Clear all propose request material');
        }
        echo json_encode($Arr_Data);
    }

    public function detail_planning($so_number = null)
    {
        // Ambil header
        $header = $this->db
            ->select('a.*, b.due_date, c.name_customer')
            ->join('so_internal b', 'a.so_number = b.so_number', 'left')
            ->join('master_customers c', 'a.id_customer = c.id_customer', 'left')
            ->get_where('material_planning_base_on_produksi a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil detail
        $detail = $this->db
            ->select('a.*, b.max_stok, b.min_stok')
            ->join('new_inventory_4 b', 'a.id_material = b.code_lv4', 'left')
            ->get_where('material_planning_base_on_produksi_detail a', ['a.so_number' => $so_number])
            ->result_array();

        // Ambil data inventory level 4 (dulunya dari get_inventory_lv4)
        $GET_LEVEL4 = [];
        $query = $this->db->select('code_lv4, nama')
            ->from('new_inventory_4')
            ->where('deleted_date IS NULL')
            ->get()
            ->result_array();
        foreach ($query as $row) {
            $GET_LEVEL4[$row['code_lv4']] = ['nama' => $row['nama']];
        }

        // Ambil stok pusat (dulunya dari getStokMaterial)
        $get_stok_pusat = [];
        $stok = $this->db
            ->select('a.code_lv4, a.qty_stock, a.qty_booking, b.konversi')
            ->join('new_inventory_4 b', 'a.code_lv4 = b.code_lv4')
            ->get_where('warehouse_stock a', ['a.id_gudang' => 1])
            ->result_array();

        foreach ($stok as $s) {
            $stok_packing = 0;
            if ($s['qty_stock'] > 0 && $s['konversi'] > 0) {
                $stok_packing = $s['qty_stock'] / $s['konversi'];
            }
            $get_stok_pusat[$s['code_lv4']] = [
                'stok'          => $s['qty_stock'],
                'booking'       => $s['qty_booking'],
                'stok_packing'  => $stok_packing,
                'konversi'      => $s['konversi']
            ];
        }

        // Kirim ke view
        $data = [
            'so_number'       => $so_number,
            'header'          => $header,
            'detail'          => $detail,
            'GET_LEVEL4'      => $GET_LEVEL4,
            'GET_STOK_PUSAT'  => $get_stok_pusat
        ];

        $this->template->page_icon('fa fa-cart-plus');
        $this->template->title('Detail Purchase Request : ' . $so_number);
        $this->template->render('detail_planning', $data);
    }
}
