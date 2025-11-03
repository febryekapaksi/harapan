<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Surat_jalan extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Surat_Jalan.View';
    protected $addPermission    = 'Surat_Jalan.Add';
    protected $managePermission = 'Surat_Jalan.Manage';
    protected $deletePermission = 'Surat_Jalan.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Surat_jalan/surat_jalan_model',
            'jurnal_nomor/Jurnal_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Surat Jalan');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('index');
    }

    public function index_retur()
    {
        $this->template->title('List Produk Retur');
        $this->template->page_icon('fa fa-rotate-left');
        $this->template->render('index_retur');
    }

    public function index_hilang()
    {
        $this->template->title('List Produk Hilang');
        $this->template->page_icon('fa fa-eye-slash');
        $this->template->render('index_hilang');
    }

    public function data_side_surat_jalan()
    {
        $this->surat_jalan_model->data_side_surat_jalan();
    }

    public function data_side_retur()
    {
        $this->surat_jalan_model->data_side_retur();
    }

    public function data_side_hilang()
    {
        $this->surat_jalan_model->data_side_hilang();
    }

    public function add()
    {
        $sql = "
                SELECT l.no_loading, l.nopol, l.tanggal_muat
                FROM loading_delivery l
                WHERE l.status = 3
                AND l.pengiriman = 'Gudang'
                AND EXISTS (
                    SELECT 1
                    FROM loading_delivery_detail ld
                    WHERE ld.no_loading = l.no_loading
                    AND NOT EXISTS (
                        SELECT 1
                        FROM surat_jalan sj
                        WHERE sj.no_loading  = ld.no_loading
                        AND sj.no_so       = ld.no_so
                        AND sj.no_delivery = ld.no_delivery
                    )
                )
                GROUP BY l.no_loading, l.nopol, l.tanggal_muat
                ORDER BY l.tanggal_muat DESC
                ";
        $loading = $this->db->query($sql)->result_array();

        $data = [
            'loading' => $loading
        ];

        $this->template->title('Add Surat Jalan');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('form', $data);
    }

    public function get_spk()
    {
        $no_loading = $this->input->get('no_loading', TRUE);

        $header = $this->db->get_where('loading_delivery', ['no_loading' => $no_loading])->row_array();
        $detail = $this->db
            ->select('
                    ldd.*,
                    sd.no_so,
                    sd.pengiriman,
                    sd.tanggal_kirim,
                    sd.delivery_address AS alamat,
                    sdd.id_so_det,        
                    p.weight,
                    (ldd.qty_muat * COALESCE(w.harga_beli,0)) AS costbook,
                ')
            ->from('loading_delivery_detail ldd')
            ->join('spk_delivery sd', 'ldd.no_delivery = sd.no_delivery', 'left')
            ->join('spk_delivery_detail sdd', 'sdd.no_delivery = ldd.no_delivery AND sdd.id_product = ldd.id_product', 'left')
            ->join('new_inventory_4 p', 'ldd.id_product = p.code_lv4', 'left')
            ->join('warehouse_stock w', 'p.code_lv4 = w.id_material', 'left')
            ->where('ldd.no_loading', $no_loading)
            ->where("CONCAT(ldd.no_so, '|', ldd.no_delivery) NOT IN (
                    SELECT CONCAT(no_so, '|', no_delivery)
                    FROM surat_jalan
                    WHERE no_loading = '$no_loading')")
            ->get()
            ->result_array();

        echo json_encode([
            'header' => $header,
            'detail' => $detail
        ]);
    }

    public function save()
    {
        $post = $this->input->post();

        $detail = $post['detail'];

        $is_update = isset($post['id']) && !empty($post['id']);
        $tanggal_sekarang = date('Y-m-d H:i:s');

        if ($is_update) {
            // MODE UPDATE
            $id_sj = $post['id'];
            $no_surat_jalan = $post['no_surat_jalan'];

            $ArrHeader = [
                'no_loading'       => $post['no_loading'],
                'no_so'            => $post['no_so'],
                'no_delivery'      => $post['no_delivery'],
                'pengiriman'       => $post['pengiriman'],
                'driver_name'      => $post['driver_name'],
                'delivery_address' => $post['delivery_address'],
                'delivery_date'    => date('Y-m-d', strtotime($post['delivery_date'])),
                'updated_by'       => $this->auth->user_id(),
                'updated_at'       => $tanggal_sekarang,
            ];
        } else {
            // MODE INSERT
            $Ym = date('ym');
            $SQL = "SELECT MAX(no_surat_jalan) as maxM FROM surat_jalan WHERE no_surat_jalan LIKE 'SJ/G/{$Ym}/%'";
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
            $no_surat_jalan = "SJ/G/{$Ym}/{$formatUrut}";

            $ArrHeader = [
                'no_surat_jalan'   => $no_surat_jalan,
                'no_loading'       => $post['no_loading'],
                'no_so'            => $post['no_so'],
                'no_delivery'      => $post['no_delivery'],
                'pengiriman'       => $post['pengiriman'],
                'driver_name'      => $post['driver_name'],
                'delivery_address' => $post['delivery_address'],
                'delivery_date'    => date('Y-m-d', strtotime($post['delivery_date'])),
                'created_by'       => $this->auth->user_id(),
                'created_at'       => $tanggal_sekarang,
            ];
        }

        // Prepare Detail
        $ArrDetail = [];
        foreach ($detail as $key => $value) {
            $id_product     = $value['id_product'];
            $id_so_det      = $value['id_so_det'];
            $id_spk_det     = $value['id_spk_det'];
            $qty            = $value['qty'];

            $ArrDetail[$key] = [
                'no_surat_jalan'  => $no_surat_jalan,
                'id_product'      => $id_product,
                'product'         => $value['product'],
                'qty'             => $qty,
                'weight'          => $value['weight'],
                'total_berat'     => $value['total_berat'],
                'id_so_det'       => $id_so_det,
                'id_spk_det'      => $id_spk_det,
            ];

            // Update ke SPK dan SO Detail
            $this->db->update('spk_delivery', ['status' => 'ON DELIVER', 'no_surat_jalan' => $no_surat_jalan], ['no_delivery' => $post['no_delivery']]);

            $this->db->set('qty_delivery', 'qty_delivery + ' . (int) $qty, FALSE);
            $this->db->set('status_kirim', '1');
            $this->db->set('tgl_delivery', date('Y-m-d H:i:s', strtotime($post['delivery_date'])));
            $this->db->where('id', $id_so_det);
            $this->db->update('sales_order_detail');
        }

        // Simpan ke DB
        $this->db->trans_start();

        if ($is_update) {
            $this->db->update('surat_jalan', $ArrHeader, ['id' => $id_sj]);
            $this->db->delete('surat_jalan_detail', ['id_sj' => $id_sj]);

            foreach ($ArrDetail as &$row) {
                $row['id_sj'] = $id_sj;
            }
            $this->db->insert_batch('surat_jalan_detail', $ArrDetail);
        } else {
            $this->db->insert('surat_jalan', $ArrHeader);
            $id_sj = $this->db->insert_id();

            foreach ($ArrDetail as &$row) {
                $row['no_surat_jalan']  = $no_surat_jalan;
                $row['id_sj']  = $id_sj;
            }
            $this->db->insert_batch('surat_jalan_detail', $ArrDetail);

            //SYAMSUDIN 16-09-2025 JURNAL

            $tgl_inv  = date('Y-m-d');
            $keterangan  = "Surat Jalan" . $no_surat_jalan;
            $type        = $no_surat_jalan;
            $reff        = $no_surat_jalan;
            $no_req      = $no_surat_jalan;
            $no_po       = $no_surat_jalan;
            $total       = round($this->input->post('debet[0]'));
            $jenis       = $this->input->post('jenis');
            $tipe_jurnal       = $this->input->post('tipe');
            $jenis_jurnal       = $this->input->post('jenis_jurnal');

            $total_po           = round($this->input->post('debet[0]'));
            $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


            $Bln             = substr($tgl_inv, 5, 2);
            $Thn             = substr($tgl_inv, 0, 4);


            $dataJVhead = array(
                'nomor'             => $Nomor_JV,
                'tgl'                 => $tgl_inv,
                'jml'                => $total,
                'koreksi_no'        => '-',
                'kdcab'                => '101',
                'jenis'                => 'JV',
                'keterangan'         => $keterangan,
                'bulan'                => $Bln,
                'tahun'                => $Thn,
                'user_id'            => $this->auth->user_id(),
                'memo'                => '',
                'tgl_jvkoreksi'        => $tgl_inv,
                'ho_valid'            => ''
            );

            $this->db->insert(DBACC . '.javh', $dataJVhead);

            for ($i = 0; $i < count($this->input->post('type')); $i++) {
                $tipe = $this->input->post('type')[$i];
                $perkiraan = $this->input->post('no_coa')[$i];
                $noreff = $no_po;

                $datadetail = array(
                    'tipe'            => $this->input->post('type')[$i],
                    'nomor'           => $Nomor_JV,
                    'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                    'no_perkiraan'    => $this->input->post('no_coa')[$i],
                    'keterangan'      =>  $keterangan,
                    'no_reff'        => $no_po,
                    'debet'          => round($this->input->post('debet')[$i]),
                    'kredit'         => round($this->input->post('kredit')[$i]),
                    'created_by'      => $this->auth->user_id(),
                    'created_on'      => date('Y-m-d H:i:s')
                );
                $this->db->insert(DBACC . '.jurnal', $datadetail);
            }

            $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
            $this->db->query($Qry_Update_Cabang_acc);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $res = ['status' => 0, 'pesan' => 'Gagal menyimpan data.'];
        } else {
            $this->db->trans_commit();
            $res = ['status' => 1, 'pesan' => 'Data berhasil disimpan.'];
            history(($is_update ? 'Update' : 'Create') . " Surat Jalan : " . $no_surat_jalan);
        }

        echo json_encode($res);
    }

    public function confirm_sj($id)
    {
        // Header SJ
        $sj = $this->db
            ->select('sj.*, so.nama_sales, ld.nopol, p.id_penawaran, c.name_customer')
            ->from('surat_jalan sj')
            ->join('loading_delivery ld', 'sj.no_loading = ld.no_loading', 'left')
            ->join('sales_order so', 'sj.no_so = so.no_so', 'left')
            ->join('penawaran p', 'so.id_penawaran = p.id_penawaran')
            ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
            ->where('sj.id', $id)
            ->get()
            ->row_array();

        if (!$sj) {
            show_404();
        }

        $sdd_sub = "
        (
            SELECT
                id_so_det,
                no_delivery,
                SUM(COALESCE(qty_so, 0))  AS qty_so,
                SUM(COALESCE(qty_spk, 0)) AS qty_spk
            FROM spk_delivery_detail
            GROUP BY id_so_det, no_delivery
        ) sdd
    ";


        $wh_sub = "
        (
            SELECT
                id_material,
                MAX(harga_beli) AS costbook,
                MAX(id_unit)    AS id_unit
            FROM warehouse_stock
            GROUP BY id_material
        ) wh
    ";

        $detail = $this->db
            ->select('
            d.*,
            s.code,
            COALESCE(sdd.qty_so, 0)  AS qty_so,
            COALESCE(sdd.qty_spk, 0) AS qty_spk,
            COALESCE(wh.costbook, 0) AS costbook
        ')
            ->from('surat_jalan_detail d')
            ->join('surat_jalan sj', 'sj.id = d.id_sj') // untuk akses sj.no_delivery di join sdd
            ->join($sdd_sub, 'sdd.id_so_det = d.id_so_det AND sdd.no_delivery = sj.no_delivery', 'left')
            ->join($wh_sub, 'wh.id_material = d.id_product', 'left')
            ->join('ms_satuan s', 's.id = wh.id_unit', 'left')
            ->where('d.id_sj', $id)
            ->get()
            ->result_array();

        $data = [
            'sj'     => $sj,
            'detail' => $detail,
        ];

        $this->template->page_icon('fa fa-check');
        $this->template->title('Confirm Delivery');
        $this->template->render('confirm', $data);
    }


    public function confirm()
    {
        $post = $this->input->post();
        $detail = $post['detail'];

        $id_sj = $post['id'];
        $tgl_diterima = $post['tgl_diterima'];
        $penerima = $post['penerima'];
        $no_surat_jalan = $post['no_surat_jalan'];
        $no_delivery = $post['no_delivery'];
        $sanitized_sj = str_replace(['/', '\\'], '_', $no_surat_jalan);

        $status = 'CONFIRM';
        $ArrUpdate = [
            'tgl_diterima' => $tgl_diterima,
            'penerima'     => $penerima,
            'updated_by'   => $this->auth->user_id(),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        // ✅ Upload file dokumen jika ada
        if (!empty($_FILES['file_dokumen']['name'])) {
            $config['upload_path']   = './assets/confirm_sj/';
            $config['allowed_types'] = '*';
            $config['max_size']      = 2048;
            $config['file_name']     = 'bukti_confirm_sj_gudang_' . $sanitized_sj;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file_dokumen')) {
                $res = ['status' => 0, 'pesan' => $this->upload->display_errors()];
                echo json_encode($res);
                return;
            } else {
                $uploadData = $this->upload->data();
                $ArrUpdate['file_dokumen'] = $uploadData['file_name'];
            }
        }

        $ArrDetail = [];
        $arr_kartu_stok = [];

        foreach ($detail as $key => $value) {
            $qty_delivery = (int) $value['qty_delivery'];
            $qty_terkirim = (int) $value['qty_terkirim'];
            $qty_retur    = (int) $value['qty_retur'];
            $qty_hilang   = (int) $value['qty_hilang'];
            $id_detail    = $value['id_detail'];
            $total        = $qty_terkirim + $qty_retur + $qty_hilang;
            $id_product   = $value['id_product'];
            $qty_lebih    = isset($value['qty_lebih']) ? $value['qty_lebih'] : 0;

            $data_detail = [
                'id'           => $id_detail,
                'id_product'   => $id_product,
                // 'id_so_det'    => $value['id_so_det'],
                'qty_terkirim' => $qty_terkirim,
                'qty_retur'    => $qty_retur,
                'qty_hilang'   => $qty_hilang,
                'qty_lebih'    => $qty_lebih,
            ];

            if ($qty_retur > 0 || $total !== $qty_delivery) {
                $status = 'RETUR';
            }

            if ($qty_hilang > 0 || $total !== $qty_delivery) {
                $status = 'HILANG';
            }

            if ($qty_retur > 0 || $qty_hilang > 0) {
                $data_detail['reason'] = $value['reason'];

                // Handle upload per barang retur
                if (!empty($_FILES['detail']['name'][$key]['file_bukti'])) {
                    $_FILES['file_temp']['name']     = $_FILES['detail']['name'][$key]['file_bukti'];
                    $_FILES['file_temp']['type']     = $_FILES['detail']['type'][$key]['file_bukti'];
                    $_FILES['file_temp']['tmp_name'] = $_FILES['detail']['tmp_name'][$key]['file_bukti'];
                    $_FILES['file_temp']['error']    = $_FILES['detail']['error'][$key]['file_bukti'];
                    $_FILES['file_temp']['size']     = $_FILES['detail']['size'][$key]['file_bukti'];

                    $config_retur['upload_path']   = './assets/confirm_sj/';
                    $config_retur['allowed_types'] = '*';
                    $config_retur['max_size']      = 2048;
                    $config_retur['file_name']     = 'retur_' . $sanitized_sj . '_' . $key;

                    $this->upload->initialize($config_retur);

                    if ($this->upload->do_upload('file_temp')) {
                        $upload_data = $this->upload->data();
                        $data_detail['file_bukti'] = $upload_data['file_name'];
                    } else {
                        echo json_encode([
                            'status' => 0,
                            'pesan'  => 'Upload file retur gagal: ' . $this->upload->display_errors()
                        ]);
                        return;
                    }
                }
            }

            $ArrDetail[] = $data_detail;

            $this->db->where([
                'no_delivery' => $no_delivery,
                'id_so_det'   => $value['id_so_det']
            ])->update('spk_delivery_detail', [
                'qty_delivery' => $qty_terkirim
            ]);

            $current = $this->db->select('qty_delivery, qty_order, qty_terkirim')
                ->get_where('sales_order_detail', ['id' => $value['id_so_det']])
                ->row();

            $new_qty_terkirim = $current->qty_terkirim + $qty_terkirim;
            if ($new_qty_terkirim <= $current->qty_delivery) {
                $this->db->set('qty_terkirim', 'qty_terkirim + ' . (int) $qty_terkirim, FALSE);
                $this->db->where('id', $value['id_so_det']);
                $this->db->update('sales_order_detail');
            }

            $stok = $this->db->get_where('warehouse_stock', [
                'code_lv4' => $id_product
            ])->row_array();

            if ($stok && $qty_terkirim > 0) {
                $arr_kartu_stok[] = [
                    'no_transaksi'      => $no_surat_jalan,
                    'transaksi'         => "Delivery",
                    'tgl_transaksi'     => $tgl_diterima,
                    'code_lv4'          => $id_product,
                    'nm_product'        => $stok['nm_product'],
                    'qty'               => floatval($stok['qty_stock']),
                    'qty_book'          => floatval($stok['qty_booking']),
                    'qty_free'          => floatval($stok['qty_free']),
                    'qty_transaksi'     => $qty_terkirim * -1,
                    'qty_akhir'         => floatval($stok['qty_stock']) - $qty_terkirim,
                    'qty_book_akhir'    => floatval($stok['qty_booking']),
                    'qty_free_akhir'    => floatval($stok['qty_free']),
                    'harga_stok'        => floatval($stok['harga_beli'])
                ];
            }

            if ($qty_lebih > 0) {
                $this->db->set('qty_stock', 'qty_stock + ' . $qty_lebih, FALSE);
                $this->db->where('code_lv4', $id_product);
                $this->db->update('warehouse_stock');

                // Simpan ke kartu_stok
                $arr_kartu_stok[] = [
                    'no_transaksi'      => $no_surat_jalan,
                    'transaksi'         => "Retur Lebih",
                    'tgl_transaksi'     => $tgl_diterima,
                    'code_lv4'          => $id_product,
                    'nm_product'        => $stok ? $stok['nm_product'] : '-',
                    'qty'               => $stok ? floatval($stok['qty_stock']) : 0,
                    'qty_book'          => $stok ? floatval($stok['qty_booking']) : 0,
                    'qty_free'          => $stok ? floatval($stok['qty_free']) : 0,
                    'qty_transaksi'     => $qty_lebih,
                    'qty_akhir'         => $stok ? floatval($stok['qty_stock']) + $qty_lebih : $qty_lebih,
                    'qty_book_akhir'    => $stok ? floatval($stok['qty_booking']) : 0,
                    'qty_free_akhir'    => $stok ? floatval($stok['qty_free']) : 0,
                    'harga_stok'        => $stok ? floatval($stok['harga_beli']) : 0
                ];
            }
        }

        $ArrUpdate['status'] = $status;

        $this->db->trans_start();

        $this->db->update('surat_jalan', $ArrUpdate, ['id' => $id_sj]);

        foreach ($ArrDetail as $row) {
            $this->db->update('surat_jalan_detail', [
                'qty_terkirim' => $row['qty_terkirim'],
                'qty_retur'    => $row['qty_retur'],
                'qty_hilang'   => $row['qty_hilang'],
                'qty_lebih'    => $row['qty_lebih'],
                'reason'       => isset($row['reason']) ? $row['reason'] : null,
                'file_bukti'   => isset($row['file_bukti']) ? $row['file_bukti'] : null,
            ], ['id' => $row['id']]);
        }

        if (!empty($arr_kartu_stok)) {
            $this->db->insert_batch('kartu_stok', $arr_kartu_stok);
        }

        //SYAMSUDIN 16-09-2025 JURNAL

        $tgl_inv  = date('Y-m-d');
        $keterangan  = "Confirm Surat Jalan" . $no_surat_jalan;
        $type        = $no_surat_jalan;
        $reff        = $no_surat_jalan;
        $no_req      = $no_surat_jalan;
        $no_po       = $no_surat_jalan;
        $total       = round($this->input->post('debet[0]'));
        $jenis       = $this->input->post('jenis');
        $tipe_jurnal       = $this->input->post('tipe');
        $jenis_jurnal       = $this->input->post('jenis_jurnal');

        $total_po           = round($this->input->post('debet[0]'));
        $Nomor_JV                = $this->Jurnal_model->get_Nomor_Jurnal_Sales('101', $tgl_inv);


        $Bln             = substr($tgl_inv, 5, 2);
        $Thn             = substr($tgl_inv, 0, 4);


        $dataJVhead = array(
            'nomor'             => $Nomor_JV,
            'tgl'                 => $tgl_inv,
            'jml'                => $total,
            'koreksi_no'        => '-',
            'kdcab'                => '101',
            'jenis'                => 'JV',
            'keterangan'         => $keterangan,
            'bulan'                => $Bln,
            'tahun'                => $Thn,
            'user_id'            => $this->auth->user_id(),
            'memo'                => '',
            'tgl_jvkoreksi'        => $tgl_inv,
            'ho_valid'            => ''
        );

        $this->db->insert(DBACC . '.javh', $dataJVhead);

        for ($i = 0; $i < count($this->input->post('type')); $i++) {
            $tipe = $this->input->post('type')[$i];
            $perkiraan = $this->input->post('no_coa')[$i];
            $noreff = $no_po;

            $datadetail = array(
                'tipe'            => $this->input->post('type')[$i],
                'nomor'           => $Nomor_JV,
                'tanggal'         => $this->input->post('tgl_jurnal')[$i],
                'no_perkiraan'    => $this->input->post('no_coa')[$i],
                'keterangan'      =>  $keterangan,
                'no_reff'        => $no_po,
                'debet'          => round($this->input->post('debet')[$i]),
                'kredit'         => round($this->input->post('kredit')[$i]),
                'created_by'      => $this->auth->user_id(),
                'created_on'      => date('Y-m-d H:i:s')
            );
            $this->db->insert(DBACC . '.jurnal', $datadetail);
        }

        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nomorJC=nomorJC + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $res = ['status' => 0, 'pesan' => 'Gagal menyimpan konfirmasi.'];
        } else {
            $this->db->trans_commit();
            $res = ['status' => 1, 'pesan' => 'Konfirmasi berhasil disimpan.'];
            history("Confirm Surat Jalan : ID #{$id_sj} Status: {$status}");
        }

        echo json_encode($res);
    }



    public function print_sj($id)
    {
        // Ambil data header surat jalan + join ke sales_order dan master_customers
        $sj = $this->db
            ->select('sj.*, so.nama_sales, ld.nopol, c.name_customer')
            ->from('surat_jalan sj')
            ->join('loading_delivery ld', 'sj.no_loading = ld.no_loading', 'left')
            ->join('sales_order so', 'sj.no_so = so.no_so', 'left')
            ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
            ->where('sj.id', $id)
            ->get()
            ->row_array();

        if (!$sj) {
            show_404();
        }


        // Ambil data detail + join ke inventory dan satuan
        $detail = $this->db
            ->select('
            d.*,
            s.code,
        ')
            ->from('surat_jalan_detail d')
            ->join('new_inventory_4 inv', 'd.id_product = inv.code_lv4', 'left')
            ->join('ms_satuan s', 'inv.id_unit = s.id', 'left')
            ->where('d.id_sj', $id)
            ->get()
            ->result_array();

        $data = [
            'sj' => $sj,
            'detail' => $detail,
        ];

        $this->load->view('print_sj', $data);
    }
}

// Trash

//  public function edit($id)
//     {
//         $sj = $this->db
//             ->from('surat_jalan')
//             ->where('id', $id)
//             ->get()
//             ->row_array();

//         if (!$sj) show_404();

//         $detail = $this->db
//             ->from('surat_jalan_detail')
//             ->where('id_sj', $id)
//             ->get()
//             ->result_array();

//         $loading = $this->db->get('loading_delivery')->result_array();

//         // Ambil daftar SO berdasarkan no_loading
//         $sales_order = $this->db
//             ->where('no_loading', $sj['no_loading'])
//             ->group_by('no_so')
//             ->get('loading_delivery_detail')
//             ->result_array();

//         // Ambil daftar SPK berdasarkan no_so
//         $spk_list = $this->db
//             ->where('no_so', $sj['no_so'])
//             ->group_by('no_delivery')
//             ->get('spk_delivery')
//             ->result_array();

//         $data = [
//             'sj'          => $sj,
//             'detail'      => $detail,
//             'loading'     => $loading,
//             'sales_order' => $sales_order,
//             'spk_list'    => $spk_list
//         ];

//         $this->template->title('Add Surat Jalan');
//         $this->template->page_icon('fa fa-envelope');
//         $this->template->render('form', $data);
//     }

//     public function get_so()
//     {
//         $no_loading = $this->input->get('no_loading', TRUE);

//         $data = $this->db
//             ->select('so.no_so, c.*')
//             ->from('loading_delivery_detail ld')
//             ->join('sales_order so', 'ld.no_so = so.no_so', 'left')
//             ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
//             ->where('ld.no_loading', $no_loading)
//             ->group_by('so.no_so')
//             ->get()
//             ->result();

//         echo "<option value=''>-- Pilih --</option>";
//         foreach ($data as $so) {
//             echo "<option data-alamat='$so->address_office' value='$so->no_so'>$so->no_so - $so->name_customer</option>";
//         }
//     }

//  public function get_detail()
//     {
//         $no_delivery = $this->input->get('no_delivery', TRUE);

//         $data = $this->db
//             ->select('ldd.*, sod.id AS id_so_det') // ambil kolom id dari sales_order_detail
//             ->from('loading_delivery_detail ldd')
//             ->join('sales_order_detail sod', 'ldd.no_so = sod.no_so AND ldd.id_product = sod.id_product', 'left')
//             ->where('ldd.no_delivery', $no_delivery)
//             ->get()
//             ->result();

//         $html = '';
//         $no = 1;

//         foreach ($data as $i => $row) {
//             $html .= "<tr>
//                     <td class='text-center'>{$no}</td>
//                     <td>{$row->id_product}</td>
//                     <td>{$row->product}</td>
//                     <td class='text-center'>{$row->qty_spk}</td>

//                     <!-- Hidden fields for POST -->
//                     <input type='hidden' name='detail[{$i}][id_product]' value='{$row->id_product}'>
//                     <input type='hidden' name='detail[{$i}][product]' value=\"{$row->product}\">
//                     <input type='hidden' name='detail[{$i}][qty]' value='{$row->qty_spk}'>
//                     <input type='hidden' name='detail[{$i}][id_so_det]' value='{$row->id_so_det}'>
//                   </tr>";
//             $no++;
//         }

//         echo $html;
//     }