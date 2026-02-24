 <?php
    defined('BASEPATH') or exit('No direct script access allowed');

    class Master_debt extends Admin_Controller
    {
        //Permission
        protected $viewPermission     = 'Master_Debt.View';
        protected $addPermission      = 'Master_Debt.Add';
        protected $managePermission   = 'Master_Debt.Manage';
        protected $deletePermission   = 'Master_Debt.Delete';

        public function __construct()
        {
            parent::__construct();

            $this->load->model(array(
                'Master_debt/Master_debt_model'
            ));
            date_default_timezone_set('Asia/Bangkok');
        }

        public function index()
        {
            $this->template->page_icon('fa fa-percent');
            $this->template->title('Master Persentase Late & Bad Debt');

            $tahun = $this->input->get('tahun') ?? date('Y');
            $data['bulan'] = $this->db->order_by('bulan_no', 'asc')->get('cr_bulan')->result_array();
            $data['sales'] = $this->db->where('department', '2')->get('employee')->result_array();

            $existing = $this->db->get_where('master_debt', ['tahun' => $tahun])->result_array();

            $rekap = [];
            foreach ($existing as $row) {
                $rekap[$row['id_sales']][$row['bulan']] = [
                    'late' => $row['target_late_debt'],
                    'bad'  => $row['target_bad_debt']
                ];
            }

            $data['tahun'] = $tahun;
            $data['rekap'] = $rekap;

            $this->template->render('index', $data);
        }

        public function save()
        {
            $post = $this->input->post();
            $tahun = $post['tahun'];
            $data_input = $post['target'];
            $this->db->trans_start();

            foreach ($data_input as $id_sales => $bulan_data) {
                foreach ($bulan_data as $bulan_no => $tipe) {

                    $data = [
                        'id_sales'         => $id_sales,
                        'tahun'            => $tahun,
                        'bulan'            => $bulan_no,
                        'target_late_debt' => $tipe['late'],
                        'target_bad_debt'  => $tipe['bad'],
                        'modified_at'      => date('Y-m-d H:i:s')
                    ];

                    $check = $this->db->get_where('master_debt', [
                        'id_sales' => $id_sales,
                        'tahun'    => $tahun,
                        'bulan'    => $bulan_no
                    ])->row();

                    if ($check) {
                        $this->db->where('id', $check->id)->update('master_debt', $data);
                    } else {
                        $this->db->insert('master_debt', $data);
                    }
                }
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $status = [
                    'pesan' => 'Failed process data!',
                    'status' => 0
                ];
            } else {
                $status = [
                    'pesan' => 'Success process data!',
                    'status' => 1
                ];
            }

            echo json_encode($status);
        }
    }
