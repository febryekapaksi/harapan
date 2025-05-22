<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Sales_order extends Admin_Controller
{
  //Permission
  protected $viewPermission   = 'Sales_Order.View';
  protected $addPermission    = 'Sales_Order.Add';
  protected $managePermission = 'Sales_Order.Manage';
  protected $deletePermission = 'Sales_Order.Delete';

  public function __construct()
  {
    parent::__construct();

    // $this->load->library(array( 'upload', 'Image_lib'));
    $this->load->model(array(
      'Sales_order/Sales_order_model',
    ));
    date_default_timezone_set('Asia/Bangkok');
  }

  public function index()
  {
    $this->auth->restrict($this->viewPermission);
    $session = $this->session->userdata('app_session');

    $this->template->render('index');
  }

  public function add($id_penawaran)
  {
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();
    if (!$penawaran) {
      show_404();
    }
    $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

    // Kirim data ke view
    $data = [
      'penawaran'         => $penawaran,
      'penawaran_detail'  => $penawaran_detail,
      'customers'         => $this->db->get('master_customers')->result_array(),
      'products'          => $this->db->get('product_costing')->result_array(),
      'payment_terms'     => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
    ];

    $this->template->render('form', $data);
  }

  public function edit($id_so)
  {
    $so = $this->db->get_where('sales_order', ['no_so' => $id_so])->row_array();
    $so_detail = $this->db->get_where('sales_order_detail', ['no_so' => $id_so])->result_array();
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $so['id_penawaran']])->row_array();
    // $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $so['id_penawaran']])->result_array();

    // Kirim data ke view
    $data = [
      'so'                => $so,
      'so_detail'         => $so_detail,
      'penawaran'         => $penawaran,
      // 'penawaran_detail'  => $penawaran_detail,
      'customers'         => $this->db->get('master_customers')->result_array(),
      'products'          => $this->db->get('product_costing')->result_array(),
      'payment_terms'     => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
      'mode'              => 'edit',
    ];

    $this->template->render('form', $data);
  }

  public function save()
  {
    $data = $this->input->post();

    $id = $data['no_so'];
    $is_update = !empty($id);
    $no_so = $is_update ? $id : $this->Sales_order_model->generate_id();

    $header = [
      'no_so'                       => $no_so,
      'id_penawaran'                => $data['id_penawaran'],
      'id_customer'                 => $data['id_customer'],
      'nama_sales'                  => $data['sales'],
      'email_customer'              => $data['email'],
      'payment_term'                => $data['payment_term'],
      'freight'                     => str_replace(',', '', $data['freight']),
      'tgl_so'                      => date('Y-m-d H:i:s', strtotime($data['tgl_so'])),
      'nilai_so'                    => str_replace(',', '', $data['total_penawaran']),
      'total_diskon_persen'         => $data['total_diskon_persen'],
      'total_harga_freight'         => str_replace(',', '', $data['total_harga_freight']),
      'total_harga_freight_exppn'   => str_replace(',', '', $data['total_harga_freight_exppn']),
      'dpp'                         => str_replace(',', '', $data['dpp']),
      'ppn'                         => str_replace(',', '', $data['ppn']),
      'grand_total'                 => str_replace(',', '', $data['grand_total']),
      'status'                      => "WA",
      'due_date_credit'             => date('Y-m-d H:i:s', strtotime($data['due_date_credit'])),
      'credit_limit'                => $data['credit_limit'],
      'outstanding'                 => $data['outstanding'],
      'over_limit'                  => $data['over_limit'],
      'status_credit_limit'         => $data['status_credit_limit'],
    ];

    if ($is_update) {
      $header['modified_by'] = $this->auth->user_id();
      $header['modified_at'] = date('Y-m-d H:i:s');
    } else {
      $header['created_by'] = $this->auth->user_id();
      $header['created_at'] = date('Y-m-d H:i:s');
    }

    $this->db->trans_start();

    if ($is_update) {
      $this->db->where('no_so', $id);
      $this->db->update('sales_order', $header);
      $no_so = $id;
    } else {
      $this->db->insert('sales_order', $header);
      $no_so = $header['no_so']; // pakai ID yang baru dibuat
    }

    // Hapus dan simpan ulang product
    if ($is_update) {
      $this->db->delete('sales_order_detail', ['no_so' => $no_so]);
    }
    if (isset($_POST['product']) && is_array($_POST['product'])) {
      $detail = [];
      foreach ($_POST['product'] as $pro) {
        // Buat update warehouse
        $code_lv4 = $pro['code_lv4'];
        $stok_awal = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

        if ($stok_awal) {
          $booking_awal     = floatval($stok_awal['qty_booking']);
          $qty_free_awal    = floatval($stok_awal['qty_free']);
          $use_free_lama    = floatval($stok_awal['use_qty_free']);

          // Kembalikan qty_free ke posisi sebelum insert
          $qty_free_reset   = $qty_free_awal + $use_free_lama;

          // Rekalkulasi
          if ($is_update) {
            $qty_booking    = $pro['qty'];
          } else {
            $qty_booking    = $booking_awal + floatval($pro['qty']);
          }
          $use_free_baru    = floatval($pro['use_qty_free']);
          $qty_free_baru    = $qty_free_reset - $use_free_baru;

          $arr_stok = [
            'qty_booking'   => $qty_booking,
            'use_qty_free'  => $use_free_baru,
            'qty_free'      => $qty_free_baru
          ];

          $this->db->where('code_lv4', $code_lv4);
          $this->db->update('warehouse_stock', $arr_stok);

          // insert ke so detail 
          $detail[] = [
            'no_so'             => $no_so,
            'id_penawaran'      => $pro['id_penawaran'],
            'id_product'        => $pro['id_product'],
            'product'           => $pro['product_name'],
            'qty_order'         => $pro['qty'],
            'qty_free'          => $qty_free_baru,
            'use_qty_free'      => $use_free_baru,
            'qty_propose'       => $pro['pr'],
            'harga_beli'        => str_replace(',', '', $pro['harga_beli']),
            'product_price'     => str_replace(',', '', $pro['harga_penawaran']),
            'diskon_persen'     => $pro['diskon'],
            'pengiriman'        => $pro['pengiriman'],
            'total_harga'       => str_replace(',', '', $pro['total']),
            'created_by'        => $this->auth->user_id(),
            'created_at'        => date('Y-m-d H:i:s'),
          ];
        }
      }

      if (!empty($detail)) {
        $this->db->insert_batch('sales_order_detail', $detail);
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
        'status'      => 1
      );
    }

    echo json_encode($status);
  }

  // buat proses DEAL SO
  public function deal_so() {}

  // PRINTOUT
  public function print_so($no_so)
  {
    $this->template->page_icon('fa fa-list');

    // Ambil data sales order utama + penawaran + customer
    $get_so = $this->db
      ->select('so.*, c.*, p.quotation_date, p.total_penawaran, p.tipe_bayar,
                  e1.nm_karyawan AS created_by,
                  e2.nm_karyawan AS approved_by')
      ->from('sales_order so')
      ->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left')
      ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
      ->join('employee e1', 'e1.id = so.created_by', 'left')
      ->join('employee e2', 'e2.id = so.approved_by', 'left')
      ->where('so.no_so', $no_so)
      ->get()
      ->row();

    // Ambil detail item SO dan unit dari ms_satuan
    $get_so_detail = $this->db
      ->select('d.*, i.id_unit, s.code AS unit')
      ->from('sales_order_detail d')
      ->join('product_costing p', 'p.id = d.id_product', 'left')
      ->join('new_inventory_4 i', 'i.code_lv4 = p.code_lv4', 'left')
      ->join('ms_satuan s', 's.id = i.id_unit', 'left')
      ->where('d.no_so', $no_so)
      ->order_by('d.id', 'ASC')
      ->get()
      ->result();

    $data = [
      'data_so' => $get_so,
      'data_so_detail' => $get_so_detail
    ];

    $this->load->view('print_so', ['results' => $data]);
  }

  // SERVERSIDE
  public function data_side_penawaran()
  {
    $this->Sales_order_model->get_json_penawaran();
  }

  public function get_free_stok()
  {
    $code_lv4 = $this->input->post('code_lv4');

    $stock = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

    if ($stock) {
      echo json_encode([
        'error' => false,
        'qty_free' => number_format($stock['qty_free'])
      ]);
    } else {
      echo json_encode([
        'error' => true,
        'message' => 'Free Stok tidak ditemukan'
      ]);
    }
  }
}
