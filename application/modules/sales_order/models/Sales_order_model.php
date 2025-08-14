<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Sales_order_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
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

	public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
	{
		if ($where_field != '' && $where_value != '') {
			$query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
		} else {
			$query = $this->db->get($table);
		}

		return $query->result();
	}

	function generate_id($kode = '')
	{
		$query = $this->db->query("SELECT MAX(no_so) as max_id FROM sales_order");
		$row = $query->row_array();
		$thn = date('y');
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 4, 5);
		$counter = $max_id1 + 1;
		$idcust = "SO" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
		return $idcust;
	}

	// SERVERSIDE 
	public function get_json_sales_order()
	{
		$requestData = $_REQUEST;

		$start_date = $this->input->post('start_date'); // 'YYYY-MM-DD' atau ''
		$end_date   = $this->input->post('end_date');   // 'YYYY-MM-DD' atau ''

		$fetch = $this->get_query_json_sales_order(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length'],
			$start_date,
			$end_date
		);

		$totalData = $fetch['totalData'];
		$totalFiltered = $fetch['totalFiltered'];
		$query = $fetch['query'];

		$data = [];
		$urut = 1;

		foreach ($query->result_array() as $row) {
			$nomor = $urut + $requestData['start'];
			$warna = '';
			$status_label = '';
			$action = '';
			$tipe_quot = '';

			if ($row['tipe_penawaran'] === "Dropship") {
				$tipe_quot = "<span class='badge bg-blue'>Dropship</span>";
			} else {
				$tipe_quot = "<span class='badge bg-aqua'>Standard</span>";
			}

			if ($row['status'] === 'A') {
				$action = "<a target='_blank' href='" . base_url("sales_order/print_so/{$row['no_so']}") . "' class='btn btn-sm btn-warning' title='Print SO'><i class='fa fa-print'></i></a> ";
				$action .= "<a href='" . base_url("sales_order/edit/{$row['no_so']}") . "' class='btn btn-sm btn-default' title='View'><i class='fa fa-eye'></i></a> ";
				$status_label = "<span class='badge bg-green'>Deal</span>";

				// Tambahkan status SPK
				if ($row['status_spk'] == 'Belum SPK') {
					$status_label .= " <span class='badge bg-yellow'>Belum SPK</span>";
					$action .= "<a href='" . base_url("spk_delivery/add/{$row['no_so']}") . "' class='btn btn-sm btn-info' title='Create SPK'><i class='fa fa-truck'></i> SPK</a> ";
				} elseif ($row['status_spk'] == 'SPK Sebagian') {
					$status_label .= " <span class='badge bg-orange'>SPK Sebagian</span>";
					$action .= "<a href='" . base_url("spk_delivery/add/{$row['no_so']}") . "' class='btn btn-sm btn-info' title='Create SPK'><i class='fa fa-truck'></i> SPK</a> ";
				} elseif ($row['status_spk'] == 'SPK Lengkap') {
					$status_label .= " <span class='badge bg-blue'>SPK Lengkap</span>";
				}
			} else {
				$action = "<a href='" . base_url("sales_order/add/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success' title='Create SO'><i class='fa fa-paper-plane'></i> SO</a> ";
				$status_label = "<span class='badge bg-grey'>Draft</span>";
			}

			$data_tgl = $row['tgl_so'];
			$tgl_so = ($data_tgl != null) ? date('d/M/Y', strtotime($row['tgl_so'])) : "";

			$nestedData = [];
			$nestedData[] = "<div align='left'>{$nomor}</div>";
			$nestedData[] = "<div align='left'>" . $row['no_so'] . "</div>";
			$nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
			$nestedData[] = "<div align='center'>" . $tgl_so . "</div>";
			$nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
			$nestedData[] = "<div align='left'>" . ucfirst($row['sales']) . "</div>";
			$nestedData[] = "<div align='left'>" . number_format($row['total_penawaran'], 2) . "</div>";
			$nestedData[] = "<div align='left'>" . number_format($row['nilai_so'], 2) . "</div>";
			$nestedData[] = "<div align='center'>" . $row['revisi'] . "</div>";
			$nestedData[] = "<div align='center'>{$tipe_quot}</div>";
			$nestedData[] = "<div align='center'>{$status_label}</div>";
			$nestedData[] = "<div align='center'>{$action}</div>";

			$data[] = $nestedData;
			$urut++;
		}

		echo json_encode([
			"draw"            => intval($requestData['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		]);
	}

	public function get_query_json_sales_order($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null, $start_date = null, $end_date = null)
	{
		$columns_order_by = [
			0 => 'so.no_so',
			1 => 'so.no_so',
			2 => 'p.id_penawaran',
			3 => 'so.tgl_so',       // ← tanggal SO
			4 => 'c.name_customer',
			5 => 'p.sales',
			6 => 'p.total_penawaran',
			7 => 'so.nilai_so',
			8 => 'so.revisi',
			9 => 'p.tipe_penawaran',
			10 => 'so.status'
			// 11 action (dummy)
		];

		// ==== Total data (tanpa search & tanpa date filter) ====
		$this->db->from('penawaran p');
		$this->db->join('sales_order so', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('p.status', 'A');
		$totalData = $this->db->count_all_results();

		// ==== Total filtered (pakai search + date filter) ====
		$this->db->from('penawaran p');
		$this->db->join('sales_order so', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('p.status', 'A');

		// filter tanggal (DATE type)
		if (!empty($start_date) && !empty($end_date)) {
			$this->db->where('so.tgl_so >=', $start_date);
			$this->db->where('so.tgl_so <=', $end_date);
		} elseif (!empty($start_date)) {
			$this->db->where('so.tgl_so >=', $start_date);
		} elseif (!empty($end_date)) {
			$this->db->where('so.tgl_so <=', $end_date);
		}

		if ($like_value) {
			$this->db->group_start();
			$this->db->like('so.no_so', $like_value);
			$this->db->or_like('p.id_penawaran', $like_value);
			$this->db->or_like('c.name_customer', $like_value);
			$this->db->group_end();
		}
		$totalFiltered = $this->db->count_all_results();

		// ==== Ambil data (pakai semua filter) ====
		$this->db->select('so.no_so, so.tgl_so, so.nilai_so, so.status, so.status_do, so.status_planning, so.revisi, so.status_spk,
                       p.id_penawaran, p.total_penawaran, p.tipe_penawaran, p.sales,
                       c.name_customer');
		$this->db->from('penawaran p');
		$this->db->join('sales_order so', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('p.status', 'A');

		if (!empty($start_date) && !empty($end_date)) {
			$this->db->where('so.tgl_so >=', $start_date);
			$this->db->where('so.tgl_so <=', $end_date);
		} elseif (!empty($start_date)) {
			$this->db->where('so.tgl_so >=', $start_date);
		} elseif (!empty($end_date)) {
			$this->db->where('so.tgl_so <=', $end_date);
		}

		if ($like_value) {
			$this->db->group_start();
			$this->db->like('so.no_so', $like_value);
			$this->db->or_like('p.id_penawaran', $like_value);
			$this->db->or_like('c.name_customer', $like_value);
			$this->db->group_end();
		}

		if ($column_order !== null && isset($columns_order_by[$column_order])) {
			$this->db->order_by($columns_order_by[$column_order], $column_dir);
		} else {
			$this->db->order_by('so.tgl_so', 'desc'); // default terbaru
		}

		if ($limit_length != -1) {
			$this->db->limit($limit_length, $limit_start);
		}

		$query = $this->db->get();

		return [
			'totalData'     => $totalData,
			'totalFiltered' => $totalFiltered,
			'query'         => $query
		];
	}
}
