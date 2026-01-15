<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Kpi_model extends BF_Model
{
	protected $ENABLE_ADD;
	protected $ENABLE_MANAGE;
	protected $ENABLE_VIEW;
	protected $ENABLE_DELETE;

	public function __construct()
	{
		parent::__construct();

		$this->ENABLE_ADD     = has_permission('KPI.Add');
		$this->ENABLE_MANAGE  = has_permission('KPI.Manage');
		$this->ENABLE_VIEW    = has_permission('KPI.View');
		$this->ENABLE_DELETE  = has_permission('KPI.Delete');
	}

	public function get_all()
	{
		return $this->db->order_by('id', 'DESC')
			->where('is_delete', 0)
			->get('kpi_headers')
			->result_array();
	}

	public function get_header($id)
	{
		return $this->db
			->get_where('kpi_headers', ['id' => $id])
			->row();
	}

	public function get_items_with_thresholds($header_id)
	{
		$items = $this->db
			->get_where('kpi_items', ['header_id' => $header_id])
			->result();

		foreach ($items as &$item) {
			$thresholds = $this->db
				->from('kpi_thresholds')
				->where('kpi_item_id', $item->id)
				->order_by('status_code', 'ASC')
				->get()
				->result();

			$item->thresholds = $thresholds;
		}

		return $items;
	}

	public function get_divisions()
	{
		return $this->db
			->select('id_divisi, nm_divisi')
			->from('divisi')
			->order_by('nm_divisi', 'ASC')
			->get()
			->result();
	}

	public function get_active_employees()
	{
		return $this->db
			->select('id, nm_karyawan')
			->from('employee')
			->where('deleted', 'N')
			->get()
			->result();
	}

	public function delete_header($id)
	{
		$id = (int)$id;

		$this->db->trans_start();
		$this->db->where('id', $id)
			->set('is_delete', 1)
			->update('kpi_headers');

		$this->db->trans_complete();

		return $this->db->trans_status();
	}

	public function get_header_by_id($id)
	{
		return $this->db->get_where('kpi_headers', ['id' => $id])->row();
	}

	public function get_items_by_header($header_id)
	{
		return $this->db->get_where('kpi_items', ['header_id' => $header_id])->result();
	}

	public function get_realisations_by_item($item_id)
	{
		$this->db->select('*');
		$this->db->from('kpi_realisations');
		$this->db->where('kpi_item_id', $item_id);
		$this->db->order_by('periode', 'ASC');
		$result = $this->db->get()->result();
		$data = [];
		foreach ($result as $r) {
			$month = date('M', strtotime($r->periode));
			$data[$month] = $r;
		}
		return $data;
	}
}
