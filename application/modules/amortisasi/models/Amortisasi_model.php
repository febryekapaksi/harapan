<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Amortisasi_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // -----------------------------------------------------------------------
    // Ambil data amortisasi untuk dijurnal (join dengan asset_category untuk COA)
    // -----------------------------------------------------------------------
    public function getAmortisasiUntukJurnal($bulan, $tahun, $kdcab = '')
    {
        $where_kdcab = '';
        if (!empty($kdcab)) {
            $where_kdcab = " AND a.kdcab = '" . $kdcab . "'";
        }

        $sql = "
            SELECT
                ag.kd_asset,
                ag.nm_asset,
                ag.category,
                ag.nm_category,
                ag.kdcab,
                SUM(ag.nilai_susut) AS nilai_susut,
                ac.coa_debit,
                ac.nm_coa_debit,
                ac.coa_kredit,
                ac.nm_coa_kredit
            FROM asset_generate ag
            LEFT JOIN asset a  ON ag.kd_asset = a.kd_asset
            LEFT JOIN asset_category ac ON ag.category = ac.id
            WHERE ag.bulan = '" . $bulan . "'
              AND ag.tahun  = '" . $tahun . "'
              AND a.deleted = 'N'
              " . $where_kdcab . "
            GROUP BY ag.category, ag.kdcab
        ";

        return $this->db->query($sql)->result_array();
    }

    // -----------------------------------------------------------------------
    // Rincian per asset untuk preview
    // -----------------------------------------------------------------------
    public function getDetailAmortisasi($bulan, $tahun, $kdcab = '')
    {
        $where_kdcab = '';
        if (!empty($kdcab)) {
            $where_kdcab = " AND a.kdcab = '" . $this->db->escape_str($kdcab) . "'";
        }

        $sql = "
            SELECT
                ag.kd_asset,
                ag.nm_asset,
                ag.category,
                ag.nm_category,
                ag.bulan,
                ag.tahun,
                ag.nilai_susut,
                ag.flag,
                ag.kdcab,
                a.nilai_asset,
                a.depresiasi,
                a.tgl_perolehan,
                ac.coa_debit,
                ac.nm_coa_debit,
                ac.coa_kredit,
                ac.nm_coa_kredit,
                c.namacabang
            FROM asset_generate ag
            LEFT JOIN asset a          ON ag.kd_asset = a.kd_asset
            LEFT JOIN asset_category ac ON ag.category = ac.id
            LEFT JOIN cabang c          ON ag.kdcab = c.kdcab
            WHERE ag.bulan = '" . $this->db->escape_str($bulan) . "'
              AND ag.tahun  = '" . $this->db->escape_str($tahun) . "'
              AND a.deleted = 'N'
              " . $where_kdcab . "
            ORDER BY ag.kdcab, ag.nm_category, ag.kd_asset
        ";

        return $this->db->query($sql)->result_array();
    }

    // -----------------------------------------------------------------------
    // Cek apakah bulan/tahun sudah dijurnal
    // -----------------------------------------------------------------------
    public function cekJurnalBulan($bulan, $tahun, $kdcab = '')
    {
        // Pastikan bulan selalu 2 digit (01, 02, dst)
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        // Cek dari database DBACC
        $sql = "
            SELECT COUNT(*) AS jml FROM " . DBACC . ".jurnal
            WHERE jenis_trans = 'amortisasi asset'
              AND MONTH(tanggal) = '" . (int)$bulan . "'
              AND YEAR(tanggal)  = '" . (int)$tahun . "'
        ";
        $result = $this->db->query($sql)->result_array();
        return ($result[0]['jml'] > 0);
    }

    // -----------------------------------------------------------------------
    // DataTables – ringkasan per bulan/tahun
    // -----------------------------------------------------------------------
    public function getDataJSON()
    {
        $requestData = $_REQUEST;
        $search      = $requestData['search']['value'];
        $col         = $requestData['order'][0]['column'];
        $dir         = $requestData['order'][0]['dir'];
        $start       = $requestData['start'];
        $length      = $requestData['length'];

        $bulan   = isset($requestData['bulan'])   ? $requestData['bulan']   : '';
        $tahun   = isset($requestData['tahun'])   ? $requestData['tahun']   : date('Y');
        $kategori = isset($requestData['kategori']) ? $requestData['kategori'] : '';

        $where_bulan   = !empty($bulan)    ? " AND ag.bulan = '" . $this->db->escape_str($bulan) . "'" : '';
        $where_tahun   = !empty($tahun)    ? " AND ag.tahun = '" . $this->db->escape_str($tahun) . "'" : '';
        $where_kategori = !empty($kategori) ? " AND ag.category = '" . $this->db->escape_str($kategori) . "'" : '';

        $sql = "
            SELECT
                ag.bulan,
                ag.tahun,
                ag.category,
                ag.nm_category,
                ag.kdcab,
                c.namacabang,
                SUM(ag.nilai_susut) AS total_susut,
                COUNT(ag.kd_asset)  AS jml_asset,
                MAX(ag.flag)        AS flag,
                ac.coa_debit,
                ac.nm_coa_debit,
                ac.coa_kredit,
                ac.nm_coa_kredit
            FROM asset_generate ag
            LEFT JOIN asset a           ON ag.kd_asset = a.kd_asset
            LEFT JOIN asset_category ac ON ag.category = ac.id
            LEFT JOIN cabang c          ON ag.kdcab = c.kdcab
            WHERE a.deleted = 'N'
              " . $where_bulan . "
              " . $where_tahun . "
              " . $where_kategori . "
              AND (
                ag.nm_category LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR ag.kd_asset LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR c.namacabang LIKE '%" . $this->db->escape_like_str($search) . "%'
              )
            GROUP BY ag.bulan, ag.tahun, ag.category, ag.kdcab
        ";

        $totalData     = $this->db->query($sql)->num_rows();
        $totalFiltered = $totalData;

        $columns_order = array(
            0 => 'ag.tahun',
            1 => 'ag.bulan',
            2 => 'ag.nm_category',
            3 => 'c.namacabang',
            4 => 'jml_asset',
            5 => 'total_susut',
            6 => 'flag'
        );

        $sql .= " ORDER BY " . $columns_order[$col] . " " . $dir;
        $sql .= " LIMIT " . (int)$start . ", " . (int)$length;

        $query = $this->db->query($sql)->result_array();

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;

        $bulan_nama = array(
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

        foreach ($query as $row) {
            $nomor = ($requestData['order'][0]['dir'] == 'asc')
                ? $urut1 + $requestData['start']
                : ($totalData - $requestData['start']) - $urut2;

            $flag_badge = ($row['flag'] == 'Y')
                ? "<span class='badge bg-green'>Sudah Dijurnal</span>"
                : "<span class='badge bg-red'>Belum Dijurnal</span>";

            $btn_detail = "<button type='button' class='btn btn-xs btn-info btn-detail'
                            data-bulan='" . $row['bulan'] . "'
                            data-tahun='" . $row['tahun'] . "'
                            data-kdcab='" . $row['kdcab'] . "'
                            data-category='" . $row['category'] . "'
                            title='Detail Asset'><i class='fa fa-list'></i></button>";

            $btn_posting = '';
            if ($row['flag'] != 'Y') {
                $btn_posting = " <button type='button' class='btn btn-xs btn-primary btn-posting'
                                  data-bulan='" . $row['bulan'] . "'
                                  data-tahun='" . $row['tahun'] . "'
                                  title='Posting Jurnal'><i class='fa fa-check-circle'></i> Posting</button>";
            } else {
                $btn_posting = " <button type='button' class='btn btn-xs btn-warning btn-batal'
                                  data-bulan='" . $row['bulan'] . "'
                                  data-tahun='" . $row['tahun'] . "'
                                  title='Batal Jurnal'><i class='fa fa-times-circle'></i> Batal</button>";
            }

            $nm_bulan = isset($bulan_nama[$row['bulan']]) ? $bulan_nama[$row['bulan']] : $row['bulan'];

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='center'>" . $nm_bulan . " " . $row['tahun'] . "</div>";
            $nestedData[] = "<div align='left'>"   . strtoupper($row['nm_category']) . "</div>";
            $nestedData[] = "<div align='left'>"   . strtoupper($row['namacabang'])  . "</div>";
            $nestedData[] = "<div align='center'>" . number_format($row['jml_asset']) . " Asset</div>";
            $nestedData[] = "<div align='right'>"  . number_format($row['total_susut']) . "</div>";
            $nestedData[] = "<div align='center'>" . $flag_badge . "</div>";
            $nestedData[] = "<div align='center'>" . $btn_detail . $btn_posting . "</div>";

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
    // DataTables – log jurnal
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
            SELECT * FROM asset_jurnal_log
            WHERE ket LIKE '%AMORTISASI%'
              AND (
                ket LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR jurnal_by LIKE '%" . $this->db->escape_like_str($search) . "%'
                OR kdcab LIKE '%" . $this->db->escape_like_str($search) . "%'
              )
        ";

        $totalData     = $this->db->query($sql)->num_rows();
        $totalFiltered = $totalData;

        $columns_order = array(
            0 => 'id',
            1 => 'tanggal',
            2 => 'bulan',
            3 => 'tahun',
            4 => 'ket',
            5 => 'jurnal_by',
            6 => 'kdcab'
        );

        $sql .= " ORDER BY " . $columns_order[$col] . " " . $dir;
        $sql .= " LIMIT " . (int)$start . ", " . (int)$length;

        $query = $this->db->query($sql)->result_array();

        $data  = array();
        $urut1 = 1;
        $urut2 = 0;

        foreach ($query as $row) {
            $nomor = ($requestData['order'][0]['dir'] == 'asc')
                ? $urut1 + $requestData['start']
                : ($totalData - $requestData['start']) - $urut2;

            $status_badge = (strpos($row['ket'], 'SUCCESS') !== false)
                ? "<span class='badge bg-green'>SUCCESS</span>"
                : ((strpos($row['ket'], 'BATAL') !== false)
                    ? "<span class='badge bg-yellow'>BATAL</span>"
                    : "<span class='badge bg-red'>FAILED</span>");

            $nestedData   = array();
            $nestedData[] = "<div align='center'>" . $nomor . "</div>";
            $nestedData[] = "<div align='center'>" . $row['tanggal'] . "</div>";
            $nestedData[] = "<div align='center'>" . $row['bulan'] . "</div>";
            $nestedData[] = "<div align='center'>" . $row['tahun'] . "</div>";
            $nestedData[] = "<div align='center'>" . $status_badge . "</div>";
            $nestedData[] = "<div align='center'>" . $row['jurnal_by'] . "</div>";
            $nestedData[] = "<div align='center'>" . $row['kdcab'] . "</div>";

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
