<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Amortisasi_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // -----------------------------------------------------------------------
    // HELPER – nama bulan
    // -----------------------------------------------------------------------
    public function getBulanNama()
    {
        return array(
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        );
    }

    // -----------------------------------------------------------------------
    // MASTER – simpan kontrak baru
    // -----------------------------------------------------------------------
    public function simpan($data)
    {
        $this->db->insert('amortisasi', $data);
        return $this->db->insert_id();
    }

    // -----------------------------------------------------------------------
    // MASTER – update kontrak
    // -----------------------------------------------------------------------
    public function updateItem($id, $data)
    {
        $this->db->where('id', (int)$id)->update('amortisasi', $data);
        return $this->db->affected_rows();
    }

    // -----------------------------------------------------------------------
    // MASTER – ambil satu record
    // -----------------------------------------------------------------------
    public function getById($id)
    {
        return $this->db->get_where('amortisasi', array('id' => (int)$id))->row_array();
    }

    // -----------------------------------------------------------------------
    // MASTER – generate kode otomatis: AMT-YYYYMM-XXX
    // -----------------------------------------------------------------------
    public function generateKode()
    {
        $prefix = 'AMT-' . date('Ym') . '-';
        $sql    = "SELECT kode FROM amortisasi WHERE kode LIKE '" . $prefix . "%' ORDER BY kode DESC LIMIT 1";
        $row    = $this->db->query($sql)->row_array();

        if (empty($row)) {
            return $prefix . '001';
        }

        $last_num = (int)substr($row['kode'], -3);
        return $prefix . str_pad($last_num + 1, 3, '0', STR_PAD_LEFT);
    }

    // -----------------------------------------------------------------------
    // SCHEDULE – generate jadwal bulanan dari data kontrak
    // Menghitung pro-rata untuk bulan pertama jika mulai di tengah bulan
    // -----------------------------------------------------------------------
    public function generateSchedule($amortisasi_id, $nama_item, $total_debit, $tgl_mulai, $tgl_selesai)
    {
        // Hapus schedule lama jika ada (untuk regenerate)
        $this->db->where('amortisasi_id', (int)$amortisasi_id)->delete('amortisasi_schedule');

        $tgl_mulai_obj   = new DateTime($tgl_mulai);
        $tgl_selesai_obj = new DateTime($tgl_selesai);

        // Hitung total bulan penuh (dari awal bulan mulai s/d akhir bulan selesai)
        $bln_mulai   = (int)$tgl_mulai_obj->format('m');
        $thn_mulai   = (int)$tgl_mulai_obj->format('Y');
        $bln_selesai = (int)$tgl_selesai_obj->format('m');
        $thn_selesai = (int)$tgl_selesai_obj->format('Y');

        $total_bulan = (($thn_selesai - $thn_mulai) * 12) + ($bln_selesai - $bln_mulai) + 1;

        if ($total_bulan <= 0) {
            return 0;
        }

        // Nilai amortisasi per bulan penuh
        $nilai_per_bulan = round($total_debit / $total_bulan, 2);

        // Hitung pro-rata bulan pertama
        // Jika mulai tanggal 1, tidak ada pro-rata
        $hari_mulai      = (int)$tgl_mulai_obj->format('d');
        $hari_dalam_bulan = (int)$tgl_mulai_obj->format('t'); // total hari di bulan mulai

        if ($hari_mulai == 1) {
            $nilai_bulan_pertama = $nilai_per_bulan;
        } else {
            // Pro-rata: sisa hari / total hari di bulan itu
            $sisa_hari           = $hari_dalam_bulan - $hari_mulai + 1;
            $nilai_bulan_pertama = round($nilai_per_bulan * $sisa_hari / $hari_dalam_bulan, 2);
        }

        // Hitung total nilai yang sudah dialokasikan (untuk koreksi bulan terakhir)
        $total_dialokasikan = $nilai_bulan_pertama + ($nilai_per_bulan * ($total_bulan - 1));
        $selisih            = round($total_debit - $total_dialokasikan, 2);

        $schedule = array();
        $saldo    = (float)$total_debit;

        for ($i = 0; $i < $total_bulan; $i++) {
            $bulan_ke = str_pad($bln_mulai + $i > 12
                ? ($bln_mulai + $i) % 12 == 0 ? 12 : ($bln_mulai + $i) % 12
                : $bln_mulai + $i, 2, '0', STR_PAD_LEFT);

            $tahun_ke = $thn_mulai + (int)floor(($bln_mulai - 1 + $i) / 12);

            // Nilai amortisasi bulan ini
            if ($i == 0) {
                $nilai = $nilai_bulan_pertama;
            } elseif ($i == $total_bulan - 1) {
                // Bulan terakhir: sisa saldo (untuk menghindari selisih pembulatan)
                $nilai = $saldo;
            } else {
                $nilai = $nilai_per_bulan;
            }

            $saldo_awal  = $saldo;
            $saldo_akhir = round($saldo - $nilai, 2);
            $saldo       = $saldo_akhir;

            $schedule[] = array(
                'amortisasi_id' => (int)$amortisasi_id,
                'bulan'         => $bulan_ke,
                'tahun'         => (string)$tahun_ke,
                'nilai_amort'   => $nilai,
                'saldo_awal'    => $saldo_awal,
                'saldo_akhir'   => max(0, $saldo_akhir),
                'flag'          => 'N'
            );
        }

        if (!empty($schedule)) {
            $this->db->insert_batch('amortisasi_schedule', $schedule);
        }

        return count($schedule);
    }

    // -----------------------------------------------------------------------
    // SCHEDULE – ambil jadwal per item
    // -----------------------------------------------------------------------
    public function getScheduleByItem($amortisasi_id)
    {
        return $this->db->get_where(
            'amortisasi_schedule',
            array('amortisasi_id' => (int)$amortisasi_id)
        )->result_array();
    }

    // -----------------------------------------------------------------------
    // SCHEDULE – ambil jadwal untuk dijurnal (bulan/tahun tertentu)
    // -----------------------------------------------------------------------
    public function getScheduleUntukJurnal($bulan, $tahun, $kdcab = '')
    {
        $where_kdcab = !empty($kdcab)
            ? " AND a.kdcab = '" . $this->db->escape_str($kdcab) . "'"
            : '';

        $sql = "
            SELECT
                s.id            AS schedule_id,
                s.amortisasi_id,
                s.bulan,
                s.tahun,
                s.nilai_amort,
                s.saldo_awal,
                s.saldo_akhir,
                s.flag,
                a.kode,
                a.nama_item,
                a.coa_kredit,
                a.nm_coa_kredit,
                a.coa_debit,
                a.nm_coa_debit,
                a.kdcab
            FROM amortisasi_schedule s
            JOIN amortisasi a ON s.amortisasi_id = a.id
            WHERE s.bulan  = '" . $this->db->escape_str($bulan) . "'
              AND s.tahun  = '" . $this->db->escape_str($tahun) . "'
              AND a.status = 'active'
              AND s.flag   = 'N'
              " . $where_kdcab . "
            ORDER BY a.kode
        ";

        return $this->db->query($sql)->result_array();
    }

    // -----------------------------------------------------------------------
    // SCHEDULE – cek apakah bulan/tahun sudah dijurnal
    // -----------------------------------------------------------------------
    public function cekJurnalBulan($bulan, $tahun, $kdcab = '')
    {
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $sql = "
            SELECT COUNT(*) AS jml FROM " . DBACC . ".jurnal
            WHERE jenis_trans = 'amortisasi'
              AND MONTH(tanggal) = '" . (int)$bulan . "'
              AND YEAR(tanggal)  = '" . (int)$tahun . "'
        ";
        $result = $this->db->query($sql)->result_array();
        return ($result[0]['jml'] > 0);
    }

    // -----------------------------------------------------------------------
    // DASHBOARD – hitung total saldo remaining (belum diamortisasi)
    // -----------------------------------------------------------------------
    public function getSaldoRemaining($kdcab = '')
    {
        $where_kdcab = !empty($kdcab)
            ? " AND a.kdcab = '" . $this->db->escape_str($kdcab) . "'"
            : '';

        $sql = "
            SELECT
                COALESCE(SUM(s.saldo_akhir), 0) AS total_remaining
            FROM amortisasi_schedule s
            JOIN amortisasi a ON s.amortisasi_id = a.id
            WHERE s.flag   = 'N'
              AND a.status = 'active'
              AND CONCAT(s.tahun, '-', s.bulan) <= DATE_FORMAT(NOW(), '%Y-%m')
              " . $where_kdcab . "
        ";
        $row = $this->db->query($sql)->row_array();
        return (float)($row['total_remaining'] ?? 0);
    }

    // -----------------------------------------------------------------------
    // DASHBOARD – ringkasan per item (untuk info card)
    // -----------------------------------------------------------------------
    public function getDashboardSummary($kdcab = '')
    {
        $where_kdcab = !empty($kdcab)
            ? " AND a.kdcab = '" . $this->db->escape_str($kdcab) . "'"
            : '';

        $sql = "
            SELECT
                COUNT(DISTINCT CASE WHEN a.status = 'active' THEN a.id END)     AS jml_active,
                COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END)  AS jml_completed,
                COUNT(DISTINCT CASE WHEN a.status = 'terminated' THEN a.id END) AS jml_terminated,
                COALESCE(SUM(CASE WHEN a.status = 'active' AND s.flag = 'N'
                    AND CONCAT(s.tahun,'-',s.bulan) <= DATE_FORMAT(NOW(),'%Y-%m')
                    THEN s.saldo_akhir ELSE 0 END), 0) AS total_remaining,
                COALESCE(SUM(CASE WHEN s.flag = 'Y' THEN s.nilai_amort ELSE 0 END), 0) AS total_sudah_amort
            FROM amortisasi a
            LEFT JOIN amortisasi_schedule s ON a.id = s.amortisasi_id
            WHERE 1=1 " . $where_kdcab . "
        ";
        return $this->db->query($sql)->row_array();
    }

    // -----------------------------------------------------------------------
    // DATATABLES – daftar master amortisasi
    // -----------------------------------------------------------------------
    public function getDataJSON()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'];
        $col         = $requestData['order'][0]['column'];
        $dir         = $requestData['order'][0]['dir'];
        $start       = $requestData['start'];
        $length      = $requestData['length'];

        $filter_status = isset($requestData['filter_status']) ? $requestData['filter_status'] : '';

        $where_status = !empty($filter_status)
            ? " AND a.status = '" . $this->db->escape_str($filter_status) . "'"
            : '';

        $sql = "
            SELECT
                a.id,
                a.kode,
                a.nama_item,
                a.total_debit,
                a.tgl_mulai,
                a.tgl_selesai,
                a.coa_kredit,
                a.nm_coa_kredit,
                a.coa_debit,
                a.nm_coa_debit,
                a.status,
                a.kdcab,
                COALESCE(SUM(CASE WHEN s.flag = 'Y' THEN s.nilai_amort ELSE 0 END), 0) AS sudah_amort,
                COALESCE(SUM(CASE WHEN s.flag = 'N' THEN s.nilai_amort ELSE 0 END), 0) AS sisa_amort,
                COUNT(s.id) AS total_bulan,
                SUM(CASE WHEN s.flag = 'Y' THEN 1 ELSE 0 END) AS bulan_selesai
            FROM amortisasi a
            LEFT JOIN amortisasi_schedule s ON a.id = s.amortisasi_id
            WHERE (
                a.nama_item LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR a.kode LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR a.nm_coa_debit LIKE '%" . $this->db->escape_like_str($search) . "%'
            )
            " . $where_status . "
            GROUP BY a.id
        ";

        $totalData     = $this->db->query($sql)->num_rows();
        $totalFiltered = $totalData;

        $columns_order = array(
            0 => 'a.id',
            1 => 'a.kode',
            2 => 'a.nama_item',
            3 => 'a.total_debit',
            4 => 'a.tgl_mulai',
            5 => 'a.tgl_selesai',
            6 => 'sudah_amort',
            7 => 'sisa_amort',
            8 => 'a.status'
        );

        $order_col = isset($columns_order[$col]) ? $columns_order[$col] : 'a.id';
        $sql .= " ORDER BY " . $order_col . " " . $dir;
        $sql .= " LIMIT " . (int)$start . ", " . (int)$length;

        $query = $this->db->query($sql)->result_array();

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;

        foreach ($query as $row) {
            $nomor = ($requestData['order'][0]['dir'] == 'asc')
                ? $urut1 + $requestData['start']
                : ($totalData - $requestData['start']) - $urut2;

            // Badge status
            switch ($row['status']) {
                case 'active':
                    $badge_status = "<span class='badge bg-green'>Active</span>";
                    break;
                case 'completed':
                    $badge_status = "<span class='badge bg-blue'>Completed</span>";
                    break;
                case 'terminated':
                    $badge_status = "<span class='badge bg-red'>Terminated</span>";
                    break;
                default:
                    $badge_status = "<span class='badge bg-gray'>" . $row['status'] . "</span>";
            }

            // Progress bar
            $pct = ($row['total_debit'] > 0)
                ? round(($row['sudah_amort'] / $row['total_debit']) * 100)
                : 0;
            $progress = "<div class='progress progress-xs' style='margin-bottom:0;'>
                            <div class='progress-bar progress-bar-blue' style='width:{$pct}%'></div>
                         </div>
                         <small>{$pct}%</small>";

            // Tombol aksi
            $btn_detail = "<button type='button' class='btn btn-xs btn-info btn-detail'
                            data-id='" . $row['id'] . "' title='Lihat Jadwal'>
                            <i class='fa fa-list'></i></button>";

            $btn_edit = ($row['status'] == 'active')
                ? " <button type='button' class='btn btn-xs btn-warning btn-edit'
                     data-id='" . $row['id'] . "' title='Edit'>
                     <i class='fa fa-pencil'></i></button>"
                : '';

            $btn_terminate = ($row['status'] == 'active')
                ? " <button type='button' class='btn btn-xs btn-danger btn-terminate'
                     data-id='" . $row['id'] . "'
                     data-nama='" . htmlspecialchars($row['nama_item']) . "'
                     title='Stop/Terminate'>
                     <i class='fa fa-stop-circle'></i></button>"
                : '';

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='center'><small>" . $row['kode'] . "</small></div>";
            $nestedData[] = "<div align='left'><b>" . $row['nama_item'] . "</b><br>
                             <small class='text-muted'>" . $row['nm_coa_debit'] . "</small></div>";
            $nestedData[] = "<div align='right'>" . number_format($row['total_debit']) . "</div>";
            $nestedData[] = "<div align='center'>" . date('d/m/Y', strtotime($row['tgl_mulai'])) . "</div>";
            $nestedData[] = "<div align='center'>" . date('d/m/Y', strtotime($row['tgl_selesai'])) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['sudah_amort']) . "</div>";
            $nestedData[] = "<div align='right'><b>" . number_format($row['sisa_amort']) . "</b></div>";
            $nestedData[] = "<div align='center'>" . $badge_status . "<br>" . $progress . "</div>";
            $nestedData[] = "<div align='center'>" . $btn_detail . $btn_edit . $btn_terminate . "</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        echo json_encode(array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ));
    }

    // -----------------------------------------------------------------------
    // DATATABLES – jadwal bulanan per item
    // -----------------------------------------------------------------------
    public function getScheduleJSON($amortisasi_id)
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'];
        $col         = $requestData['order'][0]['column'];
        $dir         = $requestData['order'][0]['dir'];
        $start       = $requestData['start'];
        $length      = $requestData['length'];

        $sql = "
            SELECT
                s.id,
                s.bulan,
                s.tahun,
                s.nilai_amort,
                s.saldo_awal,
                s.saldo_akhir,
                s.flag,
                s.nomor_jurnal
            FROM amortisasi_schedule s
            WHERE s.amortisasi_id = '" . (int)$amortisasi_id . "'
              AND (
                s.bulan LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR s.tahun LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR s.nomor_jurnal LIKE '%" . $this->db->escape_like_str($search) . "%'
              )
        ";

        $totalData     = $this->db->query($sql)->num_rows();
        $totalFiltered = $totalData;

        $columns_order = array(
            0 => 's.tahun',
            1 => 's.bulan',
            2 => 's.nilai_amort',
            3 => 's.saldo_awal',
            4 => 's.saldo_akhir',
            5 => 's.flag'
        );

        $order_col = isset($columns_order[$col]) ? $columns_order[$col] : 's.tahun';
        $sql .= " ORDER BY " . $order_col . " " . $dir . ", s.bulan " . $dir;
        $sql .= " LIMIT " . (int)$start . ", " . (int)$length;

        $query = $this->db->query($sql)->result_array();

        $bulan_nama = $this->getBulanNama();
        $data       = array();
        $urut1      = 1;
        $urut2      = 0;

        foreach ($query as $row) {
            $nomor = ($requestData['order'][0]['dir'] == 'asc')
                ? $urut1 + $requestData['start']
                : ($totalData - $requestData['start']) - $urut2;

            $flag_badge = ($row['flag'] == 'Y')
                ? "<span class='badge bg-green'>Dijurnal</span>"
                : "<span class='badge bg-red'>Belum</span>";

            $nm_jurnal = !empty($row['nomor_jurnal']) ? "<br><small class='text-muted'>" . $row['nomor_jurnal'] . "</small>" : '';

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='center'>" . ($bulan_nama[$row['bulan']] ?? $row['bulan']) . " " . $row['tahun'] . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['nilai_amort']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['saldo_awal']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['saldo_akhir']) . "</div>";
            $nestedData[] = "<div align='center'>" . $flag_badge . $nm_jurnal . "</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        echo json_encode(array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ));
    }

    // -----------------------------------------------------------------------
    // DATATABLES – log jurnal
    // -----------------------------------------------------------------------
    public function getLogJSON()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'];
        $col         = $requestData['order'][0]['column'];
        $dir         = $requestData['order'][0]['dir'];
        $start       = $requestData['start'];
        $length      = $requestData['length'];

        $sql = "
            SELECT
                l.*,
                a.kode,
                a.nama_item
            FROM amortisasi_log l
            JOIN amortisasi a ON l.amortisasi_id = a.id
            WHERE (
                l.ket LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR l.jurnal_by LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR a.nama_item LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR a.kode LIKE '%" . $this->db->escape_like_str($search) . "%'
            )
        ";

        $totalData     = $this->db->query($sql)->num_rows();
        $totalFiltered = $totalData;

        $columns_order = array(
            0 => 'l.id',
            1 => 'l.tanggal',
            2 => 'a.kode',
            3 => 'a.nama_item',
            4 => 'l.bulan',
            5 => 'l.tahun',
            6 => 'l.ket',
            7 => 'l.jurnal_by'
        );

        $order_col = isset($columns_order[$col]) ? $columns_order[$col] : 'l.id';
        $sql .= " ORDER BY " . $order_col . " " . $dir;
        $sql .= " LIMIT " . (int)$start . ", " . (int)$length;

        $query = $this->db->query($sql)->result_array();

        $bulan_nama = $this->getBulanNama();
        $data       = array();
        $urut1      = 1;
        $urut2      = 0;

        foreach ($query as $row) {
            $nomor = ($requestData['order'][0]['dir'] == 'asc')
                ? $urut1 + $requestData['start']
                : ($totalData - $requestData['start']) - $urut2;

            $status_badge = (strpos($row['ket'], 'SUCCESS') !== false)
                ? "<span class='badge bg-green'>SUCCESS</span>"
                : ((strpos($row['ket'], 'BATAL') !== false)
                    ? "<span class='badge bg-yellow'>BATAL</span>"
                    : ((strpos($row['ket'], 'TERMINATED') !== false)
                        ? "<span class='badge bg-red'>TERMINATED</span>"
                        : "<span class='badge bg-red'>FAILED</span>"));

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='center'>" . $row['tanggal'] . "</div>";
            $nestedData[] = "<div align='center'><small>" . $row['kode'] . "</small></div>";
            $nestedData[] = "<div align='left'>" . $row['nama_item'] . "</div>";
            $nestedData[] = "<div align='center'>" . ($bulan_nama[$row['bulan']] ?? $row['bulan']) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['tahun'] . "</div>";
            $nestedData[] = "<div align='center'>" . $status_badge . "</div>";
            $nestedData[] = "<div align='center'>" . $row['jurnal_by'] . "</div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        echo json_encode(array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ));
    }
}
