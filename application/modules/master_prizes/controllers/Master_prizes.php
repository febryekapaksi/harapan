<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_prizes extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Master_prizes.View';
    protected $addPermission    = 'Master_prizes.Add';
    protected $managePermission = 'Master_prizes.Manage';
    protected $deletePermission = 'Master_prizes.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Master_prizes/master_prizes_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->auth->restrict($this->viewPermission);
        $this->template->page_icon('fa fa-gift');

        $data = $this->master_prizes_model->GetList();

        history("View data satuan");
        $this->template->set('results', $data);
        $this->template->title('Master Prizes');
        $this->template->render('index');
    }

    public function add($id = null)
    {
        $field_hist         = (empty($id)) ? 'Tambah' : 'Edit';

        if ($this->input->post()) {
            $post = $this->input->post();

            $id         = $post['id'];
            $code       = (!empty($post['code'])) ? $post['code'] : $this->_generateCode();

            $ArrHeader = array(
                'code'          => $code,
                'name'          => $post['name'],
                'stock_total'   => $post['stock_total'],
                'note'          => $post['note'],
            );

            $this->db->trans_start();
            if (empty($id)) {
                $this->db->insert('master_prizes', $ArrHeader);
            } else {
                $this->db->where('id', $id);
                $this->db->update('master_prizes', $ArrHeader);
            }
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $Arr_Data = array(
                    'pesan'  => 'Process Failed!',
                    'status' => 0
                );
            } else {
                $this->db->trans_commit();
                $Arr_Data = array(
                    'pesan'  => 'Process Success!',
                    'status' => 1
                );
                history($field_hist . " data Hadiah " . ($id ?: '[new]'));
            }

            echo json_encode($Arr_Data);
        } else {
            $result   = $this->db->get_where('master_prizes', ['id' => $id])->row_array();

            $data = [
                'id'            => $result['id'] ?? '',
                'code'          => $result['code'] ?? '',
                'name'          => $result['name'] ?? '',
                'stock_total'   => $result['stock_total'] ?? '',
                'status'        => $result['status'] ?? '',
                'note'          => $result['note'] ?? '',
            ];

            $this->template->title($field_hist . ' Hadiah');
            $this->template->page_icon('fa fa-edit');
            $this->template->render('form', $data);
        }
    }

    public function hapus()
    {
        $data = $this->input->post();
        $id = $data['id'];

        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete('master_prizes');
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $Arr_Data = array(
                'pesan'  => 'Process Failed!',
                'status' => 0
            );
        } else {
            $this->db->trans_commit();
            $Arr_Data = array(
                'pesan'  => 'Process Success!',
                'status' => 1
            );
            history("Delete data master prizes id " . $id);
        }

        echo json_encode($Arr_Data);
    }

    // Private Function Section 

    private function _generateCode()
    {
        $Ym = date('ym');
        $SQL = "SELECT MAX(code) as maxM FROM master_prizes WHERE code LIKE 'HP" . $Ym . "%'";
        $result = $this->db->query($SQL)->result_array();
        $angkaUrut = $result[0]['maxM'];
        $urutan = (int)substr($angkaUrut, 6, 4);
        $urutan++;
        return "HP" . $Ym . sprintf('%04s', $urutan);
    }
}
