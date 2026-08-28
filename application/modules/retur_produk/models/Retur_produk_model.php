<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Retur_produk_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Retur_produk.Add');
        $this->ENABLE_MANAGE  = has_permission('Retur_produk.Manage');
        $this->ENABLE_VIEW    = has_permission('Retur_produk.View');
        $this->ENABLE_DELETE  = has_permission('Retur_produk.Delete');
    }

    public function data_side_retur()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_retur(
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

            $createRetur = "<a href='" . site_url('retur_produk/add/' . $row['id_sj']) . "' title='Create Retur' class='btn btn-sm btn-success'><i class='fa fa-paper-plane'></i></a>";
            $reqLoading  = "<a href='" . base_url("retur_produk/req_spk/{$row['id_retur']}") . "' class='btn btn-sm btn-info' title='Request SPK'><i class='fa fa-truck'></i> SPK</a>";
            $viewRetur   = "<a href='" . site_url('retur_produk/view/' . $row['id_retur']) . "' title='View Retur' class='btn btn-sm btn-warning'><i class='fa fa-eye'></i></a>";
            $closeBtn    = "<button onclick=\"closeRetur('{$row['id_sj']}')\" title='Close - Tidak perlu kirim ulang' class='btn btn-sm btn-danger'><i class='fa fa-times-circle'></i> Close</button>";

            if ($row['status'] == 3) {
                $status = "<span class='badge bg-red'>Closed</span>";
                $action = $viewRetur;
            } else if ($row['status'] == 2) {
                // Status tr_retur mentah masih 2 (On Loading) dan TIDAK diubah di sini.
                // Badge ditampilkan lebih detail berdasarkan progres SPK/SJ pengganti
                // supaya tidak terlihat "nyangkut" walau sebenarnya sudah dikirim/diterima.
                if (!empty($row['sj_status']) && in_array($row['sj_status'], ['CONFIRM', 'RETUR', 'HILANG'])) {
                    $status = "<span class='badge bg-orange'>Delivered</span>";
                } else if (!empty($row['spk_status']) && $row['spk_status'] == 'ON DELIVER') {
                    $status = "<span class='badge bg-blue'>On Delivery</span>";
                } else {
                    $status = "<span class='badge bg-green'>On Loading</span>";
                }
                $action = $viewRetur;
            } else if ($row['status'] == 1) {
                $status = "<span class='badge bg-yellow'>Proses Retur</span>";
                $action = $reqLoading . ' ' . $closeBtn;
            } else {
                $status = "<span class='badge bg-blue'>Belum Proses</span>";
                $action = $createRetur . ' ' . $closeBtn;
            }


            $nestedData[] = "<div class='text-center'>{$urut}</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_retur']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . (($row['tgl_retur'] != null) ? date('d/M/Y', strtotime($row['tgl_retur'])) : '') . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_surat_jalan']) . "</div>";
            $nestedData[] = "<div class='text-center'>" . strtoupper($row['no_so']) . "</div>";
            $nestedData[] = "<div>" . strtoupper($row['name_customer']) . "</div>";
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

    public function get_query_json_retur($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
    {
        $columns_order_by = [
            0 => 'r.no_retur',
            1 => 'sj.no_surat_jalan',
            2 => 'sj.no_surat_jalan',
            3 => 'c.name_customer',
            4 => 'r.tgl_retur',
            5 => 'r.status'
        ];

        // =============================
        // 1. Hitung totalData
        // =============================
        $this->db->select('r.id as id_retur, r.no_retur, sj.id as id_sj, sj.no_surat_jalan, sj.no_so, r.tgl_retur, r.status, c.name_customer');
        $this->db->from('surat_jalan sj');
        $this->db->join('surat_jalan_detail sjd', 'sj.no_surat_jalan = sjd.no_surat_jalan', 'left');
        $this->db->join('tr_retur r', 'sj.no_surat_jalan = r.no_surat_jalan', 'left');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->where('sj.status', 'RETUR');
        $this->db->where('sjd.qty_retur !=', 0);
        $this->db->group_by('sj.no_surat_jalan');
        $totalData = $this->db->count_all_results();

        // =============================
        // 2. Hitung totalFiltered
        // =============================
        $this->db->select('r.id as id_retur, r.no_retur, sj.id as id_sj, sj.no_surat_jalan, sj.no_so, r.tgl_retur, r.status, c.name_customer');
        $this->db->from('surat_jalan sj');
        $this->db->join('surat_jalan_detail sjd', 'sj.no_surat_jalan = sjd.no_surat_jalan', 'left');
        $this->db->join('tr_retur r', 'sj.no_surat_jalan = r.no_surat_jalan', 'left');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->where('sj.status', 'RETUR');
        $this->db->where('sjd.qty_retur !=', 0);
        $this->db->group_by('sj.no_surat_jalan');

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
        // Subquery bantu: status real proses retur diambil dari SPK Delivery
        // (spk_delivery.status) TERAKHIR untuk no_retur ini, dijoin lagi ke
        // surat_jalan (SJ pengganti) untuk tahu apakah sudah confirm delivery.
        // Ini HANYA untuk kebutuhan tampilan/badge, tidak mengubah tr_retur.status.
        $spk_sub = "
            (
                SELECT
                    spk.no_retur,
                    spk.status         AS spk_status,
                    spk.no_surat_jalan  AS spk_no_sj,
                    sj2.status          AS sj_status
                FROM spk_delivery spk
                INNER JOIN (
                    SELECT no_retur, MAX(created_date) AS max_date
                    FROM spk_delivery
                    WHERE no_retur IS NOT NULL AND no_retur != ''
                    GROUP BY no_retur
                ) latest ON latest.no_retur = spk.no_retur AND latest.max_date = spk.created_date
                LEFT JOIN surat_jalan sj2 ON sj2.no_surat_jalan = spk.no_surat_jalan
            ) spkinfo
        ";

        $this->db->select('r.id as id_retur, r.no_retur, sj.id as id_sj, sj.no_surat_jalan, sj.no_so, r.tgl_retur, r.status, c.name_customer, spkinfo.spk_status, spkinfo.sj_status');
        $this->db->from('surat_jalan sj');
        $this->db->join('surat_jalan_detail sjd', 'sj.no_surat_jalan = sjd.no_surat_jalan', 'left');
        $this->db->join('tr_retur r', 'sj.no_surat_jalan = r.no_surat_jalan', 'left');
        $this->db->join('sales_order so', 'sj.no_so = so.no_so', 'left');
        $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
        $this->db->join($spk_sub, 'spkinfo.no_retur = r.no_retur', 'left');
        $this->db->where('sj.status', 'RETUR');
        $this->db->where('sjd.qty_retur !=', 0);
        $this->db->group_by('sj.no_surat_jalan');

        if (!empty($like_value)) {
            $this->db->group_start();
            $this->db->like('sj.no_surat_jalan', $like_value);
            $this->db->or_like('r.no_retur', $like_value);
            $this->db->or_like('sj.no_so', $like_value);
            $this->db->or_like('c.name_customer', $like_value);
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
