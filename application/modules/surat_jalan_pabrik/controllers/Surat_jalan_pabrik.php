<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Surat_jalan_pabrik extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Surat_Jalan_Pabrik.View';
    protected $addPermission    = 'Surat_Jalan_Pabrik.Add';
    protected $managePermission = 'Surat_Jalan_Pabrik.Manage';
    protected $deletePermission = 'Surat_Jalan_Pabrik.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Surat_jalan_pabrik/surat_jalan_pabrik_model',
            'jurnal_nomor/Jurnal_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Surat Jalan Pabrik');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('index');
    }

    public function data_side_surat_jalan_pabrik()
    {
        $this->surat_jalan_pabrik_model->data_side_surat_jalan_pabrik();
    }

    public function add()
    {
        $spk_delivery = $this->db
            ->select('sd.no_delivery, c.name_customer AS customer, sd.tanggal_spk, sd.delivery_address')
            ->from('spk_delivery sd')
            ->join('sales_order so', 'sd.no_so = so.no_so', 'left')
            ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
            ->where('sd.pengiriman', 'Pabrik')
            ->where("sd.no_delivery NOT IN (SELECT no_delivery FROM surat_jalan)")
            ->get()
            ->result_array();

        $data = [
            'spk_delivery' => $spk_delivery,
        ];

        $this->template->title('Add Surat Jalan Pabrik');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('form', $data);
    }

    public function get_spk_detail()
    {
        $no_delivery = $this->input->get('no_delivery', TRUE);

        // Ambil data header SPK Delivery
        $header = $this->db->get_where('spk_delivery', ['no_delivery' => $no_delivery])->row_array();

        // Ambil detail yang belum pernah dimuat ke surat jalan
        $detail = $this->db
            ->select('
            sdd.*,
            so.no_so,
            sod.id AS id_so_det,
            c.name_customer AS customer,
            c.address_office AS alamat,
            p.nama AS product,
            p.weight,
            (sdd.qty_spk * p.weight) AS total_berat
        ')
            ->from('spk_delivery_detail sdd')
            ->join('sales_order so', 'sdd.no_so = so.no_so', 'left')
            ->join('sales_order_detail sod', 'sod.no_so = sdd.no_so AND sod.id_product = sdd.id_product', 'left')
            ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
            ->join('new_inventory_4 p', 'sdd.id_product = p.code_lv4', 'left')
            ->where('sdd.no_delivery', $no_delivery)
            ->where("CONCAT(sdd.no_so, '|', sdd.no_delivery) NOT IN (
                    SELECT CONCAT(no_so, '|', no_delivery)
                    FROM surat_jalan
                    WHERE no_delivery = '$no_delivery'
                )")
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
                // 'no_loading'       => $post['no_loading'],
                'no_so'            => $post['no_so'],
                'pengiriman'       => $post['pengiriman'],
                'no_delivery'      => $post['no_delivery'],
                'driver_name'      => $post['driver_name'],
                'delivery_address' => $post['delivery_address'],
                'delivery_date'    => date('Y-m-d', strtotime($post['delivery_date'])),
                'updated_by'       => $this->auth->user_id(),
                'updated_at'       => $tanggal_sekarang,
            ];
        } else {
            // MODE INSERT
            $Ym = date('ym');
            $prefix = "SJ/P/{$Ym}/";

            $SQL = "SELECT MAX(no_surat_jalan) AS maxM
                    FROM surat_jalan
                    WHERE no_surat_jalan LIKE ?";
            $result = $this->db->query($SQL, [$prefix . '%'])->row_array();

            $angkaUrut = $result && $result['maxM'] ? $result['maxM'] : null;
            $urutan = 0;
            if ($angkaUrut) {
                $parts = explode('/', $angkaUrut);
                $urutan = isset($parts[3]) ? (int)$parts[3] : 0;
            }
            $urutan++;
            $no_surat_jalan = $prefix . sprintf('%04d', $urutan);

            $ArrHeader = [
                'no_surat_jalan'   => $no_surat_jalan,
                // 'no_loading'       => $post['no_loading'],
                'no_so'            => $post['no_so'],
                'pengiriman'       => $post['pengiriman'],
                'no_delivery'      => $post['no_delivery'],
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
            $id_product = $value['id_product'];
            $id_so_det  = $value['id_so_det'];
            $qty        = $value['qty'];

            $ArrDetail[$key] = [
                'no_surat_jalan'  => $no_surat_jalan,
                'id_product'      => $id_product,
                'product'         => $value['product'],
                'qty'             => $qty,
                'weight'          => $value['weight'],
                'total_berat'     => $value['weight'],
                'id_so_det'       => $id_so_det,
            ];

            // Update ke SPK dan SO Detail
            $this->db->update('spk_delivery', ['status' => 'ON DELIVER'], ['no_delivery' => $post['no_delivery']]);
            $this->db->update('sales_order_detail', [
                'qty_delivery' => $qty,
                'status_kirim' => '1',
                'tgl_delivery' => date('Y-m-d H:i:s', strtotime($post['delivery_date']))
            ], ['id' => $id_so_det]);
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
