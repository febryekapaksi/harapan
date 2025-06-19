<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Loading extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Loading.View';
    protected $addPermission    = 'Loading.Add';
    protected $managePermission = 'Loading.Manage';
    protected $deletePermission = 'Loading.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Loading/loading_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Muat Kendaraan');
        $this->template->page_icon('fa fa-truck');
        $this->template->render('index');
    }

    public function data_side_loading()
    {
        $this->loading_model->data_side_loading();
    }

    public function get_detail_loading()
    {
        $no_loading = $this->input->get('no_loading', TRUE);

        if (!$no_loading) {
            show_404();
        }

        $data = $this->db
            ->where('no_loading', $no_loading)
            ->order_by('no_delivery')
            ->get('loading_delivery_detail')
            ->result();

        echo json_encode($data);
    }

    public function add()
    {
        $this->template->title('Atur Muatan');
        $this->template->page_icon('fa fa-clipboard');

        $data = [
            'kendaraan' => $this->db->get('master_kendaraan')->result(),
        ];

        $this->template->render('form', $data);
    }

    public function edit($id)
    {
        // Cek apakah data ada
        $loading = $this->db->get_where('loading_delivery', ['id' => $id])->row_array();

        if (!$loading) {
            show_404(); // Jika tidak ada, tampilkan error 404
        }

        $detail = $this->db->get_where('loading_delivery_detail', ['no_loading' => $loading['no_loading']])->result_array();

        //data nya sudah dibuat ke surat jalan belom?
        $usedPairs = $this->db
            ->select('no_so, no_delivery')
            ->from('surat_jalan')
            ->where('no_loading', $loading['no_loading'])
            ->get()
            ->result_array();

        $usedKeys = array_map(function ($row) {
            return $row['no_so'] . '|' . $row['no_delivery'];
        }, $usedPairs);

        // Kirim data ke view
        $data = [
            'kendaraan' => $this->db->get('master_kendaraan')->result(),
            'loading'   => $loading,
            'detail'    => $detail,
            'usedKeys'  => $usedKeys
        ];
        // View form edit
        $this->template->render('form', $data);
    }

    public function get_spk()
    {
        $pengiriman = $this->input->get('pengiriman', TRUE);

        $data = $this->db
            ->select('
            s.no_delivery,
            s.no_so,
            s.pengiriman,
            DATE_FORMAT(s.tanggal_spk, "%d %M %Y") AS tanggal_spk,
            c.name_customer,
            d.id,
            d.id_product,
            p.nama,
            p.weight,
            d.qty_spk,
            (p.weight * d.qty_spk) AS jumlah_berat
        ')
            ->from('spk_delivery_detail d')
            ->join('spk_delivery s', 's.no_delivery = d.no_delivery')
            ->join('master_customers c', 'c.id_customer = s.id_customer')
            ->join('new_inventory_4 p', 'p.code_lv4 = d.id_product')
            // LEFT JOIN ke tabel loading_delivery_detail
            ->join('loading_delivery_detail l', 'l.no_delivery = s.no_delivery', 'left')
            // Ambil hanya yang belum pernah ada di loading_delivery_detail
            ->where('s.pengiriman', $pengiriman)
            ->where('l.no_delivery IS NULL')
            ->order_by('s.no_delivery')
            ->get()
            ->result();

        echo json_encode($data);
    }

    public function save()
    {
        $post = $this->input->post();
        $detail = $post['detail'];

        $is_edit = isset($post['id_loading']) && !empty($post['id_loading']);
        $no_loading = $is_edit ? $post['id_loading'] : $this->_generateNoLoading();

        $ArrHeader = [
            'no_loading'    => $no_loading,
            'pengiriman'    => $post['pengiriman'],
            'nopol'         => $post['kendaraan'],
            'kapasitas'     => str_replace(',', '', $post['kapasitas']),
            'total_berat'   => str_replace(',', '', $post['total_berat']),
            'tanggal_muat'  => date('Y-m-d H:i:s', strtotime($post['tanggal_muat'])),
        ];

        if ($is_edit) {
            $ArrHeader['updated_by'] = $this->auth->user_id();
            $ArrHeader['updated_at'] = date('Y-m-d H:i:s');
        } else {
            $ArrHeader['created_by'] = $this->auth->user_id();
            $ArrHeader['created_at'] = date('Y-m-d H:i:s');
        }

        $ArrDetail = [];

        foreach ($detail as $key => $value) {
            $no_delivery = $value['no_delivery'];

            $ArrDetail[$key]['no_loading']      = $no_loading;
            $ArrDetail[$key]['no_delivery']     = $no_delivery;
            $ArrDetail[$key]['id_spk_detail']   = $value['id_spk_detail'];
            $ArrDetail[$key]['no_so']           = $value['no_so'];
            $ArrDetail[$key]['customer']        = $value['customer'];
            $ArrDetail[$key]['id_product']      = $value['id_product'];
            $ArrDetail[$key]['product']         = $value['product'];
            $ArrDetail[$key]['qty_spk']         = $value['qty_spk'];
            $ArrDetail[$key]['jumlah_berat']    = $value['jumlah_berat'];

            // Update status SPK
            $this->db->update('spk_delivery', ['status' => 'LOADING'], ['no_delivery' => $no_delivery]);
        }

        $this->db->trans_start();

        if ($is_edit) {
            $this->db->update('loading_delivery', $ArrHeader, ['no_loading' => $no_loading]);

            // Hapus detail lama, insert ulang
            $this->db->delete('loading_delivery_detail', ['no_loading' => $no_loading]);
            if (!empty($ArrDetail)) {
                $this->db->insert_batch('loading_delivery_detail', $ArrDetail);
            }
        } else {
            $this->db->insert('loading_delivery', $ArrHeader);
            if (!empty($ArrDetail)) {
                $this->db->insert_batch('loading_delivery_detail', $ArrDetail);
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data  = ['pesan' => 'Save gagal disimpan ...', 'status' => 0];
        } else {
            $this->db->trans_commit();
            $Arr_Data  = ['pesan' => 'Save berhasil disimpan. Thanks ...', 'status' => 1];
            history(($is_edit ? "Update" : "Create") . " Muat Kendaraan : " . $no_loading);
        }

        echo json_encode($Arr_Data);
    }

    public function delete_detail()
    {
        $id = $this->input->post('id', TRUE);

        if ($id) {
            $this->db->where('id', $id);
            $this->db->delete('loading_delivery_detail');
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'ID not provided']);
        }
    }

    public function get_view()
    {
        $no_loading = $this->input->get('no_loading', TRUE);

        $header = $this->db->get_where('loading_delivery', ['no_loading' => $no_loading])->row_array();
        $detail = $this->db->get_where('loading_delivery_detail', ['no_loading' => $no_loading])->result_array();

        echo json_encode([
            'header' => $header,
            'detail' => $detail
        ]);
    }


    // Private Function Section 

    private function _generateNoLoading()
    {
        $Ym = date('ym');
        $SQL = "SELECT MAX(no_loading) as maxM FROM loading_delivery WHERE no_loading LIKE 'MK" . $Ym . "%'";
        $result = $this->db->query($SQL)->result_array();
        $angkaUrut = $result[0]['maxM'];
        $urutan = (int)substr($angkaUrut, 6, 4);
        $urutan++;
        return "MK" . $Ym . sprintf('%04s', $urutan);
    }
}
