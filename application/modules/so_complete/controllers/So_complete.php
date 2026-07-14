<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SO Complete Controller
 * 
 * Menu baru untuk menampilkan Sales Order yang sudah SPK Lengkap
 * dan fitur Cancel SO (pembatalan sisa SO yang belum di-SPK)
 */
class So_complete extends Admin_Controller
{
  // Permission
  protected $viewPermission   = 'SO_Complete.View';
  protected $addPermission    = 'SO_Complete.Add';
  protected $managePermission = 'SO_Complete.Manage';
  protected $deletePermission = 'SO_Complete.Delete';

  public function __construct()
  {
    parent::__construct();

    $this->load->model(array(
      'So_complete/So_complete_model',
    ));
    date_default_timezone_set('Asia/Bangkok');
  }

  /**
   * Halaman utama SO Complete
   * Menampilkan daftar SO yang status_spk = 'SPK Lengkap'
   */
  public function index()
  {
    $this->auth->restrict($this->viewPermission);

    $this->template->title('SO Completed');
    $this->template->page_icon('fa fa-check-circle');
    $this->template->render('index');
  }

  /**
   * Server-side DataTables untuk SO Complete
   */
  public function data_side_so_complete()
  {
    $this->So_complete_model->get_json_so_complete();
  }

  /**
   * Detail SO - menampilkan informasi header + detail item SO
   */
  public function detail($no_so)
  {
    $this->auth->restrict($this->viewPermission);

    $so = $this->db
      ->select('so.*, c.name_customer, p.id_penawaran, p.tipe_penawaran, p.sales')
      ->from('sales_order so')
      ->join('master_customers c', 'c.id_customer = so.id_customer', 'left')
      ->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left')
      ->where('so.no_so', $no_so)
      ->get()
      ->row_array();

    if (!$so) {
      show_404();
    }

    $so_detail = $this->db
      ->select('sod.*, 
                (sod.qty_order - sod.qty_spk) AS sisa_belum_spk')
      ->from('sales_order_detail sod')
      ->where('sod.no_so', $no_so)
      ->get()
      ->result_array();

    // Ambil data SPK terkait
    $spk_list = $this->db
      ->select('spk.no_delivery, spk.tanggal_spk, spk.pengiriman, spk.created_date')
      ->from('spk_delivery spk')
      ->where('spk.no_so', $no_so)
      ->where('spk.deleted_date IS NULL')
      ->order_by('spk.created_date', 'ASC')
      ->get()
      ->result_array();

    $data = [
      'so'        => $so,
      'so_detail' => $so_detail,
      'spk_list'  => $spk_list,
    ];

    $this->template->title('Detail SO - ' . $no_so);
    $this->template->page_icon('fa fa-check-circle');
    $this->template->render('detail', $data);
  }

  /**
   * Cancel SO - Membatalkan sisa qty SO yang belum ter-SPK
   * 
   * Logika:
   * - Cek setiap item SO, hitung sisa yang belum SPK
   * - Kurangi qty_order menjadi qty_spk (item yang sudah di-SPK tetap jalan)
   * - Kembalikan booking warehouse untuk qty yang dibatalkan
   * - Catat di kartu_stok dengan transaksi "Batal SO"
   * - Update status SO menjadi 'C' (Cancelled/Closed partial)
   */
  public function cancel_so()
  {
    $this->auth->restrict($this->managePermission);

    $no_so  = $this->input->post('no_so');
    $reason = $this->input->post('reason');

    if (empty($no_so)) {
      echo json_encode(['status' => 0, 'pesan' => 'No SO tidak valid.']);
      return;
    }

    // Ambil data SO
    $so = $this->db->get_where('sales_order', ['no_so' => $no_so])->row_array();
    if (!$so) {
      echo json_encode(['status' => 0, 'pesan' => 'Data Sales Order tidak ditemukan.']);
      return;
    }

    // Ambil detail SO
    $so_details = $this->db->get_where('sales_order_detail', ['no_so' => $no_so])->result_array();

    $this->db->trans_begin();

    $total_cancelled = 0;

    foreach ($so_details as $det) {
      $qty_order = floatval($det['qty_order']);
      $qty_spk   = floatval($det['qty_spk']);
      $sisa      = $qty_order - $qty_spk; // qty yang belum di-SPK

      if ($sisa <= 0) {
        continue; // item ini sudah full SPK, skip
      }

      $total_cancelled += $sisa;
      $code_lv4 = $det['id_product'];

      // 1. Update sales_order_detail: set qty_order = qty_spk (cancel sisanya)
      $this->db->update('sales_order_detail', [
        'qty_order'       => $qty_spk,
        'qty_belum_spk'   => 0,
        'qty_cancelled'   => $sisa,
        'status_planning' => 1, // mark as complete
      ], ['id' => $det['id']]);

      // 2. Kembalikan booking warehouse untuk qty yang dibatalkan
      $stok_before = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

      if ($stok_before) {
        $qty_booking_before = floatval($stok_before['qty_booking']);
        $qty_free_before    = floatval($stok_before['qty_free']);
        $use_qty_free_before = floatval($stok_before['use_qty_free']);

        // Kurangi booking sebesar sisa yang dicancel
        $qty_booking_after = max(0, $qty_booking_before - $sisa);
        $qty_free_after    = $qty_free_before + $sisa;

        $this->db->where('code_lv4', $code_lv4)->update('warehouse_stock', [
          'qty_booking'  => $qty_booking_after,
          'qty_free'     => $qty_free_after,
          'use_qty_free' => max(0, $use_qty_free_before - $sisa),
        ]);

        // 3. Catat di kartu_stok
        $this->db->insert('kartu_stok', [
          'no_transaksi'   => $no_so,
          'transaksi'      => 'Batal SO',
          'tgl_transaksi'  => date('Y-m-d H:i:s'),
          'code_lv4'       => $code_lv4,
          'nm_product'     => $det['product'],
          'qty'            => floatval($stok_before['qty_stock']),
          'qty_book'       => $qty_booking_before,
          'qty_free'       => $qty_free_before,
          'qty_transaksi'  => $sisa,
          'qty_akhir'      => floatval($stok_before['qty_stock']),
          'qty_book_akhir' => $qty_booking_after,
          'qty_free_akhir' => $qty_free_after,
          'harga_stok'     => isset($det['harga_beli']) ? floatval($det['harga_beli']) : 0,
        ]);
      }
    }

    // 4. Update header SO - tandai sebagai closed/completed
    $this->db->update('sales_order', [
      'status_so'      => 'CLOSED',
      'cancel_reason'  => $reason,
      'cancel_qty'     => $total_cancelled,
      'cancelled_by'   => $this->auth->user_id(),
      'cancelled_at'   => date('Y-m-d H:i:s'),
    ], ['no_so' => $no_so]);

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      echo json_encode(['status' => 0, 'pesan' => 'Gagal membatalkan sisa SO!']);
      return;
    }

    $this->db->trans_commit();
    history("Cancel SO (sisa): {$no_so} | Qty dibatalkan: {$total_cancelled}");

    echo json_encode([
      'status' => 1,
      'pesan'  => "Sisa SO berhasil dibatalkan. Total qty cancel: {$total_cancelled}. Stock booking dikembalikan."
    ]);
  }

