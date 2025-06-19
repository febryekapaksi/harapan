<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Surat_jalan_pabrik_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Surat_Jalan_Pabrik.Add');
        $this->ENABLE_MANAGE  = has_permission('Surat_Jalan_Pabrik.Manage');
        $this->ENABLE_VIEW    = has_permission('Surat_Jalan_Pabrik.View');
        $this->ENABLE_DELETE  = has_permission('Surat_Jalan_Pabrik.Delete');
    }

    public function data_side_surat_jalan_pabrik()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_surat_jalan_pabrik(
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
            $status = '';

            // $viewBtn  = "<a href='javascript:void(0);' data-id='" . $row['no_surat_jalan'] . "' class='btn btn-sm btn-info view-sj'><i class='fa fa-eye'></i></a>";
            // $editBtn  = "<a href='" . site_url('surat_jalan/edit/' . $row['id']) . "' class='btn btn-sm btn-warning'><i class='fa fa-edit'></i></a>";
            $printBtn = "<a href='" . site_url('surat_jalan_pabrik/print_sj/' . $row['id']) . "' target='_blank' class='btn btn-sm btn-warning'><i class='fa fa-print'></i></a>";
            $confimDo = "<a href='" . site_url('surat_jalan_pabrik/confirm_sj/' . $row['id']) . "' class='btn btn-sm btn-info' title='Confirm Delivery'><i class='fa fa-check'></i></a>";

            $action =  ($row['status'] == 'CONFIRM') ? $printBtn : $printBtn . ' ' . $confimDo;

            if ($row['status'] == 'ON DELIVER') {
                $status = " <span class='badge bg-yellow'>ON DELIVERY</span>";
            } else if ($row['status'] == 'CONFIRM') {
                $status = " <span class='badge bg-green'>CONFIRM DELIVERY</span>";
            } else {
                $status = " <span class='badge bg-gray'>RETUR</span>";
            }


            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_surat_jalan']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['name_customer']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . date('d/M/Y', strtotime($row['delivery_date'])) . "</div>";
            $nestedData[] = "<div class='text-center'>" . $status . "</div>";
            $nestedData[] = "<div class='text-center'>" . $action . "</div>";

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


    public function get_query_json_surat_jalan_pabrik($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $columns_order_by = [
            0 => 'sj.no_surat_jalan',
            1 => 'sj.no_surat_jalan',
            2 => 'c.name_customer',
            3 => 'sj.delivery_date',
            4 => 'sj.status'
        ];

        // =============================
        // 1. Hitung totalData
        // =============================
        $this->db->from('surat_jalan sj');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->where('sj.pengiriman', 'Pabrik');
        $totalData = $this->db->count_all_results();

        // =============================
        // 2. Hitung totalFiltered
        // =============================
        $this->db->from('surat_jalan sj');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->where('sj.pengiriman', 'Pabrik');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('sj.no_surat_jalan', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // =============================
        // 3. Ambil data paginasi
        // =============================
        $this->db->select('sj.id, sj.no_surat_jalan, sj.delivery_date, sj.status, c.name_customer');
        $this->db->from('surat_jalan sj');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->where('sj.pengiriman', 'Pabrik');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('sj.no_surat_jalan', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
            $this->db->group_end();
        }

        if (isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('sj.created_at', 'desc');
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
