<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Master_komisi_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Master_Komisi.Add');
        $this->ENABLE_MANAGE  = has_permission('Master_Komisi.Manage');
        $this->ENABLE_VIEW    = has_permission('Master_Komisi.View');
        $this->ENABLE_DELETE  = has_permission('Master_Komisi.Delete');
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

    public function get_json_komisi($bulan_filter = null)
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_komisi(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length'],
            $bulan_filter // <- parameter ke-6, harus ada di definisi fungsi
        );

        $totalData = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query = $fetch['query'];

        $data = [];
        $urut = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut + $requestData['start'];

            $action = "<a href='javascript:void(0)' data-id='{$row['id']}' class='btn btn-sm btn-primary edit'><i class='fa fa-edit'></i></a> ";
            $action .= "<a class='btn btn-danger btn-sm delete' data-id='{$row['id']} href='javascript:void(0)' title='Delete'><i class='fa fa-trash'></i></a>";

            $nestedData = [];
            $nestedData[] = "<div align='left'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>" . ucfirst($row['nm_karyawan']) . "</div>";
            $nestedData[] = "<div align='left'>{$row['bulan']}</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['persentase_ontime'], 2) . "%</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['persentase_tunggakan'], 2) . "%</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['persentase_penjualan'], 2) . "%</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['grand_total'], 2) . "</div>";
            $nestedData[] = "<div align='center'>{$action}</div>";

            $data[] = $nestedData;
            $urut++;
        }

        $json_data = [
            "draw" => intval($requestData['draw']),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ];

        echo json_encode($json_data);
    }

    public function get_query_json_komisi($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null, $bulan_filter = null)
    {
        $this->db->select('id, nm_karyawan, bulan, persentase_ontime, persentase_tunggakan, persentase_penjualan, grand_total');
        $this->db->from('komisi_realisasi');

        if (!empty($bulan_filter)) {
            $this->db->where('bulan_id', $bulan_filter);
        }

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('nm_karyawan', $like_value);
            $this->db->or_like('bulan', $like_value);
            $this->db->group_end();
        }

        // Order by column
        $columns_order_by = [
            0 => 'nm_karyawan',
            1 => 'bulan',
            2 => 'persentase_ontime',
            3 => 'persentase_tunggakan',
            4 => 'persentase_penjualan',
            5 => 'grand_total'
        ];

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('id', 'desc');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();
        $totalData = $this->db->count_all_results('komisi_realisasi', false); // total semua data (tanpa filter)
        $totalFiltered = $query->num_rows(); // total data terfilter

        return [
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered,
            'query' => $query
        ];
    }
}
