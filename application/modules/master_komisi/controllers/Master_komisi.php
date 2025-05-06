<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_komisi extends Admin_Controller
{
    //Permission
    protected $viewPermission     = 'Master_Komisi.View';
    protected $addPermission      = 'Master_Komisi.Add';
    protected $managePermission   = 'Master_Komisi.Manage';
    protected $deletePermission   = 'Master_Komisi.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array(
            'Master_komisi/master_komisi_model'
        ));
        date_default_timezone_set('Asia/Bangkok');
    }

    // Master Komisi Penjualan, Tagihan Ontime, Pembayaran Tunggakan

    public function komisi_penjualan()
    {
        $this->template->page_icon('fa fa-percent');
        $this->template->title('Master Komisi Penjualan');

        $data['mode'] = 'penjualan';
        $data['komisi'] = $this->db->where('komisi_type', 'penjualan')->get('master_komisi')->result_array();
        $this->template->render('index', $data);
    }

    public function komisi_tagihan_ontime()
    {
        $this->template->page_icon('fa fa-percent');
        $this->template->title('Master Komisi Tagihan Ontime');

        $data['mode'] = 'tagihan_ontime';
        $data['komisi'] = $this->db->where('komisi_type', 'tagihan_ontime')->get('master_komisi')->result_array();
        $this->template->render('index', $data);
    }

    public function komisi_pembayaran_tunggakan()
    {
        $this->template->page_icon('fa fa-percent');
        $this->template->title('Master Komisi Pembayaran Tunggakan');

        $data['mode'] = 'pembayaran_tunggakan';
        $data['komisi'] = $this->db->where('komisi_type', 'pembayaran_tunggakan')->get('master_komisi')->result_array();
        $this->template->render('index', $data);
    }

    public function save()
    {
        $komisiType = $this->input->post('komisi_type');
        $rows = $this->input->post('data'); // Ambil array data baris

        if (!$komisiType || empty($rows)) {
            echo json_encode(['status' => 0, 'message' => 'Data tidak valid.']);
            return;
        }

        // Hapus data lama sesuai komisi_type
        $this->db->where('komisi_type', $komisiType)->delete('master_komisi');

        foreach ($rows as $row) {
            if (!isset($row['dari'], $row['sampai'], $row['koefisien'])) continue;

            $this->db->insert('master_komisi', [
                'komisi_type' => $komisiType,
                'dari'        => $row['dari'],
                'sampai'      => $row['sampai'],
                'koefisien'   => $row['koefisien'],
            ]);
        }

        echo json_encode(['status' => 1, 'message' => 'Data berhasil disimpan.']);
    }

    // Master Faktor Komisi Penyelesaian Tunggakan

    public function komisi_penyelesaian_tunggakan()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Master Komisi Penyelesaian Tunggakan');

        $this->template->render('index_tunggakan');
    }
}
