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
        $result_data   = $query->result_array();

        // Mapping no_delivery dari detail
        $mapDelivery = [];
        if (!empty($result_data)) {
            $ids = array_column($result_data, 'id');
            $details = $this->db->select('no_loading, GROUP_CONCAT(DISTINCT no_delivery SEPARATOR ",") as deliveries')
                ->from('loading_delivery_detail')
                ->where_in('no_loading', array_column($result_data, 'no_loading'))
                ->group_by('no_loading')
                ->get()->result_array();

            foreach ($details as $d) {
                $mapDelivery[$d['no_loading']] = explode(',', $d['deliveries']);
            }
        }

        $data  = [];
        $urut  = 1;

        foreach ($result_data as $row) {
            $nestedData = [];

            // Buat status muatan
            if ($row['status'] == 0) {
                $status = "<span class='badge bg-yellow'>Draft</span>";
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                $action .= "<a href='"  . base_url("loading/confirm_qty/{$row['id']}") .  "' class='btn btn-sm btn-info' title='Confirm Qty'><i class='fa fa-cubes'></i></a> ";
            } else if ($row['status'] == 1) {
                $status = "<span class='badge bg-aqua'>Confirm QTY</span>";
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
                $action .= "<a href='"  . base_url("loading/confirm_berat/{$row['id']}") .  "' class='btn btn-sm btn-success' title='Confirm Berat'><i class='fa fa-tachometer'></i></a> ";
            } else if ($row['status'] == 2) {
                $status = "<span class='badge bg-blue'>Confirm Berat</span>";
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
            } else {
                $status = "<span class='badge bg-green'>Approved</span>";
                $action = "<a target='_blank' href='"  . base_url("loading/print/{$row['id']}") .  "' class='btn btn-sm btn-warning' title='Print'><i class='fa fa-print'></i></a> ";
            }

            $nestedData[] = "<div>" . $urut . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['no_loading']) . "</div>";

            // Tambahkan list no_delivery sebagai <ul>
            if (!empty($mapDelivery[$row['no_loading']])) {
                $ul = "<ul style='padding-left:16px;margin:0'>";
                foreach ($mapDelivery[$row['no_loading']] as $spk) {
                    $ul .= "<li>" . htmlspecialchars($spk) . "</li>";
                }
                $ul .= "</ul>";
                $nestedData[] = $ul;
            } else {
                $nestedData[] = '-';
            }

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
        // Whitelist kolom utk ORDER BY (gunakan alias yg tersedia di SELECT)
        $columns_order_by = [
            0 => 'l.no_loading',
            1 => 'l.no_loading',
            2 => 's.list_spk',     // list_spk berasal dari subquery alias s
            3 => 'l.nopol',
            4 => 'l.pengiriman',
            5 => 'l.total_berat',
            6 => 'l.tanggal_muat',
        ];

        // Normalisasi input
        $order_col = isset($columns_order_by[$column_order]) ? $columns_order_by[$column_order] : 'l.no_loading';
        $order_dir = (strtoupper($column_dir) === 'ASC') ? 'ASC' : 'DESC';

        $limit_start  = is_null($limit_start)  ? 0  : (int)$limit_start;
        $limit_length = is_null($limit_length) ? 10 : (int)$limit_length;

        // ---------------------------
        // Subquery agregasi untuk list_spk
        // ---------------------------
        $sub_list_spk = "
        SELECT
            d.no_loading,
            GROUP_CONCAT(DISTINCT d.no_delivery ORDER BY d.no_delivery SEPARATOR '<br>') AS list_spk
        FROM loading_delivery_detail d
        GROUP BY d.no_loading
    ";

        // ---------------------------
        // WHERE (filter pencarian)
        // Pakai parameter binding, termasuk EXISTS utk cari di no_delivery
        // ---------------------------
        $where_sql = " WHERE 1=1 ";
        $params = [];

        if (!empty($like_value)) {
            $like = '%' . $this->db->escape_like_str($like_value) . '%';

            // Cari di kolom utama
            $where_sql .= " AND ( 
              l.no_loading   LIKE ? 
           OR l.pengiriman   LIKE ? 
           OR l.nopol        LIKE ?
           OR EXISTS (
                SELECT 1 
                FROM loading_delivery_detail dd 
                WHERE dd.no_loading = l.no_loading 
                  AND dd.no_delivery LIKE ?
           )
        )";
            // Bind 4 parameter
            array_push($params, $like, $like, $like, $like);
        }

        // ---------------------------
        // COUNT total (tanpa filter)
        // ---------------------------
        $sql_count_total = "SELECT COUNT(*) AS cnt FROM loading_delivery l";
        $totalData = (int) $this->db->query($sql_count_total)->row()->cnt;

        // ---------------------------
        // COUNT filtered (dengan filter)
        // ---------------------------
        $sql_count_filtered = "
        SELECT COUNT(*) AS cnt
        FROM loading_delivery l
        LEFT JOIN ( $sub_list_spk ) s ON s.no_loading = l.no_loading
        $where_sql
    ";
        $totalFiltered = (int) $this->db->query($sql_count_filtered, $params)->row()->cnt;

        // ---------------------------
        // DATA utama (tanpa GROUP BY di luar)
        // ---------------------------
        $sql_data = "
        SELECT
            l.id,
            l.no_loading,
            l.pengiriman,
            l.nopol,
            l.kapasitas,
            l.total_berat,
            l.tanggal_muat,
            l.status,
            COALESCE(s.list_spk, '') AS list_spk
        FROM loading_delivery l
        LEFT JOIN ( $sub_list_spk ) s ON s.no_loading = l.no_loading
        $where_sql
        ORDER BY $order_col $order_dir
    ";

        // Limit (ikuti pola datatables: -1 berarti tanpa limit)
        if ($limit_length != -1) {
            $sql_data .= " LIMIT ?, ? ";
            $params_data = array_merge($params, [$limit_start, $limit_length]);
        } else {
            $params_data = $params;
        }

        $query = $this->db->query($sql_data, $params_data);

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $query,
        ];
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
