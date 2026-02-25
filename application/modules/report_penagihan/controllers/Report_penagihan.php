<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_penagihan extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Report_Penagihan.View';
    protected $addPermission    = 'Report_Penagihan.Add';
    protected $managePermission = 'Report_Penagihan.Manage';
    protected $deletePermission = 'Report_Penagihan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_penagihan/Report_penagihan_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-credit-card');
        $this->template->title('Report Tagihan vs Penerimaan');

        $tahun = $this->input->get('tahun') ?? date('Y');

        // 1. Ambil Data Sales & Bulan
        $sales = $this->db->where('department', '2')->get('employee')->result_array();
        $bulan = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();

        // 2. Query Realisasi Tagihan & Penerimaan
        $this->db->select("
        c.id as id_sales,
        MONTH(a.jatuh_tempo) as bulan,
        SUM(a.piutang) as total_tagihan,
        SUM(a.total_bayar) as total_penerimaan
        ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $query_data = $this->db->get()->result_array();

        $rekap = [];
        foreach ($query_data as $row) {
            $rekap[$row['id_sales']][$row['bulan']] = [
                'tagihan' => $row['total_tagihan'],
                'penerimaan' => $row['total_penerimaan']
            ];
        }

        $data = [
            'sales' => $sales,
            'bulan' => $bulan,
            'rekap' => $rekap,
            'tahun_pilih' => $tahun
        ];

        $this->template->render('index', $data);
    }

    public function export_excel()
    {
        $tahun = $this->input->get('tahun') ?? date('Y');

        // 1. Ambil Data (Sama dengan logic Index)
        $sales = $this->db->where('department', '2')->get('employee')->result_array();
        $bulan = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();

        $this->db->select("
        c.id as id_sales,
        MONTH(a.jatuh_tempo) as bulan,
        SUM(a.piutang) as total_tagihan,
        SUM(a.total_bayar) as total_penerimaan
    ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $query_data = $this->db->get()->result_array();

        $rekap = [];
        foreach ($query_data as $row) {
            $rekap[$row['id_sales']][$row['bulan']] = [
                'tagihan' => $row['total_tagihan'],
                'penerimaan' => $row['total_penerimaan']
            ];
        }

        // 2. Setup PHPExcel
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');
        PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

        $xls   = new PHPExcel();
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('A1', 'REPORT TAGIHAN VS PENERIMAAN - TAHUN ' . $tahun);
        $sheet->mergeCells('A1:O2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 3. Header Tabel
        $headers = ['A' => 'Nama Sales', 'B' => 'Keterangan'];
        $col = 'C';
        foreach ($bulan as $b) {
            $headers[$col] = substr($b['bulan'], 0, 3);
            $col++;
        }
        $headers['O'] = 'T Score';

        $rowHeader = 4;
        foreach ($headers as $c => $label) {
            $sheet->setCellValue($c . $rowHeader, $label);
            $sheet->getColumnDimension($c)->setAutoSize(true);
            $sheet->getStyle($c . $rowHeader)->getFont()->setBold(true);
            $sheet->getStyle($c . $rowHeader)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        // 4. Isi Data Sales
        $r = $rowHeader + 1;
        $grand_total_tagihan = array_fill(1, 12, 0);
        $grand_total_penerimaan = array_fill(1, 12, 0);

        foreach ($sales as $s) {
            // Merge Nama Sales
            $sheet->setCellValue('A' . $r, strtoupper($s['nm_karyawan']));
            $sheet->mergeCells('A' . $r . ':A' . ($r + 1));
            $sheet->getStyle('A' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            // Baris Tagihan
            $sheet->setCellValue('B' . $r, 'Total tagihan');
            $row_t_tagihan = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap[$s['id']][$b['bulan_no']]['tagihan'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $row_t_tagihan += $val;
                $grand_total_tagihan[$b['bulan_no']] += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, $row_t_tagihan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('O' . $r)->getFont()->setBold(true);

            // Baris Penerimaan
            $r++;
            $sheet->setCellValue('B' . $r, 'Total penerimaan');
            $row_t_penerimaan = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap[$s['id']][$b['bulan_no']]['penerimaan'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $row_t_penerimaan += $val;
                $grand_total_penerimaan[$b['bulan_no']] += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, $row_t_penerimaan, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('O' . $r)->getFont()->setBold(true);

            $r++; // Baris baru untuk sales berikutnya
        }

        // 5. Baris Target Cabang (Grand Total)
        $sheet->setCellValue('A' . $r, 'Target Cabang');
        $sheet->mergeCells('A' . $r . ':A' . ($r + 1));
        $sheet->getStyle('A' . $r . ':O' . ($r + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        $sheet->setCellValue('B' . $r, 'Total tagihan');
        $c = 'C';
        $total_cabang_t = 0;
        foreach ($grand_total_tagihan as $gt) {
            $sheet->setCellValueExplicit($c . $r, $gt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($c . $r)->getFont()->setBold(true);

            $total_cabang_t += $gt;
            $c++;
        }
        $sheet->setCellValueExplicit('O' . $r, $total_cabang_t, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('O' . $r)->getFont()->setBold(true);

        $r++;
        $sheet->setCellValue('B' . $r, 'Total penerimaan');
        $c = 'C';
        $total_cabang_p = 0;
        foreach ($grand_total_penerimaan as $gp) {
            $sheet->setCellValueExplicit($c . $r, $gp, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle($c . $r)->getFont()->setBold(true);
            $total_cabang_p += $gp;
            $c++;
        }
        $sheet->setCellValueExplicit('O' . $r, $total_cabang_p, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('O' . $r)->getFont()->setBold(true);

        // 6. Styling Akhir
        $sheet->getStyle('A4:O' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        // 7. Output
        $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Report_Penagihan_' . $tahun . '.xls"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
