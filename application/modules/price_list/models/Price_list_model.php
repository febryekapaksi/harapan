<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Price_list_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->ENABLE_ADD     = has_permission('Price_List.Add');
		$this->ENABLE_MANAGE  = has_permission('Price_List.Manage');
		$this->ENABLE_VIEW    = has_permission('Price_List.View');
		$this->ENABLE_DELETE  = has_permission('Price_List.Delete');
	}

	public function get_data($table, $where_field = '', $where_value = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_where_array($table, $where)
	{
		if (!empty($where)) {
			$query = $this->db->get_where($table, $where);
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	public function get_json_product_costing()
	{
		$requestData = $_REQUEST;

		$fetch = $this->get_query_json_product_costing(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);

		$totalData = $fetch['totalData'];
		$totalFiltered = $fetch['totalFiltered'];
		$query = $fetch['query'];

		$data = [];
		$urut1 = 1;

		foreach ($query->result_array() as $row) {
			$nomor = $urut1 + $requestData['start'];

			$this->db->where('id_product_costing', $row['id']);
			$kompetitor = $this->db->get('product_costing_kompetitor')->result();

			$kompetitor_list = '';
			if ($kompetitor) {
				foreach ($kompetitor as $k) {
					$kompetitor_list .= "<div><strong>{$k->nama}</strong>: " . number_format($k->harga, 2) . "</div>";
				}
			} else {
				$kompetitor_list = '<div class="text-muted">-</div>';
			}


			$status_label = 'Waiting Approval';
			$warna = 'primary';
			if ($row['status'] == 'A') {
				$status_label = 'Approved';
				$warna = 'green';
			} elseif ($row['status'] == 'R') {
				$status_label = 'Rejected';
				$warna = 'red';
			}

			// Tombol aksi hanya untuk WA dan R
			$action = '';
			if (in_array($row['status'], ['WA', 'R'])) {
				$action .= "<a href='javascript:void(0)' data-id='" . $row['id'] . "' class='btn btn-sm btn-success process' title='Process'><i class='fa fa-check-square-o'></i></a> ";
				// $action .= "<a href='" . site_url($this->uri->segment(1) . '/delete/' . $row['id']) . "' class='btn btn-sm btn-danger' title='Delete' onclick=\"return confirm('Yakin hapus data ini?')\"><i class='fa fa-trash'></i></a>";
			}

			$nestedData = [];
			$nestedData[] = "<div align='center'>{$nomor}</div>";
			$nestedData[] = "<div align='left'>" . strtoupper($row['nama_level1']) . "</div>";
			$nestedData[] = "<div align='left'>" . strtoupper($row['product_name']) . "</div>";
			$nestedData[] = "<div align='right'>" . number_format($row['price'], 2) . "</div>";
			$nestedData[] = "<div align='right'>" . number_format($row['propose_price'], 2) . "</div>";
			$nestedData[] = $kompetitor_list;
			$nestedData[] = "<span class='badge bg-{$warna}'>{$status_label}</span>";
			// $nestedData[] = htmlspecialchars($row['reason']);
			$nestedData[] = "<div align='center'>{$action}</div>";

			$data[] = $nestedData;
			$urut1++;
		}

		$json_data = [
			"draw"            => intval($requestData['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		];

		echo json_encode($json_data);
	}

	public function get_query_json_product_costing($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
	{
		// Daftar kolom yang bisa diurutkan (disesuaikan dengan DataTables frontend)
		$columns_order_by = [
			0 => 'pc.id',
			1 => 'ni1.nama',
			2 => 'pc.product_name',
			3 => 'pc.price',
			4 => 'pc.propose_price',
			5 => 'pc.status',
		];

		// =====================
		// 1. Hitung totalData
		// =====================
		$this->db->select('pc.id');
		$this->db->from('product_costing pc');
		$this->db->join('new_inventory_1 ni1', 'pc.code_lv1 = ni1.code_lv1', 'left');
		// $this->db->where('pc.deleted_at IS NULL'); // opsional: jika soft delete
		$totalData = $this->db->count_all_results();

		// ============================
		// 2. Hitung totalFiltered
		// ============================
		$this->db->select('pc.id');
		$this->db->from('product_costing pc');
		$this->db->join('new_inventory_1 ni1', 'pc.code_lv1 = ni1.code_lv1', 'left');

		if ($like_value) {
			$this->db->group_start();
			$this->db->like('pc.product_name', $like_value);
			$this->db->or_like('ni1.nama', $like_value);
			$this->db->group_end();
		}

		$totalFiltered = $this->db->count_all_results();

		// ============================
		// 3. Ambil data paginasi
		// ============================
		$this->db->select('pc.*, ni1.nama AS nama_level1');
		$this->db->from('product_costing pc');
		$this->db->join('new_inventory_1 ni1', 'pc.code_lv1 = ni1.code_lv1', 'left');

		if ($like_value) {
			$this->db->group_start();
			$this->db->like('pc.product_name', $like_value);
			$this->db->or_like('ni1.nama', $like_value);
			$this->db->group_end();
		}

		if ($column_order !== null && isset($columns_order_by[$column_order])) {
			$this->db->order_by($columns_order_by[$column_order], $column_dir);
		} else {
			$this->db->order_by('pc.created_at', 'desc');
		}

		if ($limit_length != -1) {
			$this->db->limit($limit_length, $limit_start);
		}

		$query = $this->db->get();

		// ============================
		// 4. Return hasil
		// ============================
		return [
			'totalData' => $totalData,
			'totalFiltered' => $totalFiltered,
			'query' => $query
		];
	}
}
