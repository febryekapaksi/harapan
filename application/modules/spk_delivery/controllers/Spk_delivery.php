<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Spk_delivery extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'SPK_Delivery.View';
  protected $addPermission    = 'SPK_Delivery.Add';
  protected $managePermission = 'SPK_Delivery.Manage';
  protected $deletePermission = 'SPK_Delivery.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->library(array('upload', 'Image_lib'));
    $this->load->model(array(
      'Spk_delivery/spk_delivery_model'
    ));

    date_default_timezone_set('Asia/Bangkok');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $this->template->page_icon('fa fa-clipboard');
    $session  = $this->session->userdata('app_session');

    $listSO = $this->db->get_where('sales_order', array('status' => 'A'))->result_array();
    $data = [
      'listSO' => $listSO
    ];
    history("View data spk delivery");
    $this->template->title('SPK Delivery');
    $this->template->render('index', $data);
  }

  public function data_side_spk_deliv()
  {
    $this->spk_delivery_model->data_side_spk_deliv();
  }

  public function add($no_so = null)
  {
    // Cek apakah no_so dikirim
    if (!$no_so) {
      show_404(); // atau bisa redirect dengan flash message
    }

    // Ambil data sales_order berdasarkan no_so
    $so = $this->db
      ->select('s.*, c.name_customer, c.id_customer, c.address_office')
      ->from('sales_order s')
      ->join('master_customers c', 'c.id_customer = s.id_customer')
      ->where('s.no_so', $no_so)
      ->where('s.status', 'A') // optional: hanya status aktif
      ->get()
      ->row_array();

    // Jika tidak ditemukan
    if (!$so) {
      show_error("Data Sales Order dengan nomor {$no_so} tidak ditemukan.", 404);
    }

    // Siapkan data ke view
    $data = [
      'so' => $so,
    ];

    $this->template->page_icon('fa fa-truck');
    $this->template->title("Add SPK Delivery for SO {$no_so}");
    $this->template->render('form', $data);
  }


  public function get_so()
  {
    $id_customer = $this->input->get('id_customer', TRUE);

    $data = $this->db
      ->where('id_customer', $id_customer)
      ->where('status', 'A') // Tambahan filter status
      ->get('sales_order')
      ->result();

    echo "<option value=''>-- Pilih --</option>";
    foreach ($data as $so) {
      echo "<option value='$so->no_so'>" . $so->no_so . " - " . date('d/m/Y', strtotime($so->tgl_so)) . "</option>";
    }
  }

  public function get_so_detail()
  {
    $no_so = $this->input->get('no_so', TRUE);

    $data = $this->db
      ->select('
            sod.*,
            (sod.qty_order - sod.qty_spk) AS qty_belum_spk
        ')
      ->from('sales_order_detail sod')
      ->where('sod.no_so', $no_so)
      ->where('(sod.qty_order - sod.qty_spk) >', 0) // hanya yang masih bisa di-SPK
      ->get()
      ->result();

    echo json_encode($data);
  }

  public function get_spk_detail()
  {
    $no_delivery = $this->input->get('no_delivery', TRUE);

    if (!$no_delivery) {
      show_404();
    }

    $this->db->select('
        spd.*,
        sod.product as product,
        so.no_so,
        c.name_customer as customer
    ');
    $this->db->from('spk_delivery_detail spd');
    $this->db->join('sales_order_detail sod', 'sod.id = spd.id_so_det', 'left');
    $this->db->join('sales_order so', 'so.no_so = sod.no_so', 'left');
    $this->db->join('master_customers c', 'c.id_customer = so.id_customer', 'left');
    $this->db->where('spd.no_delivery', $no_delivery);
    $this->db->order_by('spd.no_delivery');

    $data = $this->db->get()->result();

    echo json_encode($data);
  }

  public function save()
  {
    $data = $this->input->post();

    $id_customer      = $data['id_customer'];
    $no_so            = $data['no_so'];
    $tanggal_spk      = !empty($data['tanggal_spk']) ? date('Y-m-d', strtotime($data['tanggal_spk'])) : NULL;
    $tanggal_kirim    = !empty($data['tanggal_kirim']) ? date('Y-m-d', strtotime($data['tanggal_kirim'])) : NULL;
    $delivery_address = $data['delivery_address'];
    $detail           = $data['detail'];

    // Generate nomor SPK baru
    $Ym = date('ym');
    $SQL = "SELECT MAX(no_delivery) as maxP FROM spk_delivery WHERE no_delivery LIKE 'SPK" . $Ym . "%'";
    $result = $this->db->query($SQL)->row_array();
    $angkaUrut = isset($result['maxP']) ? $result['maxP'] : null;
    $lastNum = ($angkaUrut) ? (int)substr($angkaUrut, 7, 4) : 0;
    $no_delivery = 'SPK' . $Ym . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

    $pengiriman = isset($detail[0]['pengiriman']) ? $detail[0]['pengiriman'] : null;


    // Header insert
    $ArrHeader = [
      'no_delivery'      => $no_delivery,
      'id_customer'      => $id_customer,
      'no_so'            => $no_so,
      'tanggal_spk'      => $tanggal_spk,
      'tanggal_kirim'    => $tanggal_kirim,
      'delivery_address' => $delivery_address,
      'pengiriman'       => $pengiriman,
      'created_by'       => $this->id_user,
      'created_date'     => $this->datetime
    ];

    $ArrDetail = [];

    $this->db->trans_start();
    $this->db->insert('spk_delivery', $ArrHeader);

    foreach ($detail as $key => $value) {
      $id_so_det   = $value['id_so_det'];
      $id_product  = $value['id_product'];
      $qty_spk     = (float)str_replace(',', '', $value['qty_spk']);

      // Ambil qty_order dari detail SO
      $so_det = $this->db->get_where('sales_order_detail', ['id' => $id_so_det])->row_array();
      $qty_order = (float)$so_det['qty_order'];

      // Hitung total qty_spk dari semua spk_delivery_detail untuk id_so_det
      $spk_sum = $this->db->select_sum('qty_spk')
        ->get_where('spk_delivery_detail', ['id_so_det' => $id_so_det])
        ->row();
      $total_spk_now = (float)$spk_sum->qty_spk + $qty_spk;

      // Hitung ulang sisa belum SPK
      $qty_belum_spk = max(0, $qty_order - $total_spk_now);

      // Insert detail SPK (tanpa menyalin qty_so karena sudah ada di master SO)
      $ArrDetail[] = [
        'no_delivery'     => $no_delivery,
        'no_so'           => $no_so,
        'id_so_det'       => $id_so_det,
        'id_product'      => $id_product,
        'qty_so'          => $qty_order,
        'qty_booking'     => $value['qty_booking'],
        'qty_spk'         => $qty_spk,
        'qty_belum_muat'  => $qty_spk
      ];

      // Update qty_spk dan status_planning di sales_order_detail
      $this->db->update('sales_order_detail', [
        'qty_spk'         => $total_spk_now,
        'qty_belum_spk'   => $qty_belum_spk,
        'status_planning' => ($qty_belum_spk > 0) ? 0 : 1,
      ], ['id' => $id_so_det]);
    }

    // Insert detail SPK
    if (!empty($ArrDetail)) {
      $this->db->insert_batch('spk_delivery_detail', $ArrDetail);
    }

    // Hitung status SPK untuk header
    $summary = $this->db->select('SUM(qty_order) as total_order, SUM(qty_spk) as total_spk')
      ->get_where('sales_order_detail', ['no_so' => $no_so])
      ->row_array();

    $status_spk = 'Belum SPK';
    if ((float)$summary['total_spk'] >= (float)$summary['total_order']) {
      $status_spk = 'SPK Lengkap';
    } elseif ((float)$summary['total_spk'] > 0) {
      $status_spk = 'SPK Sebagian';
    }

    // Update status header SO
    $this->db->update('sales_order', ['status_spk' => $status_spk], ['no_so' => $no_so]);

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      echo json_encode([
        'pesan'  => 'Save gagal disimpan ...',
        'status' => 0
      ]);
    } else {
      $this->db->trans_commit();
      history("Create SPK Delivery: " . $no_delivery);
      echo json_encode([
        'pesan'  => 'Save berhasil disimpan. Thanks ...',
        'status' => 1
      ]);
    }
  }


  public function data_side_spk_reprint()
  {
    $this->spk_delivery_model->data_side_spk_reprint();
  }

  //TRASH

  // $QUERY = "SELECT
  //                 a.no_so,
  //                 a.id_penawaran,
  //                 c.name_customer,
  //                 a.project,
  //                 a.delivery_date,
  //                 a.invoice_address
  //               FROM
  //                 sales_order a
  //                 LEFT JOIN penawaran b ON a.id_penawaran = b.id_penawaran
  //                 LEFT JOIN master_customers c ON b.id_customer = c.id_customer
  //               WHERE a.status = 'A' AND a.no_so = '" . $no_so . "' ";
  // $getData = $this->db->query($QUERY)->result_array();

  // $getDetail = $this->db
  //   ->select('a.*, SUM(b.qty_delivery) AS qty_delivery')
  //   ->group_by('a.id')
  //   ->join('spk_delivery_detail b', 'a.id = b.id_so_det', 'left')
  //   ->get_where('sales_order_detail a', array('a.no_so' => $no_so))->result_array();

  // $data = [
  //   'getData' => $getData,
  //   'getDetail' => $getDetail
  // ];

  // public function print_spk()
  // {
  //   $kode  = $this->uri->segment(3);
  //   $data_session  = $this->session->userdata;
  //   $session        = $this->session->userdata('app_session');
  //   $printby    = $session['id_user'];

  //   $data_url    = base_url();
  //   $Split_Beda    = explode('/', $data_url);
  //   $Jum_Beda    = count($Split_Beda);
  //   $Nama_Beda    = $Split_Beda[$Jum_Beda - 2];

  //   $getData        = $this->db->get_where('spk_delivery', array('no_delivery' => $kode))->result_array();
  //   $getDataDetail  = $this->db->select('a.*, b.no_bom')->join('sales_order_detail b', 'a.id_so_det=b.id')->get_where('spk_delivery_detail a', array('a.no_delivery' => $kode))->result_array();

  //   $data = array(
  //     'Nama_Beda' => $Nama_Beda,
  //     'printby' => $printby,
  //     'getData' => $getData,
  //     'getDataDetail' => $getDataDetail,
  //     'GET_DET_Lv4' => get_inventory_lv4(),
  //     'kode' => $kode
  //   );

  //   history('Print spk delivery ' . $kode);
  //   $this->load->view('print_spk', $data);
  // }

  // public function request_to_subgudang()
  // {
  //   $data         = $this->input->post();
  //   $session      = $this->session->userdata('app_session');

  //   $id        = $data['id'];
  //   $detail    = $data['detail'];
  //   $mix1      = str_replace(',', '', $data['mix1']);
  //   $mix2      = str_replace(',', '', $data['mix2']);
  //   $mix3      = str_replace(',', '', $data['mix3']);
  //   $mix4      = str_replace(',', '', $data['mix4']);
  //   $mix5      = str_replace(',', '', $data['mix5']);
  //   $mix6      = str_replace(',', '', $data['mix6']);
  //   $mix7      = str_replace(',', '', $data['mix7']);
  //   $getdata = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();

  //   $ArrUpdateMat = [];
  //   foreach ($detail as $key => $value) {
  //     $ArrUpdateMat[$key]['id'] = $value['id'];
  //     $ArrUpdateMat[$key]['mix1'] = (!empty($value['mix1'])) ? $value['mix1'] : null;
  //     $ArrUpdateMat[$key]['mix2'] = (!empty($value['mix2'])) ? $value['mix2'] : null;
  //     $ArrUpdateMat[$key]['mix3'] = (!empty($value['mix3'])) ? $value['mix3'] : null;
  //     $ArrUpdateMat[$key]['mix4'] = (!empty($value['mix4'])) ? $value['mix4'] : null;
  //     $ArrUpdateMat[$key]['mix5'] = (!empty($value['mix5'])) ? $value['mix5'] : null;
  //     $ArrUpdateMat[$key]['mix6'] = (!empty($value['mix6'])) ? $value['mix6'] : null;
  //     $ArrUpdateMat[$key]['mix7'] = (!empty($value['mix7'])) ? $value['mix7'] : null;
  //   }

  //   $ArrUpdate = array(
  //     'sts_request' => 'Y',
  //     'mix1' => $mix1,
  //     'mix2' => $mix2,
  //     'mix3' => $mix3,
  //     'mix4' => $mix4,
  //     'mix5' => $mix5,
  //     'mix6' => $mix6,
  //     'mix7' => $mix7,
  //     'request_by' => $this->id_user,
  //     'request_date' => $this->datetime
  //   );

  //   $this->db->where('id', $id);
  //   $this->db->update('so_internal_spk', $ArrUpdate);

  //   $this->db->update_batch('so_internal_spk_material', $ArrUpdateMat, 'id');

  //   $Arr_Data  = array(
  //     'status'    => 1,
  //     'id'    => $id,
  //     'kode_det'    => $getdata[0]['kode_det'],
  //   );
  //   echo json_encode($Arr_Data);
  // }

  // public function plan_mixing_add($id)
  // {
  //   $this->auth->restrict($this->viewPermission);
  //   $session  = $this->session->userdata('app_session');

  //   $getDataSPK = $this->db->get_where('so_internal_spk', array('id' => $id))->result_array();
  //   $getData = $this->db->get_where('so_internal', array('id' => $getDataSPK[0]['id_so']))->result_array();
  //   $getMaterialMixing    = $this->db->select('code_material, weight AS berat, id')->where('kode_det', $getDataSPK[0]['kode_det'])->get_where('so_internal_spk_material', array('type_name' => 'mixing'))->result_array();


  //   $data = [
  //     'id' => $id,
  //     'getDataSPK' => $getDataSPK,
  //     'getData' => $getData,
  //     'GET_DET_Lv4' => get_inventory_lv4(),
  //     'getMaterialMixing' => $getMaterialMixing,
  //   ];

  //   $this->template->title('Plan Mixing');
  //   $this->template->render('plan_mixing', $data);
  // }

  // //Re-Print SPK
  // public function reprint_spk()
  // {
  //   $this->auth->restrict($this->viewPermission);
  //   $session  = $this->session->userdata('app_session');
  //   $this->template->page_icon('fa fa-users');

  //   $this->template->title('SPK Re-Print');
  //   $this->template->render('reprint_spk');
  // }
}
