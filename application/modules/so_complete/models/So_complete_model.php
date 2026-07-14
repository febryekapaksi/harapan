<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * So_complete_model
 * 
 * Model untuk menu SO Complete - menampilkan SO dengan status SPK Lengkap
 */
class So_complete_model extends BF_Model
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Server-side DataTables untuk SO Complete
   * Filter: status SO = 'A' (Deal) DAN status_spk = 'SPK Lengkap'
   */
  public function get_json_so_complete()
  {
    $requestData = $_REQUEST;

    $start_date = $this->input->post('start_date');
    $end_date   = $this->input->post('end_date');

    $fetch = $this->get_query_json_so_complete(
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length'],
      $start_date,
      $end_date
    );

    $totalData     = $fetch['totalData'];
    $totalFiltered = $fetch['totalFiltered'];
    $query         = $fetch['query'];

    $data = [];
    $urut = 1;

    foreach ($query->result_array() as $row) {
      $nomor = $urut + $requestData['start'];

      // Tipe Quotation badge
      $tipe_quot = '';
      if ($row['tipe_penawaran'] === "Dropship") {
        $tipe_quot = "<span class='badge bg-blue'>Dropship</span>";
      } else {
        $tipe_quot = "<span class='badge bg-aqua'>Standard</span>";
      }

      // Status SO
      $status_label = "<span class='badge bg-green'>SPK Lengkap</span>";
      if ($row['status_so'] == 'CLOSED') {
        $status_label .= " <span class='badge bg-red'>Closed</span>";
      }

      // Action buttons
      $action = "";
      $action .= "<a href='" . base_url("so_complete/detail/{$row['no_so']}") . "' class='btn btn-sm btn-info' title='Detail'><i class='fa fa-eye'></i></a> ";
      $action .= "<a target='_blank' href='" . base_url("sales_order/print_so/{$row['no_so']}") . "' class='btn btn-sm btn-warning' title='Print SO'><i class='fa fa-print'></i></a> ";

      // Tombol Cancel SO hanya jika belum CLOSED dan masih ada sisa
      if ($row['status_so'] != 'CLOSED') {
        $action .= "<button class='btn btn-sm btn-danger cancel-so' data-no='{$row['no_so']}' title='Cancel Sisa SO'><i class='fa fa-times'></i> Cancel</button> ";
      }

      $tgl_so = ($row['tgl_so'] != null) ? date('d/M/Y', strtotime($row['tgl_so'])) : "";

      $nestedData = [];
      $nestedData[] = "<div align='center'>{$nomor}</div>";
      $nestedData[] = "<div align='left'>" . $row['no_so'] . "</div>";
      $nestedData[] = "<div align='left'>" . $row['id_penawaran'] . "</div>";
      $nestedData[] = "<div align='center'>" . $tgl_so . "</div>";
      $nestedData[] = "<div align='left'>" . strtoupper($row['name_customer']) . "</div>";
      $nestedData[] = "<div align='left'>" . ucfirst($row['sales']) . "</div>";
      $nestedData[] = "<div align='right'>" . number_format($row['nilai_so'], 2) . "</div>";
      $nestedData[] = "<div align='center'>{$tipe_quot}</div>";
      $nestedData[] = "<div align='center'>{$status_label}</div>";
      $nestedData[] = "<div align='center'>{$action}</div>";

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

  /**
   * Query builder untuk DataTables SO Complete
   */
  public function get_query_json_so_complete($like_value = null, $column_order = null, $column_dir = null, $limit_start = null, $limit_length = null, $start_date = null, $end_date = null)
  {
    $columns_order_by = [
      0 => 'so.no_so',
      1 => 'so.no_so',
      2 => 'p.id_penawaran',
      3 => 'so.tgl_so',
      4 => 'c.name_customer',
      5 => 'p.sales',
      6 => 'so.nilai_so',
      7 => 'p.tipe_penawaran',
      8 => 'so.status_spk',
    ];

    // ==== Base WHERE conditions ====
    // SO harus Deal (status = 'A') dan SPK Lengkap
    $base_where = function () {
      $this->db->where('so.status', 'A');
      $this->db->where('so.status_spk', 'SPK Lengkap');
    };

    // ==== Total data (tanpa search/date) ====
    $this->db->from('sales_order so');
    $this->db->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left');
    $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
    $base_where();
    $totalData = $this->db->count_all_results();

    // ==== Total filtered ====
    $this->db->from('sales_order so');
    $this->db->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left');
    $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
    $base_where();

    if (!empty($start_date) && !empty($end_date)) {
      $this->db->where('so.tgl_so >=', $start_date);
      $this->db->where('so.tgl_so <=', $end_date);
    } elseif (!empty($start_date)) {
      $this->db->where('so.tgl_so >=', $start_date);
    } elseif (!empty($end_date)) {
      $this->db->where('so.tgl_so <=', $end_date);
    }

    if ($like_value) {
      $this->db->group_start();
      $this->db->like('so.no_so', $like_value);
      $this->db->or_like('p.id_penawaran', $like_value);
      $this->db->or_like('c.name_customer', $like_value);
      $this->db->or_like('p.sales', $like_value);
      $this->db->group_end();
    }
    $totalFiltered = $this->db->count_all_results();

    // ==== Fetch data ====
    $this->db->select('so.no_so, so.tgl_so, so.nilai_so, so.status, so.status_spk, so.status_so,
                       p.id_penawaran, p.total_penawaran, p.tipe_penawaran, p.sales,
                       c.name_customer');
    $this->db->from('sales_order so');
    $this->db->join('penawaran p', 'p.id_penawaran = so.id_penawaran', 'left');
    $this->db->join('master_customers c', 'so.id_customer = c.id_customer', 'left');
    $base_where();

    if (!empty($start_date) && !empty($end_date)) {
      $this->db->where('so.tgl_so >=', $start_date);
      $this->db->where('so.tgl_so <=', $end_date);
    } elseif (!empty($start_date)) {
      $this->db->where('so.tgl_so >=', $start_date);
    } elseif (!empty($end_date)) {
      $this->db->where('so.tgl_so <=', $end_date);
    }

    if ($like_value) {
      $this->db->group_start();
      $this->db->like('so.no_so', $like_value);
      $this->db->or_like('p.id_penawaran', $like_value);
      $this->db->or_like('c.name_customer', $like_value);
      $this->db->or_like('p.sales', $like_value);
      $this->db->group_end();
    }

    if ($column_order !== null && isset($columns_order_by[$column_order])) {
      $this->db->order_by($columns_order_by[$column_order], $column_dir);
    } else {
      $this->db->order_by('so.tgl_so', 'desc');
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
