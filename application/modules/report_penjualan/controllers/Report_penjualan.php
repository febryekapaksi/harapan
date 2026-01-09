<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_penjualan extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Report_Penjualan.View';
    protected $addPermission    = 'Report_Penjualan.Add';
    protected $managePermission = 'Report_Penjualan.Manage';
    protected $deletePermission = 'Report_Penjualan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_penjualan/Report_penjualan_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    // =============================
    // Report Seluruh Penjualan
    // =============================
    public function index()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Report Penjualan');
        $this->template->render('index');
    }

    public function data_side_report()
    {
        $this->Report_penjualan_model->data_side_report();
    }

    public function export_excel_report()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $tgl_dari   = $this->input->get('tgl_dari', true);
        $tgl_sampai = $this->input->get('tgl_sampai', true);
        $search     = $this->input->get('search', true);

        // ambil data (tanpa paging)
        $rows = $this->Report_penjualan_model->get_export_report($search, $tgl_dari, $tgl_sampai);

        $this->load->library("PHPExcel");
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Report Penjualan');

        // =========================
        // STYLE (samakan dengan report lain)
        // =========================
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'B30000']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $tableHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7'] // biru muda
            ]
        ];

        $tableBody = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        // =========================
        // Judul & Periode
        // =========================
        $sheet->setCellValue('A1', 'Laporan Penjualan');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1:I1')->applyFromArray($styleTitle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $periodeText = 'Periode: ';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $periodeText .= date('d/m/Y', strtotime($tgl_dari)) . ' s/d ' . date('d/m/Y', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $periodeText .= 'Mulai ' . date('d/m/Y', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $periodeText .= 'Sampai ' . date('d/m/Y', strtotime($tgl_sampai));
        } else {
            $periodeText .= 'Semua';
        }
        $sheet->setCellValue('A2', $periodeText);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2:I2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // =========================
        // Header kolom (baris 3)
        // =========================
        $headers = [
            'A' => 'No',
            'B' => 'Nomor Invoice',
            'C' => 'Tanggal',
            'D' => 'Customer',
            'E' => 'Total Invoice',
            'F' => 'Total Bayar',
            'G' => 'Piutang',
            'H' => 'Umur (hr)',
            'I' => 'Status'
        ];

        $rowHeader = 3;
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . $rowHeader, $header);
            $sheet->getStyle($col . $rowHeader)->applyFromArray($tableHeader);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getRowDimension($rowHeader)->setRowHeight(20);

        // Freeze pane biar header tetap
        $sheet->freezePane('A4');

        // =========================
        // Isi data (mulai baris 4)
        // =========================
        $rowNum = 4;
        $no = 1;

        foreach ($rows as $row) {

            if ((int)$row->is_cancel === 1) {
                $status = 'Credit Note';
            } else {
                $status = ((int)$row->sts === 1) ? 'Belum Lunas' : 'Lunas';
            }

            $sheet->setCellValueExplicit("A{$rowNum}", (string)$no++, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$rowNum}", (string)strtoupper($row->id_invoice), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowNum}", (!empty($row->created_on) ? date('d/M/Y', strtotime($row->created_on)) : ''), PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$rowNum}", (string)strtoupper($row->nm_customer), PHPExcel_Cell_DataType::TYPE_STRING);

            $sheet->setCellValueExplicit("E{$rowNum}", (float)$row->total, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("F{$rowNum}", (float)$row->total_bayar, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("G{$rowNum}", (float)$row->piutang, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("H{$rowNum}", (int)$row->umur, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->setCellValueExplicit("I{$rowNum}", (string)$status, PHPExcel_Cell_DataType::TYPE_STRING);

            // apply body style + number format
            $sheet->getStyle("A{$rowNum}:I{$rowNum}")->applyFromArray($tableBody);

            // format angka ribuan
            $sheet->getStyle("E{$rowNum}:G{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');

            // alignment per kolom
            $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("E{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("I{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        // =========================
        // Filename aman (tanpa karakter aneh)
        // =========================
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $filePeriode = date('Ymd', strtotime($tgl_dari)) . '_' . date('Ymd', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $filePeriode = 'mulai_' . date('Ymd', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $filePeriode = 'sampai_' . date('Ymd', strtotime($tgl_sampai));
        } else {
            $filePeriode = 'semua';
        }

        $filename = "Report_Piutang_{$filePeriode}.xls";
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $objWriter->save("php://output");
        exit;
    }

    // =============================
    // Report Penjualan Per Customer
    // =============================
    public function report_customer()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Report Penjualan per Pelanggan');
        $this->template->render('index_customer');
    }

    public function data_side_customer()
    {
        $this->Report_penjualan_model->data_side_customer();
    }

    public function export_excel_customer()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $tgl_dari   = $this->input->get('tgl_dari', true);
        $tgl_sampai = $this->input->get('tgl_sampai', true);
        $search     = $this->input->get('search', true);

        // ambil data (tanpa paging)
        $rows = $this->Report_penjualan_model->get_export_customer($search, $tgl_dari, $tgl_sampai);

        $this->load->library("PHPExcel");
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Penjualan per Pelanggan');

        // =========================
        // STYLE (samakan dengan report lain)
        // =========================
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'B30000']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $styleSubTitle = [
            'font' => ['bold' => false, 'size' => 11],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $tableHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7'] // biru muda
            ]
        ];

        $tableBody = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $rowTotal = [
            'font' => ['bold' => true],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7']
            ]
        ];

        // =========================
        // TITLE (sesuai gambar)
        // =========================
        $sheet->setCellValue('A1', 'Penjualan per Pelanggan');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1:B1')->applyFromArray($styleTitle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Periode format seperti gambar: "Dari 01 Des 2025 s/d 31 Des 2025"
        $periodeText = 'Dari ';
        if (!empty($tgl_dari)) {
            $periodeText .= date('d M Y', strtotime($tgl_dari));
        } else {
            $periodeText .= '-';
        }
        $periodeText .= ' s/d ';
        if (!empty($tgl_sampai)) {
            $periodeText .= date('d M Y', strtotime($tgl_sampai));
        } else {
            $periodeText .= '-';
        }

        $sheet->setCellValue('A2', $periodeText);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2:B2')->applyFromArray($styleSubTitle);

        // (opsional) Cabang seperti gambar (kalau belum ada data cabang, tulis default)
        // $sheet->setCellValue('B3', 'Cabang : [Semua Cabang]');
        // $sheet->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // =========================
        // HEADER TABLE (mulai baris 4)
        // =========================
        $rowHeader = 4;
        $sheet->setCellValue("A{$rowHeader}", 'Pelanggan');
        $sheet->setCellValue("B{$rowHeader}", 'Penjualan');
        $sheet->getStyle("A{$rowHeader}:B{$rowHeader}")->applyFromArray($tableHeader);
        $sheet->getRowDimension($rowHeader)->setRowHeight(20);

        // width (biar mirip report)
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(18);

        // freeze header
        $sheet->freezePane('A5');

        // =========================
        // BODY
        // =========================
        $rowNum = 5;
        $grandTotal = 0;

        foreach ($rows as $row) {
            $nm = strtoupper((string)$row->nm_customer);
            $val = (float)$row->total_invoice;

            $sheet->setCellValueExplicit("A{$rowNum}", $nm, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$rowNum}", $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle("A{$rowNum}:B{$rowNum}")->applyFromArray($tableBody);

            // align
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // format angka
            $sheet->getStyle("B{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');

            $grandTotal += $val;
            $rowNum++;
        }

        // =========================
        // TOTAL (sesuai gambar: "Total Pelanggan")
        // =========================
        $sheet->setCellValue("A{$rowNum}", 'Total Pelanggan');
        $sheet->setCellValueExplicit("B{$rowNum}", (float)$grandTotal, PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->getStyle("A{$rowNum}:B{$rowNum}")->applyFromArray($rowTotal);
        $sheet->getStyle("B{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // garis atas lebih tebal biar "kerasa total"
        $sheet->getStyle("A{$rowNum}:B{$rowNum}")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

        // =========================
        // OUTPUT
        // =========================
        // filename aman
        $filePeriode = 'semua';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $filePeriode = date('Ymd', strtotime($tgl_dari)) . '_' . date('Ymd', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $filePeriode = 'mulai_' . date('Ymd', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $filePeriode = 'sampai_' . date('Ymd', strtotime($tgl_sampai));
        }

        $filename = "Penjualan_per_Pelanggan_{$filePeriode}.xls";
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $objWriter->save("php://output");
        exit;
    }

    // =============================
    // Report Penjualan Per Produk
    // =============================
    public function report_product()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Report Penjualan per Barang');
        $this->template->render('index_product');
    }

    public function data_side_product()
    {
        $this->Report_penjualan_model->data_side_product();
    }

    public function export_excel_product()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $tgl_dari   = $this->input->get('tgl_dari', true);
        $tgl_sampai = $this->input->get('tgl_sampai', true);
        $search     = $this->input->get('search', true);

        // ambil data (tanpa paging)
        $rows = $this->Report_penjualan_model->get_export_product($search, $tgl_dari, $tgl_sampai);

        $this->load->library("PHPExcel");
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Penjualan per Barang');

        // =========================
        // STYLE (samakan tema report lain)
        // =========================
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'B30000']],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $styleSubTitle = [
            'font' => ['bold' => false, 'size' => 11],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $tableHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7'] // biru muda
            ]
        ];

        $tableBody = [
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        $rowTotal = [
            'font' => ['bold' => true],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7']
            ]
        ];

        // =========================
        // TITLE (sesuai gambar)
        // =========================
        $sheet->setCellValue('A1', 'Penjualan per Barang');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Periode format seperti gambar: "Dari 01 Des 2025 s/d 31 Jan 2026"
        $periodeLine = 'Dari ';
        $periodeLine .= !empty($tgl_dari) ? date('d M Y', strtotime($tgl_dari)) : '-';
        $periodeLine .= ' s/d ';
        $periodeLine .= !empty($tgl_sampai) ? date('d M Y', strtotime($tgl_sampai)) : '-';

        $sheet->setCellValue('A2', $periodeLine);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2:D2')->applyFromArray($styleSubTitle);

        // =========================
        // HEADER TABLE (mulai baris 4)
        // =========================
        $rowHeader = 4;
        $sheet->setCellValue("A{$rowHeader}", 'Nama Barang');
        $sheet->setCellValue("B{$rowHeader}", 'Satuan');
        $sheet->setCellValue("C{$rowHeader}", 'Kuantitas');
        $sheet->setCellValue("D{$rowHeader}", 'Penjualan');
        $sheet->getStyle("A{$rowHeader}:D{$rowHeader}")->applyFromArray($tableHeader);
        $sheet->getRowDimension($rowHeader)->setRowHeight(20);

        // width biar mirip gambar
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);

        // wrap text untuk nama barang panjang
        $sheet->getStyle('A:A')->getAlignment()->setWrapText(true);

        // freeze header
        $sheet->freezePane('A5');

        // =========================
        // BODY
        // =========================
        $rowNum = 5;
        $grandQty = 0;
        $grandSales = 0;

        foreach ($rows as $row) {
            $nama   = strtoupper((string)$row->nama_barang);
            $satuan = strtoupper((string)$row->satuan);
            $qty    = (float)$row->qty_total;
            $sales  = (float)$row->penjualan_total;

            $sheet->setCellValueExplicit("A{$rowNum}", $nama, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$rowNum}", $satuan, PHPExcel_Cell_DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$rowNum}", $qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D{$rowNum}", $sales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($tableBody);

            // alignment
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("B{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // format angka ribuan
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');

            $grandQty += $qty;
            $grandSales += $sales;

            $rowNum++;
        }

        // =========================
        // TOTAL (sesuai gambar: "Total Nama Barang")
        // =========================
        $sheet->setCellValue("A{$rowNum}", 'Total Nama Barang');
        $sheet->mergeCells("A{$rowNum}:B{$rowNum}");
        $sheet->setCellValueExplicit("C{$rowNum}", (float)$grandQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit("D{$rowNum}", (float)$grandSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($rowTotal);
        $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // border top lebih tebal untuk baris total
        $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);

        // =========================
        // OUTPUT
        // =========================
        $filePeriode = 'semua';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $filePeriode = date('Ymd', strtotime($tgl_dari)) . '_' . date('Ymd', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $filePeriode = 'mulai_' . date('Ymd', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $filePeriode = 'sampai_' . date('Ymd', strtotime($tgl_sampai));
        }

        $filename = "Penjualan_per_Barang_{$filePeriode}.xls";
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $objWriter->save("php://output");
        exit;
    }


    // =============================
    // Report Penjualan Barang per Customer
    // =============================
    public function report_product_customer()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Report Penjualan Barang per Customer');
        $this->template->render('index_product_customer');
    }

    public function data_side_barang_per_pelanggan()
    {
        $this->Report_penjualan_model->data_side_barang_per_pelanggan();
    }

    public function export_excel_barang_per_pelanggan()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $tgl_dari   = $this->input->get('tgl_dari', true);
        $tgl_sampai = $this->input->get('tgl_sampai', true);
        $search     = $this->input->get('search', true);

        $rows = $this->Report_penjualan_model->get_export_barang_per_pelanggan($search, $tgl_dari, $tgl_sampai);

        $this->load->library("PHPExcel");
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Penjualan Barang-Pelanggan');

        // =========================
        // Styles
        // =========================
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'B30000']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
        ];

        $styleHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7'] // biru muda
            ]
        ];

        $styleBody = [
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
        ];

        $styleSubtotal = [
            'font' => ['bold' => true],
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7']
            ]
        ];

        $styleGrand = [
            'font' => ['bold' => true],
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
            'borders' => [
                'allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]
            ],
            'fill' => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'D9EDF7']
            ]
        ];

        // =========================
        // Layout & Title
        // =========================
        $sheet->setCellValue('A1', 'Penjualan Barang per Pelanggan');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1')->applyFromArray($styleTitle);

        // Periode text
        $periodeText = 'Periode: ';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $periodeText .= date('d/m/Y', strtotime($tgl_dari)) . ' s/d ' . date('d/m/Y', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $periodeText .= 'Mulai ' . date('d/m/Y', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $periodeText .= 'Sampai ' . date('d/m/Y', strtotime($tgl_sampai));
        } else {
            $periodeText .= 'Semua';
        }
        $sheet->setCellValue('A2', $periodeText);
        $sheet->mergeCells('A2:D2');

        // Header table (baris 4)
        $sheet->setCellValue('A4', 'Nama Barang');
        $sheet->setCellValue('B4', 'Pelanggan');
        $sheet->setCellValue('C4', 'Kuantitas');
        $sheet->setCellValue('D4', 'Penjualan');
        $sheet->getStyle('A4:D4')->applyFromArray($styleHeader);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);

        // Freeze pane
        $sheet->freezePane('A5');

        // =========================
        // Isi data (detail + subtotal + grand total)
        // =========================
        $rowNum = 5;

        $currentBarang = null;
        $startBarangRow = null;
        $lastDetailRow = null;

        $subQty = 0;
        $subSales = 0;

        $grandQty = 0;
        $grandSales = 0;

        if (!empty($rows)) {
            foreach ($rows as $r) {
                $barang    = strtoupper((string)$r->nama_barang);
                $pelanggan = strtoupper((string)$r->pelanggan);
                $qty       = (float)$r->qty_total;
                $sales     = (float)$r->penjualan_total;

                // jika barang berubah -> tutup group sebelumnya (merge + subtotal)
                if ($currentBarang !== null && $barang !== $currentBarang) {

                    // merge cell Nama Barang untuk group sebelumnya (hanya baris detail)
                    if ($startBarangRow !== null && $lastDetailRow !== null) {
                        if ($lastDetailRow > $startBarangRow) {
                            $sheet->mergeCells("A{$startBarangRow}:A{$lastDetailRow}");
                        }
                        $sheet->setCellValue("A{$startBarangRow}", $currentBarang);
                        $sheet->getStyle("A{$startBarangRow}:A{$lastDetailRow}")->applyFromArray($styleBody);
                        $sheet->getStyle("A{$startBarangRow}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    }

                    // subtotal row
                    $sheet->setCellValue("A{$rowNum}", '');
                    $sheet->setCellValue("B{$rowNum}", 'TOTAL PELANGGAN');
                    $sheet->setCellValueExplicit("C{$rowNum}", $subQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("D{$rowNum}", $subSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                    $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($styleSubtotal);
                    $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $rowNum++;

                    // reset subtotal untuk group baru
                    $subQty = 0;
                    $subSales = 0;

                    // start group baru
                    $currentBarang = $barang;
                    $startBarangRow = $rowNum;
                }

                // group pertama
                if ($currentBarang === null) {
                    $currentBarang = $barang;
                    $startBarangRow = $rowNum;
                }

                // baris detail
                $sheet->setCellValue("A{$rowNum}", ''); // akan diisi di baris pertama group setelah merge
                $sheet->setCellValue("B{$rowNum}", $pelanggan);
                $sheet->setCellValueExplicit("C{$rowNum}", $qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("D{$rowNum}", $sales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($styleBody);
                $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                // akumulasi
                $subQty += $qty;
                $subSales += $sales;

                $grandQty += $qty;
                $grandSales += $sales;

                $lastDetailRow = $rowNum;
                $rowNum++;
            }

            // tutup group terakhir (merge + subtotal)
            if ($startBarangRow !== null && $lastDetailRow !== null) {
                if ($lastDetailRow > $startBarangRow) {
                    $sheet->mergeCells("A{$startBarangRow}:A{$lastDetailRow}");
                }
                $sheet->setCellValue("A{$startBarangRow}", $currentBarang);
                $sheet->getStyle("A{$startBarangRow}:A{$lastDetailRow}")->applyFromArray($styleBody);
                $sheet->getStyle("A{$startBarangRow}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            }

            // subtotal terakhir
            $sheet->setCellValue("A{$rowNum}", '');
            $sheet->setCellValue("B{$rowNum}", 'TOTAL PELANGGAN');
            $sheet->setCellValueExplicit("C{$rowNum}", $subQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D{$rowNum}", $subSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($styleSubtotal);
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $rowNum++;

            // grand total
            $sheet->setCellValue("A{$rowNum}", 'TOTAL NAMA BARANG');
            $sheet->setCellValue("B{$rowNum}", '');
            $sheet->setCellValueExplicit("C{$rowNum}", $grandQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("D{$rowNum}", $grandSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($styleGrand);
            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
            // kalau tidak ada data
            $sheet->setCellValue("A{$rowNum}", 'Tidak ada data');
            $sheet->mergeCells("A{$rowNum}:D{$rowNum}");
            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->applyFromArray($styleBody);
            $sheet->getStyle("A{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        // =========================
        // Output
        // =========================
        // nama file aman (tanpa slash)
        $filePeriode = '';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $filePeriode = date('Ymd', strtotime($tgl_dari)) . '_' . date('Ymd', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $filePeriode = 'mulai_' . date('Ymd', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $filePeriode = 'sampai_' . date('Ymd', strtotime($tgl_sampai));
        } else {
            $filePeriode = 'semua';
        }

        $filename = "Penjualan_Barang_per_Pelanggan_{$filePeriode}.xls";
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $objWriter->save("php://output");
        exit;
    }

    // =============================
    // Report Penjualan Customer per Barang
    // =============================
    public function report_customer_product()
    {
        $this->template->page_icon('fa fa-clipboard');
        $this->template->title('Report Penjualan Customer per Product');
        $this->template->render('index_customer_product');
    }

    public function data_side_customer_per_barang()
    {
        $this->Report_penjualan_model->data_side_customer_per_barang();
    }

    public function export_excel_customer_per_barang()
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $tgl_dari   = $this->input->get('tgl_dari', true);
        $tgl_sampai = $this->input->get('tgl_sampai', true);
        $search     = $this->input->get('search', true);

        // ambil data (tanpa paging) - urut customer ASC, produk ASC
        $rows = $this->Report_penjualan_model->get_export_customer_per_barang($search, $tgl_dari, $tgl_sampai);

        $this->load->library("PHPExcel");
        $objPHPExcel = new PHPExcel();
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Pelanggan per Barang');

        // ===== STYLE =====
        $styleTitle = [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'B30000']],
            'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
        ];

        $tableHeader = [
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D9EDF7']]
        ];

        $tableBody = [
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            'alignment' => ['vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP]
        ];

        $rowSubtotal = [
            'font' => ['bold' => true],
            'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN]],
            'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'D9EDF7']]
        ];

        // ===== JUDUL =====
        $sheet->setCellValue('A1', 'Penjualan Pelanggan per Barang');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray($styleTitle);

        // periode
        $periodeText = 'Periode: ';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $periodeText .= date('d/m/Y', strtotime($tgl_dari)) . ' s/d ' . date('d/m/Y', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $periodeText .= 'Mulai ' . date('d/m/Y', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $periodeText .= 'Sampai ' . date('d/m/Y', strtotime($tgl_sampai));
        } else {
            $periodeText .= 'Semua';
        }
        $sheet->setCellValue('A2', $periodeText);
        $sheet->mergeCells('A2:E2');

        // header kolom
        $sheet->setCellValue('A4', 'Pelanggan');
        $sheet->setCellValue('B4', 'Nama Barang');
        $sheet->setCellValue('C4', 'Satuan');
        $sheet->setCellValue('D4', 'Kuantitas');
        $sheet->setCellValue('E4', 'Penjualan');
        $sheet->getStyle('A4:E4')->applyFromArray($tableHeader);

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(18);

        $sheet->freezePane('A5');

        // ===== ISI DATA + SUBTOTAL + GRAND TOTAL =====
        $rowNum = 5;

        $currentCust = null;
        $startCustRow = null;
        $lastDetailRow = null;

        $subQty = 0;
        $subSales = 0;

        $grandQty = 0;
        $grandSales = 0;

        if (!empty($rows)) {
            foreach ($rows as $r) {
                $cust   = strtoupper((string)$r->pelanggan);
                $produk = strtoupper((string)$r->nama_barang);
                $satuan = strtoupper((string)$r->satuan);
                $qty    = (float)$r->qty_total;
                $sales  = (float)$r->penjualan_total;

                // jika customer berubah -> tutup group sebelumnya (merge + subtotal)
                if ($currentCust !== null && $cust !== $currentCust) {

                    // merge cell pelanggan untuk group sebelumnya (hanya baris detail)
                    if ($startCustRow !== null && $lastDetailRow !== null) {
                        if ($lastDetailRow > $startCustRow) {
                            $sheet->mergeCells("A{$startCustRow}:A{$lastDetailRow}");
                        }
                        $sheet->setCellValue("A{$startCustRow}", $currentCust);
                        $sheet->getStyle("A{$startCustRow}:E{$lastDetailRow}")->applyFromArray($tableBody);
                        $sheet->getStyle("A{$startCustRow}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                    }

                    // subtotal row (Total Nama Barang)
                    $sheet->setCellValue("A{$rowNum}", '');
                    $sheet->setCellValue("B{$rowNum}", 'TOTAL NAMA BARANG');
                    $sheet->setCellValue("C{$rowNum}", '');
                    $sheet->setCellValueExplicit("D{$rowNum}", $subQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit("E{$rowNum}", $subSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                    $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($rowSubtotal);
                    $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                    $rowNum++;

                    // reset subtotal
                    $subQty = 0;
                    $subSales = 0;

                    // start group baru
                    $currentCust = $cust;
                    $startCustRow = $rowNum;
                }

                // group pertama
                if ($currentCust === null) {
                    $currentCust = $cust;
                    $startCustRow = $rowNum;
                }

                // baris detail
                $sheet->setCellValue("A{$rowNum}", ''); // diisi setelah merge
                $sheet->setCellValue("B{$rowNum}", $produk);
                $sheet->setCellValue("C{$rowNum}", $satuan);
                $sheet->setCellValueExplicit("D{$rowNum}", $qty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->setCellValueExplicit("E{$rowNum}", $sales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

                $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($tableBody);
                $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("C{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                // akumulasi
                $subQty += $qty;
                $subSales += $sales;
                $grandQty += $qty;
                $grandSales += $sales;

                $lastDetailRow = $rowNum;
                $rowNum++;
            }

            // tutup group terakhir (merge + subtotal)
            if ($startCustRow !== null && $lastDetailRow !== null) {
                if ($lastDetailRow > $startCustRow) {
                    $sheet->mergeCells("A{$startCustRow}:A{$lastDetailRow}");
                }
                $sheet->setCellValue("A{$startCustRow}", $currentCust);
                $sheet->getStyle("A{$startCustRow}:E{$lastDetailRow}")->applyFromArray($tableBody);
                $sheet->getStyle("A{$startCustRow}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            }

            // subtotal terakhir
            $sheet->setCellValue("A{$rowNum}", '');
            $sheet->setCellValue("B{$rowNum}", 'TOTAL NAMA BARANG');
            $sheet->setCellValue("C{$rowNum}", '');
            $sheet->setCellValueExplicit("D{$rowNum}", $subQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E{$rowNum}", $subSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($rowSubtotal);
            $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $rowNum++;

            // grand total (Total Pelanggan)
            $sheet->setCellValue("A{$rowNum}", 'TOTAL PELANGGAN');
            $sheet->setCellValue("B{$rowNum}", '');
            $sheet->setCellValue("C{$rowNum}", '');
            $sheet->setCellValueExplicit("D{$rowNum}", $grandQty, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit("E{$rowNum}", $grandSales, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($rowSubtotal);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
            $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("D{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
            $sheet->setCellValue("A{$rowNum}", 'Tidak ada data');
            $sheet->mergeCells("A{$rowNum}:E{$rowNum}");
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($tableBody);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        // filename aman
        $filePeriode = 'semua';
        if (!empty($tgl_dari) && !empty($tgl_sampai)) {
            $filePeriode = date('Ymd', strtotime($tgl_dari)) . '_' . date('Ymd', strtotime($tgl_sampai));
        } elseif (!empty($tgl_dari)) {
            $filePeriode = 'mulai_' . date('Ymd', strtotime($tgl_dari));
        } elseif (!empty($tgl_sampai)) {
            $filePeriode = 'sampai_' . date('Ymd', strtotime($tgl_sampai));
        }

        $filename = "Penjualan_Pelanggan_per_Barang_{$filePeriode}.xls";
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        ob_end_clean();
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $objWriter->save("php://output");
        exit;
    }
}
