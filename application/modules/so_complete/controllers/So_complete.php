<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * SO Complete Controller
 * 
 * Menu baru untuk menampilkan Sales Order yang sudah SPK Lengkap.
 * Cancel SO dilakukan di menu Sales Order, bukan di sini.
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
   * Detail SO - menampilkan informasi header + detail item SO + daftar SPK
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
