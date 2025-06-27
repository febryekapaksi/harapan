<?php
defined('BASEPATH') or exit('No direct script access allowed');

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

		$this->id_user  = $this->auth->user_id();
		$this->datetime = date('Y-m-d H:i:s');
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
		$this->db->select('c.id_customer, c.name_customer, c.npwp, c.telephone, c.fax, c.address_office');
		$this->db->from('tr_invoice_sales a');
		$this->db->join('sales_order b', 'b.no_so = a.id_so', 'left');
		$this->db->join('master_customers c', 'c.id_customer = b.id_customer', 'left');
		$this->db->where('c.deleted_by IS NULL');
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
			DATE_FORMAT(i.created_on, "%d/%b/%Y") as tgl_inv,
			DATE_FORMAT(so.tgl_so, "%d/%b/%Y") as tgl_so,
			c.name_customer
		')
			->from('tr_invoice_sales i')
			->join('sales_order so', 'so.no_so = i.id_so', 'left')
			->join('master_customers c', 'c.id_customer = i.id_customer', 'left')
			->where('i.id_customer', $id_customer)
			->order_by('i.created_on', 'DESC')
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

		$kd_pembayaran = $this->penerimaan_model->generate_nopn($tgl_pembayaran);
		$customer = $this->db->get_where('master_customers', ['id_customer' => $id_customer])->row();

		// Generate OTP
		$otp_code = rand(100000, 999999);
		$otp_expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

		// Simpan ke tabel sementara (tr_invoice_payment_otp)
		$otp_data = [
			'kd_pembayaran' => $kd_pembayaran,
			'id_customer' => $id_customer,
			'otp_code' => $otp_code,
			'expired_at' => $otp_expiry,
			'data_json' => json_encode([
				'header' => [
					'tgl_pembayaran' => $tgl_pembayaran,
					'nm_customer' => $customer->name_customer,
					'jumlah_piutang' => $total_invoice,
					'jumlah_pembayaran_idr' => $total_terima,
					'keterangan' => $post['ket_bayar'],
					'created_by' => $this->session->userdata('id_user'),
					'created_on' => date('Y-m-d H:i:s'),
					'tipe_bayar' => "CASH"
				],
				'detail' => $detail
			])
		];
		$this->db->insert('tr_invoice_payment_otp', $otp_data);

		// Kirim OTP via WhatsApp API Gateway
		$wa_number = preg_replace('/^0/', '62', $customer->telephone); // convert 08xxx → 628xxx
		$otp_message = "Kode OTP untuk verifikasi pembayaran Anda adalah: *$otp_code*\n\nKode ini berlaku hingga " . date('H:i', strtotime($otp_expiry)) . " WIB.\n\nJangan bagikan kode ini ke siapa pun.";

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
			$invoice_detail = $this->db->get_where('tr_invoice_sales_detail', ['id_invoice' => $row->id_invoice])->result();
			foreach ($invoice_detail as $d) {
				$this->db->insert('tr_invoice_payment_detail', [
					'kd_pembayaran' => $kd_pembayaran,
					'no_invoice' => $row->id_invoice,
					'no_ipp' => $row->id_so,
					'so_number' => $row->id_so,
					'total_product' => $d->qty,
					'total_product_idr' => $d->harga,
					'total_bayar_idr' => $d->subtotal,
					'created_by' => $this->session->userdata('id_user'),
					'created_on' => date('Y-m-d H:i:s'),
					'tipe_bayar' => "CASH"
				]);
			}
		}

		// Hapus OTP setelah sukses
		$this->db->delete('tr_invoice_payment_otp', ['kd_pembayaran' => $kd_pembayaran]);

		echo json_encode(['status' => 1, 'message' => 'Verifikasi berhasil. Pembayaran disimpan.']);
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
		$expired_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

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
		$msg = "Kode OTP baru Anda: *$otp_code*\n\nBerlaku sampai " . date('H:i', strtotime($expires_at)) . " WIB.";

		$response = $this->send_wa($wa, $msg);

		echo json_encode(['status' => 1, 'message' => 'OTP dikirim ulang']);
	}
}
