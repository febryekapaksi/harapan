<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 *
 */
class Master_kendaraan extends Admin_Controller
{
	//Permission
	protected $viewPermission 	= 'Master_Kendaraan.View';
	protected $addPermission  	= 'Master_Kendaraan.Add';
	protected $managePermission = 'Master_Kendaraan.Manage';
	protected $deletePermission = 'Master_Kendaraan.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(
			array('Master_kendaraan/master_kendaraan_model')
		);

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$this->auth->restrict($this->viewPermission);
		$this->template->page_icon('fa fa-truck');
		$this->template->title('Master Kendaraan');

		$data = $this->db->get('master_kendaraan')->result();

		$this->template->render('index', ['data' => $data]);
	}

	public function add($id = null)
	{
		if ($this->input->post()) {
			$data = $this->input->post();

			$id = $this->input->post('id');

			// Siapkan array data untuk insert/update
			$saveData = [
				'nopol'     => $data['nopol'],
				'jenis'     => $data['jenis'],
				'kapasitas' => str_replace(',', '', $data['kapasitas']), // jika format angka
			];

			$this->db->trans_start();

			if (empty($id)) {
				$this->db->insert('master_kendaraan', $saveData);
			} else {
				$this->db->where('id', $id);
				$this->db->update('master_kendaraan', $saveData);
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
			$result = $this->db->get_where('master_kendaraan', ['id' => $id])->result();

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

		// Hapus data dari tabel master_kendaraan
		$this->db->where('id', $id)->delete('master_kendaraan');

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
