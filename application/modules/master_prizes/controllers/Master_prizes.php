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
            ];

            $this->template->title($field_hist . ' Hadiah');
            $this->template->page_icon('fa fa-edit');
            $this->template->render('form', $data);
        }
    }

    public function hapus()
    {
        $data = $this->input->post();
        $id = $data['id'];

        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete('master_prizes');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array(
                'pesan'  => 'Process Failed!',
                'status' => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data = array(
                'pesan'  => 'Process Success!',
                'status' => 1
            );
            history("Delete data master prizes id " . $id);
        }

        echo json_encode($Arr_Data);
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

        // baca master untuk tahu zonk atau tidak
        $prizeRow   = $this->db->get_where('master_prizes', ['id' => $prizeId])->row_array();
        $isZonkPrize = $prizeRow && !empty($prizeRow['is_zonk']);

        // prefix default beda biar gampang dibedakan saat cetak (opsional)
        $prefix = $prefix ?: (($isZonkPrize ? 'ZNK' : 'VC') . date('ymd'));

        $now = date('Y-m-d H:i:s');
        $made = 0;

        $this->db->trans_begin();
        for ($i = 0; $i < $qty; $i++) {
            $token = bin2hex(random_bytes(16));
            while ($this->vouchers_model->token_exists($token)) {
                $token = bin2hex(random_bytes(16));
            }
            $code = $this->vouchers_model->next_code($prefix);

            // Simpan prize_id apapun (termasuk zonk) supaya laporan bisa mengelompok,
            // tapi penentuan "zonk" saat scan akan melihat flag is_zonk.
            $this->vouchers_model->insert([
                'prize_id'   => (int)$prizeId,
                'code'       => $code,
                'token'      => $token,
                'status'     => 'UNSCANNED',
                'created_at' => $now,
                'updated_at' => $now
            ]);

            $scanUrl = site_url('master_prizes/public_scan/resolve/' . $token);
            $this->_saveQrPng($scanUrl, $code);
            $made++;
        }

        if (!$this->db->trans_status()) {
            $this->db->trans_rollback();
            return [false, 'DB error saat generate voucher', $made];
        }
        $this->db->trans_commit();
        return [true, '', $made];
    }


    private function _saveQrPng($data, $code)
    {
        require_once APPPATH . 'libraries/phpqrcode/qrlib.php';
        $dir = $this->qrDir . date('Ymd') . '/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $path = $dir . $code . '.png';
        QRcode::png($data, $path, QR_ECLEVEL_M, 5, 2);     // size=5, margin=2
        return $path;
    }
}
