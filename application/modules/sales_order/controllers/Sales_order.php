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

    $this->template->page_icon('fa fa-shopping-cart');
    $this->template->render('index');
  }

  public function add($id_penawaran)
  {
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();
    if (!$penawaran) {
      show_404();
    }
    $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

    $products = $this->db
      ->select('
                    pc.id,
                    pc.product_name,
                    pc.propose_price,
                    pc.harga_beli,
                    pc.dropship_price,
                    pc.dropship_tempo,
                    ni4.code_lv4
                    ')
      ->from('product_costing pc')
      ->join('new_inventory_4 ni4', 'ni4.code_lv4 = pc.code_lv4', 'left')
      ->where('pc.status', 'A')
      ->where('ni4.deleted_date', null)
      ->where('ni4.deleted_by', null)
      ->get()
      ->result_array();

    // Kirim data ke view
    $data = [
      'penawaran'         => $penawaran,
      'penawaran_detail'  => $penawaran_detail,
      'customers'         => $this->db->get('master_customers')->result_array(),
      'products'          => $products,
      'payment_terms'     => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
      'mode'              => 'add',
    ];

    $this->template->title('Create Sales Order');
    $this->template->page_icon('fa fa-shopping-cart');
    $this->template->render('form', $data);
  }

  public function cancel()
  {
    $post = $this->input->post();
    $id_penawaran = $post['id_penawaran'];

    // Cek apakah data penawaran tersedia
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

    if (!$penawaran) {
      echo json_encode([
        'status' => 0,
        'pesan'  => 'Data penawaran tidak ditemukan.'
      ]);
      return;
    }

    // Siapkan data untuk update loss
    $update = [
      'approved_at_manager'   => null,
      'approved_by_manager'   => null,
      'status'                => 'WA',
      'status_draft'          => 0
    ];

    // Lakukan update ke tabel penawaran
    $this->db->where('id_penawaran', $id_penawaran);
    $this->db->update('penawaran', $update);

    echo json_encode([
      'status' => 1,
      'pesan'  => 'Sales Order dibatalakan.'
    ]);
  }

  public function edit($id_so)
  {
    $so = $this->db->get_where('sales_order', ['no_so' => $id_so])->row_array();
    $so_detail = $this->db->get_where('sales_order_detail', ['no_so' => $id_so])->result_array();
    $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $so['id_penawaran']])->row_array();
    // $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $so['id_penawaran']])->result_array();

    $products = $this->db
      ->select('
                    pc.id,
                    pc.product_name,
                    pc.propose_price,
                    pc.harga_beli,
                    pc.dropship_price,
                    pc.dropship_tempo,
                    ni4.code_lv4
                    ')
      ->from('product_costing pc')
      ->join('new_inventory_4 ni4', 'ni4.code_lv4 = pc.code_lv4', 'left')
      ->where('pc.status', 'A')
      ->where('ni4.deleted_date', null)
      ->where('ni4.deleted_by', null)
      ->get()
      ->result_array();


    // Kirim data ke view
    $data = [
      'so'                => $so,
      'so_detail'         => $so_detail,
      'penawaran'         => $penawaran,
      // 'penawaran_detail'  => $penawaran_detail,
      'customers'         => $this->db->get('master_customers')->result_array(),
      'products'          => $products,
      'payment_terms'     => $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array(),
      'mode'              => 'edit',
    ];

    $this->template->title('Edit Sales Order');
    $this->template->page_icon('fa fa-shopping-cart');
    $this->template->render('form', $data);
  }

  public function deal($id_so)
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
      'mode'              => 'deal',
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
      'diskon_khusus'               => str_replace(',', '', $data['diskon_khusus']),
      'total_harga_freight'         => str_replace(',', '', $data['total_harga_freight']),
      'total_harga_freight_exppn'   => str_replace(',', '', $data['total_harga_freight_exppn']),
      'dpp'                         => str_replace(',', '', $data['dpp']),
      'ppn'                         => str_replace(',', '', $data['ppn']),
      'grand_total'                 => str_replace(',', '', $data['grand_total']),
      'status'                      => 'A', // langsung set A (Approved/Deal)
      'approved_by'                 => $this->auth->user_id(),
      'approved_at'                 => date('Y-m-d H:i:s'),
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
      $no_so = $header['no_so'];
    }

    // Hapus dan simpan ulang product
    if ($is_update) {
      $this->db->delete('sales_order_detail', ['no_so' => $no_so]);
    }

    $arr_kartu_stok = [];

    if (isset($_POST['product']) && is_array($_POST['product'])) {
      $detail = [];
      foreach ($_POST['product'] as $pro) {
        $code_lv4 = $pro['code_lv4'];
        $stok = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

        if ($stok) {
          $qty_booking_lama = floatval($stok['qty_booking']);
          $qty_free_lama    = floatval($stok['qty_free']);
          $use_qty_free_lama = floatval($stok['use_qty_free']);

          $qty_free_reset   = $qty_free_lama + $use_qty_free_lama;
          $qty_booking_baru = $is_update ? $pro['qty'] : $qty_booking_lama + $pro['qty'];

          $use_qty_free_baru = floatval($pro['use_qty_free']);
          $qty_free_baru     = $qty_free_reset - $use_qty_free_baru;

          // update warehouse
          $this->db->where('code_lv4', $code_lv4)->update('warehouse_stock', [
            'qty_booking'  => $qty_booking_baru,
            'use_qty_free' => $use_qty_free_baru,
            'qty_free'     => $qty_free_baru
          ]);

          // insert sales_order_detail
          $detail[] = [
            'no_so'             => $no_so,
            'id_penawaran'      => $pro['id_penawaran'],
            'id_product'        => $pro['id_product'],
            'product'           => $pro['product_name'],
            'qty_order'         => $pro['qty'],
            'qty_free'          => $qty_free_baru,
            'use_qty_free'      => $use_qty_free_baru,
            'qty_propose'       => $pro['pr'],
            'harga_beli'        => str_replace(',', '', $pro['harga_beli']),
            'price_list'        => str_replace(',', '', $pro['price_list']),
            'harga_penawaran'   => str_replace(',', '', $pro['harga_penawaran']),
            'diskon_persen'     => $pro['diskon'],
            'diskon_nilai'      => $pro['diskon_nilai'],
            'pengiriman'        => $pro['pengiriman'],
            'total_harga'       => str_replace(',', '', $pro['total']),
            'total_pl'          => str_replace(',', '', $pro['total_pl']),
            'created_by'        => $this->auth->user_id(),
            'created_at'        => date('Y-m-d H:i:s'),
          ];

          // insert kartu_stok
          $arr_kartu_stok[] = [
            'no_transaksi'      => $no_so,
            'transaksi'         => "Sales Order",
            'tgl_transaksi'     => $header['tgl_so'],
            'code_lv4'          => $code_lv4,
            'nm_product'        => $pro['product_name'],
            'qty'               => floatval($stok['qty_stock']),
            'qty_book'          => $qty_booking_lama - $use_qty_free_baru,
            'qty_free'          => $qty_free_lama + $use_qty_free_baru,
            'qty_transaksi'     => 0,
            'qty_akhir'         => $stok['qty_stock'],
            'qty_book_akhir'    => $qty_booking_baru,
            'qty_free_akhir'    => $qty_free_baru,
            'harga_stok'        => $pro['harga_beli']
          ];
        }
      }

      if (!empty($detail)) {
        $this->db->insert_batch('sales_order_detail', $detail);
      }
      if (!empty($arr_kartu_stok)) {
        $this->db->insert_batch('kartu_stok', $arr_kartu_stok);
      }
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      $status = [
        'status' => 0,
        'pesan' => 'Gagal Save. Try Again Later ...'
      ];
    } else {
      $this->db->trans_commit();
      $this->send_wa_so($no_so);
      $status = [
        'status' => 1,
        'pesan' => 'Sales Order berhasil disimpan dan langsung DEAL.',
        'no_so' => $no_so // ← penting!
      ];
    }

    echo json_encode($status);
  }

  //Send Wa
  private function send_wa_so($no_so)
  {
    $so = $this->db
      ->select('so.*, c.name_customer, c.telephone, c.telephone_2, c.address_office')
      ->from('sales_order so')
      ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
      ->where('so.no_so', $no_so)
      ->get()->row_array();

    $produk = $this->db
      ->select('product')
      ->from('sales_order_detail')
      ->where('no_so', $no_so)
      ->get()->result_array();

    $raw_phone = preg_replace('/[^0-9]/', '', $so['telephone']);
    if (substr($raw_phone, 0, 1) === '0') {
      $wa_number = '62' . substr($raw_phone, 1);
    } elseif (substr($raw_phone, 0, 2) === '62') {
      $wa_number = $raw_phone;
    } else {
      $wa_number = ltrim($raw_phone, '+');
    }

    $pesan = "Terimakasih kepada CV/TB {$so['name_customer']} yang telah melakukan pemesanan dengan Nomor Sales Order *{$so['no_so']}* melalui sales kami bapak/ibu *{$so['nama_sales']}*, dengan isi pesanan:\n\n";
    $no = 1;
    foreach ($produk as $p) {
      $pesan .= "{$no}. {$p['product']}\n";
      $no++;
    }
    $pesan .= "\nTotal nilai pesanan sebesar *Rp " . number_format($so['nilai_so'], 0, ',', '.') . "*\n";
    $pesan .= "Hubungi no pelayanan pelanggan kami di *+6282130728009* jika ada kesalahan.\n\nHormat kami,\n\n*PT Surya Bangun Fajar*";

    // Panggil API WA (silent)
    $this->send($wa_number, $pesan);
  }

  private function send($number, $message)
  {
    $url = 'https://app.whacenter.com/api/send';
    $data = [
      'device_id' => 'ea118812b9454dc34a477ae1c053f0fc',
      'number' => $number,
      'message' => $message
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }


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
  public function data_side_sales_order()
  {
    $this->Sales_order_model->get_json_sales_order();
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


// TRASH

// buat proses DEAL SO
  // public function deal_so()
  // {
  //   $post = $this->input->post();

  //   $no_so = $post['no_so'];
  //   $tgl_so = $post['tgl_so'];

  //   if (empty($no_so)) {
  //     echo json_encode(['status' => 0, 'pesan' => 'Nomor SO tidak ditemukan']);
  //     return;
  //   }

  //   $so = $this->db->get_where('sales_order', ['no_so' => $no_so])->row_array();
  //   if (!$so) {
  //     echo json_encode(['status' => 0, 'pesan' => 'Data Sales Order tidak ditemukan']);
  //     return;
  //   }

  //   $this->db->where('no_so', $no_so);
  //   $this->db->update('sales_order', [
  //     'status'        => 'A', //Deal Sales Order
  //     'approved_by'   => $this->auth->user_id(),
  //     'approved_at'   => date('Y-m-d H:i:s'),
  //   ]);

  //   if (isset($_POST['product']) && is_array($_POST['product'])) {
  //     $arr = [];
  //     foreach ($_POST['product'] as $pro) {
  //       // ambil data warehouse buat stok awal
  //       $code_lv4 = $pro['code_lv4'];
  //       $stok = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

  //       if ($stok) {
  //         $qty_stock      = floatval($stok['qty_stock']);
  //         $qty_booking    = floatval($stok['qty_booking']);
  //         $qty_free       = floatval($stok['qty_free']);

  //         $qty_post       = floatval($pro['qty']);
  //         $booking_post   = floatval($pro['use_qty_free']);
  //         $free_post      = floatval($pro['qty_free']);

  //         // Kalkulasi untuk mengembalikan stok semula
  //         $stok_awal      = $qty_stock;
  //         $booking_awal   = $qty_booking - $booking_post;
  //         $free_awal      = $qty_free + $booking_post;

  //         $stok_akhir      = $qty_stock;
  //         $booking_akhir   = $qty_booking;
  //         $free_akhir      = $qty_free;
  //       }

  //       // insert ke kartu stok
  //       $arr[] = [
  //         'no_transaksi'      => $no_so,
  //         'transaksi'         => "Sales Order",
  //         'tgl_transaksi'     => $tgl_so,
  //         'code_lv4'          => $pro['code_lv4'],
  //         'nm_product'        => $pro['product_name'],
  //         'qty'               => floatval($stok_awal),
  //         'qty_book'          => floatval($booking_awal),
  //         'qty_free'          => floatval($free_awal),
  //         'qty_transaksi'     => 0,
  //         'qty_akhir'         => $stok_akhir,
  //         'qty_book_akhir'    => $booking_akhir,
  //         'qty_free_akhir'    => $free_akhir,
  //         'harga_stok'        => $pro['harga_beli']
  //       ];
  //     }
  //   }

  //   if (!empty($arr)) {
  //     $this->db->insert_batch('kartu_stok', $arr);
  //   }

  //   echo json_encode([
  //     'status' => 1,
  //     'pesan' => 'Sales Order Deal!!.'
  //   ]);
  // }
