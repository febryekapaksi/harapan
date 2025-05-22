<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Penawaran extends Admin_Controller
{
    //Permission
    protected $viewPermission       = 'Penawaran.View';
    protected $addPermission        = 'Penawaran.Add';
    protected $managePermission     = 'Penawaran.Manage';
    protected $deletePermission     = 'Penawaran.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Penawaran/penawaran_model',
            'Price_list/price_list_model',
            'Product_costing/product_costing_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->render('index');
    }

    public function add()
    {
        $data['customers'] = $this->db
            ->where('deleted', 0)
            ->where('deleted_by', null)
            ->get('master_customers')
            ->result_array();

        $data['products'] = $this->db->get_where('product_costing', ['status' => 'A'])->result_array();
        $payment_terms = $this->db
            ->where('group_by', 'top invoice')
            ->where('sts', 'Y')
            ->order_by('id', 'asc')
            ->get('list_help')
            ->result_array();
        $data['payment_terms'] = $payment_terms;
        $data['mode'] = "add";

        $this->template->render('form', $data);
    }

    public function edit($id_penawaran)
    {
        // Cek apakah data ada
        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            show_404(); // Jika tidak ada, tampilkan error 404
        }

        // Ambil data detail produk terkait
        $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

        // Data customer dan produk (jika diperlukan untuk select)
        $data['customers'] = $this->db->get('master_customers')->result_array();
        $data['products'] = $this->db->get('product_costing')->result_array();
        $data['payment_terms'] = $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array();

        // Kirim data ke view
        $data['penawaran'] = $penawaran;
        $data['penawaran_detail'] = $penawaran_detail;
        $data['mode'] = "edit";

        // View form edit
        $this->template->render('form', $data);
    }

    public function save()
    {
        $data = $this->input->post();
        $id = $data['id_penawaran'];

        $is_update = !empty($id);
        $id_penawaran = $is_update ? $id : $this->penawaran_model->generate_id();

        $header = [
            'id_penawaran'              => $id_penawaran,
            'id_customer'               => $data['id_customer'],
            'price_mode'                => $data['price_mode'],
            'sales'                     => $data['sales'],
            'email'                     => $data['email'],
            'payment_term'              => $data['payment_term'],
            'quotation_date'            => date('Y-m-d H:i:s', strtotime($data['quotation_date'])),
            'tipe_bayar'                => $data['tipe_bayar'],
            'freight'                   => str_replace(',', '', $data['freight']),
            'total_penawaran'           => str_replace(',', '', $data['total_penawaran']),
            'total_price_list'          => str_replace(',', '', $data['total_price_list']),
            'total_diskon_persen'       => $data['total_diskon_persen'],
            'total_harga_freight'       => str_replace(',', '', $data['total_harga_freight']),
            'total_harga_freight_exppn' => str_replace(',', '', $data['total_harga_freight_exppn']),
            'dpp'                       => str_replace(',', '', $data['dpp']),
            'ppn'                       => str_replace(',', '', $data['ppn']),
            'grand_total'               => str_replace(',', '', $data['grand_total']),
        ];

        // Buat nentuin status dan level approval
        $level_approval = 'M';
        $status = 'WA';
        $surplus_only = true;

        if (isset($_POST['product']) && is_array($_POST['product'])) {
            foreach ($_POST['product'] as $pro) {
                $diskon = floatval($pro['diskon']);

                if ($diskon < -2) {
                    // Diskon minus terlalu besar, butuh approval direksi
                    $level_approval = 'D';
                    $status = 'WA';
                    $surplus_only = false;
                    break; // langsung berhenti
                }

                if ($diskon >= -2 && $diskon <= 0) {
                    // Masih dalam range toleransi → butuh approval manager
                    $surplus_only = false;
                }
            }

            // Kalau semua diskon > 0% (surplus semua), langsung approve
            if ($surplus_only) {
                $status = 'A'; // auto approve
            }
        }
        $header['level_approval'] = $level_approval;
        $header['status'] = $status;


        if ($is_update) {
            $header['modified_by'] = $this->auth->user_id();
            $header['modified_at'] = date('Y-m-d H:i:s');
        } else {
            $header['created_by'] = $this->auth->user_id();
            $header['created_at'] = date('Y-m-d H:i:s');
        }

        $this->db->trans_start();
        if ($is_update) {
            $this->db->where('id_penawaran', $id);
            $this->db->update('penawaran', $header);
            $id_penawaran = $id;
        } else {
            $this->db->insert('penawaran', $header);
            $id_penawaran = $header['id_penawaran']; // pakai ID yang baru dibuat
        }
        // Hapus dan simpan ulang product
        if ($is_update) {
            $this->db->delete('penawaran_detail', ['id_penawaran' => $id_penawaran]);
        }
        if (isset($_POST['product']) && is_array($_POST['product'])) {
            $product_data = [];
            foreach ($_POST['product'] as $pro) {
                $product_data[] = [
                    'id_penawaran'      => $id_penawaran,
                    'id_product'        => $pro['id_product'],
                    'product_name'      => $pro['product_name'],
                    'qty'               => $pro['qty'],
                    'price_list'        => str_replace(',', '', $pro['price_list']),
                    'harga_penawaran'   => str_replace(',', '', $pro['harga_penawaran']),
                    'diskon'            => $pro['diskon'],
                    'total'             => str_replace(',', '', $pro['total']),
                    'total_pl'          => str_replace(',', '', $pro['total_pl']),
                ];
            }
            if (!empty($product_data)) {
                $this->db->insert_batch('penawaran_detail', $product_data);
            }
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status    = array(
                'pesan'        => 'Gagal Save. Try Again Later ...',
                'status'    => 0
            );
        } else {
            $this->db->trans_commit();
            $status    = array(
                'pesan'        => 'Success Save. Thanks ...',
                'status'    => 1
            );
        }

        echo json_encode($status);
    }

    // Bagian Print out
    public function print_penawaran($id_penawaran)
    {
        $this->template->page_icon('fa fa-list');

        // Ambil data penawaran utama dari tabel 'penawaran' + join 'master_customers'
        $get_penawaran = $this->db
            ->select('p.*, c.*, 
                    e1.nm_karyawan AS created_by,
                    e2.nm_karyawan AS approved_by_manager,
                    e3.nm_karyawan AS approved_by_direksi')
            ->from('penawaran p')
            ->join('master_customers c', 'p.id_customer = c.id_customer', 'left')
            ->join('employee e1', 'e1.id = p.created_by', 'left')
            ->join('employee e2', 'e2.id = p.approved_by_manager', 'left')
            ->join('employee e3', 'e3.id = p.approved_by_direksi', 'left')
            ->where('p.id_penawaran', $id_penawaran)
            ->get()
            ->row();

        // Ambil detail item penawaran (tabel penawaran_detail dan join terkait bisa disesuaikan)
        $get_penawaran_detail = $this->db->select('d.*')
            ->from('penawaran_detail d')
            ->where('d.id_penawaran', $id_penawaran)
            ->order_by('d.id', 'ASC')
            ->get()
            ->result();

        // Bangun data yang akan dikirim ke view
        $data = [
            'data_penawaran' => $get_penawaran,
            'data_penawaran_detail' => $get_penawaran_detail,
        ];

        // Kirim ke view
        $this->load->view('print_penawaran', ['results' => $data]);
    }


    // Bagian Approval 

    public function approval_manager()
    {
        $this->template->render('list_approval_manager');
    }

    public function approve_manager($id_penawaran)
    {
        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            show_404();
        }

        $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

        $data['customers'] = $this->db->get('master_customers')->result_array();
        $data['products'] = $this->db->get('product_costing')->result_array();
        $data['payment_terms'] = $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array();

        // Kirim data ke view
        $data['penawaran'] = $penawaran;
        $data['penawaran_detail'] = $penawaran_detail;
        $data['mode'] = 'approval_manager';

        // View form edit
        $this->template->render('form', $data);
    }

    public function save_approval_manager()
    {
        $post = $this->input->post();
        $id_penawaran = $post['id_penawaran'];

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            echo json_encode(['status' => 0, 'pesan' => 'Data penawaran tidak ditemukan']);
            return;
        }

        // Siapkan data header update
        $update = [
            'approved_by_manager' => $this->auth->user_id(),
            'approved_at_manager' => date('Y-m-d H:i:s')
        ];

        // Cek apakah level approval butuh direksi
        if ($penawaran['level_approval'] == 'D') {
            $update['status'] = 'WA'; // Tunggu approval Direksi
        } else {
            $update['status'] = 'A'; // Final approval dari Manager
        }

        // Simpan update ke penawaran
        $this->db->where('id_penawaran', $id_penawaran);
        $this->db->update('penawaran', $update);

        // Proses revisi data produk (penawaran_detail)
        if (isset($post['product']) && is_array($post['product'])) {
            $product_data = [];

            foreach ($post['product'] as $pro) {
                $product_data[] = [
                    'id_penawaran'      => $id_penawaran,
                    'id_product'        => $pro['id_product'],
                    'product_name'      => $pro['product_name'],
                    'qty'               => (int) $pro['qty'],
                    'price_list'        => str_replace(',', '', $pro['price_list']),
                    'harga_penawaran'   => str_replace(',', '', $pro['harga_penawaran']),
                    'diskon'            => $pro['diskon'],
                    'total'             => str_replace(',', '', $pro['total']),
                ];
            }

            if (!empty($product_data)) {
                $this->db->where('id_penawaran', $id_penawaran)->delete('penawaran_detail');

                $this->db->insert_batch('penawaran_detail', $product_data);
            }
        }

        echo json_encode([
            'status' => 1,
            'pesan' => 'Penawaran berhasil diapprove oleh Manager.'
        ]);
    }



    public function approval_direksi()
    {
        $this->template->render('list_approval_direksi');
    }

    public function approve_direksi($id_penawaran)
    {
        // Cek apakah data ada
        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            show_404(); // Jika tidak ada, tampilkan error 404
        }

        // Ambil data detail produk terkait
        $penawaran_detail = $this->db->get_where('penawaran_detail', ['id_penawaran' => $id_penawaran])->result_array();

        // Data customer dan produk (jika diperlukan untuk select)
        $data['customers'] = $this->db->get('master_customers')->result_array();
        $data['products'] = $this->db->get('product_costing')->result_array();
        $data['payment_terms'] = $this->db->where('group_by', 'top invoice')->where('sts', 'Y')->get('list_help')->result_array();

        // Kirim data ke view
        $data['penawaran'] = $penawaran;
        $data['penawaran_detail'] = $penawaran_detail;
        $data['mode'] = 'approval_direksi';

        // View form edit
        $this->template->render('form', $data);
    }

    public function save_approval_direksi()
    {
        $post = $this->input->post();
        $id_penawaran = $post['id_penawaran'];

        if (empty($id_penawaran)) {
            echo json_encode(['status' => 0, 'pesan' => 'ID penawaran tidak ditemukan']);
            return;
        }

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id_penawaran])->row_array();

        if (!$penawaran) {
            echo json_encode(['status' => 0, 'pesan' => 'Data penawaran tidak ditemukan']);
            return;
        }

        $this->db->where('id_penawaran', $id_penawaran);
        $this->db->update('penawaran', [
            'status' => 'A', // FINAL Approved
            'approved_by_direksi' => $this->auth->user_id(),
            'approved_at_direksi' => date('Y-m-d H:i:s')
        ]);

        echo json_encode([
            'status' => 1,
            'pesan' => 'Approval direksi berhasil diproses.'
        ]);
    }

    // reject 
    public function reject($id = null)
    {
        if (!$id) {
            echo json_encode(['save' => 0, 'message' => 'ID tidak ditemukan']);
            return;
        }

        $penawaran = $this->db->get_where('penawaran', ['id_penawaran' => $id])->row();
        if (!$penawaran) {
            echo json_encode(['save' => 0, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $reason = $this->input->post('reason');
        if (!$reason) {
            echo json_encode(['save' => 0, 'message' => 'Alasan harus diisi']);
            return;
        }

        $data = [
            'status' => "R",
            'reject_reason' => $reason,
            'modified_by' => $this->auth->user_id(),
            'modified_at' => date('Y-m-d H:i:s')
        ];

        if ($penawaran->level_approval == "D" && $penawaran->approved_by_manager !== null) {
            $data['status'] = "WA";
            $data['approved_by_manager'] = null;
            $data['approved_at_manager'] = null;
        }

        $this->db->where('id_penawaran', $id);
        $update = $this->db->update('penawaran', $data);

        if ($update) {
            echo json_encode(['save' => 1]);
        } else {
            echo json_encode(['save' => 0, 'message' => 'Gagal menyimpan alasan penolakan']);
        }
    }


    // FUNGSI BUAT AJAX SERVERSIDE
    public function pilih_harga_ajax()
    {
        $kategori_toko = $this->input->post('kategori_toko');
        $tipe_bayar = $this->input->post('tipe_bayar');
        $id_product = $this->input->post('id_product');

        // Ambil dari tabel kalkulasi
        $row = $this->db->get_where('master_kalkulasi_price_list', [
            'id_product' => $id_product,
            'toko' => $kategori_toko
        ])->row_array();

        if (!$row) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'message' => 'Harga tidak ditemukan untuk toko yang dipilih.']));
        }

        $harga = ($tipe_bayar === 'cash') ? $row['cash'] : $row['tempo'];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'error' => false,
                'harga' => intval($harga)
            ]));
    }

    public function get_nama_sales()
    {
        $id_karyawan = $this->input->post('id_karyawan');

        $karyawan = $this->db->get_where('employee', ['id' => $id_karyawan])->row_array();

        if ($karyawan) {
            echo json_encode([
                'error' => false,
                'nama_sales' => ucfirst($karyawan['nm_karyawan'])
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Sales tidak ditemukan'
            ]);
        }
    }

    public function get_free_stok()
    {
        $code_lv4 = $this->input->post('code_lv4');

        $stock = $this->db->get_where('warehouse_stock', ['code_lv4' => $code_lv4])->row_array();

        if ($stock) {
            echo json_encode([
                'error' => false,
                'qty_free' => number_format($stock['qty_free'])
            ]);
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Free Stok tidak ditemukan'
            ]);
        }
    }

    public function data_side_penawaran()
    {
        $this->penawaran_model->get_json_penawaran();
    }

    public function data_side_approval_manager()
    {
        $this->penawaran_model->get_json_approval_manager();
    }

    public function data_side_approval_direksi()
    {
        $this->penawaran_model->get_json_approval_direksi();
    }

    // BUAT TEST TEST
    public function getHargaPenawaran($id_product, $nama_toko, $tipe_bayar)
    {
        // Ambil produk
        $product = $this->db->get_where('product_costing', ['id' => $id_product])->row_array();

        if (!$product) {
            return [
                'error' => true,
                'message' => 'Produk tidak ditemukan.'
            ];
        }

        $harga_awal = $product['propose_price'];

        // Ambil daftar toko & urutkan berdasarkan urutan kalkulasi
        $tokoList = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();

        if (empty($tokoList)) {
            return [
                'error' => true,
                'message' => 'Data persentase toko kosong.'
            ];
        }

        // Inisialisasi
        $current_cash = $harga_awal;
        $current_tempo = 0;
        $harga_terpilih = null;

        // Hitung harga berantai dan ambil yang cocok
        foreach ($tokoList as $index => $toko) {
            $cash_percent = floatval($toko['cash']) / 100;
            $tempo_percent = floatval($toko['tempo']) / 100;

            // Jika bukan toko pertama, cash dihitung dari tempo sebelumnya
            if ($index > 0) {
                $current_cash = $current_tempo + ($current_tempo * $cash_percent);
            }

            $current_tempo = $current_cash + ($current_cash * $tempo_percent);

            if (strtolower($toko['nama']) === strtolower($nama_toko)) {
                $harga_terpilih = ($tipe_bayar === 'cash') ? $current_cash : $current_tempo;
                break;
            }
        }

        if ($harga_terpilih === null) {
            return [
                'error' => true,
                'message' => 'Toko tidak ditemukan dalam skema kalkulasi.'
            ];
        }

        return [
            'error' => false,
            'id_product' => $id_product,
            'nama_produk' => $product['product_name'],
            'toko' => $nama_toko,
            'tipe_bayar' => $tipe_bayar,
            'harga' => $harga_terpilih,
        ];
    }

    public function test()
    {
        // $data = $this->getHargaPenawaran('PC2500001', 'Toko 2', 'tempo');
        $data = $this->getHargaSemuaToko('PC2500001');

        echo '<pre>';
        print_r($data);
        echo '</pre>';
        die();
    }

    public function getHargaSemuaToko($id_product)
    {
        // Ambil produk
        $product = $this->db->get_where('product_costing', ['id' => $id_product])->row_array();

        if (!$product) {
            return [
                'error' => true,
                'message' => 'Produk tidak ditemukan.'
            ];
        }

        $harga_awal = $product['propose_price'];

        // Ambil data toko
        $tokoList = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();

        if (empty($tokoList)) {
            return [
                'error' => true,
                'message' => 'Data master persentase kosong.'
            ];
        }

        // Inisialisasi
        $current_cash = $harga_awal;
        $current_tempo = 0;
        $result = [];

        foreach ($tokoList as $index => $toko) {
            $cash_percent = floatval($toko['cash']) / 100;
            $tempo_percent = floatval($toko['tempo']) / 100;

            if ($index > 0) {
                $current_cash = $current_tempo + ($current_tempo * $cash_percent);
            }

            $current_tempo = $current_cash + ($current_cash * $tempo_percent);

            $result[] = [
                'toko' => $toko['nama'],
                'cash' => intval($current_cash),
                'tempo' => intval($current_tempo),
            ];
        }

        return [
            'error' => false,
            'produk' => $product['product_name'],
            'harga' => $result
        ];
    }
}
