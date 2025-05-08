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

        $data['mode'] = 'ontime';
        $data['komisi'] = $this->db->where('komisi_type', 'ontime')->get('master_komisi')->result_array();
        $this->template->render('index', $data);
    }

    public function komisi_pembayaran_tunggakan()
    {
        $this->template->page_icon('fa fa-percent');
        $this->template->title('Master Komisi Pembayaran Tunggakan');

        $data['mode'] = 'tunggakan';
        $data['komisi'] = $this->db->where('komisi_type', 'tunggakan')->get('master_komisi')->result_array();
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

    public function komisi_penyelesaian()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Master Faktor Komisi Penyelesaian Tunggakan');

        $data = [
            'result' => $this->db->get('master_komisi_penyelesaian')->result(),
        ];

        $this->template->render('index_penyelesaian', $data);
    }

    public function add_penyelesaian($id = null)
    {
        $data['komisi'] = $this->db->get_where('master_komisi_penyelesaian', ['id' => $id])->row();

        $this->template->render('form_penyelesaian', $data);
    }

    public function save_penyelesaian()
    {
        $post = $this->input->post();
        $id = isset($post['id']) ? $post['id'] : null; // ← penting

        $data = [
            'komisi_penyelesaian' => $post['komisi_penyelesaian'],
            'keterangan' => $post['keterangan'],
        ];

        $this->db->trans_start();

        if (empty($id)) {
            $this->db->insert('master_komisi_penyelesaian', $data);
        } else {
            $this->db->where('id', $id)->update('master_komisi_penyelesaian', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status = [
                'pesan' => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $this->db->trans_commit();
            $status = [
                'pesan' => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }


    public function delete()
    {
        $this->auth->restrict($this->deletePermission);

        $id = $this->input->post('id');

        if (!$id) {
            echo json_encode([
                'pesan' => 'ID tidak valid.',
                'status' => 0
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->db->where('id', $id)->delete("master_komisi_penyelesaian");

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status = [
                'pesan' => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $this->db->trans_commit();
            $status = [
                'pesan' => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }

    // Master Target Penjualan
    public function index_target()
    {
        $this->template->page_icon('fa fa-dollar');
        $this->template->title('Target Penjualan');

        $data['target'] = $this->db->order_by('id', 'asc')->get('target_penjualan')->result_array();
        $data['bulan'] = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();

        $this->template->render('index_target', $data);
    }

    public function add_target($id = null)
    {
        $data['target'] = $this->db->get_where('target_penjualan', ['id' => $id])->row();
        $data['bulan'] = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();
        $data['sales'] = $this->db->where('department', '2')->get('employee')->result_array();

        $this->template->render('form_target', $data);
    }

    public function save_target()
    {
        $post = $this->input->post();
        $id = isset($post['id']) ? $post['id'] : null;

        // Ambil data bulan dari tabel cr_bulan
        $bulan = $this->db->order_by('bulan_no', 'ASC')->get('cr_bulan')->result();

        // Siapkan data
        $data = [
            'id_karyawan' => $post['id_karyawan'],
            'nm_karyawan' => $post['nm_karyawan'],
        ];

        foreach ($bulan as $b) {
            $key = $b->bulan_id;
            $data[$key] = isset($post[$key]) ? str_replace(',', '', $post[$key]) : 0;
        }

        $this->db->trans_start();

        if (empty($id)) {
            $this->db->insert('target_penjualan', $data);
        } else {
            $this->db->where('id', $id)->update('target_penjualan', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status = [
                'pesan' => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $this->db->trans_commit();
            $status = [
                'pesan' => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }

    public function delete_target()
    {
        $this->auth->restrict($this->deletePermission);

        $id = $this->input->post('id');

        if (!$id) {
            echo json_encode([
                'pesan' => 'ID tidak valid.',
                'status' => 0
            ]);
            return;
        }

        $this->db->trans_begin();

        $this->db->where('id', $id)->delete("target_penjualan");

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status = [
                'pesan' => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $this->db->trans_commit();
            $status = [
                'pesan' => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }

    // BUAT PERHITUNGAN KOMISI
    public function data_side_komisi()
    {
        $bulan = $this->input->post('bulan');
        $this->master_komisi_model->get_json_komisi($bulan);
    }

    public function index_komisi()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Daftar Perhitungan Komisi');

        $data = [
            'bulan' => $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array(),
        ];

        $this->template->render('index_komisi', $data);
    }

    public function get_koefisien()
    {
        $type = $this->input->post('komisi_type');
        $persen = $this->input->post('persen');

        $row = $this->db
            ->where('komisi_type', $type)
            ->where('dari <=', $persen)
            ->where('sampai >', $persen)
            ->get('master_komisi')
            ->row();

        if ($row) {
            echo json_encode(['success' => true, 'koefisien' => (float) $row->koefisien]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function add_komisi($id = null)
    {
        $data = [
            'sales' => $this->db->where('department', '2')->get('employee')->result_array(),
            'bulan' => $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array(),
            'komisi' => $this->db->get_where('komisi_realisasi', ['id' => $id])->row(),
        ];
        $this->template->render('form_komisi', $data);
    }

    public function save_komisi()
    {
        $post = $this->input->post();
        $id = isset($post['id']) ? $post['id'] : null;

        // Siapkan data yang akan disimpan
        $data = [
            'id_karyawan'              => $post['id_karyawan'],
            'nm_karyawan'              => $post['nm_karyawan'],
            'bulan_id'                 => $post['bulan_id'],
            'bulan'                    => $post['bulan'],
            'tahun'                    => date('Y'),
            'target_ontime'            => str_replace(',', '', $post['target_ontime']),
            'realisasi_ontime'         => str_replace(',', '', $post['realisasi_ontime']),
            'persentase_ontime'        => str_replace(',', '', $post['persentase_ontime']),
            'koefisien_ontime'         => str_replace(',', '', $post['koefisien_ontime']),
            'nilai_komisi_ontime'      => str_replace(',', '', $post['nilai_komisi_ontime']),
            'target_tunggakan'         => str_replace(',', '', $post['target_tunggakan']),
            'realisasi_tunggakan'      => str_replace(',', '', $post['realisasi_tunggakan']),
            'persentase_tunggakan'     => str_replace(',', '', $post['persentase_tunggakan']),
            'koefisien_tunggakan'      => str_replace(',', '', $post['koefisien_tunggakan']),
            'nilai_komisi_tunggakan'   => str_replace(',', '', $post['nilai_komisi_tunggakan']),
            'total_ontime_tunggakan'   => str_replace(',', '', $post['total_ontime_tunggakan']),
            'target_penjualan'         => str_replace(',', '', $post['target_penjualan']),
            'realisasi_penjualan'      => str_replace(',', '', $post['realisasi_penjualan']),
            'persentase_penjualan'     => str_replace(',', '', $post['persentase_penjualan']),
            'koefisien_penjualan'      => str_replace(',', '', $post['koefisien_penjualan']),
            'nilai_komisi_penjualan'   => str_replace(',', '', $post['nilai_komisi_penjualan']),
            'grand_total'              => str_replace(',', '', $post['grand_total']),
        ];

        $this->db->trans_start();

        if (empty($id)) {
            $this->db->insert('komisi_realisasi', $data);
        } else {
            $this->db->where('id', $id)->update('komisi_realisasi', $data);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $status = [
                'pesan' => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $this->db->trans_commit();
            $status = [
                'pesan' => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }
}
