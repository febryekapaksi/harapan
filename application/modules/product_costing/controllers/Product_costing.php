<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_costing extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Product_Costing.View';
    protected $addPermission      = 'Product_Costing.Add';
    protected $managePermission   = 'Product_Costing.Manage';
    protected $deletePermission   = 'Product_Costing.Delete';

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
            // Ambil revisi terakhir
            $last = $this->db->select('revisi')->get_where('product_costing', ['id' => $id])->row();
            $header['revisi'] = $last ? intval($last->revisi) + 1 : 1;
            $header['modified_by'] = $this->auth->user_id();
            $header['modified_at'] = date('Y-m-d H:i:s');
        } else {
            $header['created_by'] = $this->auth->user_id();
            $header['created_at'] = date('Y-m-d H:i:s');
            $header['revisi'] = 0;
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

    public function generate_price_list_ajax()
    {
        if ($this->generate_price_list()) {
            echo json_encode([
                'error' => false,
                'message' => 'Kalkulasi berhasil diperbarui.',
                'last_update' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Produk atau toko kosong.'
            ]);
        }
    }


    public function generate_price_list()
    {
        $products = $this->db->get_where('product_costing', ['status' => 'A'])->result_array();
        $tokoList = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();

        if (empty($products) || empty($tokoList)) {
            return false;
        }

        $this->db->truncate('master_kalkulasi_price_list');

        foreach ($products as $product) {
            $harga_awal = $product['propose_price'];
            $current_cash = $harga_awal;

            foreach ($tokoList as $index => $toko) {
                $cash_percent = floatval($toko['cash']) / 100;
                $tempo_percent = floatval($toko['tempo']) / 100;

                $current_cash = $current_cash + ($current_cash * $cash_percent);
                $current_tempo = $current_cash + ($current_cash * $tempo_percent);

                $this->db->insert('master_kalkulasi_price_list', [
                    'id_product' => $product['id'],
                    'product_name' => $product['product_name'],
                    'toko' => $toko['nama'],
                    'cash' => floor($current_cash),
                    'tempo' => floor($current_tempo),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $current_cash = $current_tempo;
            }
        }

        return true;
    }


    public function list_price_list()
    {
        // Ambil semua toko dengan urutan (untuk header)
        $tokoList = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();

        // Ambil semua kalkulasi dari DB
        $rows = $this->db->get('master_kalkulasi_price_list')->result_array();

        // Kelompokkan berdasarkan produk
        $groupedData = [];
        foreach ($rows as $row) {
            $groupedData[$row['product_name']][$row['toko']] = [
                'cash' => $row['cash'],
                'tempo' => $row['tempo']
            ];
        }

        $this->template->render('kalkulasi_price_list', [
            'tokoList' => $tokoList,
            'groupedData' => $groupedData
        ]);
    }

    public function master_persentase()
    {
        $data['persentase'] = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();
        $this->template->render('master_persentase', $data);
    }

    public function save_persentase()
    {
        $data = $this->input->post('data');

        // Bersihkan semua dulu
        $this->db->truncate('master_persentase');

        foreach ($data as $item) {
            if (!isset($item['nama']) || trim($item['nama']) === '') continue;

            $this->db->insert('master_persentase', [
                'nama' => $item['nama'],
                'urutan' => $item['urutan'],
                'cash' => $item['cash'],
                'tempo' => $item['tempo']
            ]);
        }

        $this->generate_price_list();

        echo json_encode([
            'status' => 1,
            'message' => 'Data berhasil diperbarui.'
        ]);
    }


    public function data_side_product_costing()
    {
        $this->product_costing_model->get_json_product_costing();
    }
}
