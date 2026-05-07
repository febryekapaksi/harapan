<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Amortisasi extends Admin_Controller
{
    protected $viewPermission   = 'Amortisasi.View';
    protected $addPermission    = 'Amortisasi.Add';
    protected $managePermission = 'Amortisasi.Manage';
    protected $deletePermission = 'Amortisasi.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Amortisasi/Amortisasi_model',
            'Asset/Asset_model',
            'jurnal_nomor/Jurnal_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-calculator');
    }

    // -----------------------------------------------------------------------
    // INDEX – Daftar jadwal amortisasi bulanan
    // -----------------------------------------------------------------------
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Amortisasi Asset');

        $bulan_list = array(
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

        $dataArr = array(
            'bulan_list' => $bulan_list,
            'tahun_now'  => date('Y'),
            'bulan_now'  => date('m'),
            'kategori'   => $this->Asset_model->getList('asset_category')
        );

        history("View index amortisasi");
        $this->template->render('index', $dataArr);
    }

    // -----------------------------------------------------------------------
    // DATA SIDE – DataTables server-side untuk jadwal amortisasi
    // -----------------------------------------------------------------------
    public function data_side()
    {
        $this->Amortisasi_model->getDataJSON();
    }

    // -----------------------------------------------------------------------
    // DETAIL – Rincian asset per bulan/tahun
    // -----------------------------------------------------------------------
    public function detail()
    {
        $bulan = $this->input->get('bulan');
        $tahun = $this->input->get('tahun');
        $kdcab = $this->input->get('kdcab');

        $session = $this->session->userdata('app_session');
        if (empty($kdcab)) {
            $kdcab = $session['kdcab'];
        }

        $data = $this->Amortisasi_model->getDetailAmortisasi($bulan, $tahun, $kdcab);
        echo json_encode($data);
    }

    // -----------------------------------------------------------------------
    // PREVIEW JURNAL – tampilkan preview sebelum posting
    // -----------------------------------------------------------------------
    public function preview_jurnal()
    {
        $bulan = $this->input->post('bulan');
        $tahun = $this->input->post('tahun');

        $session = $this->session->userdata('app_session');
        $kdcab   = $session['kdcab'];

        // Cek apakah sudah pernah dijurnal bulan ini
        $cek_jurnal = $this->Amortisasi_model->cekJurnalBulan($bulan, $tahun, $kdcab);

        $detail = $this->Amortisasi_model->getDetailAmortisasi($bulan, $tahun, $kdcab);

        $dataArr = array(
            'detail'      => $detail,
            'bulan'       => $bulan,
            'tahun'       => $tahun,
            'kdcab'       => $kdcab,
            'sudah_jurnal' => $cek_jurnal
        );

        $this->load->view('Amortisasi/preview_jurnal', $dataArr);
    }

    // -----------------------------------------------------------------------
    // PROSES JURNAL OTOMATIS – dipanggil oleh scheduler / cron
    // -----------------------------------------------------------------------
    public function proses_otomatis()
    {
        // Endpoint ini bisa dipanggil via cron job:
        // curl -s "http://domain.com/index.php/amortisasi/proses_otomatis"
        $bulan = date('m');
        $tahun = date('Y');

        $this->_proses_jurnal($bulan, $tahun, 'System-Auto');
    }

    // -----------------------------------------------------------------------
    // POSTING MANUAL – dipanggil dari form
    // -----------------------------------------------------------------------
    public function posting_jurnal()
    {
        $bulan   = $this->input->post('bulan');
        $tahun   = $this->input->post('tahun');
        $session = $this->session->userdata('app_session');
        $user    = $session['username'];

        $result = $this->_proses_jurnal($bulan, $tahun, $user);
        echo json_encode($result);
    }

    // -----------------------------------------------------------------------
    // BATAL JURNAL – hapus jurnal bulan tertentu
    // -----------------------------------------------------------------------
    // BATAL JURNAL – hapus jurnal bulan tertentu
    // -----------------------------------------------------------------------
    public function batal_jurnal()
    {
        $bulan   = $this->input->post('bulan');
        $tahun   = $this->input->post('tahun');

        // Pastikan bulan selalu 2 digit (01, 02, dst)
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $session = $this->session->userdata('app_session');
        $kdcab   = $session['kdcab'];
        $user    = $session['username'];

        // Ambil nomor jurnal yang akan dihapus dari database DBACC
        $ArrNomor = $this->db->query(
            "SELECT DISTINCT nomor FROM " . DBACC . ".jurnal
             WHERE jenis_trans = 'amortisasi asset'
             AND MONTH(tanggal) = '" . (int)$bulan . "'
             AND YEAR(tanggal)  = '" . (int)$tahun . "'"
        )->result_array();

        if (empty($ArrNomor)) {
            echo json_encode(array('status' => 0, 'pesan' => 'Tidak ada jurnal amortisasi untuk periode ini.'));
            return;
        }

        $listNomor = array_column($ArrNomor, 'nomor');
        $inNomor   = "('" . implode("','", $listNomor) . "')";

        $this->db->trans_start();
        // Hapus dari database DBACC
        $this->db->query("DELETE FROM " . DBACC . ".jurnal WHERE nomor IN " . $inNomor . " AND jenis_trans = 'amortisasi asset'");
        $this->db->query("DELETE FROM " . DBACC . ".javh   WHERE nomor IN " . $inNomor);

        // Update flag di database default
        $this->db->query(
            "UPDATE asset_generate SET flag = 'N'
             WHERE bulan = '" . $bulan . "' AND tahun = '" . $tahun . "'"
        );
        $this->db->query(
            "INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab)
             VALUES ('" . date('Y-m-d H:i:s') . "', 'BATAL', '" . $user . "', '" . $bulan . "', '" . $tahun . "', '" . $kdcab . "')"
        );
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'pesan' => 'Gagal membatalkan jurnal amortisasi.'));
        } else {
            $this->db->trans_commit();
            history("Batal jurnal amortisasi " . $bulan . "/" . $tahun);
            echo json_encode(array('status' => 1, 'pesan' => 'Jurnal amortisasi berhasil dibatalkan.'));
        }
    }

    // -----------------------------------------------------------------------
    // LOG JURNAL – riwayat proses jurnal
    // -----------------------------------------------------------------------
    public function log_jurnal()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Log Jurnal Amortisasi');
        history("View log jurnal amortisasi");
        $this->template->render('log_jurnal');
    }

    public function data_log()
    {
        $this->Amortisasi_model->getLogJSON();
    }

    // -----------------------------------------------------------------------
    // PRIVATE – inti proses jurnal amortisasi
    // -----------------------------------------------------------------------
    private function _proses_jurnal($bulan, $tahun, $user = 'System')
    {
        // Pastikan bulan selalu 2 digit (01, 02, dst)
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        $session = $this->session->userdata('app_session');
        $kdcab   = '101';

        // Ambil data amortisasi bulan ini beserta COA dari category
        $ArrAsset = $this->Amortisasi_model->getAmortisasiUntukJurnal($bulan, $tahun, $kdcab);

        if (empty($ArrAsset)) {
            return array('status' => 0, 'pesan' => 'Tidak ada data amortisasi untuk periode ' . $bulan . '/' . $tahun . '.');
        }

        // Hapus jurnal lama bulan ini jika ada (re-post) - dari database DBACC
        $ArrNomorLama = $this->db->query(
            "SELECT DISTINCT nomor FROM " . DBACC . ".jurnal
             WHERE jenis_trans = 'amortisasi asset'
             AND MONTH(tanggal) = '" . (int)$bulan . "'
             AND YEAR(tanggal)  = '" . (int)$tahun . "'"
        )->result_array();

        $ArrDebit  = array();
        $ArrKredit = array();
        $ArrJavh   = array();
        $Loop      = 0;
        $tgl_jurnal = $tahun . '-' . $bulan . '-01';
        $bln_int    = ltrim($bulan, '0');

        foreach ($ArrAsset as $valx) {
            $Loop++;

            // COA dari asset_category (dinamis)
            $coaD = !empty($valx['coa_debit'])  ? $valx['coa_debit']  : '6831-00-00';
            $ketD = !empty($valx['nm_coa_debit']) ? strtoupper($valx['nm_coa_debit']) : 'BIAYA AMORTISASI ASSET';
            $coaK = !empty($valx['coa_kredit']) ? $valx['coa_kredit'] : '1309-00-00';
            $ketK = !empty($valx['nm_coa_kredit']) ? strtoupper($valx['nm_coa_kredit']) : 'AKUMULASI AMORTISASI ASSET';

            $nomor_jm = $this->Jurnal_model->get_Nomor_Jurnal_Sales($valx['kdcab'], $tgl_jurnal);

            $ArrDebit[$Loop] = array(
                'tipe'        => 'JV',
                'nomor'       => $nomor_jm,
                'tanggal'     => $tgl_jurnal,
                'no_perkiraan' => $coaD,
                'keterangan'  => $ketD . ' - ' . strtoupper($valx['nm_category']) . ' - ' . $valx['nm_asset'],
                'no_reff'     => $valx['kd_asset'],
                'debet'       => $valx['nilai_susut'],
                'kredit'      => 0,
                'jenis_trans' => 'amortisasi asset',
                'created_on'  => date('Y-m-d h:i:s'),
                'created_by'  => $this->auth->user_id()
            );

            $ArrKredit[$Loop] = array(
                'tipe'        => 'JV',
                'nomor'       => $nomor_jm,
                'tanggal'     => $tgl_jurnal,
                'no_perkiraan' => $coaK,
                'keterangan'  => $ketK . ' - ' . strtoupper($valx['nm_category']) . ' - ' . $valx['nm_asset'],
                'no_reff'     => $valx['kd_asset'],
                'debet'       => 0,
                'kredit'      => $valx['nilai_susut'],
                'jenis_trans' => 'amortisasi asset',
                'created_on'  => date('Y-m-d h:i:s'),
                'created_by'  => $this->auth->user_id()
            );

            $ArrJavh[$Loop] = array(
                'nomor'        => $nomor_jm,
                'tgl'          => $tgl_jurnal,
                'jml'          => $valx['nilai_susut'],
                'kdcab'        => $valx['kdcab'],
                'jenis'        => 'V',
                'keterangan'   => 'AMORTISASI ASSET ' . strtoupper($valx['nm_category']) . ' - ' . $valx['nm_asset'],
                'bulan'        => $bln_int,
                'tahun'        => $tahun,
                'user_id'      => $user,
                'tgl_jvkoreksi' => $tgl_jurnal
            );

            $this->Jurnal_model->update_Nomor_Jurnal($valx['kdcab'], 'JM');
        }

        // Hapus jurnal lama jika ada - dari database DBACC
        if (!empty($ArrNomorLama)) {
            $inLama = "('" . implode("','", array_column($ArrNomorLama, 'nomor')) . "')";
            $this->db->query("DELETE FROM " . DBACC . ".jurnal WHERE nomor IN " . $inLama . " AND jenis_trans = 'amortisasi asset'");
            $this->db->query("DELETE FROM " . DBACC . ".javh   WHERE nomor IN " . $inLama);
        }

        $this->db->trans_start();
        // Insert ke database DBACC
        $this->db->insert_batch(DBACC . '.javh', $ArrJavh);
        $this->db->insert_batch(DBACC . '.jurnal', $ArrDebit);
        $this->db->insert_batch(DBACC . '.jurnal', $ArrKredit);

        // Update flag di database default
        $this->db->query(
            "UPDATE asset_generate SET flag = 'Y'
             WHERE bulan = '" . $bulan . "' AND tahun = '" . $tahun . "'"
        );
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->db->query(
                "INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab)
                 VALUES ('" . date('Y-m-d H:i:s') . "', 'FAILED-AMORTISASI', '" . $user . "', '" . $bulan . "', '" . $tahun . "', '" . $kdcab . "')"
            );
            return array('status' => 0, 'pesan' => 'Jurnal amortisasi gagal disimpan. Silakan coba lagi.');
        } else {
            $this->db->trans_commit();
            $this->db->query(
                "INSERT INTO asset_jurnal_log (tanggal, ket, jurnal_by, bulan, tahun, kdcab)
                 VALUES ('" . date('Y-m-d H:i:s') . "', 'SUCCESS-AMORTISASI', '" . $user . "', '" . $bulan . "', '" . $tahun . "', '" . $kdcab . "')"
            );
            history("Posting jurnal amortisasi " . $bulan . "/" . $tahun);
            return array('status' => 1, 'pesan' => 'Jurnal amortisasi berhasil diposting untuk periode ' . $bulan . '/' . $tahun . '.');
        }
    }
}
