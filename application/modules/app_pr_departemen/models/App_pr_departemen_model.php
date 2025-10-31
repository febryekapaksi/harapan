<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class App_pr_departemen_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('Approval_PR_Departemen.Add');
        $this->ENABLE_MANAGE  = has_permission('Approval_PR_Departemen.Manage');
        $this->ENABLE_VIEW    = has_permission('Approval_PR_Departemen.View');
        $this->ENABLE_DELETE  = has_permission('Approval_PR_Departemen.Delete');
    }

    public function get_json_approval_pr_departemen()
    {
        $ENABLE_MANAGE = has_permission('Approval_PR_Departemen.Manage');

        $requestData = $_REQUEST;
        $fetch = $this->get_query_approval_pr_departemen(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $data = [];
        $no = $requestData['start'] + 1;
        foreach ($fetch['query']->result_array() as $row) {
            $barang = $this->db->get_where('rutin_non_planning_detail', ['no_pengajuan' => $row['no_pengajuan']])->result();
            $list_barang = $list_spec = $list_qty = $list_tanggal = $list_ket = [];

            foreach ($barang as $val) {
                $satuan = $this->db->get_where('ms_satuan', ['id' => $val->satuan])->row();
                $nm_satuan = $satuan ? strtolower($satuan->code) : '';
                $list_barang[] = "&bull; " . strtoupper($val->nm_barang);
                $list_spec[] = "&bull; " . strtoupper($val->spec);
                $list_qty[] = "&bull; " . floatval($val->qty) . ' ' . $nm_satuan;
                $list_tanggal[] = "&bull; " . (($val->tanggal && $val->tanggal != '0000-00-00') ? date('d-M-Y', strtotime($val->tanggal)) : 'not set');
                $list_ket[] = "&bull; " . strtoupper($val->keterangan);
            }

            // Status logic
            $warna = 'blue';
            $sts = 'WAITING APPROVAL';
            if ($row['sts_app'] === 'Y') {
                $warna = 'green';
                $sts = 'APPROVED';
            } elseif ($row['sts_app'] === 'N') {
                $warna = 'blue';
                $sts = 'WAITING APPROVAL';
            } elseif ($row['rejected'] == 1) {
                $warna = 'red';
                $sts = 'REJECTED';
                if ($row['sts_reject1'] == "1") {
                    $sts = "Rejected By Head Department";
                } elseif ($row['sts_reject2'] == "1") {
                    $sts = "Rejected By Cost Control";
                } elseif ($row['sts_reject3'] == "1") {
                    $sts = "Rejected By Management";
                }
            } elseif (!$row['app_1'] && !$row['app_2'] && !$row['app_3']) {
                $sts = 'Waiting Approval Head Department';
            } elseif ($row['app_1'] && !$row['app_2']) {
                $sts = 'Waiting Approval Cost Control';
            } elseif ($row['app_1'] && $row['app_2'] && !$row['app_3']) {
                $sts = 'Waiting Approval Management';
            }

            $approve = $ENABLE_MANAGE ? "<a href='" . base_url('app_pr_departemen/add/' . $row['no_pengajuan'] . '/approve/3') . "' class='btn btn-sm btn-info' title='Approve'><i class='fa fa-check'></i></a>" : '';
            $print = "<a href='" . base_url('pr_departemen/print_pengajuan_pr_departemen/' . $row['no_pengajuan']) . "' target='_blank' class='btn btn-sm btn-success'><i class='fa fa-print'></i></a>";

            $data[] = [
                "<div align='center'>{$no}</div>",
                "<div align='left'>" . (!empty($row['no_pr']) ? $row['no_pr'] : "<span class='text-red'>{$row['no_pengajuan']}</span>") . "</div>",
                strtoupper($row['nama']),
                implode('<br>', $list_barang),
                implode('<br>', $list_spec),
                implode('<br>', $list_qty),
                implode('<br>', $list_tanggal),
                implode('<br>', $list_ket),
                $row['pic'],
                "<span class='badge' style='background-color: {$warna};'>{$sts}</span>",
                "<div align='center'> $approve $print </div>",
            ];
            $no++;
        }

        echo json_encode([
            "draw" => intval($requestData['draw']),
            "recordsTotal" => $fetch['totalData'],
            "recordsFiltered" => $fetch['totalFiltered'],
            "data" => $data
        ]);
    }

    public function get_query_approval_pr_departemen($like = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            0 => 'a.no_pengajuan',
            1 => 'a.created_date',
            2 => 'b.nama'
        ];

        $this->db->select('a.*, b.nama, c.nm_lengkap as pic');
        $this->db->from('rutin_non_planning_detail z');
        $this->db->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left');
        $this->db->join('ms_department b', 'b.id = a.id_dept', 'left');
        $this->db->join('users c', 'c.id_user = a.created_by', 'left');
        $this->db->where('a.status_id', 1);
        $this->db->where('a.no_pr IS NULL');
        $this->db->where('a.app_post', '3');
        $this->db->where('a.close_pr IS NULL');
        $this->db->group_by('z.no_pengajuan');

        if ($like) {
            $this->db->group_start();
            $this->db->like('a.no_pengajuan', $like);
            $this->db->or_like('a.tanggal', $like);
            $this->db->or_like('a.no_pr', $like);
            $this->db->or_like('b.nama', $like);
            $this->db->or_like('c.nm_lengkap', $like);
            $this->db->group_end();
        }

        $totalData = $this->db->count_all_results('', false);
        $totalFiltered = $totalData;

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('a.created_date', 'desc');
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
}
