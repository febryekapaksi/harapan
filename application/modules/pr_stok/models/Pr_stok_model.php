<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Pr_stok_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->ENABLE_ADD     = has_permission('PR_Product.Add');
        $this->ENABLE_MANAGE  = has_permission('PR_Product.Manage');
        $this->ENABLE_VIEW    = has_permission('PR_Product.View');
        $this->ENABLE_DELETE  = has_permission('PR_Product.Delete');
    }

    public function get_json_pr_stok()
    {
        $requestData = $_REQUEST;

        $fetch = $this->get_query_pr_stok(
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $totalData     = $fetch['totalData'];
        $totalFiltered = $fetch['totalFiltered'];
        $query         = $fetch['query'];

        $data = [];
        $urut = 1;

        foreach ($query->result_array() as $row) {
            $nomor = $urut + $requestData['start'];

            // Get detail barang
            $this->db->select('a.propose_purchase, a.qty_order, b.stock_name, c.code as satuan');
            $this->db->from('material_planning_base_on_produksi_detail a');
            $this->db->join('accessories b', 'b.id = a.id_material', 'left');
            $this->db->join('ms_satuan c', 'c.id = b.id_unit_gudang', 'left');
            $this->db->where('a.so_number', $row['so_number']);
            $get_barang = $this->db->get()->result();

            $list_barang = [];
            $list_qty_barang = [];

            foreach ($get_barang as $item) {
                $jumlah = ($item->propose_purchase > 0) ? $item->propose_purchase : $item->qty_order;
                $list_barang[] = $item->stock_name;
                $list_qty_barang[] = number_format($jumlah, 2) . ' ' . strtoupper($item->satuan);
            }

            // Get kategori PR
            $kategori_pr = $this->db
                ->select('c.nm_category as kategori')
                ->from('material_planning_base_on_produksi_detail a')
                ->join('accessories b', 'b.id = a.id_material', 'left')
                ->join('accessories_category c', 'c.id = b.id_category', 'left')
                ->where('a.so_number', $row['so_number'])
                ->group_by('c.id')
                ->get()->result_array();

            $kategori_pr_str = implode(', ', array_column($kategori_pr, 'kategori'));

            // Status approval
            $status_info = $this->get_status_info($row);

            $nestedData = [
                "<div align='center'>{$nomor}</div>",
                "<div align='center'>{$row['no_pr']}</div>",
                "<div align='left'>{$kategori_pr_str}</div>",
                "<div align='left'>" . implode('<br><br>', $list_barang) . "</div>",
                "<div align='left'>" . implode('<br><br>', $list_qty_barang) . "</div>",
                "<div align='center'>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</div>",
                "<div align='center'><span class='badge' style='background-color: {$status_info['warna']};'>{$status_info['sts']}</span></div>",
                "<div align='center'>{$row['request_by']}</div>",
                "<div align='center'>{$row['request_date']}</div>",
                "<div align='center'>{$status_info['aksi']}</div>",
            ];

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

    public function get_query_pr_stok($like = null, $column_order = null, $column_dir = null, $limit_start = 0, $limit_length = 10)
    {
        $columns_order_by = [
            0 => 'a.no_pr',
            1 => 'a.tgl_dibutuhkan',
            2 => 'a.created_date'
        ];

        $this->db->select("a.*, u.nm_lengkap as request_by, DATE_FORMAT(a.created_date, '%d %M %Y') as request_date");
        $this->db->from('material_planning_base_on_produksi a');
        $this->db->join('users u', 'u.id_user = a.created_by', 'left');
        $this->db->where("a.category", 'pr stok');
        $this->db->where("a.booking_date IS NOT NULL");
        $this->db->where("a.close_pr IS NULL");

        if ($like) {
            $this->db->group_start();
            $this->db->like('a.no_pr', $like);
            $this->db->or_like('u.nm_lengkap', $like);
            $this->db->group_end();
        }

        $totalData = $this->db->count_all_results('', false);
        $totalFiltered = $totalData;

        if ($column_order !== null && isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        } else {
            $this->db->order_by('a.id', 'desc');
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

    public function server_side_reorder_point()
    {
        $requestData = $_REQUEST;

        $fetch = $this->query_data_json_reorder_point(
            $requestData['category'],
            $requestData['search']['value'],
            $requestData['order'][0]['column'],
            $requestData['order'][0]['dir'],
            $requestData['start'],
            $requestData['length']
        );

        $data = [];
        $urut = 1;
        $GET_KEBUTUHAN_PER_MONTH = get_kebutuhanPerMonth();
        $GET_WAREHOUSE_STOCK = getStokBarangAll();

        foreach ($fetch['query']->result_array() as $index => $row) {
            $nomor = ($requestData['order'][0]['dir'] === 'asc')
                ? $urut + $requestData['start']
                : ($fetch['totalData'] - $requestData['start']) - $index;

            $tgl_now = date('Y-m-d');
            $tgl_next_month = !empty($row['tgl_dibutuhkan']) ? $row['tgl_dibutuhkan'] : date('Y-m-20', strtotime('+1 month'));

            $STOCK_WRH = $GET_WAREHOUSE_STOCK[$row['id']]['stok'] ?? 0;
            $kebutuhnMonth = $GET_KEBUTUHAN_PER_MONTH[$row['id']]['kebutuhan'] ?? 0;

            $purchase_calc = ($kebutuhnMonth * 1.5) - $STOCK_WRH;
            $purchase2x = max(0, $purchase_calc);
            $purchase2 = $row['request'] ?? $purchase2x;

            $purchase_value = $purchase2 > 0 ? number_format($purchase2, 2) : '';
            $purchase_value_pack = $row['request_pack'] > 0 ? number_format($row['request_pack'], 2) : '';

            $unit_satuan = get_name('ms_satuan', 'code', 'id', $row['id_unit']) ?: '';
            $unit_packing = get_name('ms_satuan', 'code', 'id', $row['id_unit_gudang']) ?: '';

            $spec_pr = $row['spec_pr'] ?? '';
            $info_pr = $row['info_pr'] ?? '';

            $nestedData = [
                "<div align='center'>{$nomor}</div>",
                "<div align='left'>{$row['id_stock']}</div>",
                "<div align='left'>{$row['stock_name']}</div>",
                "<div align='left'>" . strtoupper($row['category_type']) . "</div>",
                "<div align='right'>" . number_format($STOCK_WRH) . "</div>",
                "<div align='right'>" . number_format($kebutuhnMonth) . "</div>",
                "<div align='right'>" . number_format($kebutuhnMonth * 1.5) . "</div>",
                "<div align='right'><input type='text' name='purchase_{$nomor}' id='purchase_{$nomor}' value='{$purchase_value}' data-id='{$row['id']}' data-no='{$nomor}' data-konversi='{$row['konversi']}' class='form-control input-md text-right input_qty_satuan maskM changeSave purchase_{$row['id']}' style='width:100%;'></div>",
                "<div align='left'><select id='satuan_{$nomor}' class='chosen_select form-control input-md'><option value='{$row['id_unit']}'>" . strtoupper($unit_satuan) . "</option></select><input type='hidden' name='tanggal_{$nomor}' id='tanggal_{$nomor}' data-id='{$row['id']}' data-no='{$nomor}' class='form-control input-md tgl changeSave' style='width:100%;' readonly value='{$tgl_next_month}'></div>",
                "<div align='right'><input type='text' name='purchase_pack_{$nomor}' id='purchase_pack_{$nomor}' value='{$purchase_value_pack}' data-id='{$row['id']}' data-no='{$nomor}' data-konversi='{$row['konversi']}' class='form-control input-md text-right input_qty_packing purchase_pack_{$row['id']} maskM changeSave' style='width:100%;'></div>",
                "<div align='center'>" . strtoupper($unit_packing) . "</div>",
                "<div align='left'><input type='text' name='spec_{$nomor}' id='spec_{$nomor}' data-id='{$row['id']}' data-no='{$nomor}' class='form-control input-md changeSave' style='width:100%;' placeholder='Spec' value='{$spec_pr}'></div>",
                "<div align='left'><input type='text' name='info_{$nomor}' id='info_{$nomor}' data-id='{$row['id']}' data-no='{$nomor}' class='form-control input-md changeSave' style='width:100%;' placeholder='Info' value='{$info_pr}'></div>",
            ];

            $data[] = $nestedData;
            $urut++;
        }

        echo json_encode([
            "draw" => intval($requestData['draw']),
            "recordsTotal" => intval($fetch['totalData']),
            "recordsFiltered" => intval($fetch['totalFiltered']),
            "data" => $data
        ]);
    }

    public function query_data_json_reorder_point($category = null, $like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
    {
        $this->db->select("a.*, b.nm_category AS category_type")
            ->from('accessories a')
            ->join('accessories_category b', 'a.id_category = b.id', 'left')
            ->where('a.status', '1')
            ->where('a.deleted_date IS NULL');

        if (!empty($category)) {
            $this->db->where('a.id_category', $category);
        }

        if (!empty($like_value)) {
            $this->db->group_start()
                ->like('a.id_stock', $like_value)
                ->or_like('a.stock_name', $like_value)
                ->or_like('b.nm_category', $like_value)
                ->group_end();
        }

        $data['totalData'] = $this->db->count_all_results('', false);
        $data['totalFiltered'] = $data['totalData'];

        $columns_order_by = [
            0 => 'a.id',
            1 => 'a.id_stock',
            2 => 'a.stock_name'
        ];

        if (isset($columns_order_by[$column_order])) {
            $this->db->order_by($columns_order_by[$column_order], $column_dir);
        }

        if ($limit_length != -1) {
            $this->db->limit($limit_length, $limit_start);
        }

        $data['query'] = $this->db->get();
        return $data;
    }

    private function get_status_info($row)
    {
        $warna = 'blue';
        $sts = 'Waiting Approval Head';
        $aksi = '';
        $so_number = $row['so_number'];

        // Cek apakah semua detail sudah diapprove
        $getCheck = $this->db
            ->get_where('material_planning_base_on_produksi_detail', [
                'so_number' => $so_number,
                'status_app' => 'N'
            ])
            ->result();

        $valid_edit = true;

        // Logika status
        if (($row['sts_reject1'] == '1' || $row['sts_reject2'] == '1' || $row['sts_reject3'] == '1') && $row['rejected'] == 1) {
            if ($row['sts_reject1'] == '1') {
                $warna = 'red';
                $sts = 'Rejected By Head';
            } elseif ($row['sts_reject2'] == '1') {
                $warna = 'red';
                $sts = 'Rejected By Cost Control';
            } elseif ($row['sts_reject3'] == '1') {
                $warna = 'red';
                $sts = 'Rejected By Management';
            }
        } else {
            if (empty($row['app_1']) && empty($row['app_2']) && empty($row['app_3'])) {
                $warna = 'blue';
                $sts = 'Waiting Approval Head';
            } elseif (!empty($row['app_1']) && empty($row['app_2']) && empty($row['app_3'])) {
                $warna = 'blue';
                $sts = 'Waiting Approval Cost Control';
            } elseif (!empty($row['app_1']) && !empty($row['app_2']) && empty($row['app_3'])) {
                $warna = 'blue';
                $sts = 'Waiting Approval Management';
            } elseif ($row['sts_app'] == 'Y') {
                $warna = 'green';
                $sts = 'Approved';
                $valid_edit = false;
            }
        }

        if (count($getCheck) <= 0) {
            $warna = 'green';
            $sts = 'Approved';
            $valid_edit = false;
        }

        // Tombol aksi
        $segment = $this->uri->segment(1);
        $btn_detail = "<a href='" . site_url("$segment/detail_planning/$so_number") . "' class='btn btn-sm btn-warning' title='Detail PR'><i class='fa fa-eye'></i></a>";

        $btn_edit = '';
        if ($valid_edit && $this->ENABLE_MANAGE) {
            $btn_edit = "<a href='" . site_url("$segment/edit_planning/$so_number") . "' class='btn btn-sm btn-info' title='Edit PR'><i class='fa fa-edit'></i></a>";
        }

        $btn_print = "<a href='" . site_url("$segment/PrintH2/$so_number") . "' class='btn btn-sm btn-primary' title='Print PR' target='_blank'><i class='fa fa-print'></i></a>";

        $btn_close = '';
        if ($this->ENABLE_DELETE) {
            $btn_close = "<button type='button' class='btn btn-sm btn-danger close_pr_modal' data-so_number='$so_number' title='Close PR'><i class='fa fa-close'></i></button>";
        }

        $aksi = $btn_detail . ' ' . $btn_edit . ' ' . $btn_print . ' ' . $btn_close;

        return [
            'warna' => $warna,
            'sts'   => $sts,
            'aksi'  => $aksi
        ];
    }
}
