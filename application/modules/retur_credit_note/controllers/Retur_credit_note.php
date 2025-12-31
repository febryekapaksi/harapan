<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retur_credit_note extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Retur_credit_note.View';
    protected $addPermission    = 'Retur_credit_note.Add';
    protected $managePermission = 'Retur_credit_note.Manage';
    protected $deletePermission = 'Retur_credit_note.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Retur_credit_note/Retur_credit_note_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Retur Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('index');
    }

    public function data_side_inv()
    {
        $this->Retur_credit_note_model->data_side_inv();
    }

    public function add($id)
    {
        $sql = "
                SELECT 
                    i.id_invoice, 
                    i.id_billing, 
                    i.id_so, 
                    sj.pengiriman, 
                    i.id_customer, 
                    i.nm_customer
                FROM tr_invoice_sales i
                JOIN surat_jalan sj ON i.id_billing = sj.no_surat_jalan
                WHERE i.id_invoice = ?
                ORDER BY i.id_invoice DESC
            ";
        $inv = $this->db->query($sql, [$id])->row_array();

        $sql2 = "
            SELECT 
                dt.id_so, 
                sjd.id_so_det, 
                dt.id_penawaran, 
                dt.id_delivery, 
                dt.id_produk, 
                dt.nm_produk, 
                round(dt.qty) as qty, 
                dt.harga, 
                round(dt.qty * dt.harga) as total
            FROM tr_invoice_sales_detail dt
            JOIN surat_jalan_detail sjd 
                ON dt.id_delivery   = sjd.no_surat_jalan
                AND dt.id_produk    = sjd.id_product
            WHERE dt.id_invoice = ?
            ORDER BY dt.id_invoice;
        ";
        $detail = $this->db->query($sql2, [$id])->result_array();

        $data = [
            'inv' => $inv,
            'detail' => $detail,
        ];

        $this->template->title('Request Credit Note');
        $this->template->page_icon('fa fa-clipboard');
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
            $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'CN/P/{$Ym}/%'";
        }
        $SQL = "SELECT MAX(no_retur) as maxM FROM tr_retur WHERE no_retur LIKE 'CN/G/{$Ym}/%'";
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
            $no_retur = "CN/P/{$Ym}/{$formatUrut}";
        }
        $no_retur = "CN/G/{$Ym}/{$formatUrut}";
        $no_surat_jalan = $post['id_billing'];

        $ArrHeader = [
            'no_retur'         => $no_retur,
            'no_surat_jalan'   => $no_surat_jalan,
            'no_so'            => $post['id_so'],
            'id_customer'      => $post['id_customer'],
            'nm_customer'      => $post['nm_customer'],
            'alasan'           => $post['alasan'],
            'tipe'             => $tipe,
            'total_harga'      => str_replace(',', '', $post['grand_total']),
            'tgl_retur'        => date('Y-m-d', strtotime($post['tgl_retur'])),
            'created_by'       => $this->auth->user_id(),
            'created_date'     => date('Y-m-d H:i:s'),
            'status'           => 1,
            'jenis_retur'      => 2
        ];

        // Prepare Detail
        $ArrDetail = [];
        foreach ($detail as $key => $value) {
            $ArrDetail[$key] = [
                'no_retur'        => $no_retur,
                'no_surat_jalan'  => $no_surat_jalan,
                'id_so_det'       => $value['id_so_det'],
                'id_product'      => $value['id_produk'],
                'nm_product'      => $value['nm_produk'],
                'qty_retur'       => $value['qty'],
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

    public function view($id)
    {
        $sql = "
                SELECT *
                FROM tr_retur r
                WHERE r.id = ?
                ORDER BY r.id DESC
            ";
        $inv = $this->db->query($sql, [$id])->row_array();

        $sql2 = "
            SELECT *
            FROM tr_retur_detail dt
            WHERE dt.no_retur = ?
            ORDER BY dt.no_retur;
        ";
        $detail = $this->db->query($sql2, [$inv['no_retur']])->result_array();

        $data = [
            'inv' => $inv,
            'detail' => $detail,
        ];

        $this->template->title('View Credit Note');
        $this->template->page_icon('fa fa-clipboard');
        $this->template->render('view', $data);
    }
}
