<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_debt extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Report_Debt.View';
    protected $addPermission    = 'Report_Debt.Add';
    protected $managePermission = 'Report_Debt.Manage';
    protected $deletePermission = 'Report_Debt.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Report_product/Report_product_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Report Late & Bad Debt per Sales');
        $this->template->page_icon('fa fa-list');

        $tahun = $this->input->get('tahun') ?? date('Y');

        $targets = $this->db->get_where('master_debt', ['tahun' => $tahun])->result_array();
        $rekap_target = [];
        foreach ($targets as $t) {
            $rekap_target[$t['id_sales']][$t['bulan']] = [
                'late_p' => $t['target_late_debt'],
                'bad_p'  => $t['target_bad_debt']
            ];
        }

        $this->db->select("
        c.id as id_sales,
        MONTH(a.jatuh_tempo) as bulan,
        SUM(a.piutang) as total_piutang,
        SUM(CASE WHEN DATEDIFF(CURRENT_DATE, a.jatuh_tempo) BETWEEN 15 AND 30 THEN a.piutang ELSE 0 END) as aging_15_30,
        SUM(CASE WHEN DATEDIFF(CURRENT_DATE, a.jatuh_tempo) > 30 THEN a.piutang ELSE 0 END) as aging_30_up
        ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $realisasi = $this->db->get()->result_array();

        $rekap_realisasi = [];
        foreach ($realisasi as $r) {
            $rekap_realisasi[$r['id_sales']][$r['bulan']] = $r;
        }

        $data = [
            'bulan' => $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array(),
            'sales' => $this->db->where('department', '2')->get('employee')->result_array(),
            'target' => $rekap_target,
            'realisasi' => $rekap_realisasi,
            'tahun_pilih' => $tahun
        ];

        $this->template->render('index', $data);
    }

    public function export_excel()
    {
        // 1) Ambil parameter & data
        $tahun = $this->input->get('tahun') ?? date('Y');

        $bulan = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();
        $sales = $this->db->where('department', '2')->get('employee')->result_array();

        // Ambil Data Target dari master_debt
        $targets = $this->db->get_where('master_debt', ['tahun' => $tahun])->result_array();
        $rekap_target = [];
        foreach ($targets as $t) {
            $rekap_target[$t['id_sales']][$t['bulan']] = [
                'late_p' => (float)$t['target_late_debt'],
                'bad_p'  => (float)$t['target_bad_debt']
            ];
        }

        // Ambil Realisasi Piutang & Aging dari tr_invoice_sales
        $this->db->select("
            c.id as id_sales, 
            MONTH(a.jatuh_tempo) as bulan,
            SUM(a.piutang) as total_piutang,
            SUM(CASE WHEN DATEDIFF(CURRENT_DATE, a.jatuh_tempo) BETWEEN 15 AND 30 THEN a.piutang ELSE 0 END) as aging_15_30,
            SUM(CASE WHEN DATEDIFF(CURRENT_DATE, a.jatuh_tempo) > 30 THEN a.piutang ELSE 0 END) as aging_30_up
        ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $realisasi = $this->db->get()->result_array();


        $rekap_realisasi = [];
        foreach ($realisasi as $r) {
            $rekap_realisasi[$r['id_sales']][$r['bulan']] = $r;
        }

        // 2) Load Library & Binder (Penting untuk mencegah eror offset)
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');

        // Set binder agar PHPExcel tidak bingung membedakan angka dan string
        PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

        $xls   = new PHPExcel();
        $sheet = $xls->getActiveSheet();

        // 3) Styling Judul
        $sheet->setCellValue('A1', 'REPORT LATE & BAD DEBT PER SALES - TAHUN ' . $tahun);
        $sheet->mergeCells('A1:O2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Header Kolom
        $headers = ['A' => 'Nama Sales', 'B' => 'Keterangan'];
        $col = 'C';
        foreach ($bulan as $b) {
            $headers[$col] = $b['bulan']; // Gunakan field nama bulan agar lebih jelas
            $col++;
        }
        $headers['O'] = 'T Score';

        $rowHeader = 4;
        foreach ($headers as $c => $label) {
            $sheet->setCellValue($c . $rowHeader, $label);
            $sheet->getColumnDimension($c)->setAutoSize(true);
            $sheet->getStyle($c . $rowHeader)->getFont()->setBold(true);
        }

        // 4) Isi Data
        $r = $rowHeader + 1;
        foreach ($sales as $s) {
            // Styling Nama Sales
            $sheet->setCellValue('A' . $r, strtoupper($s['nm_karyawan']));
            $sheet->mergeCells('A' . $r . ':A' . ($r + 4));
            $sheet->getStyle('A' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            // --- BARIS 1: TOTAL PIUTANG ---
            $sheet->setCellValue('B' . $r, 'Total piutang per akhir bulan');
            $t_piutang = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0'); // Format Ribuan
                $t_piutang += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_piutang, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // --- BARIS 2: TARGET % LATE (WARNA BIRU MUDA) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target % late debt');
            // Set Background Warna Biru Muda
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $c = 'C';
            foreach ($bulan as $b) {
                $p_late = (float)($rekap_target[$s['id']][$b['bulan_no']]['late_p'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $p_late / 100, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%'); // Format Persen
                $c++;
            }

            // --- BARIS 3: AGING 15-30 ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Total piutang berumur 15-30 hari');
            $t_1530 = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['aging_15_30'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $t_1530 += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_1530, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // --- BARIS 4: TARGET % BAD (WARNA BIRU MUDA) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target bad debt %');
            // Set Background Warna Biru Muda
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $c = 'C';
            foreach ($bulan as $b) {
                $p_bad = (float)($rekap_target[$s['id']][$b['bulan_no']]['bad_p'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $p_bad / 100, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%');
                $c++;
            }

            // --- BARIS 5: AGING >30 ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Total piutang berumur > 30 hari');
            $t_30up = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['aging_30_up'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $t_30up += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_30up, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            $r++; // Spasi untuk sales berikutnya
        }

        // Tambahkan Border untuk semua data yang terisi
        $sheet->getStyle('A4:O' . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        // 5) Styling Terakhir: Tambah Border & Format Angka
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            )
        );
        $sheet->getStyle('A4:O' . ($r - 1))->applyFromArray($styleArray);
        // $sheet->getStyle('C5:O' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0');

        // 6) Output sebagai Excel5 (.xls)
        $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Report_Debt_' . $tahun . '.xls"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
