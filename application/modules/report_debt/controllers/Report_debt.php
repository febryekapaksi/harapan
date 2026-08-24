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
        SUM(CASE WHEN DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) BETWEEN 15 AND 30 THEN a.piutang ELSE 0 END) as aging_15_30,
        SUM(CASE WHEN DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) > 30 THEN a.piutang ELSE 0 END) as aging_30_up
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

    /**
     * Export Detail Excel per Sales per Bulan
     * Parameter GET:
     * - tahun: tahun data
     * - bulan: nomor bulan (1-12)
     * - id_sales: ID karyawan/sales
     * - tipe: 'total' | 'late' (15-30 hari) | 'bad' (>30 hari)
     */
    public function export_detail()
    {
        $tahun    = $this->input->get('tahun') ?? date('Y');
        $bulan    = (int)($this->input->get('bulan') ?? date('n'));
        $id_sales = $this->input->get('id_sales');
        $tipe     = $this->input->get('tipe') ?? 'total';

        // Validasi
        if (empty($id_sales) || $bulan < 1 || $bulan > 12) {
            show_error('Parameter tidak valid.', 400);
            return;
        }

        // Ambil nama sales
        $sales = $this->db->where('id', $id_sales)->get('employee')->row_array();
        $nama_sales = $sales ? ucwords($sales['nm_karyawan']) : 'Unknown';

        // Ambil nama bulan
        $bln_row = $this->db->where('bulan_no', $bulan)->get('cr_bulan')->row_array();
        $nama_bulan = $bln_row ? $bln_row['bulan'] : 'Bulan ' . $bulan;

        // Query data detail berdasarkan tipe
        $awal_bulan  = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
        $akhir_bulan = date('Y-m-t', strtotime($awal_bulan));

        $this->db->select("a.id_invoice as no_invoice, a.nm_customer, a.created_on as tgl_invoice, a.jatuh_tempo, a.grand_total as total_invoice, a.total_bayar, a.piutang, DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) as hari_lewat", false);
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer', 'left');
        $this->db->where('b.id_karyawan', $id_sales);
        $this->db->where("YEAR(a.jatuh_tempo) = " . (int)$tahun, null, false);
        $this->db->where("MONTH(a.jatuh_tempo) = " . (int)$bulan, null, false);
        $this->db->where('a.piutang >', 0);

        if ($tipe == 'late') {
            // Aging 15-30 hari
            $this->db->where('DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) BETWEEN 15 AND 30', null, false);
            $judul = 'Detail Piutang Late Debt (15-30 Hari)';
            $filename = 'Detail_Late_Debt_' . str_replace(' ', '_', $nama_sales) . '_' . $nama_bulan . '_' . $tahun . '.xls';
        } elseif ($tipe == 'bad') {
            // Aging >30 hari
            $this->db->where('DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) > 30', null, false);
            $judul = 'Detail Piutang Bad Debt (> 30 Hari)';
            $filename = 'Detail_Bad_Debt_' . str_replace(' ', '_', $nama_sales) . '_' . $nama_bulan . '_' . $tahun . '.xls';
        } else {
            // Total piutang
            $judul = 'Detail Total Piutang';
            $filename = 'Detail_Total_Piutang_' . str_replace(' ', '_', $nama_sales) . '_' . $nama_bulan . '_' . $tahun . '.xls';
        }

        $this->db->order_by('a.jatuh_tempo', 'ASC');
        $query = $this->db->get();
        $data_detail = $query ? $query->result_array() : [];

        // Setup PHPExcel
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');
        PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

        $xls   = new PHPExcel();
        $sheet = $xls->getActiveSheet();

        // Judul
        $sheet->setCellValue('A1', strtoupper($judul));
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Info
        $sheet->setCellValue('A2', 'Sales: ' . $nama_sales);
        $sheet->setCellValue('A3', 'Periode: ' . $nama_bulan . ' ' . $tahun);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        // Header tabel
        $headers = ['A' => 'No', 'B' => 'No Invoice', 'C' => 'Customer', 'D' => 'Tgl Invoice', 'E' => 'Jatuh Tempo', 'F' => 'Hari Lewat', 'G' => 'Total Invoice', 'H' => 'Total Bayar', 'I' => 'Sisa Piutang'];
        $rowHeader = 5;
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $rowHeader, $label);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . $rowHeader)->getFont()->setBold(true);
            $sheet->getStyle($col . $rowHeader)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $rowHeader)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4472C4');
            $sheet->getStyle($col . $rowHeader)->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Isi data
        $r = $rowHeader + 1;
        $no = 1;
        $total_invoice_sum = 0;
        $total_bayar_sum = 0;
        $total_piutang_sum = 0;

        foreach ($data_detail as $row) {
            $sheet->setCellValue('A' . $r, $no);
            $sheet->setCellValue('B' . $r, $row['no_invoice']);
            $sheet->setCellValue('C' . $r, $row['nm_customer']);
            $sheet->setCellValue('D' . $r, $row['tgl_invoice']);
            $sheet->setCellValue('E' . $r, $row['jatuh_tempo']);

            $hari_lewat = (int)$row['hari_lewat'];
            $sheet->setCellValueExplicit('F' . $r, $hari_lewat, PHPExcel_Cell_DataType::TYPE_NUMERIC);

            $total_inv = (float)$row['total_invoice'];
            $total_bay = (float)$row['total_bayar'];
            $piutang   = (float)$row['piutang'];

            $sheet->setCellValueExplicit('G' . $r, $total_inv, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('G' . $r)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->setCellValueExplicit('H' . $r, $total_bay, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('H' . $r)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->setCellValueExplicit('I' . $r, $piutang, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('I' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // Highlight baris berdasarkan hari lewat
            if ($hari_lewat > 30) {
                $sheet->getStyle('A' . $r . ':I' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFCCCC');
            } elseif ($hari_lewat >= 15) {
                $sheet->getStyle('A' . $r . ':I' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
            }

            $total_invoice_sum += $total_inv;
            $total_bayar_sum += $total_bay;
            $total_piutang_sum += $piutang;

            $no++;
            $r++;
        }

        // Baris total
        $sheet->setCellValue('E' . $r, 'TOTAL');
        $sheet->getStyle('E' . $r)->getFont()->setBold(true);

        $sheet->setCellValueExplicit('G' . $r, $total_invoice_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('G' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G' . $r)->getFont()->setBold(true);

        $sheet->setCellValueExplicit('H' . $r, $total_bayar_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('H' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H' . $r)->getFont()->setBold(true);

        $sheet->setCellValueExplicit('I' . $r, $total_piutang_sum, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('I' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('I' . $r)->getFont()->setBold(true);

        // Border
        $sheet->getStyle('A' . $rowHeader . ':I' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        // Output
        $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
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
            SUM(CASE WHEN DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) BETWEEN 15 AND 30 THEN a.piutang ELSE 0 END) as aging_15_30,
            SUM(CASE WHEN DATEDIFF(LEAST(CURRENT_DATE, LAST_DAY(a.jatuh_tempo)), a.jatuh_tempo) > 30 THEN a.piutang ELSE 0 END) as aging_30_up
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
            $sheet->mergeCells('A' . $r . ':A' . ($r + 8));
            $sheet->getStyle('A' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            // --- BARIS 1: TOTAL PIUTANG ---
            $sheet->setCellValue('B' . $r, 'Total piutang per akhir bulan');
            $t_piutang = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $val = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $t_piutang += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_piutang, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // --- BARIS 2: TARGET % LATE (WARNA BIRU MUDA) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target % late debt');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $c = 'C';
            foreach ($bulan as $b) {
                $p_late = (float)($rekap_target[$s['id']][$b['bulan_no']]['late_p'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $p_late / 100, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%');
                $c++;
            }

            // --- BARIS 3: TARGET AMOUNT LATE DEBT (KUNING) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target amount late debt (amount)');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFont()->setBold(true);
            $t_late_amount = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $piutang_bln = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $p_late = (float)($rekap_target[$s['id']][$b['bulan_no']]['late_p'] ?? 0);
                $val = $piutang_bln * ($p_late / 100);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $t_late_amount += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_late_amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // --- BARIS 4: ACTUAL AMOUNT LATE DEBT (AGING 15-30, KUNING) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Actual amount late debt');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
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

            // --- BARIS 5: PERSENTASE ACTUAL AMOUNT (MERAH) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Persentase actual amount');
            $c = 'C';
            foreach ($bulan as $b) {
                $piutang_bln = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $aging_late  = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['aging_15_30'] ?? 0);
                $pct = $piutang_bln > 0 ? ($aging_late / $piutang_bln) : 0;
                $sheet->setCellValueExplicit($c . $r, $pct, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%');
                $sheet->getStyle($c . $r)->getFont()->getColor()->setRGB('FF0000');
                $c++;
            }
            $pct_total_late = $t_piutang > 0 ? ($t_1530 / $t_piutang) : 0;
            $sheet->setCellValueExplicit('O' . $r, $pct_total_late, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('O' . $r)->getFont()->getColor()->setRGB('FF0000');

            // --- BARIS 6: TARGET % BAD (WARNA BIRU MUDA) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target bad debt %');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $c = 'C';
            foreach ($bulan as $b) {
                $p_bad = (float)($rekap_target[$s['id']][$b['bulan_no']]['bad_p'] ?? 0);
                $sheet->setCellValueExplicit($c . $r, $p_bad / 100, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%');
                $c++;
            }

            // --- BARIS 7: TARGET AMOUNT BAD DEBT (KUNING) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Target amount bad debt (amount)');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFont()->setBold(true);
            $t_bad_amount = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $piutang_bln = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $p_bad = (float)($rekap_target[$s['id']][$b['bulan_no']]['bad_p'] ?? 0);
                $val = $piutang_bln * ($p_bad / 100);
                $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $t_bad_amount += $val;
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, (float)$t_bad_amount, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');

            // --- BARIS 8: ACTUAL AMOUNT BAD DEBT (AGING >30, KUNING) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Actual amount bad debt');
            $sheet->getStyle('B' . $r . ':O' . $r)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
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

            // --- BARIS 9: PERSENTASE ACTUAL AMOUNT BAD DEBT (MERAH) ---
            $r++;
            $sheet->setCellValue('B' . $r, 'Persentase actual amount bad debt');
            $c = 'C';
            foreach ($bulan as $b) {
                $piutang_bln = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                $aging_bad   = (float)($rekap_realisasi[$s['id']][$b['bulan_no']]['aging_30_up'] ?? 0);
                $pct = $piutang_bln > 0 ? ($aging_bad / $piutang_bln) : 0;
                $sheet->setCellValueExplicit($c . $r, $pct, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('0.00%');
                $sheet->getStyle($c . $r)->getFont()->getColor()->setRGB('FF0000');
                $c++;
            }
            $pct_total_bad = $t_piutang > 0 ? ($t_30up / $t_piutang) : 0;
            $sheet->setCellValueExplicit('O' . $r, $pct_total_bad, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('0.00%');
            $sheet->getStyle('O' . $r)->getFont()->getColor()->setRGB('FF0000');

            $r++;
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
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Report_Debt_' . $tahun . '.xls"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
