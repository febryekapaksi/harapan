<?php
defined('BASEPATH') or exit('No direct script access allowed');


class PR_Departemen extends Admin_Controller
{

    protected $viewPermission       = 'PR_Departemen.View';
    protected $addPermission        = 'PR_Departemen.Add';
    protected $managePermission     = 'PR_Departemen.Manage';
    protected $deletePermission     = 'PR_Departemen.Delete';


    public function __construct()
    {
        parent::__construct();
        $this->load->model(array(
            'Pr_departemen/pr_departemen_model',
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $get_department = $this->db->get_where('ms_department', ['deleted_by' => null])->result();

        $data = array(
            'list_department' => $get_department,
        );

        $this->template->page_icon('fa fa-users');
        $this->template->title('PR Departemen');
        $this->template->render('index', $data);
    }

    public function data_side_pr_departemen()
    {
        $this->pr_departemen_model->get_json_pr_departemen();
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

        $this->template->page_icon('fa fa-users');
        $this->template->title('Re-order Point Departemen');
        $this->template->render('add', $data);
    }

    public function get_row()
    {
        $id = $this->uri->segment(3);
        $satuan = $this->db->get_where('ms_satuan', ['deleted' => 'N', 'category' => 'packing'])->result_array();

        // Buat dropdown satuan
        $select_satuan = "<select name='detail[{$id}][satuan]' class='form-control chosen_select wajib' required>";
        $select_satuan .= "<option value='0'>Pilih</option>";
        foreach ($satuan as $s) {
            $select_satuan .= "<option value='{$s['id']}'>{$s['code']}</option>";
        }
        $select_satuan .= "</select>";

        // Susun baris detail
        $d_Header  = "<tr class='header_{$id}'>";
        $d_Header .= "<td align='center'>{$id}</td>";
        $d_Header .= "<td><input type='text' name='detail[{$id}][nm_barang]' class='form-control input-md'></td>";
        $d_Header .= "<td><input type='text' name='detail[{$id}][spec]' class='form-control input-md'></td>";
        $d_Header .= "<td><input type='text' id='qty_{$id}' name='detail[{$id}][qty]' class='form-control input-md text-center autoNumeric2 sum_tot'></td>";
        $d_Header .= "<td>{$select_satuan}</td>";
        $d_Header .= "<td><input type='text' id='harga_{$id}' name='detail[{$id}][harga]' class='form-control input-md text-right maskM sum_tot'></td>";
        $d_Header .= "<td><input type='text' id='total_harga_{$id}' name='detail[{$id}][total_harga]' class='form-control input-md text-right maskM jumlah_all' readonly></td>";
        $d_Header .= "<td><input type='text' name='detail[{$id}][tanggal]' class='form-control input-md text-center datepicker tgl_dibutuhkan' readonly></td>";
        $d_Header .= "<td><input type='text' name='detail[{$id}][keterangan]' class='form-control input-md'></td>";
        $d_Header .= "<td align='center'><button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button></td>";
        $d_Header .= "</tr>";

        // AutoNumeric init
        $d_Header .= "<script>$('.autoNumeric2').autoNumeric('init', {mDec: '2', aPad: false});</script>";

        echo json_encode(['header' => $d_Header]);
    }

    public function save()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $data_session = $this->session->userdata;
            $dateTime = date('Y-m-d H:i:s');

            $code_plan = $data['id'];
            $code_planx = $data['id'];
            $ym = date('ym');
            $no_so = !empty($data['no_so']) ? $data['no_so'] : NULL;
            $project_name = !empty($data['project_name']) ? $data['project_name'] : NULL;
            $id_dept = !empty($data['id_dept']) ? $data['id_dept'] : NULL;
            $coa = $data['coa'];
            $detail = $data['detail'];

            if (empty($code_planx)) {
                $query = $this->db->query("SELECT MAX(no_pengajuan) as maxP FROM rutin_non_planning_header WHERE no_pengajuan LIKE 'PLN{$ym}%'")->row();
                $last = $query->maxP;
                $urutan = sprintf('%03s', (int)substr($last, 7, 3) + 1);
                $code_plan = "PLN{$ym}{$urutan}";
            }

            $SUM_QTY = 0;
            $SUM_HARGA = 0;
            $ArrDetail = [];

            foreach ($detail as $valx) {
                $qty = str_replace(',', '', $valx['qty']);
                $harga = str_replace(',', '', $valx['harga']);

                $SUM_QTY += $qty;
                $SUM_HARGA += $harga * $qty;

                $ArrDetail[] = [
                    'no_pengajuan' => $code_plan,
                    'nm_barang' => strtolower($valx['nm_barang']),
                    'spec' => strtolower($valx['spec']),
                    'satuan' => $valx['satuan'],
                    'qty' => $qty,
                    'harga' => $harga,
                    'keterangan' => strtolower($valx['keterangan']),
                    'tanggal' => $valx['tanggal'],
                    'created_by' => $this->auth->user_id(),
                    'created_date' => $dateTime
                ];
            }

            // Upload dokumen
            $file_name = null;
            if (!empty($_FILES["upload_spk"]["name"])) {
                $config['upload_path'] = './assets/pr/';
                $config['allowed_types'] = '*';
                $config['encrypt_name'] = TRUE;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('upload_spk')) {
                    $uploadData = $this->upload->data();
                    $file_name = $uploadData['file_name'];
                }
            }

            $ArrHeader = [
                'id_dept'       => $id_dept,
                'project_name'  => $project_name,
                'qty'           => $SUM_QTY,
                'harga'         => $SUM_HARGA,
                'coa'           => $coa,
                'tingkat_pr'    => $data['tingkat_pr'],
                'app_post'      => '3',
                'app_1'         => '1',
                'app_2'         => '1',
            ];

            if (!empty($file_name)) {
                $ArrHeader['document'] = $file_name;
            }

            if (empty($code_planx)) {
                $ArrHeader['no_pengajuan'] = $code_plan;
                $ArrHeader['created_by'] = $this->auth->user_id();
                $ArrHeader['created_date'] = $dateTime;
            } else {
                $ArrHeader['updated_by'] = $this->auth->user_id();
                $ArrHeader['updated_date'] = $dateTime;
            }

            // Simpan data
            $this->db->trans_start();

            if (empty($code_planx)) {
                $this->db->insert('rutin_non_planning_header', $ArrHeader);
            } else {
                $this->db->update('rutin_non_planning_header', $ArrHeader, ['no_pengajuan' => $code_plan]);
                $this->db->delete('rutin_non_planning_detail', ['no_pengajuan' => $code_plan]);
            }

            $this->db->insert_batch('rutin_non_planning_detail', $ArrDetail);
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(['status' => 0, 'pesan' => 'Gagal simpan data']);
            } else {
                $this->db->trans_commit();
                history(($code_planx ? 'Edit' : 'Add') . ' pengajuan budget non rutin ' . $code_plan);
                echo json_encode(['status' => 1, 'pesan' => 'Data berhasil disimpan']);
            }
        }
    }
}
