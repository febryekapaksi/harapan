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

		$fetch = $this->get_query_json_sales_order(
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
				$status_label = "<span class='badge bg-green'>Deal</span>";

				// Tambahkan status SPK
				if ($row['status_spk'] == 'Belum SPK') {
					$status_label .= " <span class='badge bg-yellow'>Belum SPK</span>";
					$action .= "<a href='" . base_url("spk_delivery/add/{$row['no_so']}") . "' class='btn btn-sm btn-success' title='Create SPK'><i class='fa fa-paper-plane'></i> SPK</a> ";
				} elseif ($row['status_spk'] == 'SPK Sebagian') {
					$status_label .= " <span class='badge bg-orange'>SPK Sebagian</span>";
					$action .= "<a href='" . base_url("spk_delivery/add/{$row['no_so']}") . "' class='btn btn-sm btn-success' title='Create SPK'><i class='fa fa-paper-plane'></i> SPK</a> ";
				} elseif ($row['status_spk'] == 'SPK Lengkap') {
					$status_label .= " <span class='badge bg-blue'>SPK Lengkap</span>";
				}
			} else {
				$action = "<a href='" . base_url("sales_order/edit/{$row['no_so']}") . "' class='btn btn-sm btn-primary' title='Edit SO'><i class='fa fa-edit'></i></a> ";
				$action .= "<a href='" . base_url("sales_order/deal/{$row['no_so']}") . "' class='btn btn-sm btn-success' title='Deal SO'><i class='fa fa-check'></i></a> ";
				$status_label = "<span class='badge bg-grey'>Draft</span>";
			}

			$nestedData = [];
			$nestedData[] = "<div align='left'>{$nomor}</div>";
			$nestedData[] = "<div align='left'>" . $row['no_so'] . "</div>";
			$nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
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


	public function get_query_json_sales_order($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
	{
		$columns_order_by = [
			0 => 'so.no_so',
			1 => 'so.no_so',
			2 => 'p.id_penawaran',
			3 => 'c.name_customer',
			4 => 'p.sales',
			5 => 'p.total_penawaran',
			6 => 'so.nilai_so',
			7 => 'so.revisi',
			8 => 'so.status'
		];

		// Total data
		$this->db->from('sales_order so');
		$this->db->join('penawaran p', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('so.no_so IS NOT NULL');
		$totalData = $this->db->count_all_results();

		// Total filtered
		$this->db->from('sales_order so');
		$this->db->join('penawaran p', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('so.no_so IS NOT NULL');

		if ($like_value) {
			$this->db->group_start();
			$this->db->like('so.no_so', $like_value);
			$this->db->or_like('p.id_penawaran', $like_value);
			$this->db->or_like('c.name_customer', $like_value);
			$this->db->group_end();
		}

		$totalFiltered = $this->db->count_all_results();

		// Ambil data
		$this->db->select('so.no_so, so.nilai_so, so.status, so.status_do, so.status_planning, p.id_penawaran, p.total_penawaran, p.tipe_penawaran, so.revisi, so.status_spk, p.sales, c.name_customer');
		$this->db->from('sales_order so');
		$this->db->join('penawaran p', 'so.id_penawaran = p.id_penawaran', 'left');
		$this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
		$this->db->where('so.no_so IS NOT NULL');

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
			$this->db->order_by('so.created_at', 'desc');
		}

		if ($limit_length != -1) {
			$this->db->limit($limit_length, $limit_start);
		}

		$query = $this->db->get();

		return [
			'totalData' => $totalData,
			'totalFiltered' => $totalFiltered,
			'query' => $query
		];
	}
}
