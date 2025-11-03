<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_delivery_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->ENABLE_ADD     = has_permission('SPK_Delivery.Add');
    $this->ENABLE_MANAGE  = has_permission('SPK_Delivery.Manage');
    $this->ENABLE_VIEW    = has_permission('SPK_Delivery.View');
    $this->ENABLE_DELETE  = has_permission('SPK_Delivery.Delete');
  }

  public function data_side_spk_deliv()
  {
    $controller    = ucfirst(strtolower($this->uri->segment(1)));
    $requestData   = $_REQUEST;

    $fetch         = $this->get_query_json_spk_deliv(
      $requestData['sales_order'],
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
    $urut1 = 1;
    $urut2 = 0;

    // ========== PRELOAD: Mapping qty_delivery ==========
    $result_data = $query->result_array();
    $no_deliveries = array_column($result_data, 'no_delivery');
    $qty_map = [];

    if (!empty($no_deliveries)) {
      $qtys = $this->db->select('no_delivery, SUM(qty_delivery) as qty_delivery')
        ->from('spk_delivery_detail')
        ->where_in('no_delivery', $no_deliveries)
        ->group_by('no_delivery')
        ->get()->result_array();

      foreach ($qtys as $rowQty) {
        $qty_map[$rowQty['no_delivery']] = $rowQty['qty_delivery'];
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

      $nestedData[] = "<div align='center'>" . $nomor . "</div>";
      $nestedData[] = "<div align='center'>" . strtoupper($row['no_delivery']) . "</div>";
      $nestedData[] = "<div align='center'>" . strtoupper($row['no_so']) . "</div>";
      $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
      $nestedData[] = "<div align='center'>" . strtoupper($row['pengiriman']) . "</div>";
      $nestedData[] = "<div align='center'>" . date('d/M/Y', strtotime($row['tanggal_spk'])) . "</div>";

      $qty_delivery = isset($qty_map[$row['no_delivery']]) ? $qty_map[$row['no_delivery']] : 0;

      // get qty_order dari sales_order_detail
      $getQTYSO = $this->db->select('SUM(qty_order) AS qty_order')
        ->get_where('sales_order_detail', ['no_so' => $row['no_so']])
        ->row_array();
      $qty_order = !empty($getQTYSO['qty_order']) ? $getQTYSO['qty_order'] : 0;

      // Status logic
      $status = 'Unknown';
      $warna = 'default';
      $action = "<a href='javascript:void(0);' data-id='" . $row['no_delivery'] . "' class='btn btn-sm btn-warning view-spk' title='View'><i class='fa fa-eye'></i></a>";

      switch ($row['status']) {
        case 'NOT YET DELIVER':
          $status = 'Waiting Loading';
          $warna = 'blue';
          break;
        case 'LOADING':
          $status = 'On Loading';
          $warna = 'yellow';
          break;
        case 'ON DELIVER':
          $status = 'Delivery';
          $warna = 'green';
          break;
        case 'DELIVERY CONFIRMED':
          if ($qty_order == $qty_delivery) {
            $status = 'Closed';
            $warna = 'green';
          } elseif ($qty_order > $qty_delivery && $qty_delivery > 0) {
            $status = 'Partial SPK';
            $warna = 'yellow';
          }
          break;
      }

      $nestedData[] = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";
      $nestedData[] = "<div align='center'>" . $action . "</div>";

      // Optional print button
      $ButtonPrint = "";
      $LI_A = "";
      $getSPKDelivery = $this->db->get_where('spk_delivery', ['no_so' => $row['no_so'], 'deleted_date' => NULL])->result_array();
      foreach ($getSPKDelivery as $value) {
        $LI_A .= "<li><a href='" . base_url('spk_delivery/print_spk/' . $value['no_delivery']) . "' target='_blank'>" . $value['no_delivery'] . "</a></li>";
      }

      if ($qty_delivery > 0) {
        $ButtonPrint = '<div class="dropdown">
        <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Print
        <span class="caret"></span></button>
        <ul class="dropdown-menu">' . $LI_A . '</ul>
      </div>';
      }

      // Optional create button
      $create = "";
      if ($qty_order != $qty_delivery && $this->ENABLE_ADD) {
        $create = "<a href='" . base_url('spk_delivery/add/' . $row['no_so']) . "' class='btn btn-sm btn-primary' title='Create SPK Delivery' data-role='qtip'><i class='fa fa-plus'></i></a>";
      }

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


  public function get_query_json_spk_deliv($sales_order = null, $like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null)
  {
    $columns_order_by = [
      0 => 'a.no_delivery',
      1 => 'a.no_so',
      2 => 'b.id_penawaran',
      3 => 'c.name_customer',
      4 => 'a.pengiriman',
      5 => 'a.tanggal_spk',
    ];

    // ====================
    // 1. Total Data Count
    // ====================
    $this->db->from('spk_delivery a');
    $this->db->join('sales_order d', 'a.no_so = d.no_so', 'left');
    $this->db->join('penawaran b', 'd.id_penawaran = b.id_penawaran', 'left');
    $this->db->join('master_customers c', 'b.id_customer = c.id_customer', 'left');
    $this->db->where('a.deleted_date IS NULL');
    if ($sales_order) $this->db->where('a.no_so', $sales_order);
    $totalData = $this->db->count_all_results();

    // ========================
    // 2. Total Filtered Count
    // ========================
    $this->db->from('spk_delivery a');
    $this->db->join('sales_order d', 'a.no_so = d.no_so', 'left');
    $this->db->join('penawaran b', 'd.id_penawaran = b.id_penawaran', 'left');
    $this->db->join('master_customers c', 'b.id_customer = c.id_customer', 'left');
    $this->db->where('a.deleted_date IS NULL');
    if ($sales_order) $this->db->where('a.no_so', $sales_order);
    if ($like_value) {
      $this->db->group_start();
      $this->db->like('a.no_so', $like_value);
      $this->db->or_like('a.no_delivery', $like_value);
      $this->db->or_like('b.id_penawaran', $like_value);
      $this->db->or_like('c.name_customer', $like_value);
      $this->db->group_end();
    }
    $totalFiltered = $this->db->count_all_results();

    // ========================
    // 3. Data Query (Paginated)
    // ========================
    $this->db->select('
    a.no_delivery,
    a.no_so,
    b.id_penawaran,
    c.name_customer,
    a.tanggal_spk,
    a.delivery_address,
    a.status,
    a.upload_spk,
    a.pengiriman,
    a.created_by,
    a.created_date
  ');
    $this->db->from('spk_delivery a');
    $this->db->join('sales_order d', 'a.no_so = d.no_so', 'left');
    $this->db->join('penawaran b', 'd.id_penawaran = b.id_penawaran', 'left');
    $this->db->join('master_customers c', 'b.id_customer = c.id_customer', 'left');
    $this->db->where('a.deleted_date IS NULL');

    if ($sales_order) $this->db->where('a.no_so', $sales_order);
    if ($like_value) {
      $this->db->group_start();
      $this->db->like('a.no_so', $like_value);
      $this->db->or_like('a.no_delivery', $like_value);
      $this->db->or_like('b.id_penawaran', $like_value);
      $this->db->or_like('c.name_customer', $like_value);
      $this->db->group_end();
    }

    // Ordering
    if ($column_order !== null && isset($columns_order_by[$column_order])) {
      $this->db->order_by($columns_order_by[$column_order], $column_dir);
    } else {
      $this->db->order_by('a.tanggal_spk', 'desc');
    }

    // Pagination
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


  //Re-Print
  public function data_side_spk_reprint()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_spk_reprint(
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData      = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query          = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_USER = get_list_user();
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      if ($asc_desc == 'desc') {
        $nomor = $urut1 + $start_dari;
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='left'>ORIGA</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_product']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty']) . "</div>";
      $username = (!empty($GET_USER[$row['release_by']]['username'])) ? $GET_USER[$row['release_by']]['username'] : '-';
      $nestedData[]  = "<div align='center'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['release_date'])) . "</div>";

      $print  = "<a href='" . base_url('plan_mixing/print_spk/' . $row['kode_det']) . "' target='_blank' title='Print SPK' data-role='qtip'>Print</a>";

      $nestedData[]  = "<div align='center'>" . $print . "</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_spk_reprint($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.kode,
              b.no_spk,
              b.request_by AS release_by,
              b.request_date AS release_date,
              b.qty,
              b.kode_det
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1',
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request = 'Y' AND b.status_id = '1' AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.nama_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.kode LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'nama_product',
      4 => 'b.no_spk',
      5 => 'propose'
    );

    $sql .= " ORDER BY b.request_date DESC,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