  /**
   * Cancel SPK individual dari halaman SO Complete
   * Redirect ke SPK Delivery cancel_spk
   */
  public function cancel_spk()
  {
    $this->auth->restrict($this->managePermission);

    $no_delivery = $this->input->post('no_delivery');

    if (empty($no_delivery)) {
      echo json_encode(['status' => 0, 'pesan' => 'No Delivery tidak valid.']);
      return;
    }

    $this->db->trans_begin();

    // 1. Ambil data SPK header
    $spk = $this->db->get_where('spk_delivery', ['no_delivery' => $no_delivery])->row_array();

    if (!$spk) {
      $this->db->trans_rollback();
      echo json_encode(['status' => 0, 'pesan' => 'Data SPK tidak ditemukan.']);
      return;
    }

    $no_so = $spk['no_so'];

    // 2. Ambil detail SPK yang akan di-cancel
    $spk_details = $this->db->get_where('spk_delivery_detail', ['no_delivery' => $no_delivery])->result_array();

    // 3. Untuk setiap item SPK: kembalikan qty_spk di SO detail & catat di kartu stok
    foreach ($spk_details as $det) {
      $id_so_det  = $det['id_so_det'];
      $id_product = $det['id_product'];
      $qty_spk    = floatval($det['qty_spk']);

      // Update sales_order_detail: kurangi qty_spk
      $so_det = $this->db->get_where('sales_order_detail', ['id' => $id_so_det])->row_array();
      if ($so_det) {
        $new_qty_spk       = max(0, floatval($so_det['qty_spk']) - $qty_spk);
        $new_qty_belum_spk = floatval($so_det['qty_order']) - $new_qty_spk;

        $this->db->update('sales_order_detail', [
          'qty_spk'         => $new_qty_spk,
          'qty_belum_spk'   => $new_qty_belum_spk,
          'status_planning' => ($new_qty_belum_spk > 0) ? 0 : 1,
        ], ['id' => $id_so_det]);
      }

      // Catat reversal di kartu stok
      $stok_now = $this->db->get_where('warehouse_stock', ['code_lv4' => $id_product])->row_array();
      if ($stok_now) {
        $this->db->insert('kartu_stok', [
          'no_transaksi'   => $no_delivery,
          'transaksi'      => 'Batal SPK',
          'tgl_transaksi'  => date('Y-m-d H:i:s'),
          'code_lv4'       => $id_product,
          'nm_product'     => isset($so_det['product']) ? $so_det['product'] : '',
          'qty'            => floatval($stok_now['qty_stock']),
          'qty_book'       => floatval($stok_now['qty_booking']),
          'qty_free'       => floatval($stok_now['qty_free']),
          'qty_transaksi'  => $qty_spk,
          'qty_akhir'      => floatval($stok_now['qty_stock']),
          'qty_book_akhir' => floatval($stok_now['qty_booking']),
          'qty_free_akhir' => floatval($stok_now['qty_free']),
          'harga_stok'     => isset($so_det['harga_beli']) ? floatval($so_det['harga_beli']) : 0,
        ]);
      }
    }

    // 4. Hapus SPK (Detail lalu Header)
    $this->db->delete('spk_delivery_detail', ['no_delivery' => $no_delivery]);
    $this->db->delete('spk_delivery', ['no_delivery' => $no_delivery]);

    // 5. Update status_spk di header Sales Order
    $summary = $this->db->select('SUM(qty_order) as total_order, SUM(qty_spk) as total_spk')
      ->get_where('sales_order_detail', ['no_so' => $no_so])
      ->row_array();

    $status_spk = 'Belum SPK';
    if ((float)$summary['total_spk'] >= (float)$summary['total_order']) {
      $status_spk = 'SPK Lengkap';
    } elseif ((float)$summary['total_spk'] > 0) {
      $status_spk = 'SPK Sebagian';
    }

    $this->db->update('sales_order', ['status_spk' => $status_spk], ['no_so' => $no_so]);

    if ($this->db->trans_status() === FALSE) {
      $this->db->trans_rollback();
      echo json_encode(['status' => 0, 'pesan' => 'Gagal membatalkan SPK!']);
      return;
    }

    $this->db->trans_commit();
    history("Cancel SPK dari SO Complete: {$no_delivery} | SO: {$no_so}");

    echo json_encode([
      'status' => 1,
      'pesan'  => 'SPK berhasil dibatalkan. SO bisa di-SPK ulang.'
    ]);
  }

