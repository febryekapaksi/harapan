<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Loading_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Loading.Add');
        $this->ENABLE_MANAGE  = has_permission('Loading.Manage');
        $this->ENABLE_VIEW    = has_permission('Loading.View');
        $this->ENABLE_DELETE  = has_permission('Loading.Delete');
    }

    public function data_side_loading()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_loading(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data  = [];
        $urut  = 1;

        foreach ($query->result_array() as $row) {
            $nestedData = [];

            // $action = "<a href='javascript:void(0);' data-id='" . $row['no_loading'] . "' class='btn btn-sm btn-info view-loading' title='View'><i class='fa fa-eye'></i></a> ";
            if ($row['status'] == 0) {
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                $action .= "<a href='"  . base_url("loading/confirm_qty/{$row['id']}") .  "' class='btn btn-sm btn-info' title='Confirm Qty'><i class='fa fa-cubes'></i></a> ";
            } else if ($row['status'] == 1) {
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                $action .= "<a href='"  . base_url("loading/confirm_berat/{$row['id']}") .  "' class='btn btn-sm btn-success' title='Confirm Berat'><i class='fa fa-tachometer'></i></a> ";
            } else if ($row['status'] == 2) {
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
            } else {
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
            }

            // Buat status muatan 
            if ($row['status'] == 0) {
                $status = "<span class='badge bg-yellow'>Draft</span>";
            } else if ($row['status'] == 1) {
                $status = "<span class='badge bg-aqua'>Confirm QTY</span>";
            } else if ($row['status'] == 2) {
                $status = "<span class='badge bg-blue'>Confirm Berat</span>";
            } else {
                $status = "<span class='badge bg-green'>Approved</span>";
            }

            $nestedData[] = "<div>" . $urut . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['no_loading']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nopol']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['pengiriman']) . "</div>";
            $nestedData[] = "<div>" . number_format($row['total_berat'], 2) . " / " . number_format($row['kapasitas'], 2) . " Kg</div>";
            $nestedData[] = "<div>" . date('d/M/Y', strtotime($row['tanggal_muat'])) . "</div>";

            $nestedData[] = "<div align='center'>" . $status . "</div>";
            $nestedData[] = "<div align='center'>" . $action . "</div>";

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


    public function get_query_json_loading($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "SELECT
                (@row:=@row+1) AS nomor,
                id,
                no_loading,
                pengiriman,
                nopol,
                kapasitas,
                total_berat,
                tanggal_muat,
                status,
                created_by,
                created_at
            FROM loading_delivery, (SELECT @row := 0) AS r
            WHERE 1=1 AND (
                no_loading LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR pengiriman LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR nopol LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )";

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = [
            0 => 'no_loading',
            1 => 'nopol',
            2 => 'pengiriman',
            3 => 'total_berat',
            4 => 'tanggal_muat',
        ];

        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        $sql .= " LIMIT " . $limit_start . ", " . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }

    public function data_side_approval_loading()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_approval_loading(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data  = [];
        $urut  = 1;

        $action = '';
        $status = '';

        foreach ($query->result_array() as $row) {
            $nestedData = [];

            $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
            $action .= "<a href='"  . base_url("loading/approval/{$row['id']}") .  "' class='btn btn-sm btn-success' title='Approval'><i class='fa fa-check-square-o'></i></a> ";

            // Buat status muatan 
            $status = "<span class='badge bg-secondary'>Waiting Approval</span>";

            $nestedData[] = "<div>" . $urut . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['no_loading']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nopol']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['pengiriman']) . "</div>";
            $nestedData[] = "<div>" . number_format($row['total_berat'], 2) . " / " . number_format($row['kapasitas'], 2) . " Kg</div>";
            $nestedData[] = "<div>" . date('d/M/Y', strtotime($row['tanggal_muat'])) . "</div>";

            $nestedData[] = "<div align='center'>" . $status . "</div>";
            $nestedData[] = "<div align='center'>" . $action . "</div>";

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


    public function get_query_json_approval_loading($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $sql = "SELECT
            (@row:=@row+1) AS nomor,
            id,
            no_loading,
            pengiriman,
            nopol,
            kapasitas,
            total_berat,
            tanggal_muat,
            status,
            created_by,
            created_at
        FROM loading_delivery, (SELECT @row := 0) AS r
        WHERE status = 2 AND (
            no_loading LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            OR pengiriman LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            OR nopol LIKE '%" . $this->db->escape_like_str($like_value) . "%'
        )";

        $data['totalData'] = $this->db->query($sql)->num_rows();
        $data['totalFiltered'] = $this->db->query($sql)->num_rows();

        $columns_order_by = [
            0 => 'no_loading',
            1 => 'nopol',
            2 => 'pengiriman',
            3 => 'total_berat',
            4 => 'tanggal_muat',
        ];

        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        $sql .= " LIMIT " . $limit_start . ", " . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
