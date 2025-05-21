<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * @author Harboens
 * @copyright Copyright (c) 2020
 *
 * This is model class for table "Warehouse"
 */

class Warehouse_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Warehouse.Add');
        $this->ENABLE_MANAGE  = has_permission('Warehouse.Manage');
        $this->ENABLE_VIEW    = has_permission('Warehouse.View');
        $this->ENABLE_DELETE  = has_permission('Warehouse.Delete');
    }

    // list data
    public function GetListWarehouse()
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->order_by('a.id', 'asc');
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    // get data
    public function GetDataWarehouse($id)
    {
        $this->db->select('a.*');
        $this->db->from($this->table_name . ' a');
        $this->db->where('a.id', $id);
        $query = $this->db->get();
        if ($query->num_rows() != 0) {
            return $query->row();
        } else {
            return false;
        }
    }

    //server side
    public function get_json_warehouse_stock()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_warehouse_stock(
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
            $qty_stock = isset($row['qty_stock']) ? floatval($row['qty_stock']) : 0;
            $qty_booking = isset($row['qty_booking']) ? floatval($row['qty_booking']) : 0;
            $available = $qty_stock - $qty_booking;

            $nestedData = [];
            $nestedData[] = "<div align='center'>{$nomor}</div>";
            $nestedData[] = "<div align='left'>{$row['code_product']}</div>";
            $nestedData[] = "<div align='left'>{$row['nm_product']}</div>";
            $nestedData[] = "<div align='center'>{$row['unit']}</div>";
            $nestedData[] = "<div align='center'>{$row['unit_packing']}</div>";
            $nestedData[] = "<div align='right'>" . number_format($qty_stock, 2) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($qty_booking, 2) . "</div>";
            $nestedData[] = "<div align='right'>" . number_format($available, 2) . "</div>";

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

    public function get_query_json_warehouse_stock($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $columns_order_by = [
            0 => 'ws.id',
            1 => 'ws.code_product',
            2 => 'ws.nm_product',
            3 => 'sp.nama',
            4 => 'sm.nama',
            5 => 'ws.qty_stock',
            6 => 'ws.qty_booking'
        ];

        // Total Data
        $this->db->select('ws.id');
        $this->db->from('warehouse_stock ws');
        $totalData = $this->db->count_all_results();

        // Total Filtered
        $this->db->select('ws.id');
        $this->db->from('warehouse_stock ws');
        $this->db->join('ms_satuan sm', 'ws.id_unit = sm.id', 'left');
        $this->db->join('ms_satuan sp', 'ws.id_unit_packing = sp.id', 'left');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('ws.code_product', $like_value);
            $this->db->or_like('ws.nm_product', $like_value);
            $this->db->or_like('sm.nama', $like_value);
            $this->db->or_like('sp.nama', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // Data utama
        $this->db->select('
                            ws.*,
                            sm.nama AS unit,
                            sp.nama AS unit_packing
                        ');
        $this->db->from('warehouse_stock ws');
        $this->db->join('ms_satuan sm', 'ws.id_unit = sm.id', 'left');
        $this->db->join('ms_satuan sp', 'ws.id_unit_packing = sp.id', 'left');

        if ($like_value) {
            $this->db->group_start();
            $this->db->like('ws.code_product', $like_value);
            $this->db->or_like('ws.nm_product', $like_value);
            $this->db->or_like('sp.nama', $like_value);
            $this->db->or_like('sm.nama', $like_value);
            $this->db->group_end();
        }

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('ws.id', 'desc');
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
