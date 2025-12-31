<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Retur_credit_note_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Retur_credit_note.Add');
        $this->ENABLE_MANAGE  = has_permission('Retur_credit_note.Manage');
        $this->ENABLE_VIEW    = has_permission('Retur_credit_note.View');
        $this->ENABLE_DELETE  = has_permission('Retur_credit_note.Delete');
    }

    public function data_side_inv()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_inv(
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
            $action = '';

            $createRetur = "<a href='" . site_url('retur_credit_note/add/' . $row['id_invoice']) . "' title='Create Credit Note' class='btn btn-sm btn-success'><i class='fa fa-paper-plane'></i></a>";
            // $reqLoading = "<a href='" . base_url("retur_credit_note/req_spk/{$row['id_retur']}") . "' class='btn btn-sm btn-info' title='Request SPK'><i class='fa fa-truck'></i> SPK</a>";
            $viewRetur = "<a href='" . site_url('retur_credit_note/view/' . $row['id_retur']) . "' title='View Credit Note' class='btn btn-sm btn-warning'><i class='fa fa-eye'></i></a>";


            if ($row['status'] == 1) {
                $status = "<span class='badge bg-green'>Selesai</span>";
                $action = $viewRetur;
            } else {
                $status = " <span class='badge bg-blue'>Belum Proses</span>";
                $action = $createRetur;
            }


            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_retur']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . (($row['tgl_retur'] != null) ? date('d/M/Y', strtotime($row['tgl_retur'])) : '') . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['id_invoice']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['id_so']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nm_customer']) . "</div>";
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

    public function get_query_json_inv($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $columns_order_by = [
            0 => 'r.no_retur',
            1 => 'i.id_invoice',
            2 => 'i.no_surat_jalan',
            3 => 'i.nm_customer',
            4 => 'r.tgl_retur',
            5 => 'r.status'
        ];

        // =============================
        // 1. Hitung totalData
        // =============================
        $this->db->select('r.id as id_retur, r.no_retur, i.id_invoice, i.id_billing, i.id_so, r.tgl_retur, r.status, i.nm_customer');
        $this->db->from('tr_invoice_sales i');
        $this->db->join('tr_retur r', 'i.id_invoice = r.id_invoice', 'left');
        $this->db->where('i.is_cancel', 1);
        $this->db->group_by('i.id_invoice');
        $totalData = $this->db->count_all_results();

        // =============================
        // 2. Hitung totalFiltered
        // =============================
        $this->db->select('r.id as id_retur, r.no_retur, i.id_invoice, i.id_billing, i.id_so, r.tgl_retur, r.status, i.nm_customer');
        $this->db->from('tr_invoice_sales i');
        $this->db->join('tr_retur r', 'i.id_invoice = r.id_invoice', 'left');
        $this->db->where('i.is_cancel', 1);
        $this->db->group_by('i.id_invoice');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('i.id_billing', $like_value);
            $this->db->or_like('i.nm_customer', $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // =============================
        // 3. Ambil data paginasi
        // =============================
        $this->db->select('r.id as id_retur, r.no_retur, i.id_invoice, i.id_billing, i.id_so, r.tgl_retur, r.status, i.nm_customer');
        $this->db->from('tr_invoice_sales i');
        $this->db->join('tr_retur r', 'i.id_invoice = r.id_invoice', 'left');
        $this->db->where('i.is_cancel', 1);
        $this->db->group_by('i.id_invoice');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('i.id_invoice', $like_value);
            $this->db->or_like('r.no_retur', $like_value);
            $this->db->or_like('i.id_so', $like_value);
            $this->db->or_like('i.nm_customer', $like_value);
            $this->db->group_end();
        }

        if (isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('r.created_date', 'desc');
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
