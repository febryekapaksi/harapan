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
        $data['customers'] = $this->db->get('master_customers')->result_array();
        $data['products'] = $this->db->get_where('product_costing', ['status' => 'A'])->result_array();

        // Ambil daftar TOP (Term of Payment)
        $payment_terms = $this->db
            ->where('group_by', 'top invoice')
            ->where('sts', 'Y')
            ->order_by('id', 'asc')
            ->get('list_help')
            ->result_array();

        $data['payment_terms'] = $payment_terms;

        $this->template->render('form', $data);
    }

    public function save()
    {
        $data = $this->input->post();
        $id = $data['id_penawaran'];

        $is_update = !empty($id);
        $id_penawaran = $is_update ? $id : $this->penawaran_model->generate_id();

        $header = [
            'id_penawaran'          => $id_penawaran,
            'id_customer'           => $data['id_customer'],
            'sales'                 => $data['sales'],
            'email'                 => $data['email'],
            'payment_term'          => $data['payment_term'],
            'quotation_date'        => date('Y-m-d H:i:s', strtotime($data['quotation_date'])),
            'tipe_bayar'            => $data['tipe_bayar'],
            'total_penawaran'       => str_replace(',', '', $data['total_penawaran']),
            'total_price_list'      => str_replace(',', '', $data['total_price_list']),
            'total_diskon_persen'   => $data['total_diskon_persen'],
            'status'                => "WA",
        ];

        if ($is_update) {
            $header['modified_by'] = $this->auth->user_id();
            $header['modified_at'] = date('Y-m-d H:i:s');
        } else {
            $header['created_by'] = $this->auth->user_id();
            $header['created_at'] = date('Y-m-d H:i:s');
        }

        $this->db->trans_start();
        if ($is_update) {
            $this->db->where('id', $id);
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

    public function hitung_harga_ajax()
    {
        $kategori_toko = $this->input->post('kategori_toko');
        $tipe_bayar = $this->input->post('tipe_bayar');
        $harga_awal = floatval($this->input->post('harga_awal'));

        // Ambil urutan semua toko
        $tokoList = $this->db->order_by('urutan', 'asc')->get('master_persentase')->result_array();

        $current_cash = $harga_awal;
        $current_tempo = 0;
        $harga_terpilih = null;

        foreach ($tokoList as $index => $toko) {
            $cash_percent = floatval($toko['cash']) / 100;
            $tempo_percent = floatval($toko['tempo']) / 100;

            if ($index > 0) {
                $current_cash = $current_tempo + ($current_tempo * $cash_percent);
            }

            $current_tempo = $current_cash + ($current_cash * $tempo_percent);

            if (strtolower($toko['nama']) === strtolower($kategori_toko)) {
                $harga_terpilih = ($tipe_bayar == 'cash') ? $current_cash : $current_tempo;
                break;
            }
        }

        if (!$harga_terpilih) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => true, 'message' => 'Data toko tidak ditemukan']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'error' => false,
                'harga' => intval($harga_terpilih)
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

    public function data_side_penawaran()
    {
        $this->penawaran_model->get_json_penawaran();
    }



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
        $data = $this->getHargaPenawaran('PC2500001', 'Toko 2', 'tempo');

        if ($data['error']) {
            echo $data['message'];
        } else {
            echo "Produk : {$data['nama_produk']}<br> Toko : {$data['toko']}, <br>Bayar : {$data['tipe_bayar']}, <br> Harga : Rp " . number_format($data['harga'], 2, ',', '.');
        }
    }
}
