<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Kelas extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Kelas.View';
	protected $addPermission  	= 'Master_Kelas.Add';
	protected $managePermission = 'Master_Kelas.Manage';
	protected $deletePermission = 'Master_Kelas.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(
			array('kelas/kelas_model')
		);
		$this->template->title('Manage Data Kelas');
		$this->template->page_icon('fa fa-building-o');

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-users');

		$data = $this->kelas_model->GetList();

		history("View data satuan");
		$this->template->set('results', $data);
		$this->template->title('Kelas Kredit Limit');
		$this->template->render('index');
	}

	public function add($id = null)
	{
		if ($this->input->post()) {
			$data = $this->input->post();

			$session 	= $this->session->userdata('app_session');

			$id 			  = $data['id'];
			$kelas 		  = trim(strtoupper($data['kelas']));
			$kredit_limit = str_replace(',', '', $data['kredit_limit']); // hilangkan format ribuan

			$field_hist = (empty($id)) ? 'Add' : 'Edit';

			$ArrHeader = array(
				'kelas'        => $kelas,
				'kredit_limit' => $kredit_limit,
			);

			$this->db->trans_start();
			if (empty($id)) {
				$this->db->insert('kelas', $ArrHeader);
			} else {
				$this->db->where('id', $id);
				$this->db->update('kelas', $ArrHeader);
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
				history($field_hist . " data kelas " . ($id ?: '[new]'));
			}

			echo json_encode($Arr_Data);
		} else {
			$header   = $this->db->get_where('kelas', ['id' => $id])->row_array();

			$data = [
				'id'           => $header['id'] ?? '',
				'kelas'        => $header['kelas'] ?? '',
				'kredit_limit' => $header['kredit_limit'] ?? '',
			];

			$this->template->title('Add / Edit Kelas');
			$this->template->page_icon('fa fa-edit');
			$this->template->render('add', $data);
		}
	}


	public function hapus()
	{
		$data = $this->input->post();
		$id = $data['id'];

		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->delete('kelas');
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
			history("Delete data kelas id " . $id);
		}

		echo json_encode($Arr_Data);
	}
}
