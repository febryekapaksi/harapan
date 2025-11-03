<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Setor_bank_model extends BF_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Setor_Bank.Add');
        $this->ENABLE_MANAGE  = has_permission('Setor_Bank.Manage');
        $this->ENABLE_VIEW    = has_permission('Setor_Bank.View');
        $this->ENABLE_DELETE  = has_permission('Setor_Bank.Delete');
    }

    public function generateKodeSetoran($tgl_setor)
    {
        // Format jadi: ST-KS + YYMMDD
        $prefix = 'ST-BN' . date('ymd', strtotime($tgl_setor));

        // Cari nomor urut terakhir di tanggal yang sama
        $last = $this->db->like('id', $prefix)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tr_setor_bank')
            ->row();

        $no = 1;
        if ($last) {
            $no = (int) substr($last->id, -3) + 1;
        }

        // Return: ST-KS250804001
        return $prefix . str_pad($no, 3, '0', STR_PAD_LEFT);
    }

    public function get_json_setoran_bank()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_json_setoran_bank(
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

        $data = [];
        $urut1 = 1;
        $urut2 = 0;

        // Mapping kode_penerimaan
        $mapPenerimaan = [];
        $kd_pembayaran = '-';
        if (!empty($result_data)) {
            $ids = array_column($result_data, 'id');
            $details = $this->db->select('id_setor_bank, GROUP_CONCAT(kd_pembayaran SEPARATOR ",") as kode_penerimaan')
                ->from('tr_setor_bank_detail')
                ->where_in('id_setor_bank', $ids)
                ->group_by('id_setor_bank')
                ->get()->result_array();
            foreach ($details as $d) {
                $mapPenerimaan[$d['id_setor_bank']] = $d['kode_penerimaan'];
            }
        }

        foreach ($result_data as $row) {
            $total_data = $totalData;
            $start_dari = $requestData['start'];
            $asc_desc = $requestData['order'][0]['dir'];
            $nomor = ($asc_desc == 'asc')
                ? ($total_data - $start_dari) - $urut2
                : $urut1 + $start_dari;

            $nestedData = [];
            $nestedData[] = "<div align='center'>{$nomor}</div>";
            $nestedData[] = "<div align='center'>{$row['id']}</div>";
            $nestedData[] = "<div align='center'>" . date('d/m/Y', strtotime($row['tgl_setor'])) . "</div>";
            if (!empty($mapPenerimaan[$row['id']])) {
                $invoiceList = explode(',', $mapPenerimaan[$row['id']]);
                $kd_pembayaran = "<ul style='padding-left:16px;margin:0'>";
                foreach ($invoiceList as $inv) {
                    $kd_pembayaran .= "<li>" . htmlspecialchars($inv) . "</li>";
                }
                $kd_pembayaran .= "</ul>";
            }
            $nestedData[] = "<div align='left'>{$kd_pembayaran}</div>";
            $nestedData[] = "<div align='right'>" . number_format($row['total_setoran'], 0, ',', '.') . "</div>";

            $nestedData[] = "<div align='center'>
            <a href='javascript:void(0)' class='btn btn-sm btn-warning view-setoran' data-id='{$row['id']}'><i class='fa fa-eye'></i></a>
        </div>";

            $data[] = $nestedData;
            $urut1++;
            $urut2++;
        }

        $json_data = [
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ];

        echo json_encode($json_data);
    }

    public function get_query_json_setoran_bank($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $columns_order_by = [
            0 => 's.id',
            1 => 's.tgl_setor',
            2 => 's.id',
            3 => 's.total_setoran',
        ];

        // Total data
        $this->db->from('tr_setor_bank s');
        $totalData = $this->db->count_all_results();

        // Filtered data
        $this->db->from('tr_setor_bank s');
        if ($like_value) {
            $this->db->group_start();
            $this->db->like('s.id', $like_value);
            $this->db->or_like('s.tgl_setor', $like_value);
            $this->db->group_end();
        }
        $totalFiltered = $this->db->count_all_results();

        // Main query
        $this->db->select('s.id, s.tgl_setor, s.total_setoran');
        $this->db->from('tr_setor_bank s');
        if ($like_value) {
            $this->db->group_start();
            $this->db->like('s.id', $like_value);
            $this->db->or_like('s.tgl_setor', $like_value);
            $this->db->group_end();
        }
        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('s.tgl_setor', 'desc');
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
