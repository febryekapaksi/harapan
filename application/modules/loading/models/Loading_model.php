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

            $action = "<a href='javascript:void(0);' data-id='" . $row['no_loading'] . "' class='btn btn-sm btn-success view-loading'><i class='fa fa-eye'></i> View</a> ";

            $nestedData[] = "<div>" . $urut . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['no_loading']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nopol']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['pengiriman']) . "</div>";
            $nestedData[] = "<div>" . number_format($row['total_berat'], 2) . " / " . number_format($row['kapasitas'], 2) . " Kg</div>";

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
                no_loading,
                pengiriman,
                nopol,
                kapasitas,
                total_berat,
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
        ];

        $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir;
        $sql .= " LIMIT " . $limit_start . ", " . $limit_length;

        $data['query'] = $this->db->query($sql);
        return $data;
    }
}
