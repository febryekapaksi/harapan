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
      'penawaran' => $penawaran,
      'penawaran_detail' => $penawaran_detail,
      'customers' => $this->db->get('master_customers')->result_array(),
      'products' => $this->db->get('product_costing')->result_array(),
      'payment_terms' => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
    ];

    $this->template->render('form', $data);
  }

  public function edit($id_so)
  {
    $so = $this->db->get_where('sales_order', ['no_so' => $id_so])->row_array();
    $so_detail = $this->db->get_where('sales_order_detail', ['no_so' => $id_so])->result_array();
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $so['id_penawaran']])->row_array();

    // Kirim data ke view
    $data = [
      'so' => $so,
      'so_detail' => $so_detail,
      'penawaran' => $penawaran,
      'customers' => $this->db->get('master_customers')->result_array(),
      'products' => $this->db->get('product_costing')->result_array(),
      'payment_terms' => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
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
      'no_so'                 => $no_so,
      'id_penawaran'          => $data['id_penawaran'],
      'id_customer'           => $data['id_customer'],
      'nama_sales'            => $data['sales'],
      'email_customer'        => $data['email'],
      'payment_term'          => $data['payment_term'],
      'tgl_so'                => date('Y-m-d H:i:s', strtotime($data['tgl_so'])),
      'nilai_so'              => str_replace(',', '', $data['total_penawaran']),
      'total_diskon_persen'   => $data['total_diskon_persen'],
      'dpp'                   => str_replace(',', '', $data['dpp']),
      'ppn'                   => str_replace(',', '', $data['ppn']),
      'grand_total'           => str_replace(',', '', $data['grand_total']),
      'status'                => "WA",
      'due_date_credit'       => date('Y-m-d H:i:s', strtotime($data['due_date_credit'])),
      'credit_limit'          => $data['credit_limit'],
      'outstanding'           => $data['outstanding'],
      'over_limit'            => $data['over_limit'],
      'status_credit_limit'   => $data['status_credit_limit'],
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
      $this->db->where('id', $id);
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
        $detail[] = [
          'no_so'             => $no_so,
          'id_penawaran'      => $pro['id_penawaran'],
          'id_product'        => $pro['id_product'],
          'product'           => $pro['product_name'],
          'qty_order'         => $pro['qty'],
          'product_price'     => str_replace(',', '', $pro['harga_penawaran']),
          'diskon_persen'     => $pro['diskon'],
          'pengiriman'        => $pro['pengiriman'],
          'total_harga'       => str_replace(',', '', $pro['total']),
          'created_by'        => $this->auth->user_id(),
          'created_at'        => date('Y-m-d H:i:s'),
        ];
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

  // SERVERSIDE
  public function data_side_penawaran()
  {
    $this->Sales_order_model->get_json_penawaran();
  }
}
