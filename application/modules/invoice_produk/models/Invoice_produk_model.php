<?php
class Invoice_produk_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Server-side DataTables source untuk tab "Invoice Delivery".
	 *
	 * Menggantikan pola lama di Invoice_produk::change_tab() yang mengambil
	 * SEMUA baris surat_jalan lalu menjalankan 3-6 query tambahan per baris
	 * (N+1 query problem, ribuan query untuk ~1.800 baris data).
	 *
	 * Di sini paging dilakukan di level SQL (hanya baris yang ditampilkan yang
	 * diambil), dan seluruh query "per-baris" yang lama digabung jadi query
	 * batch (WHERE ... IN (...)) yang dijalankan sekali untuk seluruh baris
	 * pada halaman saat ini.
	 */
	public function data_side_delivery()
	{
		$requestData = $_REQUEST;

		$search       = isset($requestData['search']['value']) ? trim($requestData['search']['value']) : '';
		$col_order    = isset($requestData['order'][0]['column']) ? (int) $requestData['order'][0]['column'] : 2;
		$col_dir      = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : 'desc';
		$limit_start  = isset($requestData['start']) ? (int) $requestData['start'] : 0;
		$limit_length = isset($requestData['length']) ? (int) $requestData['length'] : 10;

		$start = $this->input->post('start_date', true);
		$end   = $this->input->post('end_date', true);
		$norm  = function ($v) {
			if (!$v) return null;
			return preg_match('#^\d{4}-\d{2}-\d{2}$#', $v) ? $v : null;
		};
		$start = $norm($start);
		$end   = $norm($end);

		$columns_order_by = [
			0 => 'sj.no_surat_jalan',
			1 => 'sj.no_so',
			2 => 'sj.delivery_date',
			3 => 'i.id_invoice',
			4 => 'i.created_on',
			5 => 'c.name_customer',
		];

		$apply_base_filters = function () use ($start, $end) {
			$this->db->from('surat_jalan sj');
			$this->db->join('tr_invoice_sales i', 'sj.no_surat_jalan = i.id_billing AND i.tipe_billing="delivery"', 'left');
			$this->db->join('spk_delivery a', 'a.no_delivery = sj.no_delivery', 'left');
			$this->db->join('sales_order b', 'b.no_so = sj.no_so', 'left');
			$this->db->join('master_customers c', 'c.id_customer = b.id_customer', 'left');
			$this->db->join('(SELECT no_surat_jalan, SUM(qty_terkirim) AS total_terkirim FROM surat_jalan_detail GROUP BY no_surat_jalan) sjd_sum', 'sjd_sum.no_surat_jalan = sj.no_surat_jalan', 'left');
			$this->db->where('sj.status !=', 'ON DELIVER');
			$this->db->where('sj.status IS NOT NULL');
			// Tampilkan hanya jika: sudah punya invoice (tetap tampil untuk view/print)
			// ATAU data relasi SO+customer valid DAN ada barang yang terkirim (qty_terkirim > 0)
			$this->db->where('(
				i.id_invoice IS NOT NULL
				OR (
					b.no_so IS NOT NULL
					AND c.id_customer IS NOT NULL
					AND COALESCE(sjd_sum.total_terkirim, 0) > 0
				)
			)');

			if ($start && $end) {
				$this->db->where('DATE(i.created_on) >=', $start);
				$this->db->where('DATE(i.created_on) <=', $end);
			} elseif ($start) {
				$this->db->where('DATE(i.created_on) >=', $start);
			} elseif ($end) {
				$this->db->where('DATE(i.created_on) <=', $end);
			}
		};

		$apply_search = function () use ($search) {
			if (!empty($search)) {
				$this->db->group_start();
				$this->db->like('sj.no_surat_jalan', $search);
				$this->db->or_like('sj.no_so', $search);
				$this->db->or_like('i.id_invoice', $search);
				$this->db->or_like('c.name_customer', $search);
				$this->db->group_end();
			}
		};

		// 1. Total data (tanpa search box, hanya filter tanggal + aturan tampil)
		$apply_base_filters();
		$totalData = $this->db->count_all_results();

		// 2. Total filtered (dengan search box)
		$apply_base_filters();
		$apply_search();
		$totalFiltered = $this->db->count_all_results();

		// 3. Ambil data untuk halaman yang diminta saja
		$this->db->select('sj.no_surat_jalan, sj.delivery_date, sj.no_delivery, sj.no_so, c.name_customer, i.id_invoice, i.created_on, i.is_cancel');
		$apply_base_filters();
		$apply_search();
		if (isset($columns_order_by[$col_order])) {
			$this->db->order_by($columns_order_by[$col_order], $col_dir);
		} else {
			$this->db->order_by('sj.delivery_date', 'desc');
		}
		$this->db->order_by('sj.created_at', 'desc');
		if ($limit_length != -1) {
			$this->db->limit($limit_length, $limit_start);
		}
		$rows = $this->db->get()->result();

		$data = [];

		if (!empty($rows)) {
			$no_sj_list      = array_values(array_unique(array_map(function ($r) {
				return $r->no_surat_jalan;
			}, $rows)));
			$no_so_list      = array_values(array_unique(array_map(function ($r) {
				return $r->no_so;
			}, $rows)));
			$id_invoice_list = array_values(array_unique(array_filter(array_map(function ($r) {
				return $r->id_invoice;
			}, $rows))));

			// Batch 1: hitung nilai invoice per SJ (gantikan query per-baris get_hitung_nilai_invoice)
			$subtotal_map = [];
			if (!empty($no_sj_list)) {
				$hitung_rows = $this->db
					->select('sjd.no_surat_jalan, sod.harga_penawaran, sjd.qty_terkirim AS qty')
					->from('surat_jalan_detail sjd')
					->join('sales_order_detail sod', 'sod.id = sjd.id_so_det', 'left')
					->where_in('sjd.no_surat_jalan', $no_sj_list)
					->get()
					->result();

				foreach ($hitung_rows as $h) {
					$total_harga = round(((float) $h->harga_penawaran * (float) $h->qty), -2); // bulat ribuan
					$subtotal_map[$h->no_surat_jalan] = ($subtotal_map[$h->no_surat_jalan] ?? 0) + $total_harga;
				}
			}

			// Batch 2: tentukan SJ pertama per SO (gantikan query per-baris get_sj_pertama)
			$sj_pertama_map = [];
			if (!empty($no_so_list)) {
				$sj_rows = $this->db
					->select('no_so, no_surat_jalan')
					->from('surat_jalan')
					->where_in('no_so', $no_so_list)
					->order_by('no_so', 'asc')
					->order_by('delivery_date', 'asc')
					->order_by('id', 'asc')
					->get()
					->result();

				foreach ($sj_rows as $s) {
					if (!isset($sj_pertama_map[$s->no_so])) {
						$sj_pertama_map[$s->no_so] = $s->no_surat_jalan;
					}
				}
			}

			// Batch 3: freight & diskon_khusus per SO, dipakai hanya jika SJ ini SJ pertama
			$freight_map = [];
			if (!empty($no_so_list)) {
				$freight_rows = $this->db
					->select('a.no_so, b.freight, b.diskon_khusus')
					->from('sales_order a')
					->join('penawaran b', 'b.id_penawaran = a.id_penawaran')
					->where_in('a.no_so', $no_so_list)
					->get()
					->result();

				foreach ($freight_rows as $f) {
					$freight_map[$f->no_so] = $f;
				}
			}

			// Batch 4: status retur per invoice (gantikan query per-baris tr_retur)
			$retur_map = [];
			if (!empty($id_invoice_list)) {
				$retur_rows = $this->db
					->select('id_invoice, status')
					->from('tr_retur')
					->where_in('id_invoice', $id_invoice_list)
					->get()
					->result();

				foreach ($retur_rows as $r) {
					$retur_map[$r->id_invoice] = $r;
				}
			}

			foreach ($rows as $item) {
				$is_sj_pertama = isset($sj_pertama_map[$item->no_so]) && $sj_pertama_map[$item->no_so] === $item->no_surat_jalan;

				$freight       = 0;
				$diskon_khusus = 0;
				if ($is_sj_pertama && isset($freight_map[$item->no_so])) {
					$freight       = $freight_map[$item->no_so]->freight;
					$diskon_khusus = $freight_map[$item->no_so]->diskon_khusus;
				}

				$subtotal = $subtotal_map[$item->no_surat_jalan] ?? 0;

				$includeppn      = $subtotal - $diskon_khusus;
				$excludeppn      = ($includeppn + $freight) / 1.11;
				$dpp             = $excludeppn * 11 / 12;
				$ppn             = $dpp * 12 / 100;
				$nominal_invoice = ($excludeppn + $ppn);

				$tanggal = ($item->created_on != null) ? date('d/M/Y', strtotime($item->created_on)) : '';

				$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->no_surat_jalan . '" data-tipe_billing="delivery" title="Create"><i class="fa fa-check"></i></button>';

				// id_invoice sudah didapat dari JOIN di query utama, jadi tidak perlu query count/row lagi
				$has_invoice = !empty($item->id_invoice);

				if ($item->is_cancel == 1) {
					$button = '<span class="badge bg-red">Credit Note (Full)</span>';
				} elseif ($item->is_cancel == 2) {
					$view   = '<button type="button" class="btn btn-sm btn-info view_invoice_modal_delivery" data-no_so="' . $item->no_so . '" data-id="' . $item->no_surat_jalan . '" data-tipe_billing="delivery" data-id_invoice="' . $item->id_invoice . '"><i class="fa fa-eye"></i></button>';
					$print  = '<a href="' . site_url('invoice_produk/print_invoice_delivery/' . $item->id_invoice) . '" target="_blank" class="btn btn-sm btn-primary" title="Print Invoice"><i class="fa fa-print"></i></a>';
					$button = $view . ' ' . $print . ' <span class="badge bg-orange">CN Partial</span>';
				} elseif ($has_invoice) {
					$view   = '<button type="button" class="btn btn-sm btn-info view_invoice_modal_delivery" data-no_so="' . $item->no_so . '" data-id="' . $item->no_surat_jalan . '" data-tipe_billing="delivery" data-id_invoice="' . $item->id_invoice . '"><i class="fa fa-eye"></i></button>';
					$print  = '<a href="' . site_url('invoice_produk/print_invoice_delivery/' . $item->id_invoice) . '" target="_blank" class="btn btn-sm btn-primary print_invoice_delivery" data-id_invoice="' . $item->id_invoice . '" title="Print Invoice"><i class="fa fa-print"></i></a>';

					$retur_row = $retur_map[$item->id_invoice] ?? null;
					if ($retur_row && (int) $retur_row->status === 0) {
						$button = $view . ' ' . $print . ' <span class="badge bg-yellow" style="color:#333;">Retur Pending</span>';
					} elseif ($retur_row && (int) $retur_row->status === 1) {
						$button = $view . ' ' . $print . ' <span class="badge bg-blue">Menunggu CN</span>';
					} else {
						$button = $view . ' ' . $print;
					}
				} else {
					$button = $edit;
				}

				$data[] = [
					"<div class='text-center'>" . $item->no_surat_jalan . "</div>",
					"<div class='text-center'>" . $item->no_so . "</div>",
					"<div class='text-center'>" . date('d/M/Y', strtotime($item->delivery_date)) . "</div>",
					"<div class='text-center'>" . $item->id_invoice . "</div>",
					"<div class='text-center'>" . $tanggal . "</div>",
					"<div class='text-left'>" . $item->name_customer . "</div>",
					"<div class='text-right'>" . number_format($nominal_invoice, 2) . "</div>",
					"<div class='text-center'>" . $button . "</div>",
				];
			}
		}

		echo json_encode([
			'draw'            => intval($requestData['draw'] ?? 0),
			'recordsTotal'    => intval($totalData),
			'recordsFiltered' => intval($totalFiltered),
			'data'            => $data,
		]);
	}

	function generate_id()
	{
		$query = $this->db->query("SELECT MAX(id) as max_id FROM tr_billing_plan WHERE id LIKE '%BILLING-" . date('Ymd') . "%'");
		$row = $query->row_array();
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 17, 5);
		$counter = $max_id1 + 1;
		$counter = sprintf('%05s', $counter);
		$idcust = "BILLING-" . date('Ymd') . "-" . $counter;
		return $idcust;
	}

	function  generate_id_invoice()
	{
		$query = $this->db->query("SELECT MAX(id_invoice) as max_id FROM tr_invoice_sales WHERE id_invoice LIKE '%INV-OM-" . date('y') . "-" . date('m') . "%'");
		$row = $query->row_array();
		$max_id = $row['max_id'];
		$max_id1 = (int) substr($max_id, 13, 3);
		$counter = $max_id1 + 1;
		$counter = sprintf('%03s', $counter);
		$idcust = "INV-OM-" . date('y') . '-' . date('m') . '-' . $counter;
		return $idcust;
	}
}
