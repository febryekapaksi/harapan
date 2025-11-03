<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Approval_penawaran_direksi_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Approval_Penawaran_Direksi.Add');
        $this->ENABLE_MANAGE  = has_permission('Approval_Penawaran_Direksi.Manage');
        $this->ENABLE_VIEW    = has_permission('Approval_Penawaran_Direksi.View');
        $this->ENABLE_DELETE  = has_permission('Approval_Penawaran_Direksi.Delete');
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

            $action = "<a href='" . base_url("approval_penawaran_direksi/approval/{$row['id_penawaran']}") . "' class='btn btn-sm btn-success'><i class='fa fa-check-square-o'></i></a> ";
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
}