  /**
   * Export Excel SO Completed
   */
  public function export_excel()
  {
    $start = $this->input->get('start_date', true);
    $end   = $this->input->get('end_date', true);

    $this->db->select('so.no_so, so.tgl_so, so.nilai_so, so.status_spk, so.status_so,
                       p.id_penawaran, p.tipe_penawaran, p.sales,
                       c.name_customer');
    $this->db->from('sales_order so');
    $this->db->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left');
    $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
    $this->db->where('so.status', 'A');
    $this->db->where('so.status_spk', 'SPK Lengkap');
    if (!empty($start)) $this->db->where('so.tgl_so >=', $start);
    if (!empty($end))   $this->db->where('so.tgl_so <=', $end);
    $this->db->order_by('so.tgl_so', 'DESC');

    $rows = $this->db->get()->result();

    if (empty($rows)) {
      echo "<script>alert('Data tidak ditemukan'); window.history.back();</script>";
      return;
    }

    set_time_limit(0);
    ini_set('memory_limit', '512M');
    $this->load->library('PHPExcel');

    $xls   = new PHPExcel();
    $sheet = $xls->getActiveSheet();

    $periode = ($start && $end) ? $start . ' s/d ' . $end : 'Semua Data';
    $sheet->setCellValue('A1', 'REPORT SO COMPLETED - ' . $periode);
    $sheet->mergeCells('A1:H2');

    $headers = ['A' => '#', 'B' => 'No. SO', 'C' => 'No. Penawaran', 'D' => 'Tanggal SO', 'E' => 'Customer', 'F' => 'Sales', 'G' => 'Nilai SO', 'H' => 'Tipe Quotation', 'I' => 'Status'];
    $rowHeader = 4;
    foreach ($headers as $col => $label) {
      $sheet->setCellValue($col . $rowHeader, $label);
      $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $r = $rowHeader + 1;
    $no = 1;
    foreach ($rows as $row) {
      $sheet->setCellValue('A' . $r, $no++);
      $sheet->setCellValueExplicit('B' . $r, (string)$row->no_so, PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('C' . $r, (string)$row->id_penawaran, PHPExcel_Cell_DataType::TYPE_STRING);
      if (!empty($row->tgl_so)) {
        $tgl = (float)PHPExcel_Shared_Date::PHPToExcel(strtotime($row->tgl_so));
        $sheet->setCellValueExplicit('D' . $r, $tgl, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('D' . $r)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
      }
      $sheet->setCellValueExplicit('E' . $r, (string)$row->name_customer, PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('F' . $r, (string)$row->sales, PHPExcel_Cell_DataType::TYPE_STRING);
      $sheet->setCellValueExplicit('G' . $r, (float)$row->nilai_so, PHPExcel_Cell_DataType::TYPE_NUMERIC);
      $sheet->getStyle('G' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValueExplicit('H' . $r, (string)$row->tipe_penawaran, PHPExcel_Cell_DataType::TYPE_STRING);
      $status_text = ($row->status_so == 'CLOSED') ? 'Closed' : 'Active';
      $sheet->setCellValueExplicit('I' . $r, $status_text, PHPExcel_Cell_DataType::TYPE_STRING);
      $r++;
    }

    $sheet->setTitle('SO Completed');
    $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
    ob_end_clean();
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="SO_Completed_' . date('Ymd_His') . '.xls"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
  }
}
