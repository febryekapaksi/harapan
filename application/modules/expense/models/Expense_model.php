<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Purchase Request"
 */

class Expense_model extends BF_Model
{
	/**
	 * @var string  User Table Name
	 */
	protected $table_name = 'tr_expense';
	protected $key        = 'id';

	/**
	 * @var string Field name to use for the created time column in the DB table
	 * if $set_created is enabled.
	 */
	protected $created_field = 'created_on';

	/**
	 * @var string Field name to use for the modified time column in the DB
	 * table if $set_modified is enabled.
	 */
	protected $modified_field = 'modified_on';

	/**
	 * @var bool Set the created time automatically on a new record (if true)
	 */
	protected $set_created = true;

	/**
	 * @var bool Set the modified time automatically on editing a record (if true)
	 */
	protected $set_modified = true;

	/**
	 * @var string The type of date/time field used for $created_field and $modified_field.
	 * Valid values are 'int', 'datetime', 'date'.
	 */
	protected $date_format = 'datetime';

	/**
	 * @var bool If true, will log user id in $created_by_field, $modified_by_field,
	 * and $deleted_by_field.
	 */
	protected $log_user = true;

	/**
	 * Function construct used to load some library, do some actions, etc.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// list data kasbon
	public function GetListDataKasbon($where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser');
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		// $this->db->join('employee c', 'b.employee_id = c.id');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data kasbon
	public function GetDataKasbon($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_kasbon a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// Get COA Kasbon
	public function GetCoaKasbon()
	{
		$row = $this->db
			->select('coa')
			->from('coa_expense')
			->where('jenis_pengeluaran', 'Kasbon')
			->get()
			->row();

		if (!$row || !$row->coa) {
			return [];
		}

		$coa_list = array_filter(explode(';', $row->coa));

		$this->db->select('a.no_perkiraan, a.nama');
		$this->db->from(DBACC . '.coa_master a');
		$this->db->where_in('a.no_perkiraan', $coa_list);

		return $this->db->get()->result();
	}

	// list data transport request
	public function GetListDataTransportRequest($id_user = '', $where = '')
	{
		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		if ($id_user != '') $this->db->where('a.created_by', $id_user);
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// list data transport request
	public function GetListDataTransportRequestAll($id_user = '', $where = '')
	{
		$this->db->select('a.*, b.nm_lengkap as nmuser,c.tgl_doc as tgl_trans,c.keperluan');
		$this->db->from('tr_transport_req a');
		$this->db->join('tr_transport c', 'a.no_doc=c.no_req', 'left');
		$this->db->join('users b', 'b.id_user = a.created_by', 'left');
		if ($id_user !== '') $this->db->where('a.created_by', $id_user);
		if ($where !== '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	// get data transport req
	public function GetDataTransportReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// get data transport req detail
	public function GetDataTransportInReq($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.no_req', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}


	// list data transport
	public function GetListDatatransport($id_user = '')
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		if ($id_user != '') $this->db->where('a.nama', $id_user);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data transport
	public function GetDataTransport($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_transport a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	// list data
	public function GetListData($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	public function GetListDataAll($where = '')
	{
		$this->db->select('a.*, b.username as nmuser, c.username as nmapproval,d.tanggal,d.deskripsi');
		$this->db->from($this->table_name . ' a');
		$this->db->join('users b', 'a.nama=b.username', 'left');
		$this->db->join('users c', 'a.approval=c.username', 'left');
		$this->db->join('tr_expense_detail d', 'a.no_doc=d.no_doc', 'left');
		if ($where != '') $this->db->where($where);
		$this->db->order_by('a.id', 'desc');
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}
	// get data
	public function GetDataHeader($id)
	{
		$this->db->select('a.*');
		$this->db->from($this->table_name . ' a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetDataDetail($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.no_doc', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->result();
		} else {
			return false;
		}
	}

	public function GetDetailPurchaseRequest($id)
	{
		$this->db->select('a.*');
		$this->db->from('tr_expense_detail a');
		$this->db->where('a.id', $id);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudget($coa, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function GetBudgetDivisi($type, $divisi, $tahun)
	{
		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $type);
		$this->db->where('a.divisi', $divisi);
		$this->db->where('a.tahun', $tahun);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	public function Update_budget($coa, $tgl, $nilai, $divisi, $nilai_pr = 0)
	{
		$bulan = date("n", strtotime($tgl));
		$tahun = date("Y", strtotime($tgl));

		$this->db->select('a.*');
		$this->db->from('ms_coa_budget a');
		$this->db->where('a.coa', $coa);
		$this->db->where('a.tahun', $tahun);
		$this->db->where('a.divisi', $divisi);
		$query = $this->db->get();
		if ($query->num_rows() != 0) {
			$data = $query->row();
			$terpakai_bulan = $data->{"terpakai_bulan_" . $bulan};
			$terpakai = $data->terpakai;
			$sisa = $data->sisa;
			$idbudget = $data->id;
			$upd_terpakai_bulan = ($terpakai_bulan + $nilai - $nilai_pr);
			$upd_terpakai = ($terpakai + $nilai - $nilai_pr);
			$upd_sisa = ($sisa - $nilai + $nilai_pr);
			$this->db->query("update ms_coa_budget set terpakai_bulan_" . $bulan . "=" . $upd_terpakai_bulan . ", terpakai=" . $upd_terpakai . ", sisa=" . $upd_sisa . " where id=" . $idbudget . " and coa='" . $coa . "' and tahun='" . $tahun . "'");
			return true;
		} else {
			return false;
		}
	}

	public function getArray($table, $WHERE = array(), $keyArr = '', $valArr = '')
	{
		if ($WHERE) {
			$query = $this->db->get_where($table, $WHERE);
		} else {
			$query = $this->db->get($table);
		}
		$dataArr	= $query->result_array();

		if (!empty($keyArr)) {
			$Arr_Data	= array();
			foreach ($dataArr as $key => $val) {
				$nilai_id					= $val[$keyArr];
				if (!empty($valArr)) {
					$nilai_val				= $val[$valArr];
					$Arr_Data[$nilai_id]	= $nilai_val;
				} else {
					$Arr_Data[$nilai_id]	= $val;
				}
			}

			return $Arr_Data;
		} else {
			return $dataArr;
		}
	}

	public function get_data_transport_input()
	{
		$draw = $this->input->post('draw');
		$length = $this->input->post('length');
		$start = $this->input->post('start');
		$search = $this->input->post('search');

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.nama, a.keperluan, a.nopol, a.status, (a.bensin + a.tol + a.parkir + a.lainnya) as ttl_transport, a.created_by as nmuser');
		$this->db->from('tr_transport a');
		$this->db->where('a.created_by', $this->auth->user_name());
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->group_by('a.id');
		$this->db->order_by('a.id', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);
		$get_data = $this->db->get()->result();

		$hasil = [];

		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-blue">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';

			if (has_permission('Transportasi.View')) {
				$action .= '<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission('Transportasi.Manage') && $item->status == 0) {
				$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission('Transportasi.Delete') && $item->status == 0) {
				$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'id_pengajuan' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nama,
				'keperluan' => $item->keperluan,
				'no_polisi' => $item->nopol,
				'total' => number_format($item->ttl_transport),
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_transport_req_fin_list()
	{
		$post = $this->input->post();

		$viewPermission 	= 'Pengajuan_Transportasi_Approval.View';
		$addPermission  	= 'Pengajuan_Transportasi_Approval.Add';
		$managePermission = 'Pengajuan_Transportasi_Approval.Manage';
		$deletePermission = 'Pengajuan_Transportasi_Approval.Delete';

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];

		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		// $this->db->where('a.created_by', $this->auth->user_name());
		$this->db->where('a.status', 0);
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.created_by', $search['value'], 'both');
			$this->db->group_end();
		}
		$this->db->order_by('a.no_doc', 'desc');

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-primary">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';
			if (has_permission($viewPermission)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a> <a class="btn btn-warning btn-sm view" href="' . base_url('expense/transport_req_view/' . $item->id . '/_fin') . '" title="View"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && $item->status == 0) {
				$action .= ' <a class="btn btn-success btn-sm approve" href="' . base_url('expense/transport_req_edit/' . $item->id . '/_fin') . '" title="Approve"><i class="fa fa-check-square-o"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'no_transport' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nmuser,
				'status' => $status,
				'action' => $action,
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_transport_req()
	{
		$post = $this->input->post();

		$viewPermission 	= 'Pengajuan_Transportasi_Approval.View';
		$addPermission  	= 'Pengajuan_Transportasi_Approval.Add';
		$managePermission = 'Pengajuan_Transportasi_Approval.Manage';
		$deletePermission = 'Pengajuan_Transportasi_Approval.Delete';

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];

		$this->db->select('a.*, a.created_by as nmuser');
		$this->db->from('tr_transport_req a');
		$this->db->where('a.created_by', $this->auth->user_name());
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.created_by', $search['value'], 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$this->db->limit($length, $start);

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-green">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Disetujui Management</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-primary">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '';
			if (has_permission($viewPermission)) {
				$action .= ' <a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a> <a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(' . $item->id . ')"><i class="fa fa-eye"></i></a>';
			}

			if (has_permission($managePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
			}

			if (has_permission($deletePermission) && ($item->status == 0 || $item->status == 9)) {
				$action .= ' <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(' . $item->id . ')"><i class="fa fa-trash"></i></a>';
			}

			$hasil[] = [
				'no' => $no,
				'no_transport' => $item->no_doc,
				'tanggal' => date('d F Y', strtotime($item->tgl_doc)),
				'nama' => $item->nmuser,
				'total' => number_format($item->jumlah_expense),
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => $count_all,
			'recordsFiltered' => $count_all,
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_transport_req_all()
	{
		$post = $this->input->post();

		$draw = $post['draw'];
		$length = $post['length'];
		$start = $post['start'];
		$search = $post['search'];
		$order = $post['order'];

		$this->db->select('a.id, a.no_doc, a.tgl_doc, a.date1, a.date2, a.jumlah_expense, a.status, a.nama, a.approved_on, a.status');
		$this->db->from('tr_transport_req a');
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search['value'], 'both');
			$this->db->or_like('a.tgl_doc', $search['value'], 'both');
			$this->db->or_like('a.nama', $search['value'], 'both');
			$this->db->or_like('a.approved_on', $search['value'], 'both');
			$this->db->or_like('a.jumlah_expense', $search['value'], 'both');
			$this->db->group_end();
		}

		$db_clone = clone $this->db;
		$count_all = $db_clone->count_all_results();

		$column_order = [
			1 => 'no_doc',
			2 => 'tgl_doc',
			3 => 'nama',
			4 => 'approved_on',
			5 => 'jumlah_expense',
			6 => 'status'
		]; // List of columns to sort by
		$column_index = $order[0]['column']; // Column index from the order parameter
		$column_dir = $order[0]['dir']; // Ascending or Descending direction

		// Apply order by dynamically
		if (isset($column_order[$column_index])) {
			$this->db->order_by($column_order[$column_index], $column_dir);
		} else {
			$this->db->order_by('a.tgl_doc', 'desc');  // Default sorting
		}

		$this->db->limit($length, $start);


		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = (0 + $start);

		foreach ($get_data as $item) {
			$no++;

			$tgl_doc = ($item->tgl_doc !== '0000-00-00') ? date('d F Y', strtotime($item->tgl_doc)) : '';
			$approval_date = ($item->approved_on !== null) ? date('d F Y H:i:s', strtotime($item->approved_on)) : '';

			$status = '<span class="badge bg-yellow">Baru</span>';
			if ($item->status == '1') {
				$status = '<span class="badge bg-blue">Disetujui</span>';
			}
			if ($item->status == '2') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item->status == '3') {
				$status = '<span class="badge bg-green">Selesai</span>';
			}
			if ($item->status == '9') {
				$status = '<span class="badge bg-red">Ditolak</span>';
			}

			$action = '
				<a class="btn btn-default btn-sm print" href="' . base_url('expense/transport_req_print/' . $item->id) . '" target="transport_req_print" title="Print"><i class="fa fa-print"></i> </a>
				<a class="btn btn-warning btn-sm view" href="' . base_url('expense/transport_req_view/' . $item->id . '/_all') . '" title="View"><i class="fa fa-eye"></i></a>
			';

			$hasil[] = [
				'no' => $no,
				'no_doc' => $item->no_doc,
				'tanggal' => $tgl_doc,
				'nama' => $item->nama,
				'approval_date' => $approval_date,
				'total_transport' => number_format($item->jumlah_expense),
				'status' => $status,
				'action' => $action
			];
		}

		$response = [
			'draw' => intval($draw),
			'recordsTotal' => intval($count_all),
			'recordsFiltered' => intval($count_all),
			'data' => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_kasbon()
	{
		$post = $this->input->post();

		$viewPermission   = 'Kasbon.View';
		$managePermission = 'Kasbon.Manage';
		$deletePermission = 'Kasbon.Delete';

		$draw   = isset($post['draw']) ? intval($post['draw']) : 1;
		$length = isset($post['length']) ? intval($post['length']) : 10;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		// 1. Total Data Count
		$this->db->from('tr_kasbon a');
		$count_all = $this->db->count_all_results();

		// 2. Filtered Count & Query
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		$this->db->join('users c', 'c.username = a.created_by OR c.id_user = a.created_by', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.created_by', $search, 'both');
			$this->db->or_like('c.nm_lengkap', $search, 'both');
			$this->db->or_like('a.keperluan', $search, 'both');
			$this->db->group_end();
		}

		$db_filtered = clone $this->db;
		$count_filtered = $db_filtered->count_all_results();

		// 3. Fetch Paginated Records
		$this->db->select('a.*, b.nm_lengkap as nmuser, COALESCE(c.nm_lengkap, c.username, a.created_by) as creator_name');
		
		$columns_order = [
			0 => 'a.id',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'a.jumlah_kasbon',
			5 => 'creator_name',
			6 => 'a.created_on',
			7 => 'a.status'
		];

		$order_col = isset($post['order'][0]['column']) ? intval($post['order'][0]['column']) : 0;
		$order_dir = isset($post['order'][0]['dir']) ? $post['order'][0]['dir'] : 'desc';

		if (isset($columns_order[$order_col])) {
			$this->db->order_by($columns_order[$order_col], $order_dir);
		} else {
			$this->db->order_by('a.id', 'desc');
		}

		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = $start;

		foreach ($get_data as $item) {
			$no++;

			// Determine requester name
			$nmuser = !empty($item->nmuser) ? $item->nmuser : $item->nama;
			$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_doc])->result();
			if (!empty($check_detail)) {
				if ($item->tipe_pr == 'pr departemen') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('rutin_non_planning_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}

				if ($item->tipe_pr == 'pr stok') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('material_planning_base_on_produksi a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}

				if ($item->tipe_pr == 'pr asset') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('tran_pr_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}
			}

			// Created By & Created Date
			$created_by = !empty($item->creator_name) ? $item->creator_name : (!empty($item->created_by) ? $item->created_by : '-');
			$created_date = '-';
			if (!empty($item->created_on) && $item->created_on != '0000-00-00 00:00:00') {
				$created_date = date('d M Y H:i', strtotime($item->created_on));
			} elseif (!empty($item->tgl_doc) && $item->tgl_doc != '0000-00-00') {
				$created_date = date('d M Y', strtotime($item->tgl_doc));
			}

			$tgl_doc = (!empty($item->tgl_doc) && $item->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($item->tgl_doc)) : '-';
			$nominal_kasbon = number_format($item->jumlah_kasbon, 0, ',', '.');

			// Status badge
			$status = '<span class="badge bg-yellow text-light">New</span>';
			if ($item->status == '1' || $item->status == '2') {
				$status = '<span class="badge bg-blue text-light">Approved</span>';
			} elseif ($item->status == '3') {
				$check_expense_report = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $item->no_doc, 'status' => 2])->row();
				if (!empty($check_expense_report)) {
					$status = '<span class="badge bg-navy text-light">Close</span>';
				} else {
					$status = '<span class="badge bg-green text-light">Paid</span>';
				}
			} elseif ($item->status == '4') {
				$status = '<span class="badge bg-purple text-light">Kurang</span>';
			} elseif ($item->status == '9') {
				$status = '<span class="badge bg-red text-light">Reject</span>';
			}

			// Actions
			$action = '';
			if (has_permission($viewPermission) && $item->approved_by !== null) {
				$action .= '<a class="btn btn-default btn-sm print" href="' . base_url('expense/kasbon_print/' . $item->id) . '" target="_blank" title="Print" style="margin-right: 3px;"><i class="fa fa-print"></i></a>';
				$action .= '<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(\'' . $item->id . '\')" style="margin-right: 3px;"><i class="fa fa-eye"></i></a>';
			}
			if (has_permission($managePermission)) {
				if ($item->status == 0 || $item->status == 9) {
					$action .= '<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit(\'' . $item->id . '\')" style="margin-right: 3px;"><i class="fa fa-edit"></i></a>';
				}
			}
			if (has_permission($deletePermission)) {
				if ($item->status == 0 || $item->status == 9) {
					$action .= '<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete(\'' . $item->id . '\')"><i class="fa fa-trash"></i></a>';
				}
			}

			$hasil[] = [
				'no'             => '<div class="text-center">' . $no . '</div>',
				'no_doc'         => '<div class="text-center"><b>' . $item->no_doc . '</b></div>',
				'tgl_doc'        => '<div class="text-center">' . $tgl_doc . '</div>',
				'nama'           => '<div>' . $nmuser . '</div>',
				'nominal_kasbon' => '<div class="text-right" style="font-weight: 600;">' . $nominal_kasbon . '</div>',
				'created_by'     => '<div>' . $created_by . '</div>',
				'created_date'   => '<div class="text-center">' . $created_date . '</div>',
				'status'         => '<div class="text-center">' . $status . '</div>',
				'action'         => '<div class="text-center" style="white-space: nowrap;">' . $action . '</div>'
			];
		}

		$response = [
			'draw'            => intval($draw),
			'recordsTotal'    => intval($count_all),
			'recordsFiltered' => intval($count_filtered),
			'data'            => $hasil
		];

		echo json_encode($response);
	}

	public function get_data_kasbon_all()
	{
		$post = $this->input->post();

		$viewPermission   = 'Kasbon_List.View';
		$managePermission = 'Kasbon_List.Manage';
		$deletePermission = 'Kasbon_List.Delete';

		$draw   = isset($post['draw']) ? intval($post['draw']) : 1;
		$length = isset($post['length']) ? intval($post['length']) : 10;
		$start  = isset($post['start']) ? intval($post['start']) : 0;
		$search = isset($post['search']['value']) ? trim($post['search']['value']) : '';

		// 1. Total Data Count
		$this->db->from('tr_kasbon a');
		$count_all = $this->db->count_all_results();

		// 2. Filtered Count & Query
		$this->db->from('tr_kasbon a');
		$this->db->join('users b', 'a.nama = b.username', 'left');
		$this->db->join('users c', 'c.username = a.created_by OR c.id_user = a.created_by', 'left');

		if (!empty($search)) {
			$this->db->group_start();
			$this->db->like('a.no_doc', $search, 'both');
			$this->db->or_like('a.tgl_doc', $search, 'both');
			$this->db->or_like('a.nama', $search, 'both');
			$this->db->or_like('b.nm_lengkap', $search, 'both');
			$this->db->or_like('a.created_by', $search, 'both');
			$this->db->or_like('c.nm_lengkap', $search, 'both');
			$this->db->or_like('a.keperluan', $search, 'both');
			$this->db->group_end();
		}

		$db_filtered = clone $this->db;
		$count_filtered = $db_filtered->count_all_results();

		// 3. Fetch Paginated Records
		$this->db->select('a.*, b.nm_lengkap as nmuser, COALESCE(c.nm_lengkap, c.username, a.created_by) as creator_name');
		
		$columns_order = [
			0 => 'a.id',
			1 => 'a.no_doc',
			2 => 'a.tgl_doc',
			3 => 'b.nm_lengkap',
			4 => 'a.jumlah_kasbon',
			5 => 'creator_name',
			6 => 'a.created_on',
			7 => 'a.approved_on',
			8 => 'a.status'
		];

		$order_col = isset($post['order'][0]['column']) ? intval($post['order'][0]['column']) : 0;
		$order_dir = isset($post['order'][0]['dir']) ? $post['order'][0]['dir'] : 'desc';

		if (isset($columns_order[$order_col])) {
			$this->db->order_by($columns_order[$order_col], $order_dir);
		} else {
			$this->db->order_by('a.id', 'desc');
		}

		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$get_data = $this->db->get()->result();

		$hasil = [];
		$no = $start;

		foreach ($get_data as $item) {
			$no++;

			// Determine requester name
			$nmuser = !empty($item->nmuser) ? $item->nmuser : $item->nama;
			$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_doc])->result();
			if (!empty($check_detail)) {
				if ($item->tipe_pr == 'pr departemen') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('rutin_non_planning_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}

				if ($item->tipe_pr == 'pr stok') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('material_planning_base_on_produksi a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}

				if ($item->tipe_pr == 'pr asset') {
					$this->db->select('b.nm_lengkap');
					$this->db->from('tran_pr_header a');
					$this->db->join('users b', 'b.id_user = a.created_by');
					$this->db->where('a.no_pr', $item->id_pr);
					$get_single_detail = $this->db->get()->row();
					if (!empty($get_single_detail)) {
						$nmuser = $get_single_detail->nm_lengkap;
					}
				}
			}

			// Created By & Created Date
			$created_by = !empty($item->creator_name) ? $item->creator_name : (!empty($item->created_by) ? $item->created_by : '-');
			$created_date = '-';
			if (!empty($item->created_on) && $item->created_on != '0000-00-00 00:00:00') {
				$created_date = date('d M Y H:i', strtotime($item->created_on));
			} elseif (!empty($item->tgl_doc) && $item->tgl_doc != '0000-00-00') {
				$created_date = date('d M Y', strtotime($item->tgl_doc));
			}

			$tgl_doc = (!empty($item->tgl_doc) && $item->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($item->tgl_doc)) : '-';
			$nominal_kasbon = number_format($item->jumlah_kasbon, 0, ',', '.');
			$approval_date = (!empty($item->approved_on) && $item->approved_on != '0000-00-00 00:00:00') ? date('d M Y H:i', strtotime($item->approved_on)) : '-';

			// Status badge
			$status = '<span class="badge bg-yellow text-light">New</span>';
			if ($item->status == '1' || $item->status == '2') {
				$status = '<span class="badge bg-blue text-light">Approved</span>';
			} elseif ($item->status == '3') {
				$check_expense_report = $this->db->get_where('tr_expense_detail', ['id_kasbon' => $item->no_doc, 'status' => 2])->row();
				if (!empty($check_expense_report)) {
					$status = '<span class="badge bg-navy text-light">Close</span>';
				} else {
					$status = '<span class="badge bg-green text-light">Paid</span>';
				}
			} elseif ($item->status == '4') {
				$status = '<span class="badge bg-purple text-light">Kurang</span>';
			} elseif ($item->status == '9') {
				$status = '<span class="badge bg-red text-light">Reject</span>';
			}

			// Actions
			$action = '';
			if (has_permission($viewPermission)) {
				if ($item->approved_by !== null) {
					$action .= '<a class="btn btn-default btn-sm print" href="' . base_url('expense/kasbon_print/' . $item->id) . '" target="_blank" title="Print" style="margin-right: 3px;"><i class="fa fa-print"></i></a>';
				}
				$action .= '<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view(\'' . $item->id . '\')" style="margin-right: 3px;"><i class="fa fa-eye"></i></a>';
			}

			$hasil[] = [
				'no'             => '<div class="text-center">' . $no . '</div>',
				'no_doc'         => '<div class="text-center"><b>' . $item->no_doc . '</b></div>',
				'tgl_doc'        => '<div class="text-center">' . $tgl_doc . '</div>',
				'nama'           => '<div>' . $nmuser . '</div>',
				'nominal_kasbon' => '<div class="text-right" style="font-weight: 600;">' . $nominal_kasbon . '</div>',
				'created_by'     => '<div>' . $created_by . '</div>',
				'created_date'   => '<div class="text-center">' . $created_date . '</div>',
				'approval_date'  => '<div class="text-center">' . $approval_date . '</div>',
				'status'         => '<div class="text-center">' . $status . '</div>',
				'action'         => '<div class="text-center" style="white-space: nowrap;">' . $action . '</div>'
			];
		}

		$response = [
			'draw'            => intval($draw),
			'recordsTotal'    => intval($count_all),
			'recordsFiltered' => intval($count_filtered),
			'data'            => $hasil
		];

		echo json_encode($response);
	}
}
