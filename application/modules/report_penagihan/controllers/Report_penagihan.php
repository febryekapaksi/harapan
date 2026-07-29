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
        $this->template->title('Report Rencana Penagihan vs Realisasi Tagihan');

        $tahun = $this->input->get('tahun') ?? date('Y');
        $bulan_sekarang = (int) date('n'); // bulan saat ini (1-12)
        $tahun_sekarang = (int) date('Y');

        // 1. Ambil Data Sales & Bulan
        $sales = $this->db->where('department', '2')->get('employee')->result_array();
        $bulan = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();

        // 2. Query Target Tagihan (Rencana Penagihan)
        // Target = tagihan yang sudah jatuh tempo bulan sebelumnya + akan jatuh tempo bulan berjalan
        // Artinya: semua invoice yang jatuh tempo <= akhir bulan tersebut dan belum lunas
        $rekap_target = [];
        foreach ($sales as $s) {
            for ($m = 1; $m <= 12; $m++) {
                // Hanya isi sampai bulan berjalan (jika tahun yang dipilih = tahun sekarang)
                if ($tahun == $tahun_sekarang && $m > $bulan_sekarang) {
                    continue;
                }

                // Target tagihan = invoice yang jatuh tempo <= akhir bulan ini
                // (mencakup yang sudah jatuh tempo di bulan-bulan sebelumnya + yang jatuh tempo di bulan ini)
                $akhir_bulan = date('Y-m-t', strtotime("$tahun-$m-01"));

                $this->db->select("SUM(a.piutang) as target_tagihan", false);
                $this->db->from('tr_invoice_sales a');
                $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
                $this->db->join('employee c', 'b.id_karyawan = c.id');
                $this->db->where('c.id', $s['id']);
                $this->db->where('a.jatuh_tempo <=', $akhir_bulan);
                $this->db->where('a.piutang >', 0);
                $result = $this->db->get()->row_array();

                $rekap_target[$s['id']][$m] = (float)($result['target_tagihan'] ?? 0);
            }
        }

        // 3. Query Realisasi Tagihan (total pembayaran yang diterima per bulan)
        $this->db->select("
        c.id as id_sales,
        MONTH(a.jatuh_tempo) as bulan,
        SUM(a.total_bayar) as total_realisasi
        ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $query_data = $this->db->get()->result_array();

        $rekap_realisasi = [];
        foreach ($query_data as $row) {
            $rekap_realisasi[$row['id_sales']][$row['bulan']] = (float)$row['total_realisasi'];
        }

        $data = [
            'sales' => $sales,
            'bulan' => $bulan,
            'rekap_target' => $rekap_target,
            'rekap_realisasi' => $rekap_realisasi,
            'tahun_pilih' => $tahun,
            'bulan_sekarang' => $bulan_sekarang,
            'tahun_sekarang' => $tahun_sekarang,
        ];

        $this->template->render('index', $data);
    }

    public function export_excel()
    {
        $tahun = $this->input->get('tahun') ?? date('Y');
        $bulan_sekarang = (int) date('n');
        $tahun_sekarang = (int) date('Y');

        // 1. Ambil Data Sales & Bulan
        $sales = $this->db->where('department', '2')->get('employee')->result_array();
        $bulan = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();

        // 2. Query Target Tagihan (Rencana Penagihan)
        $rekap_target = [];
        foreach ($sales as $s) {
            for ($m = 1; $m <= 12; $m++) {
                if ($tahun == $tahun_sekarang && $m > $bulan_sekarang) {
                    continue;
                }
                $akhir_bulan = date('Y-m-t', strtotime("$tahun-$m-01"));

                $this->db->select("SUM(a.piutang) as target_tagihan", false);
                $this->db->from('tr_invoice_sales a');
                $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
                $this->db->join('employee c', 'b.id_karyawan = c.id');
                $this->db->where('c.id', $s['id']);
                $this->db->where('a.jatuh_tempo <=', $akhir_bulan);
                $this->db->where('a.piutang >', 0);
                $result = $this->db->get()->row_array();

                $rekap_target[$s['id']][$m] = (float)($result['target_tagihan'] ?? 0);
            }
        }

        // 3. Query Realisasi Tagihan
        $this->db->select("
        c.id as id_sales,
        MONTH(a.jatuh_tempo) as bulan,
        SUM(a.total_bayar) as total_realisasi
        ");
        $this->db->from('tr_invoice_sales a');
        $this->db->join('master_customers b', 'a.id_customer = b.id_customer');
        $this->db->join('employee c', 'b.id_karyawan = c.id');
        $this->db->where('YEAR(a.jatuh_tempo)', $tahun);
        $this->db->group_by('c.id, MONTH(a.jatuh_tempo)');
        $query_data = $this->db->get()->result_array();

        $rekap_realisasi = [];
        foreach ($query_data as $row) {
            $rekap_realisasi[$row['id_sales']][$row['bulan']] = (float)$row['total_realisasi'];
        }

        // 4. Setup PHPExcel
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $this->load->library('PHPExcel');
        PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

        $xls   = new PHPExcel();
        $sheet = $xls->getActiveSheet();

        $sheet->setCellValue('A1', 'REPORT RENCANA PENAGIHAN VS REALISASI TAGIHAN - TAHUN ' . $tahun);
        $sheet->mergeCells('A1:O2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // 5. Header Tabel
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

        // 6. Isi Data Sales
        $r = $rowHeader + 1;
        $grand_total_target = array_fill(1, 12, 0);
        $grand_total_realisasi = array_fill(1, 12, 0);

        foreach ($sales as $s) {
            // Merge Nama Sales
            $sheet->setCellValue('A' . $r, strtoupper($s['nm_karyawan']));
            $sheet->mergeCells('A' . $r . ':A' . ($r + 1));
            $sheet->getStyle('A' . $r)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            // Baris Rencana Penagihan (Target)
            $sheet->setCellValue('B' . $r, 'Rencana Penagihan');
            $row_t_target = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $bln_no = (int)$b['bulan_no'];
                // Kosongkan bulan yang belum terjadi
                if ($tahun == $tahun_sekarang && $bln_no > $bulan_sekarang) {
                    $sheet->setCellValue($c . $r, '-');
                    $sheet->getStyle($c . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                } else {
                    $val = (float)($rekap_target[$s['id']][$bln_no] ?? 0);
                    $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                    $row_t_target += $val;
                    $grand_total_target[$bln_no] += $val;
                }
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, $row_t_target, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('O' . $r)->getFont()->setBold(true);

            // Baris Realisasi Tagihan
            $r++;
            $sheet->setCellValue('B' . $r, 'Realisasi Tagihan');
            $row_t_realisasi = 0;
            $c = 'C';
            foreach ($bulan as $b) {
                $bln_no = (int)$b['bulan_no'];
                if ($tahun == $tahun_sekarang && $bln_no > $bulan_sekarang) {
                    $sheet->setCellValue($c . $r, '-');
                    $sheet->getStyle($c . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                } else {
                    $val = (float)($rekap_realisasi[$s['id']][$bln_no] ?? 0);
                    $sheet->setCellValueExplicit($c . $r, $val, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                    $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                    $row_t_realisasi += $val;
                    $grand_total_realisasi[$bln_no] += $val;
                }
                $c++;
            }
            $sheet->setCellValueExplicit('O' . $r, $row_t_realisasi, PHPExcel_Cell_DataType::TYPE_NUMERIC);
            $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('O' . $r)->getFont()->setBold(true);

            $r++;
        }

        // 7. Baris Target Cabang (Grand Total)
        $sheet->setCellValue('A' . $r, 'Target Cabang');
        $sheet->mergeCells('A' . $r . ':A' . ($r + 1));
        $sheet->getStyle('A' . $r . ':O' . ($r + 1))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        $sheet->setCellValue('B' . $r, 'Rencana Penagihan');
        $c = 'C';
        $total_cabang_t = 0;
        foreach ($bulan as $b) {
            $bln_no = (int)$b['bulan_no'];
            if ($tahun == $tahun_sekarang && $bln_no > $bulan_sekarang) {
                $sheet->setCellValue($c . $r, '-');
                $sheet->getStyle($c . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                $gt = $grand_total_target[$bln_no];
                $sheet->setCellValueExplicit($c . $r, $gt, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($c . $r)->getFont()->setBold(true);
                $total_cabang_t += $gt;
            }
            $c++;
        }
        $sheet->setCellValueExplicit('O' . $r, $total_cabang_t, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('O' . $r)->getFont()->setBold(true);

        $r++;
        $sheet->setCellValue('B' . $r, 'Realisasi Tagihan');
        $c = 'C';
        $total_cabang_r = 0;
        foreach ($bulan as $b) {
            $bln_no = (int)$b['bulan_no'];
            if ($tahun == $tahun_sekarang && $bln_no > $bulan_sekarang) {
                $sheet->setCellValue($c . $r, '-');
                $sheet->getStyle($c . $r)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            } else {
                $gp = $grand_total_realisasi[$bln_no];
                $sheet->setCellValueExplicit($c . $r, $gp, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $sheet->getStyle($c . $r)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle($c . $r)->getFont()->setBold(true);
                $total_cabang_r += $gp;
            }
            $c++;
        }
        $sheet->setCellValueExplicit('O' . $r, $total_cabang_r, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $sheet->getStyle('O' . $r)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('O' . $r)->getFont()->setBold(true);

        // 8. Styling Akhir
        $sheet->getStyle('A4:O' . $r)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        // 9. Output
        $writer = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
        ob_end_clean();
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Report_Rencana_Penagihan_' . $tahun . '.xls"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
