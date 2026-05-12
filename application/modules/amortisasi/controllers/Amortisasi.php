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
            'jurnal_nomor/Jurnal_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
        $this->template->page_icon('fa fa-file-text-o');
    }

    // -----------------------------------------------------------------------
    // INDEX – Dashboard + daftar master amortisasi
    // -----------------------------------------------------------------------
    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->title('Amortisasi');

        $session = $this->session->userdata('app_session');
        $kdcab   = $session['kdcab'];

        $summary = $this->Amortisasi_model->getDashboardSummary($kdcab);

        $dataArr = array(
            'summary' => $summary
        );

        history("View index amortisasi");
        $this->template->render('index', $dataArr);
    }

    // -----------------------------------------------------------------------
    // DATA SIDE – DataTables server-side daftar master
    // -----------------------------------------------------------------------
    public function data_side()
    {
        $this->Amortisasi_model->getDataJSON();
    }

    // -----------------------------------------------------------------------
    // FORM TAMBAH – tampilkan modal form (dipanggil via AJAX load)
    // -----------------------------------------------------------------------
    public function form_tambah()
    {
        $this->auth->restrict($this->addPermission);

        $dataArr = array(
            'kode_baru' => $this->Amortisasi_model->generateKode(),
            'item'      => null
        );

        $this->template->render('form_item', $dataArr);
    }

    // -----------------------------------------------------------------------
    // FORM EDIT – tampilkan modal form edit (dipanggil via AJAX load)
    // -----------------------------------------------------------------------
    public function form_edit($id = 0)
    {
        $this->auth->restrict($this->managePermission);

        $item = $this->Amortisasi_model->getById($id);
        if (empty($item)) {
            echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
            return;
        }

        $dataArr = array(
            'kode_baru' => $item['kode'],
            'item'      => $item
        );

        $this->template->render('form_item', $dataArr);
    }

    // -----------------------------------------------------------------------
    // SIMPAN – proses simpan (tambah/edit) + auto-generate schedule
    // -----------------------------------------------------------------------
    public function simpan()
    {
        $this->auth->restrict($this->addPermission);

        $user    = $this->auth->user_id();
        $kdcab   = '101';

        $id          = (int)$this->input->post('id');
        $nama_item   = trim($this->input->post('nama_item'));
        $total_debit = str_replace(',', '', $this->input->post('total_debit'));
        $tgl_mulai   = $this->input->post('tgl_mulai');
        $tgl_selesai = $this->input->post('tgl_selesai');
        $coa_kredit  = trim($this->input->post('coa_kredit'));
        $nm_coa_kredit = trim($this->input->post('nm_coa_kredit'));
        $coa_debit   = trim($this->input->post('coa_debit'));
        $nm_coa_debit = trim($this->input->post('nm_coa_debit'));
        $keterangan  = trim($this->input->post('keterangan'));

        // Normalisasi format tanggal – terima yyyy-mm-dd, dd/mm/yyyy, mm/dd/yyyy
        $tgl_mulai   = $this->_parse_date($tgl_mulai);
        $tgl_selesai = $this->_parse_date($tgl_selesai);

        // Validasi dasar
        if (
            empty($nama_item) || $total_debit <= 0 || empty($tgl_mulai) || empty($tgl_selesai)
            || empty($coa_kredit) || empty($coa_debit)
        ) {
            echo json_encode(array('status' => 0, 'pesan' => 'Semua field wajib diisi dengan benar.'));
            return;
        }

        if ($tgl_selesai <= $tgl_mulai) {
            echo json_encode(array('status' => 0, 'pesan' => 'Tanggal selesai harus lebih besar dari tanggal mulai.'));
            return;
        }

        if ($id > 0) {
            // ---- EDIT ----
            $item = $this->Amortisasi_model->getById($id);
            if (empty($item) || $item['status'] != 'active') {
                echo json_encode(array('status' => 0, 'pesan' => 'Data tidak ditemukan atau sudah tidak aktif.'));
                return;
            }

            // Cek apakah sudah ada yang dijurnal – jika ya, tidak boleh ubah nilai/tanggal
            $sudah_jurnal = $this->db->get_where(
                'amortisasi_schedule',
                array('amortisasi_id' => $id, 'flag' => 'Y')
            )->num_rows();

            $data_update = array(
                'nama_item'    => $nama_item,
                'keterangan'   => $keterangan,
                'updated_by'   => $user,
                'updated_on'   => date('Y-m-d H:i:s')
            );

            if ($sudah_jurnal == 0) {
                // Belum ada yang dijurnal, boleh ubah semua
                $data_update['total_debit']   = $total_debit;
                $data_update['tgl_mulai']     = $tgl_mulai;
                $data_update['tgl_selesai']   = $tgl_selesai;
                $data_update['coa_kredit']    = $coa_kredit;
                $data_update['nm_coa_kredit'] = $nm_coa_kredit;
                $data_update['coa_debit']     = $coa_debit;
                $data_update['nm_coa_debit']  = $nm_coa_debit;
            }

            $this->Amortisasi_model->updateItem($id, $data_update);

            // Regenerate schedule jika belum ada yang dijurnal
            if ($sudah_jurnal == 0) {
                $total_bln = $this->Amortisasi_model->generateSchedule(
                    $id,
                    $nama_item,
                    $total_debit,
                    $tgl_mulai,
                    $tgl_selesai
                );
                history("Edit amortisasi ID:{$id} - {$nama_item}, regenerate {$total_bln} bulan");
            } else {
                history("Edit nama/keterangan amortisasi ID:{$id} - {$nama_item}");
            }

            echo json_encode(array('status' => 1, 'pesan' => 'Data amortisasi berhasil diperbarui.'));
        } else {
            // ---- TAMBAH BARU ----
            $kode = $this->Amortisasi_model->generateKode();

            $data_insert = array(
                'kode'          => $kode,
                'nama_item'     => $nama_item,
                'total_debit'   => $total_debit,
                'tgl_mulai'     => $tgl_mulai,
                'tgl_selesai'   => $tgl_selesai,
                'coa_kredit'    => $coa_kredit,
                'nm_coa_kredit' => $nm_coa_kredit,
                'coa_debit'     => $coa_debit,
                'nm_coa_debit'  => $nm_coa_debit,
                'kdcab'         => $kdcab,
                'status'        => 'active',
                'keterangan'    => $keterangan,
                'created_by'    => $user,
                'created_on'    => date('Y-m-d H:i:s')
            );

            $new_id = $this->Amortisasi_model->simpan($data_insert);

            // Auto-generate jadwal bulanan
            $total_bln = $this->Amortisasi_model->generateSchedule(
                $new_id,
                $nama_item,
                $total_debit,
                $tgl_mulai,
                $tgl_selesai
            );

            history("Tambah amortisasi {$kode} - {$nama_item}, generate {$total_bln} bulan");
            echo json_encode(array(
                'status' => 1,
                'pesan'  => "Amortisasi <b>{$kode}</b> berhasil disimpan. Jadwal {$total_bln} bulan telah di-generate."
            ));
        }
    }

    // -----------------------------------------------------------------------
    // DETAIL SCHEDULE – jadwal bulanan per item (DataTables)
    // -----------------------------------------------------------------------
    public function detail_schedule($id = 0)
    {
        $this->Amortisasi_model->getScheduleJSON((int)$id);
    }

    // -----------------------------------------------------------------------
    // VIEW DETAIL – halaman jadwal per item
    // -----------------------------------------------------------------------
    public function detail($id = 0)
    {
        $this->auth->restrict($this->viewPermission);

        $item = $this->Amortisasi_model->getById($id);
        if (empty($item)) {
            show_404();
        }

        $schedule = $this->Amortisasi_model->getScheduleByItem($id);

        $dataArr = array(
            'item'     => $item,
            'schedule' => $schedule
        );

        history("View detail amortisasi ID:{$id}");
        $this->template->title('Detail Amortisasi - ' . $item['nama_item']);
        $this->template->render('detail', $dataArr);
    }

    // -----------------------------------------------------------------------
    // POSTING JURNAL MANUAL – posting satu item untuk bulan tertentu
    // -----------------------------------------------------------------------
    public function posting_jurnal()
    {
        $this->auth->restrict($this->managePermission);

        $bulan   = str_pad($this->input->post('bulan'), 2, '0', STR_PAD_LEFT);
        $tahun   = $this->input->post('tahun');
        $user    = $this->auth->user_id();
        $kdcab   = '101';

        $result = $this->_proses_jurnal($bulan, $tahun, $user, $kdcab);
        echo json_encode($result);
    }

    // -----------------------------------------------------------------------
    // PROSES JURNAL OTOMATIS – dipanggil scheduler/cron akhir bulan
    // curl -s "http://domain.com/amortisasi/proses_otomatis"
    // -----------------------------------------------------------------------
    public function proses_otomatis()
    {
        $bulan = date('m');
        $tahun = date('Y');
        $this->_proses_jurnal($bulan, $tahun, 'System-Auto', '');
    }

    // -----------------------------------------------------------------------
    // BATAL JURNAL – hapus jurnal bulan tertentu
    // -----------------------------------------------------------------------
    public function batal_jurnal()
    {
        $this->auth->restrict($this->managePermission);

        $bulan   = str_pad($this->input->post('bulan'), 2, '0', STR_PAD_LEFT);
        $tahun   = $this->input->post('tahun');
        $user    = $this->auth->user_id();
        $kdcab   = '101';


        // Ambil nomor jurnal yang akan dihapus
        $ArrNomor = $this->db->query(
            "SELECT DISTINCT nomor FROM " . DBACC . ".jurnal
             WHERE jenis_trans = 'amortisasi'
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

        // Hapus dari DBACC
        $this->db->query("DELETE FROM " . DBACC . ".jurnal WHERE nomor IN " . $inNomor . " AND jenis_trans = 'amortisasi'");
        $this->db->query("DELETE FROM " . DBACC . ".javh   WHERE nomor IN " . $inNomor);

        // Reset flag schedule
        $this->db->query(
            "UPDATE amortisasi_schedule SET flag = 'N', nomor_jurnal = NULL
             WHERE bulan = '" . $bulan . "' AND tahun = '" . $tahun . "'"
        );

        // Log per item yang dibatalkan
        $items_batal = $this->db->query(
            "SELECT DISTINCT amortisasi_id FROM amortisasi_schedule
             WHERE bulan = '" . $bulan . "' AND tahun = '" . $tahun . "'"
        )->result_array();

        foreach ($items_batal as $itm) {
            $this->db->insert('amortisasi_log', array(
                'amortisasi_id' => $itm['amortisasi_id'],
                'tanggal'       => date('Y-m-d H:i:s'),
                'bulan'         => $bulan,
                'tahun'         => $tahun,
                'ket'           => 'BATAL',
                'jurnal_by'     => $user,
                'kdcab'         => $kdcab
            ));
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'pesan' => 'Gagal membatalkan jurnal amortisasi.'));
        } else {
            $this->db->trans_commit();
            history("Batal jurnal amortisasi " . $bulan . "/" . $tahun);
            echo json_encode(array('status' => 1, 'pesan' => 'Jurnal amortisasi periode ' . $bulan . '/' . $tahun . ' berhasil dibatalkan.'));
        }
    }

    // -----------------------------------------------------------------------
    // TERMINATE – hentikan amortisasi di tengah jalan
    // Sisa saldo langsung di-expense (jurnal sekaligus)
    // -----------------------------------------------------------------------
    public function terminate()
    {
        $this->auth->restrict($this->managePermission);

        $id      = (int)$this->input->post('id');
        $user    = $this->auth->user_id();
        $kdcab   = '101';


        $item = $this->Amortisasi_model->getById($id);
        if (empty($item) || $item['status'] != 'active') {
            echo json_encode(array('status' => 0, 'pesan' => 'Data tidak ditemukan atau sudah tidak aktif.'));
            return;
        }

        // Hitung sisa saldo (schedule yang belum dijurnal)
        $sisa = $this->db->query(
            "SELECT COALESCE(SUM(nilai_amort), 0) AS sisa
             FROM amortisasi_schedule
             WHERE amortisasi_id = '" . $id . "' AND flag = 'N'"
        )->row_array();

        $sisa_nilai = (float)$sisa['sisa'];

        $tgl_jurnal = date('Y-m-d');
        $bulan      = date('m');
        $tahun      = date('Y');

        $this->db->trans_start();

        if ($sisa_nilai > 0) {
            // Buat jurnal untuk sisa saldo
            $nomor_jm = $this->Jurnal_model->get_Nomor_Jurnal_Sales($kdcab, $tgl_jurnal);

            $ArrJurnal = array(
                // Debit: Akun Biaya
                array(
                    'tipe'         => 'JV',
                    'nomor'        => $nomor_jm,
                    'tanggal'      => $tgl_jurnal,
                    'no_perkiraan' => $item['coa_debit'],
                    'keterangan'   => 'TERMINATE AMORTISASI - ' . strtoupper($item['nama_item']),
                    'no_reff'      => $item['kode'],
                    'debet'        => $sisa_nilai,
                    'kredit'       => 0,
                    'jenis_trans'  => 'amortisasi',
                    'created_on'   => date('Y-m-d H:i:s'),
                    'created_by'   => $this->auth->user_id()
                ),
                // Kredit: Akun Neraca
                array(
                    'tipe'         => 'JV',
                    'nomor'        => $nomor_jm,
                    'tanggal'      => $tgl_jurnal,
                    'no_perkiraan' => $item['coa_kredit'],
                    'keterangan'   => 'TERMINATE AMORTISASI - ' . strtoupper($item['nama_item']),
                    'no_reff'      => $item['kode'],
                    'debet'        => 0,
                    'kredit'       => $sisa_nilai,
                    'jenis_trans'  => 'amortisasi',
                    'created_on'   => date('Y-m-d H:i:s'),
                    'created_by'   => $this->auth->user_id()
                )
            );

            $ArrJavh = array(array(
                'nomor'         => $nomor_jm,
                'tgl'           => $tgl_jurnal,
                'jml'           => $sisa_nilai,
                'kdcab'         => $kdcab,
                'koreksi_no'    => '-',
                'jenis'         => 'JV',
                'keterangan'    => 'TERMINATE AMORTISASI - ' . strtoupper($item['nama_item']),
                'bulan'         => (int)$bulan,
                'tahun'         => $tahun,
                'user_id'       => $user,
                'tgl_jvkoreksi' => $tgl_jurnal
            ));

            $this->db->insert_batch(DBACC . '.javh', $ArrJavh);
            $this->db->insert_batch(DBACC . '.jurnal', $ArrJurnal);
        }

        // Hapus schedule yang belum dijurnal
        $this->db->query(
            "DELETE FROM amortisasi_schedule
             WHERE amortisasi_id = '" . $id . "' AND flag = 'N'"
        );

        // Update status master jadi terminated
        $this->db->where('id', $id)->update('amortisasi', array(
            'status'     => 'terminated',
            'updated_by' => $user,
            'updated_on' => date('Y-m-d H:i:s')
        ));

        // Log
        $this->db->insert('amortisasi_log', array(
            'amortisasi_id' => $id,
            'tanggal'       => date('Y-m-d H:i:s'),
            'bulan'         => $bulan,
            'tahun'         => $tahun,
            'ket'           => 'TERMINATED - SISA: ' . number_format($sisa_nilai),
            'jurnal_by'     => $user,
            'kdcab'         => $kdcab
        ));

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'pesan' => 'Gagal melakukan terminate amortisasi.'));
        } else {
            $this->db->trans_commit();
            history("Terminate amortisasi ID:{$id} - " . $item['nama_item'] . ", sisa: " . number_format($sisa_nilai));
            echo json_encode(array(
                'status' => 1,
                'pesan'  => 'Amortisasi <b>' . $item['nama_item'] . '</b> berhasil dihentikan. Sisa saldo Rp ' . number_format($sisa_nilai) . ' telah dijurnalkan.'
            ));
        }
    }

    // -----------------------------------------------------------------------
    // GET COA LIST – untuk COA picker di form (sama dengan Asset controller)
    // -----------------------------------------------------------------------
    public function get_coa_list()
    {
        $sql    = "SELECT no_perkiraan, nama FROM " . DBACC . ".coa_master ORDER BY no_perkiraan ASC";
        $result = $this->db->query($sql)->result_array();
        echo json_encode($result);
    }

    // -----------------------------------------------------------------------
    // LOG JURNAL – halaman riwayat proses
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
    // PRIVATE – inti proses jurnal amortisasi bulanan
    // -----------------------------------------------------------------------
    private function _proses_jurnal($bulan, $tahun, $user = 'System', $kdcab = '')
    {
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        if (empty($kdcab)) {
            $kdcab   = '101';
        }

        // Ambil semua schedule bulan ini yang belum dijurnal
        $ArrSchedule = $this->Amortisasi_model->getScheduleUntukJurnal($bulan, $tahun, $kdcab);

        if (empty($ArrSchedule)) {
            return array('status' => 0, 'pesan' => 'Tidak ada data amortisasi yang perlu dijurnal untuk periode ' . $bulan . '/' . $tahun . '.');
        }

        $tgl_jurnal = $tahun . '-' . $bulan . '-01';
        $bln_int    = (int)$bulan;

        // Satu nomor jurnal untuk semua item dalam periode ini
        $nomor_jm       = $this->Jurnal_model->get_Nomor_Jurnal_Sales($kdcab, $tgl_jurnal);
        $ArrDebit       = array();
        $ArrKredit      = array();
        $total_amort    = 0;
        $schedule_ids   = array();
        $Loop           = 0;

        foreach ($ArrSchedule as $row) {
            $Loop++;
            $total_amort  += $row['nilai_amort'];
            $schedule_ids[] = $row['schedule_id'];

            $ket_jurnal = strtoupper($row['nama_item']) . ' - ' . $bulan . '/' . $tahun;

            // Debit: Akun Biaya (Beban Sewa/Amortisasi)
            $ArrDebit[$Loop] = array(
                'tipe'         => 'JV',
                'nomor'        => $nomor_jm,
                'tanggal'      => $tgl_jurnal,
                'no_perkiraan' => $row['coa_debit'],
                'keterangan'   => strtoupper($row['nm_coa_debit']) . ' - ' . $ket_jurnal,
                'no_reff'      => $row['kode'],
                'debet'        => $row['nilai_amort'],
                'kredit'       => 0,
                'jenis_trans'  => 'amortisasi',
                'created_on'   => date('Y-m-d H:i:s'),
                'created_by'   => $this->auth->user_id()
            );

            // Kredit: Akun Neraca (Biaya Dibayar Dimuka)
            $ArrKredit[$Loop] = array(
                'tipe'         => 'JV',
                'nomor'        => $nomor_jm,
                'tanggal'      => $tgl_jurnal,
                'no_perkiraan' => $row['coa_kredit'],
                'keterangan'   => strtoupper($row['nm_coa_kredit']) . ' - ' . $ket_jurnal,
                'no_reff'      => $row['kode'],
                'debet'        => 0,
                'kredit'       => $row['nilai_amort'],
                'jenis_trans'  => 'amortisasi',
                'created_on'   => date('Y-m-d H:i:s'),
                'created_by'   => $this->auth->user_id()
            );
        }

        // Satu baris javh untuk periode ini
        $ArrJavh = array(array(
            'nomor'         => $nomor_jm,
            'tgl'           => $tgl_jurnal,
            'jml'           => $total_amort,
            'kdcab'         => $kdcab,
            'koreksi_no'    => '-',
            'jenis'         => 'JV',
            'keterangan'    => 'AMORTISASI - ' . $ket_jurnal,
            'bulan'         => $bln_int,
            'tahun'         => $tahun,
            'user_id'       => $user,
            'tgl_jvkoreksi' => $tgl_jurnal
        ));

        $this->db->trans_start();

        $this->db->insert_batch(DBACC . '.javh', $ArrJavh);
        $this->db->insert_batch(DBACC . '.jurnal', $ArrDebit);
        $this->db->insert_batch(DBACC . '.jurnal', $ArrKredit);

        // Update flag schedule + simpan nomor jurnal
        $in_ids = implode(',', array_map('intval', $schedule_ids));
        $this->db->query(
            "UPDATE amortisasi_schedule
             SET flag = 'Y', nomor_jurnal = '" . $nomor_jm . "'
             WHERE id IN (" . $in_ids . ")"
        );

        // Tandai item yang semua schedule-nya sudah dijurnal sebagai completed
        $this->db->query(
            "UPDATE amortisasi a
             SET a.status = 'completed', a.updated_by = '" . $user . "', a.updated_on = NOW()
             WHERE a.status = 'active'
               AND NOT EXISTS (
                   SELECT 1 FROM amortisasi_schedule s
                   WHERE s.amortisasi_id = a.id AND s.flag = 'N'
               )"
        );

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();

            // Log gagal per item
            foreach ($ArrSchedule as $row) {
                $this->db->insert('amortisasi_log', array(
                    'amortisasi_id' => $row['amortisasi_id'],
                    'tanggal'       => date('Y-m-d H:i:s'),
                    'bulan'         => $bulan,
                    'tahun'         => $tahun,
                    'ket'           => 'FAILED',
                    'jurnal_by'     => $user,
                    'kdcab'         => $kdcab
                ));
            }

            return array('status' => 0, 'pesan' => 'Jurnal amortisasi gagal disimpan. Silakan coba lagi.');
        } else {
            $this->db->trans_commit();

            // Log sukses per item
            foreach ($ArrSchedule as $row) {
                $this->db->insert('amortisasi_log', array(
                    'amortisasi_id' => $row['amortisasi_id'],
                    'tanggal'       => date('Y-m-d H:i:s'),
                    'bulan'         => $bulan,
                    'tahun'         => $tahun,
                    'ket'           => 'SUCCESS - No: ' . $nomor_jm,
                    'jurnal_by'     => $user,
                    'kdcab'         => $kdcab
                ));
            }

            history("Posting jurnal amortisasi " . $bulan . "/" . $tahun . " - " . count($ArrSchedule) . " item");
            return array(
                'status' => 1,
                'pesan'  => 'Jurnal amortisasi periode ' . $bulan . '/' . $tahun . ' berhasil diposting. ' . count($ArrSchedule) . ' item, total Rp ' . number_format($total_amort) . '.'
            );
        }
    }

    // -----------------------------------------------------------------------
    // PRIVATE – normalisasi format tanggal ke yyyy-mm-dd
    // Menerima: yyyy-mm-dd, dd/mm/yyyy, mm/dd/yyyy, dd-mm-yyyy
    // -----------------------------------------------------------------------
    private function _parse_date($tgl)
    {
        if (empty($tgl)) return '';

        // Sudah format yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return $tgl;
        }

        // Format mm/dd/yyyy (default datepicker Bootstrap)
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $tgl, $m)) {
            return $m[3] . '-' . $m[1] . '-' . $m[2];
        }

        // Format dd-mm-yyyy
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        // Fallback: coba strtotime
        $ts = strtotime($tgl);
        return $ts ? date('Y-m-d', $ts) : '';
    }
}
