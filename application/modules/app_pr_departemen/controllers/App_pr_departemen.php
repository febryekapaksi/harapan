<?php
defined('BASEPATH') or exit('No direct script access allowed');

class App_pr_departemen extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Approval_PR_Departemen_Management.View';
    protected $addPermission    = 'Approval_PR_Departemen_Management.Add';
    protected $managePermission = 'Approval_PR_Departemen_Management.Manage';
    protected $deletePermission = 'Approval_PR_Departemen_Management.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->model(array('App_pr_departemen/app_pr_departemen_model'));
        date_default_timezone_set('Asia/Bangkok');

        $this->id_user  = $this->auth->user_id();
        $this->datetime = date('Y-m-d H:i:s');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-calendar-check-o');
        $this->template->title('Approval PR Departemen');
        $this->template->render('index');
    }

    public function data_side_approval_pr_departemen()
    {
        $this->app_pr_departemen_model->get_json_approval_pr_departemen();
    }

    public function add()
    {
        $id = $this->uri->segment(3);
        $approve = $this->uri->segment(4);

        $tingkat_approval = 3;

        $header = $this->db->get_where('rutin_non_planning_header', ['no_pengajuan' => $id])->result();
        $detail = $this->db->get_where('rutin_non_planning_detail', ['no_pengajuan' => $id])->result_array();

        $datacoa = $this->db->query("SELECT a.coa,b.nama FROM coa_category a JOIN " . DBACC . ".coa_master b ON a.coa=b.no_perkiraan WHERE a.tipe='NONRUTIN' ORDER BY a.coa")->result_array();
        $satuan = $this->db->get_where('ms_satuan', ['deleted' => 'N'])->result_array();
        $get_list_coa = $this->db->get(DBACC . '.coa_master')->result_array();
        $get_departement = $this->db->get_where('ms_department', ['deleted_by' => null])->result();

        $tanda = !empty($header) ? 'Edit' : 'Add';
        if (!empty($approve)) {
            $tanda = ($approve == 'view') ? 'View' : 'Approve';
        }

        $data = [
            'action'            => strtolower($tanda),
            'header'            => $header,
            'detail'            => $detail,
            'datacoa'           => $datacoa,
            'satuan'            => $satuan,
            'approve'           => $approve,
            'id'                => $id,
            'list_departement'  => $get_departement,
            'tingkat_approval'  => $tingkat_approval,
            'list_coa'          => $get_list_coa
        ];

        $this->template->page_icon('fa fa-calender-check-o');
        $this->template->title('Approval PR Departemen');
        $this->template->render('add', $data);
    }


    public function approve()
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $dateTime        = date('Y-m-d H:i:s');
            $code_plan        = $data['id'];
            $tingkat_approval = $data['tingkat_approval'];
            $sts_app        = $data['sts_app'];
            $reason            = $data['reason'];
            $detail            = $data['detail'];

            $ArrDetail = array();
            $ArrDetailPR = array();
            $SUM_QTY = 0;
            $SUM_HARGA = 0;

            $Ym = date('ym');
            $qIPP = "SELECT MAX(no_pr) as maxP FROM rutin_non_planning_header WHERE no_pr LIKE 'PRN{$Ym}%' ";
            $resultIPP = $this->db->query($qIPP)->row_array();
            $angkaUrut = (int)substr($resultIPP['maxP'], 7, 4);
            $no_pr = 'PRN' . $Ym . sprintf('%04s', $angkaUrut + 1);

            foreach ($detail as $val => $valx) {
                $qty = str_replace(',', '', $valx['qty']);
                $harga = str_replace(',', '', $valx['harga']);
                $SUM_QTY += $qty;
                $SUM_HARGA += $harga * $qty;

                $ArrDetail[$val] = [
                    'id' => $valx['id'],
                    'no_pr' => $no_pr,
                    'qty_rev' => $qty,
                    'harga_rev' => $harga,
                    'sts_app' => $sts_app,
                    'sts_app_by' => $this->auth->user_id(),
                    'sts_app_date' => $dateTime
                ];

                $ArrDetailPR[$val] = [
                    'no_pr' => $no_pr,
                    'category' => 'non rutin',
                    'tgl_pr' => date('Y-m-d'),
                    'id_barang' => $valx['id'],
                    'nm_barang' => strtolower($valx['nm_barang'] . ' - ' . $valx['spec']),
                    'qty' => $qty,
                    'nilai_pr' => $harga,
                    'tgl_dibutuhkan' => $valx['tanggal'],
                    'satuan' => $valx['satuan'],
                    'app_status_3' => 'Y',
                    'app_reason_3' => strtolower($valx['keterangan']),
                    'app_by_3' => $this->auth->user_id(),
                    'app_date_3' => $dateTime,
                    'created_by' => $this->auth->user_id(),
                    'created_date' => $dateTime
                ];
            }

            $ArrHeader = [];
            if ($sts_app == 'Y') {
                $ArrHeader = [
                    'qty_rev' => $SUM_QTY,
                    'harga_rev' => $SUM_HARGA,
                    'no_pr' => $no_pr,
                    'sts_app' => $sts_app,
                    'reason' => $reason,
                    'app_3' => 1,
                    'sts_reject3' => null,
                    'app_3_by' => $this->auth->user_id(),
                    'app_3_date' => $dateTime,
                    'app_post' => 4
                ];
            } else {
                $ArrHeader = [
                    'qty_rev' => $SUM_QTY,
                    'harga_rev' => $SUM_HARGA,
                    'no_pr' => null,
                    'sts_app' => 0,
                    'reject_reason3' => $reason,
                    'sts_reject3' => 1,
                    'sts_reject3_by' => $this->auth->user_id(),
                    'sts_reject3_date' => $dateTime,
                    'app_post' => null,
                    'rejected' => 1
                ];
            }

            $this->db->trans_start();
            $this->db->update('rutin_non_planning_header', $ArrHeader, ['no_pengajuan' => $code_plan]);
            $this->db->update_batch('rutin_non_planning_detail', $ArrDetail, 'id');

            if ($sts_app == 'Y') {
                $this->db->insert('rutin_non_planning_header', [
                    'no_pr' => $no_pr,
                    'category' => 'non rutin',
                    'tanggal' => date('Y-m-d'),
                    'created_by' => $this->auth->user_id(),
                    'created_date' => $dateTime
                ]);
                $this->db->insert_batch('tran_pr_detail', $ArrDetailPR);
            }
            $this->db->trans_complete();

            echo json_encode([
                'status' => $this->db->trans_status() ? 1 : 0,
                'pesan' => $this->db->trans_status() ? 'Approval berhasil diproses.' : 'Gagal memproses approval.'
            ]);
        }
    }
}
