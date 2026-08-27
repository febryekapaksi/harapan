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
            $status     = '';
            $action     = '';

            $user_id   = $this->auth->user_id();
            $user_dept = $this->_get_user_dept_model();
            $is_admin  = ($user_id == 7);
            $id_retur  = $row['id_retur'];

            if ($row['status'] == 0) {
                // Request masuk — menunggu SJ Retur dari Gudang
                $status = "<span class='badge bg-yellow' style='color:#333;'>Menunggu SJ Retur</span>";
                if ($user_dept == 2 || $is_admin || has_permission('Retur_credit_note.BuatSJR')) {
                    $action = "<a href='" . site_url('retur_credit_note/form_sjr/' . $id_retur) . "' class='btn btn-sm btn-warning' title='Buat Surat Jalan Retur'><i class='fa fa-truck'></i> Buat SJ Retur</a>";
                } else {
                    $action = "<span class='text-muted'><i class='fa fa-clock-o'></i> Menunggu Gudang</span>";
                }
            } elseif ($row['status'] == 1) {
                // SJ Retur sudah dibuat — menunggu Credit Note dari Finance
                $status = "<span class='badge bg-blue'>Menunggu Credit Note</span>";
                if ($user_dept == 3 || $is_admin || has_permission('Retur_credit_note.BuatCN')) {
                    $action = "<a href='" . site_url('retur_credit_note/form_cn/' . $id_retur) . "' class='btn btn-sm btn-primary' title='Buat Credit Note'><i class='fa fa-file-text'></i> Buat CN</a>";
                } else {
                    $action = "<span class='text-muted'><i class='fa fa-clock-o'></i> Menunggu Finance</span>";
                }
            } else {
                // status=2: selesai
                $status = "<span class='badge bg-green'>Selesai</span>";
                if (isset($row['is_cancel']) && $row['is_cancel'] == 2) {
                    $status .= " <span class='badge bg-orange'>Partial CN</span>";
                }
                $action = "<a href='" . site_url('retur_credit_note/view/' . $id_retur) . "' class='btn btn-sm btn-warning' title='View'><i class='fa fa-eye'></i></a>";
            }

            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_sj']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_retur']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . (!empty($row['tgl_retur']) ? date('d/M/Y', strtotime($row['tgl_retur'])) : '-') . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['id_invoice']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['id_so'] ?? '') . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['nm_customer']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . $status . "</div>";
            $nestedData[] = "<div class='text-center'>" . $action . "</div>";

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function get_query_json_inv($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $columns_order_by = [
            0 => 'r.no_retur',
            1 => 'r.id_invoice',
            2 => 'r.no_surat_jalan',
            3 => 'r.nm_customer',
            4 => 'r.tgl_retur',
            5 => 'r.status'
        ];

        // SELECT yang dipakai di semua query
        $select = 'r.id as id_retur, r.no_retur, r.no_sjr,
                   r.no_surat_jalan as no_sj,
                   r.id_invoice, r.nm_customer, r.tgl_retur, r.status,
                   i.id_so, i.is_cancel';

        // =============================
        // 1. Hitung totalData
        // =============================
        $this->db->select($select);
        $this->db->from('tr_retur r');
        $this->db->join('tr_invoice_sales i', 'i.id_invoice = r.id_invoice', 'left');
        $totalData = $this->db->count_all_results();

        // =============================
        // 2. Hitung totalFiltered
        // =============================
        $this->db->select($select);
        $this->db->from('tr_retur r');
        $this->db->join('tr_invoice_sales i', 'i.id_invoice = r.id_invoice', 'left');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('r.no_surat_jalan', $like_value);
            $this->db->or_like('r.nm_customer',  $like_value);
            $this->db->or_like('r.no_retur',     $like_value);
            $this->db->or_like('r.id_invoice',   $like_value);
            $this->db->group_end();
        }

        $totalFiltered = $this->db->count_all_results();

        // =============================
        // 3. Ambil data paginasi
        // =============================
        $this->db->select($select);
        $this->db->from('tr_retur r');
        $this->db->join('tr_invoice_sales i', 'i.id_invoice = r.id_invoice', 'left');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('r.no_surat_jalan', $like_value);
            $this->db->or_like('r.nm_customer',  $like_value);
            $this->db->or_like('r.no_retur',     $like_value);
            $this->db->or_like('r.id_invoice',   $like_value);
            $this->db->or_like('i.id_so',        $like_value);
            $this->db->group_end();
        }

        if (isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('r.created_date', 'DESC');
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $query = $this->db->get();

        return [
            'totalData'     => $totalData,
            'totalFiltered' => $totalFiltered,
            'query'         => $query
        ];
    }

    private function _get_user_dept_model()
    {
        $user_id = $this->auth->user_id();
        $row = $this->db
            ->select('e.department')
            ->from('users u')
            ->join('employee e', 'e.id = u.employee_id', 'left')
            ->where('u.id_user', $user_id)
            ->get()->row();
        return $row ? (int)$row->department : 0;
    }
}
