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
            'Surat_jalan/surat_jalan_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->title('Surat Jalan');
        $this->template->page_icon('fa fa-envelope');
        $this->template->render('index');
    }

    public function data_side_surat_jalan()
    {
        $this->surat_jalan_model->data_side_surat_jalan();
    }

    public function add()
    {
        $data = [
            'loading' => $this->db->get('loading_delivery')->result_array(),
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
            ld.*,
            so.no_so,
            sd.pengiriman,
            sod.id AS id_so_det,
            c.name_customer AS customer,
            c.address_office AS alamat,
            p.nama AS product,
            p.weight,
            (ld.qty_spk * p.weight) AS total_berat
        ')
            ->from('loading_delivery_detail ld')
            ->join('spk_delivery sd', 'ld.no_delivery = sd.no_delivery', 'left')
            ->join('sales_order so', 'ld.no_so = so.no_so', 'left')
            ->join('sales_order_detail sod', 'sod.no_so = ld.no_so AND sod.id_product = ld.id_product', 'left')
            ->join('master_customers c', 'so.id_customer = c.id_customer', 'left')
            ->join('new_inventory_4 p', 'ld.id_product = p.code_lv4', 'left')
            ->where('ld.no_loading', $no_loading)
            ->where("CONCAT(ld.no_so, '|', ld.no_delivery) NOT IN (
                        SELECT CONCAT(no_so, '|', no_delivery)
                        FROM surat_jalan
                        WHERE no_loading = '$no_loading'
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

        $detail = $this->db
            ->select('
            d.*,
            s.code,
            sdd.qty_so, sdd.qty_spk
        ')
            ->from('surat_jalan_detail d')
            ->join('spk_delivery_detail sdd', 'd.id_so_det = sdd.id_so_det', 'left')
            ->join('new_inventory_4 inv', 'd.id_product = inv.code_lv4', 'left')
            ->join('ms_satuan s', 'inv.id_unit = s.id', 'left')
            ->where('d.id_sj', $id)
            ->group_by('d.id')
            ->get()
            ->result_array();

        $data = [
            'sj' => $sj,
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
        $sanitized_sj = str_replace(['/', '\\'], '_', $no_surat_jalan);

        // Inisialisasi
        $status = 'CONFIRM';

        $ArrUpdate = [
            'tgl_diterima' => $tgl_diterima,
            'penerima'     => $penerima,
            'updated_by'   => $this->auth->user_id(),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        //untuk dokumen
        if (!empty($_FILES['file_dokumen']['name'])) {
            $config['upload_path']   = './assets/confirm_sj/';
            $config['allowed_types'] = '*';
            $config['max_size']      = 2048;
            $config['file_name']     = 'bukti_confirm_sj_' . $sanitized_sj;

            // $this->load->library('upload', $config);
            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file_dokumen')) {
                $res = ['status' => 0, 'pesan' => $this->upload->display_errors()];
                echo json_encode($res);
                return;
            } else {
                $uploadData = $this->upload->data();
                $filename = $uploadData['file_name'];

                // Tambahkan ke $ArrUpdate
                $ArrUpdate['file_dokumen'] = $filename;
            }
        }

        // Logika penentuan status berdasarkan detail
        $ArrDetail = [];
        foreach ($detail as $key => $value) {
            $qty_delivery = (int) $value['qty_delivery'];
            $qty_terkirim = (int) $value['qty_terkirim'];
            $qty_retur    = (int) $value['qty_retur'];
            $qty_hilang   = (int) $value['qty_hilang'];
            $id_detail    = $value['id_detail'];
            $total        = $qty_terkirim + $qty_retur + $qty_hilang;

            $ArrDetail[$key] = [
                'id_product'    => $value['id_product'],
                'id_so_det'     => $value['id_so_det'],
                'qty_terkirim'  => $qty_terkirim,
                'qty_retur'     => $qty_retur,
                'qty_hilang'    => $qty_hilang
            ];

            if ($qty_retur > 0 || $total !== $qty_delivery) {
                $status = 'RETUR';
            }
        }

        $ArrUpdate['status'] = $status;

        // Simpan ke database
        $this->db->trans_start();

        $this->db->update('surat_jalan', $ArrUpdate, ['id' => $id_sj]);

        foreach ($ArrDetail as $row) {
            $this->db->update('surat_jalan_detail', [
                'qty_terkirim' => $row['qty_terkirim'],
                'qty_retur'    => $row['qty_retur'],
                'qty_hilang'   => $row['qty_hilang'],
            ], [
                'id'      => $id_detail,
            ]);
        }
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