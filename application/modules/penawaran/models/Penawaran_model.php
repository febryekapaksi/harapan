<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Penawaran_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Penawaran.Add');
        $this->ENABLE_MANAGE  = has_permission('Penawaran.Manage');
        $this->ENABLE_VIEW    = has_permission('Penawaran.View');
        $this->ENABLE_DELETE  = has_permission('Penawaran.Delete');
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

    function generate_id($kode = '')
    {
        $query = $this->db->query("SELECT MAX(id_penawaran) as max_id FROM penawaran");
        $row = $query->row_array();
        $thn = date('y');
        $max_id = $row['max_id'];
        $max_id1 = (int) substr($max_id, 4, 5);
        $counter = $max_id1 + 1;
        $idcust = "QU-" . $thn . str_pad($counter, 5, "0", STR_PAD_LEFT);
        return $idcust;
    }

    // SERVERSIDE 
    public function get_json_penawaran()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_penawaran(
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

            // Warna status
            if ($row['status'] == 'WA') {
                if ($row['level_approval'] == 'M') {
                    $status_label = 'Waiting Approval Manager';
                    $warna = 'secondary';
                } else if ($row['level_approval'] == 'D') {
                    $status_label = 'Waiting Approval Direksi';
                    $warna = 'secondary';
                }
            } elseif ($row['status'] == 'R') {
                $status_label = 'Rejected';
                $warna = 'red';
            } else if ($row['status'] == 'A') {
                $status_label = 'Approved';
                $warna = 'green';
            }

            // Aksi tombol
            $action = "<a href='" . base_url("penawaran/edit/{$row['id_penawaran']}") . "' class='btn btn-sm btn-primary'><i class='fa fa-edit'></i></a> ";
            $action .= "<a href='javascript:void(0)' class='btn btn-sm btn-danger delete' data-id='{$row['id_penawaran']}'><i class='fa fa-trash'></i></a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . date('d-M-Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='left'>" . number_format($row['total_penawaran'], 2) . "</div>";
            $nestedData[] = "<div align='center'>" . $row['revisi'] . "</div>";
            $nestedData[] = "<div align='center'><span class='badge bg-{$warna}'>{$status_label}</span></div>";
            $nestedData[] = "<div align='center'>{$action}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        echo json_encode($json_data);
    }

    public function get_query_json_penawaran($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $this->db->start_cache();

        $this->db->select('p.id_penawaran, p.quotation_date, p.revisi, p.status, p.level_approval, p.total_penawaran, c.name_customer');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        $this->db->stop_cache();

        $totalData = $this->db->count_all_results('', false);
        $totalFiltered = $totalData;

        $columns_order_by = [
            0 => 'p.quotation_date',
            1 => 'p.quotation_date',
            2 => 'c.name_customer',
            3 => 'p.id_penawaran',
            4 => 'p.revisi',
            5 => 'p.status'
        ];

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('p.created_at', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();
        $this->db->flush_cache();

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }

    public function get_json_approval_manager()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_approval_manager(
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

            // Warna status
            $status_label = 'Waiting Approval Manager';
            $warna = 'secondary';
            if ($row['status'] == 'A') {
                $status_label = 'Approved';
                $warna = 'green';
            } elseif ($row['status'] == 'R') {
                $status_label = 'Rejected';
                $warna = 'danger';
            }

            // Aksi tombol
            $action = "<a href='" . base_url("penawaran/approve_manager/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success'><i class='fa fa-check-square-o'></i></a> ";
            // $action .= "<a href='javascript:void(0)' class='btn btn-sm btn-danger btn-reject' data-id='{$row['id_penawaran']}'><i class='fa fa-times'></i> Reject</a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . date('d/m/Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
            // $nestedData[] = "<div align='center'>" . $row['revisi'] . "</div>";
            $nestedData[] = "<div align='center'><span class='badge bg-{$warna}'>{$status_label}</span></div>";
            $nestedData[] = "<div align='center'>{$action}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        echo json_encode($json_data);
    }

    public function get_query_json_approval_manager($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $this->db->select('p.id_penawaran, p.quotation_date, p.revisi, p.status, p.level_approval, c.name_customer');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'WA');
        $this->db->where('p.level_approval IS NOT NULL', null, false);
        $this->db->where('p.approved_by_manager IS NULL', null, false);

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null) {
            $columns_order_by = [
                0 => 'p.quotation_date',
                1 => 'p.quotation_date',
                2 => 'c.name_customer',
                3 => 'p.id_penawaran',
                4 => 'p.revisi',
                5 => 'p.status'
            ];

            if (isset($columns_order_by[$column_order])) {
                $this->db->order_by($columns_order_by[$column_order], $column_dir);
            } else {
                $this->db->order_by('p.created_at', 'desc');
            }
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        $totalData = $query->num_rows();
        $totalFiltered = $totalData;

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }


    public function get_json_approval_direksi()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_approval_direksi(
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

            if ($row['status'] == 'WA') {
                if ($row['level_approval'] == 'D') {
                    $status_label = 'Waiting Approval Direksi';
                    $warna = 'secondary';
                }
            } elseif ($row['status'] == 'R') {
                $status_label = 'Rejected';
                $warna = 'danger';
            } else if ($row['status'] == 'A') {
                $status_label = 'Approved';
                $warna = 'success';
            }

            $action = "<a href='" . base_url("penawaran/approve_direksi/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success'><i class='fa fa-check-square-o'></i></a> ";
            // $action .= "<a href='javascript:void(0)' class='btn btn-sm btn-danger btn-reject' data-id='{$row['id_penawaran']}'><i class='fa fa-times'></i> Reject</a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . date('d/m/Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
            // $nestedData[] = "<div align='center'>" . $row['revisi'] . "</div>";
            $nestedData[] = "<div align='center'><span class='badge bg-{$warna}'>{$status_label}</span></div>";
            $nestedData[] = "<div align='center'>{$action}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        echo json_encode($json_data);
    }

    public function get_query_json_approval_direksi($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $this->db->select('p.id_penawaran, p.quotation_date, p.revisi, p.status, p.level_approval, c.name_customer');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'WA');
        $this->db->where('p.level_approval', 'D');
        $this->db->where('p.approved_by_manager IS NOT NULL', null, false);

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null) {
            $columns_order_by = [
                0 => 'p.quotation_date',
                1 => 'p.quotation_date',
                2 => 'c.name_customer',
                3 => 'p.id_penawaran',
                4 => 'p.revisi',
                5 => 'p.status'
            ];

            if (isset($columns_order_by[$column_order])) {
                $this->db->order_by($columns_order_by[$column_order], $column_dir);
            } else {
                $this->db->order_by('p.created_at', 'desc');
            }
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        $totalData = $query->num_rows();
        $totalFiltered = $totalData;

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }
}
