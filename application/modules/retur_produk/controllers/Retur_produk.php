<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_produk extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Retur_produk.View';
    protected $addPermission    = 'Retur_produk.Add';
    protected $managePermission = 'Retur_produk.Manage';
    protected $deletePermission = 'Retur_produk.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Retur_produk/Retur_produk_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Retur Produk');
        $this->template->page_icon('fa fa-truck');
        $this->template->render('index');
    }

    public function data_side_retur()
    {
        $this->Retur_produk_model->data_side_retur();
    }

    public function add($id)
    {
        $sql = "
                SELECT 
                    sj.no_surat_jalan, 
                    sj.no_so, 
                    sj.pengiriman, 
                    so.id_customer, 
                    mc.name_customer
                FROM surat_jalan sj
                JOIN sales_order so ON sj.no_so = so.no_so
                JOIN master_customers mc ON so.id_customer = mc.id_customer
                WHERE sj.id = ?
                ORDER BY sj.no_surat_jalan DESC
            ";
        $sj = $this->db->query($sql, [$id])->row_array();

        $sql2 = "
            SELECT 
                sjd.id_so_det, 
                sjd.id_product, 
                sjd.product as nm_product, 
                sjd.qty as qty_order, 
                sjd.qty_terkirim, 
                sjd.qty_retur, 
                sod.harga_penawaran as harga,
                (sjd.qty_retur * sod.harga_penawaran) as total
            FROM surat_jalan_detail sjd
            JOIN sales_order_detail sod ON sjd.id_so_det = sod.id
            WHERE sjd.id_sj = ?
            AND sjd.qty_retur != 0
            ORDER BY sjd.id_so_det;
        ";
        $detail = $this->db->query($sql2, [$id])->result_array();

        $data = [
            'sj' => $sj,
            'detail' => $detail,
        ];

        $this->template->title('Request Retur');
        $this->template->page_icon('fa fa-truck');
        $this->template->render('form', $data);
    }

    public function save()
    {
        $post = $this->input->post();

        $detail = $post['detail'];
        $tipe = $post['pengiriman'];

        // UNTUK BUAT NOMOR RETUR
        $Ym = date('ym');
        if ($tipe == 'Pabrik') {
            $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'RT/P/{$Ym}/%'";
        }
        $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'RT/G/{$Ym}/%'";
        $result = $this->db->query($SQL)->result_array();
        $angkaUrut = $result[0]['maxM'];

        if ($angkaUrut) {
            $parts = explode('/', $angkaUrut);
            $urutan = isset($parts[3]) ? (int)$parts[3] : 0;
        } else {
            $urutan = 0;
        }

        $urutan++;
        $formatUrut = sprintf('%04s', $urutan);

        if ($tipe == 'Pabrik') {
            $no_retur = "RT/P/{$Ym}/{$formatUrut}";
        }
        $no_retur = "RT/G/{$Ym}/{$formatUrut}";
        $no_surat_jalan = $post['no_surat_jalan'];

        $ArrHeader = [
            'no_retur'         => $no_retur,
            'no_surat_jalan'   => $no_surat_jalan,
            'no_so'            => $post['no_so'],
            'id_customer'      => $post['id_customer'],
            'nm_customer'      => $post['nm_customer'],
            'alasan'           => $post['alasan'],
            'tipe'             => $tipe,
            'total_harga'      => str_replace(',', '', $post['grand_total']),
            'tgl_retur'        => date('Y-m-d', strtotime($post['tgl_retur'])),
            'created_by'       => $this->auth->user_id(),
            'created_date'     => date('Y-m-d H:i:s'),
            'status'           => 1,
            'jenis_retur'      => 1
        ];

        // Prepare Detail
        $ArrDetail = [];
        foreach ($detail as $key => $value) {
            $ArrDetail[$key] = [
                'no_retur'        => $no_retur,
                'no_surat_jalan'  => $no_surat_jalan,
                'id_so_det'       => $value['id_so_det'],
                'id_product'      => $value['id_product'],
                'nm_product'      => $value['nm_product'],
                'qty_retur'       => $value['qty_retur'],
                'alasan'          => $value['alasan_retur'],
                'harga'           => str_replace(',', '', $value['harga']),
                'total'           => str_replace(',', '', $value['total']),
                'created_by'      => $this->auth->user_id(),
                'created_date'    => date('Y-m-d H:i:s'),
            ];
        }

        // Simpan ke DB
        $this->db->trans_start();

        $this->db->insert('tr_retur', $ArrHeader);

        $this->db->insert_batch('tr_retur_detail', $ArrDetail);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $res = ['status' => 0, 'pesan' => 'Gagal menyimpan data.'];
        } else {
            $this->db->trans_commit();
            $res = ['status' => 1, 'pesan' => 'Data berhasil disimpan.'];
            history("Create Request Retur : " . $no_retur);
        }

        echo json_encode($res);
    }

    public function view($id_retur = null)
    {
        if (!$id_retur) {
            show_404();
        }

        // Subquery bantu untuk status tampilan saja (tidak mengubah tr_retur.status):
        // ambil SPK Delivery terakhir untuk no_retur ini, lalu cek status SJ pengganti-nya
        // supaya badge "On Loading" bisa lebih akurat jadi "On Delivery"/"Delivered".
        $spk_sub = "
            (
                SELECT
                    spk.no_retur,
                    spk.status         AS spk_status,
                    sj2.status          AS sj_status
                FROM spk_delivery spk
                INNER JOIN (
                    SELECT no_retur, MAX(created_date) AS max_date
                    FROM spk_delivery
                    WHERE no_retur IS NOT NULL AND no_retur != ''
                    GROUP BY no_retur
                ) latest ON latest.no_retur = spk.no_retur AND latest.max_date = spk.created_date
                LEFT JOIN surat_jalan sj2 ON sj2.no_surat_jalan = spk.no_surat_jalan
            ) spkinfo
        ";

        $retur = $this->db
            ->select('r.id as id_retur, r.no_retur, r.no_surat_jalan, r.no_so, r.id_customer, r.nm_customer, r.alasan, r.tgl_retur, r.total_harga, r.tipe, r.status, r.created_date, spkinfo.spk_status, spkinfo.sj_status')
            ->from('tr_retur r')
            ->join($spk_sub, 'spkinfo.no_retur = r.no_retur', 'left')
            ->where('r.id', $id_retur)
            ->get()
            ->row_array();

        if (!$retur) {
            show_error("Data Retur tidak ditemukan.", 404);
        }

        $detail = $this->db
            ->select('rd.*')
            ->from('tr_retur_detail rd')
            ->where('rd.no_retur', $retur['no_retur'])
            ->get()
            ->result_array();

        $data = [
            'retur'  => $retur,
            'detail' => $detail,
        ];

        $this->template->title("Detail Retur {$retur['no_retur']}");
        $this->template->page_icon('fa fa-eye');
        $this->template->render('view', $data);
    }

    public function req_spk($id_retur = null)
    {
        if (!$id_retur) {
            show_404();
        }

        $retur = $this->db
            ->select('r.id as id_retur, r.no_retur, r.no_so, r.id_customer, r.nm_customer, r.tipe, c.address_office')
            ->from('tr_retur r')
            ->join('master_customers c', 'c.id_customer = r.id_customer', 'left')
            ->where('r.id', $id_retur)
            ->get()
            ->row_array();

        if (!$retur) {
            show_error("Data Retur dengan nomor {$retur['no_retur']} tidak ditemukan.", 404);
        }

        $detail = $this->db
            ->select('rd.*')
            ->from('tr_retur_detail rd')
            ->where('rd.no_retur', $retur['no_retur'])
            ->get()
            ->result_array();

        $data = [
            'retur'     => $retur,
            'detail'    => $detail
        ];

        $this->template->page_icon('fa fa-truck');
        $this->template->title("Request SPK Delivery Retur {$retur['no_retur']}");
        $this->template->render('req_spk', $data);
    }

    public function close_retur()
    {
        $id_sj = (int) $this->input->post('id_sj');

        if (!$id_sj) {
            echo json_encode(['status' => 0, 'pesan' => 'ID Surat Jalan tidak valid.']);
            return;
        }

        // Ambil data surat_jalan + customer untuk isi tr_retur
        $sj = $this->db
            ->select('sj.id, sj.no_surat_jalan, sj.no_so, so.id_customer, mc.name_customer')
            ->from('surat_jalan sj')
            ->join('sales_order so', 'sj.no_so = so.no_so', 'left')
            ->join('master_customers mc', 'so.id_customer = mc.id_customer', 'left')
            ->where('sj.id', $id_sj)
            ->get()
            ->row_array();

        if (!$sj) {
            echo json_encode(['status' => 0, 'pesan' => 'Surat Jalan tidak ditemukan.']);
            return;
        }

        // Cek apakah sudah ada tr_retur
        $retur = $this->db
            ->select('id, status')
            ->where('no_surat_jalan', $sj['no_surat_jalan'])
            ->get('tr_retur')
            ->row_array();

        // Guard: jika sudah On Loading (status 2) tidak bisa di-close
        if ($retur && $retur['status'] == 2) {
            echo json_encode(['status' => 0, 'pesan' => 'Retur sudah On Loading, tidak bisa di-close.']);
            return;
        }

        // Guard: jika sudah Closed (status 3)
        if ($retur && $retur['status'] == 3) {
            echo json_encode(['status' => 0, 'pesan' => 'Retur sudah berstatus Closed.']);
            return;
        }

        $this->db->trans_begin();

        if ($retur) {
            // Sudah ada tr_retur (status 1 = Proses Retur) → update ke Closed
            $this->db->update('tr_retur', ['status' => 3], ['id' => $retur['id']]);
        } else {
            // Belum ada tr_retur (Belum Proses) → buat nomor dan insert header + detail
            $Ym  = date('ym');
            $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'RT/G/{$Ym}/%'";
            $result    = $this->db->query($SQL)->result_array();
            $angkaUrut = $result[0]['maxM'];

            if ($angkaUrut) {
                $parts  = explode('/', $angkaUrut);
                $urutan = isset($parts[3]) ? (int)$parts[3] : 0;
            } else {
                $urutan = 0;
            }
            $urutan++;
            $no_retur = "RT/G/{$Ym}/" . sprintf('%04d', $urutan);

            // Ambil detail produk retur dari surat_jalan_detail
            $sql_detail = "
                SELECT
                    sjd.id_so_det,
                    sjd.id_product,
                    sjd.product        AS nm_product,
                    sjd.qty_retur,
                    COALESCE(sod.harga_penawaran, 0)              AS harga,
                    (sjd.qty_retur * COALESCE(sod.harga_penawaran, 0)) AS total
                FROM surat_jalan_detail sjd
                LEFT JOIN sales_order_detail sod ON sjd.id_so_det = sod.id
                WHERE sjd.id_sj = ?
                  AND sjd.qty_retur != 0
                ORDER BY sjd.id_so_det
            ";
            $detail_retur = $this->db->query($sql_detail, [$id_sj])->result_array();

            // Hitung grand total
            $total_harga = array_sum(array_column($detail_retur, 'total'));

            // Insert header tr_retur
            $this->db->insert('tr_retur', [
                'no_retur'       => $no_retur,
                'no_surat_jalan' => $sj['no_surat_jalan'],
                'no_so'          => $sj['no_so'],
                'id_customer'    => $sj['id_customer'],
                'nm_customer'    => $sj['name_customer'],
                'alasan'         => 'Retur di-close, tidak perlu kirim ulang.',
                'tipe'           => 'Gudang',
                'total_harga'    => $total_harga,
                'tgl_retur'      => date('Y-m-d'),
                'created_by'     => $this->auth->user_id(),
                'created_date'   => date('Y-m-d H:i:s'),
                'status'         => 3,
                'jenis_retur'    => 1,
            ]);

            // Insert detail tr_retur_detail
            if (!empty($detail_retur)) {
                $ArrDetail = [];
                foreach ($detail_retur as $d) {
                    $ArrDetail[] = [
                        'no_retur'       => $no_retur,
                        'no_surat_jalan' => $sj['no_surat_jalan'],
                        'id_so_det'      => $d['id_so_det'],
                        'id_product'     => $d['id_product'],
                        'nm_product'     => $d['nm_product'],
                        'qty_retur'      => $d['qty_retur'],
                        'alasan'         => 'Close - tidak perlu kirim ulang',
                        'harga'          => $d['harga'],
                        'total'          => $d['total'],
                        'created_by'     => $this->auth->user_id(),
                        'created_date'   => date('Y-m-d H:i:s'),
                    ];
                }
                $this->db->insert_batch('tr_retur_detail', $ArrDetail);
            }
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode(['status' => 0, 'pesan' => 'Gagal menyimpan data.']);
            return;
        }

        $this->db->trans_commit();
        history("Close Retur SJ: " . $sj['no_surat_jalan']);
        echo json_encode(['status' => 1, 'pesan' => 'Retur berhasil di-close. Piutang mengikuti nilai confirm awal.']);
    }

    public function save_spk()
    {
        $data = $this->input->post();

        $no_retur         = $data['no_retur'];
        $id_customer      = $data['id_customer'];
        $no_so            = $data['no_so'];
        $tanggal_spk      = !empty($data['tanggal_spk']) ? date('Y-m-d', strtotime($data['tanggal_spk'])) : NULL;
        $tanggal_kirim    = !empty($data['tanggal_kirim']) ? date('Y-m-d', strtotime($data['tanggal_kirim'])) : NULL;
        $delivery_address = $data['delivery_address'];
        $notes            = $data['notes'];
        $pengiriman       = $data['tipe'];
        $detail           = $data['detail'];

        // Generate nomor SPK baru
        $Ym             = date('ym');
        $SQL            = "SELECT MAX(no_delivery) as maxP FROM spk_delivery WHERE no_delivery LIKE 'SPK" . $Ym . "%'";
        $result         = $this->db->query($SQL)->row_array();
        $angkaUrut      = isset($result['maxP']) ? $result['maxP'] : null;
        $lastNum        = ($angkaUrut) ? (int)substr($angkaUrut, 7, 4) : 0;
        $no_delivery    = 'SPK' . $Ym . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        // Header insert
        $ArrHeader = [
            'no_delivery'      => $no_delivery,
            'id_customer'      => $id_customer,
            'no_so'            => $no_so,
            'tanggal_spk'      => $tanggal_spk,
            'tanggal_kirim'    => $tanggal_kirim,
            'delivery_address' => $delivery_address,
            'pengiriman'       => $pengiriman,
            'created_by'       => $this->auth->user_id(),
            'created_date'     => date('Y-m-d H:i:s'),
            'notes'            => $notes,
            'no_retur'         => $no_retur,  // penanda bahwa SPK ini berasal dari proses retur
        ];

        $ArrDetail = [];

        $this->db->trans_start();

        foreach ($detail as $key => $value) {
            $id_so_det      = $value['id_so_det'];
            $id_product     = $value['id_product'];
            $qty_spk        = (float)str_replace(',', '', $value['qty_spk']);
            $qty_retur      = (float)str_replace(',', '', $value['qty_retur']);

            $ArrDetail[] = [
                'no_delivery'     => $no_delivery,
                'no_so'           => $no_so,
                'id_so_det'       => $id_so_det,
                'id_product'      => $id_product,
                'qty_so'          => $qty_retur,
                'qty_spk'         => $qty_spk,
                'qty_belum_muat'  => $qty_spk
            ];
        }

        // Insert detail SPK
        if (!empty($ArrDetail)) {
            $this->db->insert_batch('spk_delivery_detail', $ArrDetail);
        }

        $this->db->insert('spk_delivery', $ArrHeader);
        $this->db->update('tr_retur', ['status' => 2], ['no_retur' => $no_retur]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            echo json_encode([
                'pesan'  => 'Save gagal disimpan ...',
                'status' => 0
            ]);
        } else {
            $this->db->trans_commit();
            history("Create SPK Delivery: " . $no_delivery);
            echo json_encode([
                'pesan'  => 'Save berhasil disimpan. Thanks ...',
                'status' => 1
            ]);
        }
    }
}
