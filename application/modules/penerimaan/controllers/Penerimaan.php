<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class Penerimaan extends Admin_Controller
{

	protected $viewPermission   = 'Penerimaan_Uang.View';
	protected $addPermission    = 'Penerimaan_Uang.Add';
	protected $managePermission = 'Penerimaan_Uang.Manage';
	protected $deletePermission = 'Penerimaan_Uang.Delete';

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			'Penerimaan/master_model',
			'Penerimaan/penerimaan_model',
			'Penerimaan/All_model',
			'Penerimaan/Jurnal_model',
			'Penerimaan/Acc_model'
		));

		date_default_timezone_set('Asia/Bangkok');
	}

	public function index()
	{
		$so = $this->penerimaan_model->get_data_pn();
		$data = array(
			'title'			=> 'Penerimaan',
			'action'		=> 'index',
			'results'		=> $so,
		);
		$this->template->set($data);
		$this->template->render('list_payment');
	}

	public function index_cash()
	{
		$this->template->page_icon('fa fa-money');
		$this->template->title('Penerimaan Uang Cash');
		$this->template->render('list_payment_cash');
	}

	public function data_side_penerimaan_cash()
	{
		$this->penerimaan_model->get_data_json_payment_cash();
	}

	public function add_cash()
	{
		// Ambil daftar customer dari invoice yang masih aktif
		$this->db->select('c.id_customer, c.name_customer, c.npwp, c.telephone, c.fax, c.address_office, a.id_so');
		$this->db->from('tr_invoice_sales a');
		$this->db->join('sales_order b', 'b.no_so = a.id_so', 'left');
		$this->db->join('master_customers c', 'c.id_customer = b.id_customer', 'left');
		$this->db->where('c.deleted_by IS NULL');
		$this->db->where('a.sts', 1);
		$this->db->group_by('c.id_customer');
		$customers = $this->db->get()->result();

		$data = [
			'customers' => $customers
		];

		$this->template->title('Add Penerimaan Uang Cash');
		$this->template->page_icon('fa fa-money');
		$this->template->render('form_penerimaan_cash', $data);
	}

	public function get_inv()
	{
		$id_customer = $this->input->get('id_customer', TRUE);

		$data = $this->db
			->select('
            i.id_invoice,
            i.id_so,
            i.tipe_so,
            i.id_penawaran,
            i.id_billing,
            i.tipe_billing,
            i.nilai_dpp,
            i.nilai_asli,
            i.nilai_invoice,
            i.persen_invoice,
            i.ppn,
            i.nilai_ppn,
            i.grand_total,
			(i.grand_total - IFNULL(bayar.total_bayar, 0)) as sisa_tagihan,
            DATE_FORMAT(i.created_on, "%d/%b/%Y") as tgl_inv,
            DATE_FORMAT(so.tgl_so, "%d/%b/%Y") as tgl_so,
            c.name_customer
        ')
			->from('tr_invoice_sales i')
			->join('sales_order so', 'so.no_so = i.id_so', 'left')
			->join('master_customers c', 'c.id_customer = i.id_customer', 'left')
			->where('i.id_customer', $id_customer)
			->where('i.sts', 1)
			->join('(SELECT no_invoice, SUM(total_bayar_idr) as total_bayar 
         FROM tr_invoice_payment_detail 
         GROUP BY no_invoice) bayar', 'bayar.no_invoice = i.id_invoice', 'left')
			->where('(i.grand_total > IFNULL(bayar.total_bayar, 0))', null, false)
			->order_by('i.created_on', 'ASC')
			->get()
			->result();

		echo json_encode($data);
	}

	public function save_cash()
	{
		$post = $this->input->post();

		$tgl_pembayaran = $post['tgl_pembayaran'];
		$id_customer = $post['id_customer'];
		$detail = $post['detail'];
		$total_invoice = str_replace(",", "", $post['total_invoice']);
		$total_terima = str_replace(",", "", $post['total_terima']);
		$id_invoices = array_column($detail, 'id_invoice');
		$invoice_string = implode(', ', $id_invoices);

		$kd_pembayaran = $this->penerimaan_model->generate_nopn($tgl_pembayaran);
		$customer = $this->db->get_where('master_customers', ['id_customer' => $id_customer])->row();

		// Generate OTP
		$otp_code = rand(100000, 999999);
		$otp_expiry = date('Y-m-d H:i:s', strtotime('+1 minutes'));
		$total_rupiah = number_format($total_terima, 0, ',', '.');

		// Simpan ke tabel sementara (tr_invoice_payment_otp)
		$otp_data = [
			'kd_pembayaran' => $kd_pembayaran,
			'id_customer' => $id_customer,
			'otp_code' => $otp_code,
			'expired_at' => $otp_expiry,
			'data_json' => json_encode([
				'header' => [
					'tgl_pembayaran' 		=> $tgl_pembayaran,
					'no_invoice' 			=> $invoice_string,
					'nm_customer' 			=> $customer->name_customer,
					'jumlah_piutang_idr' 	=> $total_invoice,
					'jumlah_pembayaran_idr' => $total_terima,
					'keterangan' 			=> $post['ket_bayar'],
					'created_by' 			=> $this->session->userdata('id_user'),
					'created_on' 			=> date('Y-m-d H:i:s'),
					'tipe_bayar' 			=> "CASH"
				],
				'detail' => $detail
			])
		];
		$this->db->insert('tr_invoice_payment_otp', $otp_data);

		// Kirim OTP via WhatsApp API Gateway
		$wa_number = preg_replace('/^0/', '62', $customer->telephone); // convert 08xxx → 628xxx
		$otp_message = "Terimakasih telah melakukan pembayaran sejumlah Rp. *$total_rupiah* \n\nKode OTP untuk verifikasi pembayaran Anda adalah: *$otp_code*\n\nKode ini berlaku hingga " . date('H:i', strtotime($otp_expiry)) . " WIB.\n\nJangan bagikan kode ini ke siapa pun.";

		$response = $this->send_wa($wa_number, $otp_message);

		echo json_encode([
			'status' => 1,
			'message' => 'OTP dikirim ke customer.',
			'kd_pembayaran' => $kd_pembayaran,
			'response' => $response
		]);
	}

	function send_wa($number, $message)
	{
		$url = 'https://app.whacenter.com/api/send';

		$data = [
			'device_id' => '56e2f7c983ea935683296b276ff30ae6',
			'number' => $number, // format: 628xxx
			'message' => $message
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	public function verify_otp()
	{
		$post = $this->input->post();
		$kd_pembayaran = $post['kd_pembayaran'];
		$otp_input = $post['otp_code'];

		$otp_data = $this->db->get_where('tr_invoice_payment_otp', [
			'kd_pembayaran' => $kd_pembayaran,
			'otp_code' => $otp_input
		])->row();

		if (!$otp_data || strtotime($otp_data->expired_at) < time()) {
			echo json_encode(['status' => 0, 'message' => 'OTP salah atau telah kadaluarsa']);
			return;
		}

		$decoded = json_decode($otp_data->data_json);
		$header = (array) $decoded->header;
		$detail = $decoded->detail;

		$header['kd_pembayaran'] = $kd_pembayaran;

		// Simpan header
		$this->db->insert('tr_invoice_payment', $header);

		// Simpan detail
		foreach ($detail as $row) {
			$invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $row->id_invoice])->result();
			$total_bayar = floatval(str_replace(',', '', $row->total_bayar));
			$tagihan = floatval(str_replace(',', '', $row->tagihan));
			$sisa_invoice = floatval(str_replace(',', '', $row->sisa_invoice));
			foreach ($invoice as $inv) {
				$data_detail = [
					'kd_pembayaran' => $kd_pembayaran,
					'no_invoice' => $row->id_invoice,
					'no_ipp' => $row->id_so,
					'so_number' => $row->id_so,
					'tgl_invoice' => date('Y-m-d', strtotime($inv->created_on)),
					'total_ppn_idr' => $inv->nilai_ppn,
					'total_invoice_idr' => $tagihan,
					'total_bayar_idr' => $total_bayar,
					'sisa_invoice_idr' => $sisa_invoice,
					'created_by' => $this->session->userdata('id_user'),
					'created_on' => date('Y-m-d H:i:s'),
					'tipe_bayar' => "CASH"
				];

				$this->db->insert('tr_invoice_payment_detail', $data_detail);

				//update ke tabel tr_invoice_sales
				$invoice_lunas = ($sisa_invoice <= 0);
				$this->db->where('id_invoice', $inv->id_invoice)
					->update('tr_invoice_sales', [
						'sts' => $invoice_lunas ? 0 : 1,
						'total_bayar' => $total_bayar
					]);
			}
		}

		// Hapus OTP setelah sukses
		$this->db->delete('tr_invoice_payment_otp', ['kd_pembayaran' => $kd_pembayaran]);

		echo json_encode([
			'status' => 1,
			'message' => 'Verifikasi berhasil. Pembayaran disimpan.',
			'redirect_url' => base_url("penerimaan/print_struk/$kd_pembayaran")
		]);
	}

	public function resend_otp()
	{
		$kd = $this->input->post('kd_pembayaran');

		$otp_data = $this->db->get_where('tr_invoice_payment_otp', ['kd_pembayaran' => $kd])->row();
		if (!$otp_data) {
			echo json_encode(['status' => 0, 'message' => 'Data OTP tidak ditemukan']);
			return;
		}

		$otp_code = rand(100000, 999999);
		$expired_at = date('Y-m-d H:i:s', strtotime('+1 minutes'));

		// Update
		$this->db->update('tr_invoice_payment_otp', [
			'otp_code' => $otp_code,
			'expired_at' => $expired_at
		], ['kd_pembayaran' => $kd]);

		// Ambil customer
		$cust = $this->db->query("
        SELECT c.name_customer, c.telephone 
        FROM tr_invoice_payment_otp t
        JOIN master_customers c ON c.id_customer = t.id_customer
        WHERE t.kd_pembayaran = ?
    ", [$kd])->row();

		if (!$cust) {
			echo json_encode(['status' => 0, 'message' => 'Customer tidak ditemukan']);
			return;
		}

		$nohp = preg_replace('/[^0-9]/', '', $cust->telephone);
		$wa = (substr($nohp, 0, 1) == '0') ? '62' . substr($nohp, 1) : $nohp;
		$msg = "Kode OTP baru Anda: *$otp_code*\n\nBerlaku sampai " . date('H:i', strtotime($expired_at)) . " WIB.";

		$response = $this->send_wa($wa, $msg);

		echo json_encode(['status' => 1, 'message' => 'OTP dikirim ulang']);
	}

	public function print_struk($kd_pembayaran)
	{
		$header = $this->db
			->where('kd_pembayaran', $kd_pembayaran)
			->get('tr_invoice_payment')->row();

		$details = $this->db
			->where('kd_pembayaran', $kd_pembayaran)
			->get('tr_invoice_payment_detail')->result();

		$subtotal = 0;
		$freight = 0;
		$total_pembayaran = 0;
		$total_kurang_pembayaran = 0;

		$total_pembayaran_sebelumnya = 0;

		foreach ($details as $d) {
			$pembayaran_sebelumnya = $this->db
				->select_sum('b.total_bayar_idr')
				->from('tr_invoice_payment a')
				->join('tr_invoice_payment_detail b', 'a.kd_pembayaran = b.kd_pembayaran')
				->where('b.no_invoice', $d->no_invoice)
				->where('a.created_on <', $header->created_on)
				->get()
				->row()
				->total_bayar_idr ?? 0;

			$total_pembayaran_sebelumnya += $pembayaran_sebelumnya;
		}

		foreach ($details as $item) {
			$invoice = $this->db
				->select('a.*')
				->from('tr_invoice_sales a')
				->where('a.id_invoice', $item->no_invoice)
				->get()
				->result();

			$total_pembayaran += $item->total_bayar_idr;
			$total_kurang_pembayaran += $item->sisa_invoice_idr;

			foreach ($invoice as $inv) {
				$delivery = $this->db
					->select('a.no_surat_jalan, a.no_delivery, a.no_so')
					->from('spk_delivery a')
					->where('a.no_surat_jalan', $inv->id_billing)
					->get()
					->row();

				if (!$delivery) continue;

				$items = $this->db
					->select('c.price_list, c.harga_penawaran, c.diskon, c.diskon_nilai, a.qty_delivery as qty, c.total_pl, d.ppn')
					->from('spk_delivery_detail a')
					->join('sales_order_detail b', 'b.id = a.id_so_det')
					->join('penawaran_detail c', 'c.id_penawaran = b.id_penawaran AND c.id_product = b.id_product')
					->join('penawaran d', 'd.id_penawaran = c.id_penawaran')
					->where('a.no_delivery', $delivery->no_delivery)
					->get()->result();

				$get_spk_pertama = $this->db
					->order_by('no_delivery', 'ASC')
					->get_where('spk_delivery', ['no_so' => $delivery->no_so])
					->row();

				$is_spk_pertama = ($get_spk_pertama && $get_spk_pertama->no_surat_jalan == $delivery->no_surat_jalan);

				// Jika SPK pertama, ambil freight
				if ($is_spk_pertama) {
					$freight_data = $this->db
						->select('b.freight')
						->from('sales_order a')
						->join('penawaran b', 'b.id_penawaran = a.id_penawaran')
						->where('a.no_so', $delivery->no_so)
						->get()
						->row();

					$freight += $freight_data ? $freight_data->freight : 0;
				}

				foreach ($items as $row) {
					$disc = (float)$row->diskon;
					$total_item = round(($row->price_list * $row->qty) * (1 + ($disc / 100)), -2); // diskon dikurang
					$subtotal += $total_item;
				}
			}
		}

		// Hitung DPP & PPN (jika PPN sudah termasuk)
		$exclude_ppn = ($subtotal + $freight) / 1.11;
		$dpp = ($exclude_ppn * 11) / 12;
		$ppn = ($dpp * 12) / 100;
		$grand_total = $exclude_ppn + $ppn;

		// Kirim ke view
		$html = $this->load->view('struk_penerimaan_cash', [
			'header' => $header,
			'details' => $details,
			'subtotal' => $subtotal,
			'exclude_ppn' => $exclude_ppn,
			// 'freight' => $freight,
			// 'dpp' => $dpp,
			// 'ppn' => $ppn,
			'total_pembayaran' => $total_pembayaran,
			'total_pembayaran_sebelumnya' => $total_pembayaran_sebelumnya,
			'total_kurang_pembayaran' => $total_kurang_pembayaran,
			'grand_total' => $grand_total,
		], true);

		$dompdf = new Dompdf();
		$dompdf->loadHtml($html);
		$dompdf->setPaper([0, 0, 165, 500], 'portrait'); // thermal 58mm
		$dompdf->render();
		$dompdf->stream("STRUK_$kd_pembayaran.pdf", ["Attachment" => false]);
	}
}


// trash


// public function print_struk2($kd_pembayaran)
// 	{
// 		$header = $this->db
// 			->where('kd_pembayaran', $kd_pembayaran)
// 			->get('tr_invoice_payment')->row();

// 		$details = $this->db
// 			->where('kd_pembayaran', $kd_pembayaran)
// 			->get('tr_invoice_payment_detail')->result();

// 		$subtotal = 0;
// 		$freight = 0;
// 		$total_pembayaran = 0;
// 		$total_kurang_pembayaran = 0;

// 		$no_invoices = array_map(function ($d) {
// 			return $d->no_invoice;
// 		}, $details);

// 		$total_pembayaran_sebelumnya = $this->db
// 			->select_sum('total_bayar_idr')
// 			->from('tr_invoice_payment_detail')
// 			->where_in('no_invoice', $no_invoices)
// 			->where('kd_pembayaran !=', $kd_pembayaran)
// 			->get()
// 			->row()
// 			->total_bayar_idr ?? 0;

// 		foreach ($details as $item) {
// 			$invoice = $this->db
// 				->select('a.*')
// 				->from('tr_invoice_sales a')
// 				->where('a.id_invoice', $item->no_invoice)
// 				->get()
// 				->result();

// 			$total_pembayaran += $item->total_bayar_idr;
// 			$total_kurang_pembayaran += $item->sisa_invoice_idr;

// 			foreach ($invoice as $inv) {
// 				$delivery = $this->db
// 					->select('a.no_surat_jalan, a.no_delivery, a.no_so')
// 					->from('spk_delivery a')
// 					->where('a.no_surat_jalan', $inv->id_billing)
// 					->get()
// 					->row();

// 				if (!$delivery) continue;

// 				$items = $this->db
// 					->select('c.price_list, c.harga_penawaran, c.diskon, c.diskon_nilai, a.qty_delivery as qty, c.total_pl, d.ppn')
// 					->from('spk_delivery_detail a')
// 					->join('sales_order_detail b', 'b.id = a.id_so_det')
// 					->join('penawaran_detail c', 'c.id_penawaran = b.id_penawaran AND c.id_product = b.id_product')
// 					->join('penawaran d', 'd.id_penawaran = c.id_penawaran')
// 					->where('a.no_delivery', $delivery->no_delivery)
// 					->get()->result();

// 				$get_spk_pertama = $this->db
// 					->order_by('no_delivery', 'ASC')
// 					->get_where('spk_delivery', ['no_so' => $delivery->no_so])
// 					->row();

// 				$is_spk_pertama = ($get_spk_pertama && $get_spk_pertama->no_surat_jalan == $delivery->no_surat_jalan);

// 				// Jika SPK pertama, ambil freight
// 				if ($is_spk_pertama) {
// 					$freight_data = $this->db
// 						->select('b.freight')
// 						->from('sales_order a')
// 						->join('penawaran b', 'b.id_penawaran = a.id_penawaran')
// 						->where('a.no_so', $delivery->no_so)
// 						->get()
// 						->row();

// 					$freight += $freight_data ? $freight_data->freight : 0;
// 				}

// 				foreach ($items as $row) {
// 					$disc = (float)$row->diskon;
// 					$total_item = round(($row->price_list * $row->qty) * (1 + ($disc / 100)), -2); // diskon dikurang
// 					$subtotal += $total_item;
// 				}
// 			}
// 		}

// 		// Hitung DPP & PPN (jika PPN sudah termasuk)
// 		$exclude_ppn = ($subtotal + $freight) / 1.11;
// 		$dpp = ($exclude_ppn * 11) / 12;
// 		$ppn = ($dpp * 12) / 100;
// 		$grand_total = $exclude_ppn + $ppn;

// 		// Kirim ke view
// 		$html = $this->load->view('struk_penerimaan_cash', [
// 			'header' => $header,
// 			'details' => $details,
// 			'subtotal' => $subtotal,
// 			'exclude_ppn' => $exclude_ppn,
// 			'freight' => $freight,
// 			'dpp' => $dpp,
// 			'ppn' => $ppn,
// 			'total_pembayaran' => $total_pembayaran,
// 			'total_pembayaran_sebelumnya' => $total_pembayaran_sebelumnya,
// 			'total_kurang_pembayaran' => $total_kurang_pembayaran,
// 			'grand_total' => $grand_total,
// 		], true);

// 		$dompdf = new Dompdf();
// 		$dompdf->loadHtml($html);
// 		$dompdf->setPaper([0, 0, 165, 500], 'portrait'); // thermal 58mm
// 		$dompdf->render();
// 		$dompdf->stream("STRUK_$kd_pembayaran.pdf", ["Attachment" => false]);
// 	}
// }
