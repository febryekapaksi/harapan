<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Master_bank extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Bank.View';
	protected $addPermission  	= 'Master_Bank.Add';
	protected $managePermission = 'Master_Bank.Manage';
	protected $deletePermission = 'Master_Bank.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(
			array('Master_bank/master_bank_model')
		);

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-bank');
		$this->template->title('Master Bank');

		$data = $this->db->get('master_bank')->result();

		$this->template->render('index', ['data' => $data]);
	}

	public function add($id = null)
	{
		if ($this->input->post()) {
			$data = $this->input->post();

			$id = $this->input->post('id');

			// Siapkan array data untuk insert/update
			$saveData = [
				'no_perkiraan'     	=> $data['no_perkiraan'],
				'no_rekening'     	=> $data['no_rekening'],
				'nama'     			=> $data['nama'],
				'level'     		=> 5,
			];

			$this->db->trans_start();

			if (empty($id)) {
				$this->db->insert('master_bank', $saveData);
			} else {
				$this->db->where('id', $id);
				$this->db->update('master_bank', $saveData);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Data = [
					'pesan'  => 'Process Failed!',
					'status' => 0
				];
			} else {
				$this->db->trans_commit();
				$Arr_Data = [
					'pesan'  => 'Process Success!',
					'status' => 1
				];
			}

			echo json_encode($Arr_Data);
		} else {
			$result = $this->db->get_where('master_bank', ['id' => $id])->result();

			$data = [
				'result' => $result,
			];

			$this->template->page_icon('fa fa-truck');
			$this->template->render('add', $data);
		}
	}

	public function delete()
	{
		$id = $this->input->post('id');

		if (empty($id)) {
			echo json_encode([
				'status' => 0,
				'pesan' => 'ID tidak ditemukan.'
			]);
			return;
		}

		$this->db->trans_start();

		// Hapus data dari tabel master_bank
		$this->db->where('id', $id)->delete('master_bank');

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			echo json_encode([
				'status' => 0,
				'pesan' => 'Gagal menghapus data.'
			]);
		} else {
			echo json_encode([
				'status' => 1,
				'pesan' => 'Data berhasil dihapus.'
			]);
		}
	}
}
