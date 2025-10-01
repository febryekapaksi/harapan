<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_prizes extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Master_prizes.View';
    protected $addPermission    = 'Master_prizes.Add';
    protected $managePermission = 'Master_prizes.Manage';
    protected $deletePermission = 'Master_prizes.Delete';

    protected $qrDir;

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Master_prizes/master_prizes_model',
            'Master_prizes/vouchers_model'
        ));
        $this->qrDir = FCPATH . 'uploads/qrvouchers/';
        if (!is_dir($this->qrDir)) @mkdir($this->qrDir, 0775, true);

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->page_icon('fa fa-gift');

        $data = $this->master_prizes_model->GetList();

        history("View data satuan");
        $this->template->set('results', $data);
        $this->template->title('Master Prizes');
        $this->template->render('index');
    }

    public function add($id = null)
    {
        $field_hist         = (empty($id)) ? 'Tambah' : 'Edit';

        if ($this->input->post()) {
            $post = $this->input->post();

            $id   = $post['id'];
            $code = (!empty($post['code'])) ? $post['code'] : $this->_generateCode();

            $isZonk = !empty($post['is_zonk']) ? 1 : 0;
            $name   = $post['name'];
            if ($isZonk) {
                $name = 'ANDA KURANG BERUNTUNG';
            }

            $ArrHeader = [
                'code'        => $code,
                'name'        => $name,
                'stock_total' => $post['stock_total'],
                'note'        => $post['note'],
                'is_zonk'     => !empty($post['is_zonk']) ? 1 : 0,
            ];

            $newId = null; // [NEW] simpan id insert baru
            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('master_prizes', $ArrHeader);
                $newId = $this->db->insert_id();                    // [NEW]
            } else {
                $this->db->where('id', $id)->update('master_prizes', $ArrHeader);
                $newId = (int)$id;                                   // [NEW]
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Data = ['pesan' => 'Process Failed!', 'status' => 0];
            } else {
                $this->db->trans_commit();

                // [NEW] Generate QR SEKALIAN saat CREATE (bukan edit)
                if (empty($id)) {
                    $qtyQr = (int)$post['stock_total'];              // jumlah QR = stok total
                    list($ok, $msg, $made) = $this->_generateVouchersOnCreate($newId, $qtyQr);
                    if (!$ok) {
                        echo json_encode(['status' => 0, 'pesan' => 'Hadiah tersimpan, tapi QR gagal dibuat: ' . $msg]);
                        return;
                    }
                }

                $Arr_Data = ['pesan' => 'Process Success!', 'status' => 1];
                history(($id ? 'Edit' : 'Tambah') . " data Hadiah " . ($id ?: '[new]'));
            }

            echo json_encode($Arr_Data);
            return;
        } else {
            $result   = $this->db->get_where('master_prizes', ['id' => $id])->row_array();

            $data = [
                'id'            => $result['id'] ?? '',
                'code'          => $result['code'] ?? '',
                'name'          => $result['name'] ?? '',
                'stock_total'   => $result['stock_total'] ?? '',
                'status'        => $result['status'] ?? '',
                'note'          => $result['note'] ?? '',
                'is_zonk'       => $result['is_zonk'] ?? '',
            ];

            $this->template->title($field_hist . ' Hadiah');
            $this->template->page_icon('fa fa-edit');
            $this->template->render('form', $data);
        }
    }

    public function hapus()
    {
        $id = $this->input->post('id');

        // 1) Ambil data prize & vouchers
        $prize = $this->db->get_where('master_prizes', ['id' => $id])->row();
        if (!$prize) {
            echo json_encode(['status' => 0, 'pesan' => 'Prize tidak ditemukan']);
            return;
        }

        // Direktori dari vouchers (kalau kolom qr_directory ada)
        $vouchers = $this->db->select('id, qr_directory')
            ->from('vouchers')
            ->where('prize_id', $id)
            ->get()->result();

        // Kumpulkan direktori unik
        $dirs = [];
        foreach ($vouchers as $v) {
            if (!empty($v->qr_directory)) {
                $dirs[rtrim($v->qr_directory, '/') . '/'] = true;
            }
        }
        // fallback: jika tidak ada di vouchers, derive dari code prize
        if (empty($dirs)) {
            $dirs['uploads/qrvouchers/' . $prize->code . '/'] = true;
        }
        $dirs = array_keys($dirs);

        // 2) Transaksi DB
        $this->db->trans_start();
        $this->db->where('prize_id', $id)->delete('vouchers');       // hapus voucher2 terkait
        $this->db->where('id', $id)->delete('master_prizes');        // hapus master
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Process Failed!']);
            return;
        }

        $this->db->trans_commit();
        history("Delete data master prizes id " . $id);

        // 3) Hapus file/folder di disk (setelah commit)
        $this->load->helper('file');

        $base = realpath(FCPATH . 'uploads/qrvouchers'); // pagar pembatas keamanan
        foreach ($dirs as $dir) {
            $fullPath = rtrim(FCPATH . $dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            // validasi path tetap di bawah uploads/qrvouchers
            $targetReal = realpath($fullPath);
            if ($targetReal === false) {
                // folder mungkin sudah tidak ada — lanjut
                continue;
            }
            if (strpos($targetReal, $base) !== 0) {
                // kalau bukan di dalam base, jangan dihapus (keamanan)
                log_message('error', 'Skip delete outside base: ' . $targetReal);
                continue;
            }

            // hapus isi folder lalu foldernya
            @delete_files($targetReal, TRUE); // hapus semua file & subfolder
            @rmdir($targetReal);
        }

        echo json_encode(['status' => 1, 'pesan' => 'Process Success!']);
    }


    public function download_qr($prizeId)
    {
        // 1) Dapatkan folder QR
        $row = $this->db->select('qr_directory')
            ->from('vouchers')
            ->where('prize_id', (int)$prizeId)
            ->limit(1)
            ->get()
            ->row_array();

        if (!$row || empty($row['qr_directory'])) {
            show_error('QR belum digenerate untuk hadiah ini.', 404);
            return;
        }

        $dir = FCPATH . rtrim($row['qr_directory'], '/\\') . '/';
        if (!is_dir($dir)) {
            show_error('Folder QR tidak ditemukan.', 404);
            return;
        }

        // 2) Kumpulkan file PNG
        $files = glob($dir . '*.png');
        natsort($files);
        $files = array_values($files);

        if (empty($files)) {
            show_error('Tidak ada file QR di folder ini.', 404);
            return;
        }

        // 3) Info hadiah untuk nama file PDF
        $prize     = $this->db->get_where('master_prizes', ['id' => (int)$prizeId])->row_array();
        $prizeCode = $prize['code'] ?? ('P' . $prizeId);

        // 4) Build HTML (2 QR per halaman)
        $items = [];
        foreach ($files as $f) {
            $code  = pathinfo($f, PATHINFO_FILENAME);
            $img64 = base64_encode(@file_get_contents($f));
            if ($img64 === false) continue; // skip yang gagal dibaca
            $items[] = ['code' => $code, 'src' => 'data:image/png;base64,' . $img64];
        }

        if (empty($items)) {
            show_error('Gagal membaca file QR.', 500);
            return;
        }

        $html = '<html><head><meta charset="utf-8">
        <style>
            @page { size: A4; margin: 10mm; }
            body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
            .item { text-align:center; page-break-inside: avoid; margin: 8mm 0; }
            .qr   { width: 80mm; height: auto; }
            .code { margin-top: 4mm; font-weight: bold; }
            .pb   { page-break-after: always; }
        </style>
    </head><body>';

        $n = count($items);
        for ($i = 0; $i < $n; $i++) {
            $it = $items[$i];
            $html .= '<div class="item">
                    <img class="qr" src="' . $it['src'] . '" alt="' . $it['code'] . '">
                    <div class="code">' . $it['code'] . '</div>
                  </div>';

            // page break setelah setiap 2 item, kecuali terakhir
            if (($i % 2) === 1 && $i !== $n - 1) {
                $html .= '<div class="pb"></div>';
            }
        }

        $html .= '</body></html>';

        // 5) Render PDF dengan Dompdf
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'QR_' . $prizeCode . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => 1]); // download
    }


    public function list_guest($id)
    {

        $claims = $this->db
            ->select('c.*, v.code, v.qr_directory, v.token')
            ->from('claims c')
            ->join('vouchers v', 'v.id = c.voucher_id', 'left')
            ->where('c.prize_id', (int)$id)
            ->order_by('c.created_at', 'DESC')
            ->get()
            ->result_array();

        $data = [
            'claims'  => $claims
        ];

        $this->template->page_icon('fa fa-users');
        $this->template->title('Daftar Pemenang');
        $this->template->render('list_guest', $data);
    }

    // Private Function Section 

    private function _generateCode()
    {
        $Ym = date('ym');
        $SQL = "SELECT MAX(code) as maxM FROM master_prizes WHERE code LIKE 'HP" . $Ym . "%'";
        $result = $this->db->query($SQL)->result_array();
        $angkaUrut = $result[0]['maxM'];
        $urutan = (int)substr($angkaUrut, 6, 4);
        $urutan++;
        return "HP" . $Ym . sprintf('%04s', $urutan);
    }

    // Buat voucher+QR sebanyak $qty untuk $prizeId
    private function _generateVouchersOnCreate($prizeId, $qty, $prefix = null)
    {
        if ($qty <= 0) return [true, '', 0];

        // baca master untuk tahu zonk & dapat code hadiah
        $prizeRow    = $this->db->get_where('master_prizes', ['id' => $prizeId])->row_array();
        $isZonkPrize = $prizeRow && !empty($prizeRow['is_zonk']);
        $prizeCode   = $prizeRow['code'] ?? ('P' . $prizeId);

        // prefix kode voucher (optional)
        $prefix = $prefix ?: (($isZonkPrize ? 'ZNK' : 'VC') . date('ymd'));

        // ====== tentukan folder simpan PNG per hadiah ======
        $folderSlug = $prizeCode;                                   // mis. HP25090123
        $targetDir  = rtrim($this->qrDir, '/') . '/' . $folderSlug . '/'; // uploads/qrvouchers/HP25090123/
        if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

        $logoPath = FCPATH . 'assets/images/logo_sbf.png';

        $now  = date('Y-m-d H:i:s');
        $made = 0;

        $this->db->trans_begin();
        for ($i = 0; $i < $qty; $i++) {
            // token unik
            $token = bin2hex(random_bytes(16));
            while ($this->vouchers_model->token_exists($token)) {
                $token = bin2hex(random_bytes(16));
            }
            // kode voucher berurutan
            $code = $this->vouchers_model->next_code($prefix);

            // URL untuk discan
            $scanUrl = site_url('master_prizes/public_scan/resolve/' . $token);

            // simpan PNG ke folder hadiah; dapatkan path absolut & url publik (kalau perlu)
            $absPath = $this->_saveQrPng($scanUrl, $code, $targetDir, $logoPath);

            // simpan row voucher
            $this->vouchers_model->insert([
                'prize_id'     => (int)$prizeId,
                'code'         => $code,
                'token'        => $token,
                'status'       => 'UNSCANNED',
                'qr_directory' => str_replace(FCPATH, '', $targetDir),
                'created_at'   => $now,
                'updated_at'   => $now
            ]);

            $made++;
        }

        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return [false, 'DB error saat generate voucher', $made];
        }
        $this->db->trans_commit();
        return [true, '', $made];
    }

    private function _saveQrPng($data, $code, $targetDir = null, $logoPath = null)
    {
        // pastikan lib QR & GD siap
        if (!class_exists('QRcode')) {
            require_once APPPATH . 'libraries/phpqrcode/qrlib.php';
        }

        $dir = $targetDir ?: ($this->qrDir . date('Ymd') . DIRECTORY_SEPARATOR);
        $dir = rtrim($dir, "/\\") . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $path = $dir . $code . '.png';

        // 1) Generate QR dasar (pakai ECC H agar kuat ditempeli logo)
        //    QRcode::png($text, $outfile, $level, $size, $margin)
        QRcode::png($data, $path, QR_ECLEVEL_H, 7, 2); // size 7 biar resolusi cukup

        // 2) Jika tidak ada logo atau GD tidak tersedia → selesai
        if (empty($logoPath) || !is_file($logoPath) || !extension_loaded('gd')) {
            return $path;
        }

        // 3) Buka gambar QR & logo
        $qr = @imagecreatefrompng($path);
        if (!$qr) return $path;

        // dukung logo png/jpg
        $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
        if ($ext === 'png')      $logo = @imagecreatefrompng($logoPath);
        elseif ($ext === 'jpg' || $ext === 'jpeg') $logo = @imagecreatefromjpeg($logoPath);
        else $logo = @imagecreatefrompng($logoPath); // default coba png

        if (!$logo) {
            imagedestroy($qr);
            return $path;
        }

        // 4) Hitung ukuran target logo (±22% lebar QR)
        $qrW = imagesx($qr);
        $qrH = imagesy($qr);
        $lgW = imagesx($logo);
        $lgH = imagesy($logo);

        $targetLogoW = (int) round($qrW * 0.22);
        $targetLogoH = (int) round($lgH * ($targetLogoW / max(1, $lgW)));

        // 5) Resize logo ke truecolor with alpha
        $logoRes = imagecreatetruecolor($targetLogoW, $targetLogoH);
        imagealphablending($logoRes, false);
        imagesavealpha($logoRes, true);
        imagecopyresampled($logoRes, $logo, 0, 0, 0, 0, $targetLogoW, $targetLogoH, $lgW, $lgH);

        // 6) Koordinat tengah & bantalan putih (quiet zone di belakang logo)
        $x = (int) round(($qrW - $targetLogoW) / 2);
        $y = (int) round(($qrH - $targetLogoH) / 2);
        $pad = (int) round($qrW * 0.02); // 2% dari lebar

        // aktifkan alpha di QR
        imagealphablending($qr, true);
        imagesavealpha($qr, true);

        // gambar kotak putih di belakang logo (tanpa alpha agar kontras)
        $white = imagecolorallocate($qr, 255, 255, 255);
        imagefilledrectangle(
            $qr,
            max(0, $x - $pad),
            max(0, $y - $pad),
            min($qrW - 1, $x + $targetLogoW + $pad),
            min($qrH - 1, $y + $targetLogoH + $pad),
            $white
        );

        // 7) Tempel logo
        imagecopy($qr, $logoRes, $x, $y, 0, 0, $targetLogoW, $targetLogoH);

        // 8) Simpan kembali PNG
        imagepng($qr, $path, 6);

        // 9) Bersih-bersih
        imagedestroy($logoRes);
        imagedestroy($logo);
        imagedestroy($qr);

        return $path;
    }
}
