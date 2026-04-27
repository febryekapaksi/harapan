<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_piutang extends Admin_Controller
{
    protected $viewPermission   = 'Report_Piutang.View';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_piutang/Report_piutang_model');
        $this->template->title('Report Piutang Per Invoice');
        $this->template->page_icon('fa fa-file-text-o');
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->render('index');
    }

    /**
     * AJAX: ambil data piutang per invoice s/d tanggal yang dipilih
     * POST: tanggal (Y-m-d)
     */
    public function get_data()
    {
        $tanggal = $this->input->post('tanggal');

        if (empty($tanggal)) {
            echo json_encode(['status' => false, 'message' => 'Tanggal tidak boleh kosong.']);
            return;
        }

        $data = $this->Report_piutang_model->get_piutang_per_invoice($tanggal);
        $total_piutang = array_sum(array_column($data, 'sisa_piutang'));

        echo json_encode([
            'status'        => true,
            'data'          => $data,
            'total_piutang' => $total_piutang,
        ]);
    }

    /**
     * Halaman print / cetak report piutang
     * GET: tanggal via URI segment (format Y-m-d)
     */
    public function print_report($tanggal = null)
    {
        if (empty($tanggal)) {
            show_error('Tanggal tidak ditemukan.');
        }

        $data_report   = $this->Report_piutang_model->get_piutang_per_invoice($tanggal);
        $total_piutang = array_sum(array_column($data_report, 'sisa_piutang'));

        $data = [
            'tanggal'       => $tanggal,
            'data_report'   => $data_report,
            'total_piutang' => $total_piutang,
        ];

        $this->load->view('print_report', $data);
    }

    /**
     * Export Excel report piutang
     * GET: tanggal via URI segment (format Y-m-d)
     */
    public function export_excel($tanggal = null)
    {
        if (empty($tanggal)) {
            show_error('Tanggal tidak ditemukan.');
        }

        $data_report   = $this->Report_piutang_model->get_piutang_per_invoice($tanggal);
        $total_piutang = array_sum(array_column($data_report, 'sisa_piutang'));

        $this->load->library('PHPExcel');

        $objPHPExcel = new PHPExcel();
        $sheet       = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Piutang Per Invoice');

        $style_header = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '1A5276']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $style_data = [
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
        ];
        $style_total = [
            'font'      => ['bold' => true],
            'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'EAF2FB']],
            'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT],
        ];

        // Title
        $sheet->setCellValue('A1', 'REPORT PIUTANG PER INVOICE');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Per Tanggal: ' . date('d F Y', strtotime($tanggal)));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Header kolom
        $headers = ['Customer', 'Tgl Invoice', 'No Invoice', 'Nilai Invoice',
                    'Tgl Bayar', 'Nilai Bayar', 'Total Bayar', 'Sisa Piutang'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . '4', $h);
            $sheet->getStyle($cols[$i] . '4')->applyFromArray($style_header);
            $sheet->getColumnDimension($cols[$i])->setAutoSize(true);
        }

        // Data rows
        $row = 5;
        $months_id = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
                      7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

        foreach ($data_report as $d) {
            if ($d['is_first_row']) {
                $sheet->setCellValue('A' . $row, $d['name_customer']);
                $tgl_inv = !empty($d['tgl_invoice']) ? date('d', strtotime($d['tgl_invoice'])) . ' ' . $months_id[(int)date('n', strtotime($d['tgl_invoice']))] . ' ' . date('Y', strtotime($d['tgl_invoice'])) : '';
                $sheet->setCellValue('B' . $row, $tgl_inv);
                $sheet->setCellValue('C' . $row, $d['id_invoice']);
                $sheet->setCellValue('D' . $row, (float)$d['nilai_invoice']);
                $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');
            }

            $tgl_bayar = !empty($d['tgl_bayar']) ? date('d', strtotime($d['tgl_bayar'])) . ' ' . $months_id[(int)date('n', strtotime($d['tgl_bayar']))] . ' ' . date('Y', strtotime($d['tgl_bayar'])) : '';
            $sheet->setCellValue('E' . $row, $tgl_bayar);
            $sheet->setCellValue('F' . $row, $d['nilai_bayar'] !== '' ? (float)$d['nilai_bayar'] : null);
            $sheet->setCellValue('G' . $row, $d['total_bayar'] !== '' ? (float)$d['total_bayar'] : null);
            $sheet->setCellValue('H' . $row, (float)$d['sisa_piutang']);

            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($style_data);
            $row++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, 'Total Piutang');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, (float)$total_piutang);
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray($style_total);

        // Output
        $filename = 'Report_Piutang_' . $tanggal . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $writer->save('php://output');
        exit;
    }
}
