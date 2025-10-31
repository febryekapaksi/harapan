<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pr_departemen_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('PR_Departemen.Add');
        $this->ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
        $this->ENABLE_VIEW    = has_permission('PR_Departemen.View');
        $this->ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
    }

    public function get_json_pr_departemen()
    {
        $requestData = $_REQUEST;
        $departemen = isset($requestData['departemen']) ? $requestData['departemen'] : null;
        $fetch = $this->get_query_pr_departemen(
            $departemen,
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length'],
        );

        $data = [];
        $no = $requestData['start'] + 1;

        foreach ($fetch['query']->result() as $item) {
            $barang = $this->db->get_where('rutin_non_planning_detail', ['no_pengajuan' => $item->no_pengajuan])->result();
            $list_barang = $list_spec = $list_qty = $list_tanggal = $list_ket = [];

            foreach ($barang as $val) {
                $satuan = $this->db->get_where('ms_satuan', ['id' => $val->satuan])->row();
                $nm_satuan = $satuan ? strtolower($satuan->code) : '';
                $list_barang[] = "&bull; " . strtoupper($val->nm_barang);
                $list_spec[] = "&bull; " . strtoupper($val->spec);
                $list_qty[] = "&bull; " . floatval($val->qty) . " " . $nm_satuan;
                $list_tanggal[] = "&bull; " . (($val->tanggal && $val->tanggal != '0000-00-00') ? date('d-M-Y', strtotime($val->tanggal)) : 'not set');
                $list_ket[] = "&bull; " . strtoupper($val->keterangan);
            }

            // Status
            $warna = 'blue';
            $sts = 'WAITING APPROVAL';
            if ($item->sts_app == 'Y') {
                $warna = 'green';
                $sts = 'APPROVED';
            } elseif (($item->sts_reject1 || $item->sts_reject2 || $item->sts_reject3) && $item->rejected == 1) {
                $warna = 'red';
                if ($item->sts_reject1 == "1") $sts = "Rejected By Head Department";
                elseif ($item->sts_reject2 == "1") $sts = "Rejected By Cost Control";
                elseif ($item->sts_reject3 == "1") $sts = "Rejected By Management";
            } elseif (!$item->app_1 && !$item->app_2 && !$item->app_3) {
                $sts = 'Waiting Approval Head Department';
            } elseif ($item->app_1 && !$item->app_2) {
                $sts = 'Waiting Approval Cost Control';
            } elseif ($item->app_1 && $item->app_2 && !$item->app_3) {
                $sts = 'Waiting Approval Management';
            }

            // Aksi
            $view = "<a href='" . site_url("pr_departemen/add/{$item->no_pengajuan}/view") . "' class='btn btn-sm btn-warning'><i class='fa fa-eye'></i></a>";
            $edit = ($item->sts_app == 'N' || $item->sts_app == '') ? "<a href='" . site_url("pr_departemen/add/{$item->no_pengajuan}") . "' class='btn btn-sm btn-primary'><i class='fa fa-edit'></i></a>" : '';
            $print = "<a href='" . site_url("pr_departemen/print_pengajuan_pr_departemen/{$item->no_pengajuan}") . "' target='_blank' class='btn btn-sm btn-success'><i class='fa fa-print'></i></a>";
            $close = ($this->ENABLE_DELETE) ? "<button class='btn btn-sm btn-danger close_pr_modal' data-no_pengajuan='{$item->no_pengajuan}'><i class='fa fa-close'></i></button>" : '';

            $data[] = [
                "<div align='center'>{$no}</div>",
                "<div align='center'>" . (!empty($item->no_pr) ? $item->no_pr : "<span class='text-red'>{$item->no_pengajuan}</span>") . "</div>",
                strtoupper($item->nama),
                join('<br>', $list_barang),
                join('<br>', $list_spec),
                join('<br>', $list_qty),
                join('<br>', $list_tanggal),
                join('<br>', $list_ket),
                $item->pic,
                "<span class='badge' style='background-color: {$warna};'>{$sts}</span>",
                $view . ' ' . $edit . ' ' . $print . ' ' . $close
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


    public function get_query_pr_departemen($departemen = null, $like = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            0 => 'a.no_pengajuan',
            1 => 'a.created_date'
        ];

        $this->db->select('a.*, b.nama, c.nm_lengkap as pic');
        $this->db->from('rutin_non_planning_detail z');
        $this->db->join('rutin_non_planning_header a', 'z.no_pengajuan = a.no_pengajuan', 'left');
        $this->db->join('ms_department b', 'b.id = a.id_dept', 'left');
        $this->db->join('users c', 'c.id_user = a.created_by', 'left');
        $this->db->where('a.status_id', 1);
        $this->db->where('a.close_pr IS NULL');
        $this->db->group_by('z.no_pengajuan');

        if ($departemen) {
            $this->db->where('a.id_dept', $departemen);
        }

        if ($like) {
            $this->db->group_start();
            $this->db->like('a.no_pengajuan', $like, 'both');
            $this->db->or_like('b.nama', $like, 'both');
            $this->db->or_like('c.nm_lengkap', $like, 'both');
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
