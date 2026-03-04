<?php

use Mpdf\Tag\P;

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_pembelian_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Report_Pembelian.Add');
        $this->ENABLE_MANAGE  = has_permission('Report_Pembelian.Manage');
        $this->ENABLE_VIEW    = has_permission('Report_Pembelian.View');
        $this->ENABLE_DELETE  = has_permission('Report_Pembelian.Delete');
    }

    public function data_side_report()
    {
        $requestData = $_REQUEST;

        $tgl_dari   = $requestData['tgl_dari'] ?? null;
        $tgl_sampai = $requestData['tgl_sampai'] ?? null;

        $fetch = $this->get_query_json_report(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length'],
            $tgl_dari,
            $tgl_sampai
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data  = [];
        $urut = intval($requestData['start']) + 1;

        foreach ($query->result_array() as $row) {
            $nestedData = [];

            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = "<div class='text-left'>" . strtoupper($row['id']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . (($row['invoice_date'] != null) ? date('d/M/Y', strtotime($row['invoice_date'])) : '') . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nm_supplier']) . "</div>";
            $nestedData[] = "<div class='text-right'>" . number_format($row['total_invoice']) . "</div>";

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

    public function get_query_json_report($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL, $tgl_dari = NULL, $tgl_sampai = NULL)
    {
        // Sesuaikan mapping column order dengan index di table HTML
        $columns_order_by = [
            1 => 'i.id',
            2 => 'i.invoice_date',
            3 => 'i.nm_supplier',
            4 => 'i.total_invoice',
        ];

        $apply_date_filter = function () use ($tgl_dari, $tgl_sampai) {
            if (!empty($tgl_dari) && !empty($tgl_sampai)) {
                $this->db->where('i.invoice_date >=', $tgl_dari);
                $this->db->where('i.invoice_date <=', $tgl_sampai);
            }
        };

        // Sesuaikan select dengan kolom tr_invoice_po
        $select = 'i.id, i.id, i.invoice_date, i.nm_supplier, i.total_invoice';

        // 1) totalData
        $this->db->from('tr_invoice_po i');
        $apply_date_filter();
        $totalData = $this->db->count_all_results();

        // 2) totalFiltered
        $this->db->from('tr_invoice_po i');
        $apply_date_filter();
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('i.id', $like_value);
            $this->db->or_like('i.nm_supplier', $like_value);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // 3) Data Fetch
        $this->db->select($select)->from('tr_invoice_po i');
        $apply_date_filter();
        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('i.id', $like_value);
            $this->db->or_like('i.nm_supplier', $like_value);
            $this->db->group_end();
        }

        if (isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('i.invoice_date', 'desc');
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
