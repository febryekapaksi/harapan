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

    public function generate_id()
    {
        $prefix = 'QU';
        $yy = date('y');
        $lock = "penawaran_{$prefix}_{$yy}";

        $this->db->query("SELECT GET_LOCK(?, 5) AS l", [$lock]);

        $row = $this->db->query(
            "SELECT RIGHT(id_penawaran,5) AS kode
         FROM penawaran
         WHERE id_penawaran LIKE ?
         ORDER BY id_penawaran DESC LIMIT 1",
            [$prefix . $yy . '%']
        )->row();

        $next = $row ? (intval($row->kode) + 1) : 1;
        $id   = $prefix . $yy . str_pad($next, 5, '0', STR_PAD_LEFT);

        while ($this->db->where('id_penawaran', $id)->count_all_results('penawaran') > 0) {
            $next++;
            $id = $prefix . $yy . str_pad($next, 5, '0', STR_PAD_LEFT);
        }

        $this->db->query("SELECT RELEASE_LOCK(?)", [$lock]);

        return $id;
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

            $action = '';
            $warna = '';
            $status_label = '';

            // Warna status dan tombol action
            if ($row['status'] == 'WA') {
                if ($row['status_draft'] == 0) {
                    $status_label = 'Draft';
                    $warna = 'secondary';

                    $action = "<a href='" . base_url("penawaran/edit/{$row['id_penawaran']}") . "' class='btn btn-sm btn-primary' title='Edit'><i class='fa fa-edit'></i></a> ";
                    // $action .= "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-info btn-request' title='Request Approval'><i class='fa fa-check'></i></a> ";
                    $action .= "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-danger btn-loss' title='Loss Penawaran'><i class='fa fa-times'></i></a> ";
                } else if ($row['status_draft'] == 1) {
                    if ($row['level_approval'] == 'D' && $row['approved_by_manager'] != null) {
                        $status_label = 'Waiting Approval Direksi';
                        $warna = 'secondary';

                        $action = "<a target='_blank' href='" . base_url("penawaran/print_penawaran/{$row['id_penawaran']}") . "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                        $action .= "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-danger btn-loss' title='Loss Penawaran'><i class='fa fa-times'></i></a> ";
                    } else if ($row['level_approval'] == 'M') {
                        $status_label = 'Waiting Approval Manager';
                        $warna = 'secondary';

                        $action = "<a target='_blank' href='" . base_url("penawaran/print_penawaran/{$row['id_penawaran']}") . "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                        $action .= "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-danger btn-loss' title='Loss Penawaran'><i class='fa fa-times'></i></a> ";
                    }
                }
            } else if ($row['status'] == 'R') {
                $status_label = 'Rejected';
                $warna = 'red';

                $action = "<a href='" . base_url("penawaran/edit/{$row['id_penawaran']}") . "' class='btn btn-sm btn-primary' title='Edit'><i class='fa fa-edit'></i></a> ";
            } else if ($row['status'] == 'A') {
                if ($row['no_so'] != null) {
                    $status_label = 'SO Dibuat';
                    $warna = 'blue';

                    $action = "<a target='_blank' href='" . base_url("penawaran/print_penawaran/{$row['id_penawaran']}") . "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                } else {
                    $status_label = 'Approved';
                    $warna = 'green';

                    $action = "<a target='_blank' href='" . base_url("penawaran/print_penawaran/{$row['id_penawaran']}") . "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                    $action .= "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-danger btn-loss' title='Loss Penawaran'><i class='fa fa-times'></i></a> ";
                }
            }

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
        $columns_order_by = [
            0 => 'p.quotation_date',
            1 => 'p.quotation_date',
            2 => 'c.name_customer',
            3 => 'p.id_penawaran',
            4 => 'p.revisi',
            5 => 'p.status'
        ];

        // =====================
        // 1. Hitung totalData
        // =====================
        $this->db->select('p.id_penawaran');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->join('sales_order so', 'p.id_penawaran = so.id_penawaran', 'left'); // tambahkan join ini
        $this->db->where('p.status !=', 'L');
        $totalData = $this->db->count_all_results();

        // ============================
        // 2. Hitung totalFiltered
        // ============================
        $this->db->select('p.id_penawaran');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->join('sales_order so', 'p.id_penawaran = so.id_penawaran', 'left');
        $this->db->where('p.status !=', 'L');


        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // ============================
        // 3. Ambil data paginasi
        // ============================
        $this->db->select('
        p.id_penawaran,
        p.quotation_date,
        p.revisi,
        p.status,
        p.approved_by_manager,
        p.level_approval,
        p.total_penawaran,
        p.status_draft,
        c.name_customer,
        so.no_so
    ');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->join('sales_order so', 'p.id_penawaran = so.id_penawaran', 'left');
        $this->db->where('p.status !=', 'L');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('p.quotation_date', 'desc');
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

            if ($row['tipe_penawaran'] === "Dropship") {
                $tipe_quot = "<span class='badge bg-blue'>Dropship</span>";
            } else {
                $tipe_quot = "<span class='badge bg-aqua'>Standard</span>";
            }

            // Aksi tombol
            $action = "<a href='" . base_url("penawaran/approve_manager/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success'><i class='fa fa-check-square-o'></i></a> ";
            // $action .= "<a href='javascript:void(0)' class='btn btn-sm btn-danger btn-reject' data-id='{$row['id_penawaran']}'><i class='fa fa-times'></i> Reject</a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . date('d/m/Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
            $nestedData[] = "<div align='center'>{$tipe_quot}</div>";
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
        $this->db->select('p.id_penawaran, p.quotation_date, p.revisi, p.status, p.level_approval, p.tipe_penawaran, c.name_customer');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'WA');
        $this->db->where('p.status_draft', 1);
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

            if ($row['tipe_penawaran'] === "Dropship") {
                $tipe_quot = "<span class='badge bg-blue'>Dropship</span>";
            } else {
                $tipe_quot = "<span class='badge bg-aqua'>Standard</span>";
            }

            $action = "<a href='" . base_url("penawaran/approve_direksi/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success'><i class='fa fa-check-square-o'></i></a> ";
            // $action .= "<a href='javascript:void(0)' class='btn btn-sm btn-danger btn-reject' data-id='{$row['id_penawaran']}'><i class='fa fa-times'></i> Reject</a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . date('d/m/Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
            $nestedData[] = "<div align='center'>{$tipe_quot}</div>";
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
        $this->db->select('p.id_penawaran, p.quotation_date, p.revisi, p.status, p.level_approval, p.tipe_penawaran, c.name_customer');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'WA');
        $this->db->where('p.status_draft', 1);
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

    public function get_json_loss_penawaran()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_loss_penawaran(
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
            $status_label = "<span class='badge bg-red'>Loss</span>";
            $action = "<a href='javascript:void(0)' data-id='{$row['id_penawaran']}' class='btn btn-sm btn-info' title='Detail'><i class='fa fa-search'></i></a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>{$row['id_penawaran']}</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div align='left'>" . strtoupper($row['sales']) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['total_penawaran'], 2) . "</div>";
            $nestedData[] = "<div align='left'>" . date('d-M-Y', strtotime($row['quotation_date'])) . "</div>";
            $nestedData[] = "<div align='center'>{$status_label}</div>";
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

    public function get_query_json_loss_penawaran($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $columns_order_by = [
            0 => 'p.quotation_date',
            1 => 'p.id_penawaran',
            2 => 'c.name_customer',
            3 => 'p.sales',
            4 => 'p.total_penawaran',
            5 => 'p.quotation_date',
            6 => 'p.status'
        ];

        // 1. Total Data
        $this->db->select('p.id_penawaran');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'L');
        $totalData = $this->db->count_all_results();

        // 2. Total Filtered
        $this->db->select('p.id_penawaran');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'L');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // 3. Data
        $this->db->select('
        p.id_penawaran,
        p.quotation_date,
        p.total_penawaran,
        c.name_customer,
        p.sales
    ');
        $this->db->from('penawaran p');
        $this->db->join('master_customers c', 'p.id_customer = c.id_customer', 'left');
        $this->db->where('p.status', 'L');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('p.id_penawaran', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->or_like('u.name_user', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('p.created_at', 'desc');
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
