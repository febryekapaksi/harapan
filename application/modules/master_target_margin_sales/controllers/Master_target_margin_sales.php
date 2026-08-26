<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_target_margin_sales extends Admin_Controller
{
    //Permission
    protected $viewPermission = 'Master_Target_Margin_Sales.View';
    protected $addPermission = 'Master_Target_Margin_Sales.Add';
    protected $managePermission = 'Master_Target_Margin_Sales.Manage';
    protected $deletePermission = 'Master_Target_Margin_Sales.Delete';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Master_target_margin_sales/Master_target_margin_sales_model']);
        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);

        $this->template->page_icon('fa fa-percent');
        $this->template->title('Master Target Margin Sales');

        $tahun = $this->input->get('tahun') ?? date('Y');
        $data['bulan'] = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();
        $data['sales'] = $this->db->where('department', '2')->get('employee')->result_array();

        $existing = $this->db->get_where('master_margin', ['tahun' => $tahun])->result_array();

        $rekap = [];
        foreach ($existing as $row) {
            $rekap[$row['id_sales']][$row['bulan']] = $row['target_margin'];
        }

        $data['tahun'] = $tahun;
        $data['rekap'] = $rekap;

        $this->template->render('index', $data);
    }

    public function save()
    {
        $this->auth->restrict($this->managePermission);

        $post = $this->input->post();
        $tahun = $post['tahun'];
        $data_input = $post['target'] ?? [];

        $this->db->trans_start();

        foreach ($data_input as $id_sales => $bulan_data) {
            foreach ($bulan_data as $bulan_no => $target_margin) {

                $data = [
                    'id_sales'      => $id_sales,
                    'tahun'         => $tahun,
                    'bulan'         => $bulan_no,
                    'target_margin' => $target_margin,
                    'modified_at'   => date('Y-m-d H:i:s')
                ];

                $check = $this->db->get_where('master_margin', [
                    'id_sales' => $id_sales,
                    'tahun'    => $tahun,
                    'bulan'    => $bulan_no
                ])->row();

                if ($check) {
                    $this->db->where('id', $check->id)->update('master_margin', $data);
                } else {
                    $this->db->insert('master_margin', $data);
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $status = [
                'pesan'  => 'Failed process data!',
                'status' => 0
            ];
        } else {
            $status = [
                'pesan'  => 'Success process data!',
                'status' => 1
            ];
        }

        echo json_encode($status);
    }
}
