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
        $this->template->page_icon('fa fa-credit-card');
        $this->template->title('Penerimaan Uang');
        $this->template->render('list_payment');
    }

    public function data_side_penerimaan()
    {
        $this->penerimaan_model->get_data_json_payment();
    }

    public function add()
    {
        // Ambil daftar customer dari invoice yang masih aktif
        $this->db->select('c.id_customer, c.name_customer, c.npwp, c.telephone, c.fax, c.address_office, a.id_so');
        $this->db->from('tr_invoice_sales a');
        // $this->db->join('sales_order b', 'b.no_so = a.id_so', 'left');
        $this->db->join('master_customers c', 'c.id_customer = a.id_customer', 'left');
        $this->db->where('c.deleted_by IS NULL');
        $this->db->where('a.sts', 1);
        $this->db->group_by('c.id_customer');
        $customers = $this->db->get()->result();

        $data_bank = $this->db->get('master_bank')->result();

        $data = [
            'customers' => $customers,
            'bank'      => $data_bank,
        ];

        $this->template->title('Add Penerimaan Uang');
        $this->template->page_icon('fa fa-credit-card');
        $this->template->render('form_penerimaan', $data);
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
            // ->join('sales_order so', 'so.no_so = i.id_so', 'left')
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

    public function save()
    {
        $post = $this->input->post();

        $tgl_pembayaran = $post['tgl_pembayaran'];
        $id_customer = $post['id_customer'];
        $detail = $post['detail'];
        $total_invoice = str_replace(",", "", $post['total_invoice']);
        $total_terima = str_replace(",", "", $post['total_terima']);
        $total_bank = str_replace(",", "", $post['total_bank']);
        $keterangan = $post['ket_bayar'];
        $kd_bank = $post['bank'];

        $id_invoices = array_column($detail, 'id_invoice');
        $invoice_string = implode(', ', $id_invoices);

        $kd_pembayaran = $this->penerimaan_model->generate_nopn($tgl_pembayaran);
        $customer = $this->db->get_where('master_customers', ['id_customer' => $id_customer])->row();

        // Simpan ke tabel header
        $header = [
            'kd_pembayaran'             => $kd_pembayaran,
            'tgl_pembayaran'            => $tgl_pembayaran,
            'no_invoice'                => $invoice_string,
            'nm_customer'               => $customer->name_customer,
            'id_customer'               => $id_customer,
            'kd_bank'                   => $kd_bank,
            'jumlah_piutang_idr'        => $total_invoice,
            'jumlah_bank_idr'           => $total_bank,
            'jumlah_pembayaran_idr'     => $total_terima,
            'biaya_admin_idr'           => str_replace(",", "", $post['biaya_adm']),
            'lebih_bayar'               => str_replace(",", "", $post['lebih_bayar']),
            'keterangan'                => $keterangan,
            'created_by'                => $this->auth->user_id(),
            'created_on'                => date('Y-m-d H:i:s'),
            'tipe_bayar'                => "BANK"
        ];

        $this->db->insert('tr_invoice_payment', $header);

        // Simpan detail pembayaran & update status invoice
        foreach ($detail as $row) {
            $invoice = $this->db->get_where('tr_invoice_sales', ['id_invoice' => $row['id_invoice']])->row();
            $total_bayar = floatval(str_replace(',', '', $row['total_bayar']));
            $tagihan = floatval(str_replace(',', '', $row['tagihan']));
            $sisa_invoice = floatval(str_replace(',', '', $row['sisa_invoice']));

            $data_detail = [
                'kd_pembayaran'      => $kd_pembayaran,
                'nm_customer'        => $customer->name_customer,
                'id_customer'        => $id_customer,
                'no_invoice'         => $row['id_invoice'],
                'no_ipp'             => $row['id_so'],
                'so_number'          => $row['id_so'],
                'tgl_invoice'        => date('Y-m-d', strtotime($invoice->created_on)),
                'total_ppn_idr'      => $invoice->nilai_ppn,
                'total_invoice_idr'  => $tagihan,
                'total_bayar_idr'    => $total_bayar,
                'sisa_invoice_idr'   => $sisa_invoice,
                'created_by'         => $this->auth->user_id(),
                'created_on'         => date('Y-m-d H:i:s'),
                'tipe_bayar'         => "BANK"
            ];
            $this->db->insert('tr_invoice_payment_detail', $data_detail);

            // Update status invoice
            $invoice_lunas = ($sisa_invoice <= 0);
            $this->db->where('id_invoice', $invoice->id_invoice)
                ->update('tr_invoice_sales', [
                    'sts' => $invoice_lunas ? 0 : 1,
                    'total_bayar' => $total_bayar
                ]);
        }

        echo json_encode([
            'status' => 1,
            'message' => 'Pembayaran berhasil disimpan.',
            // 'redirect_url' => base_url("penerimaan_cash/print_struk/$kd_pembayaran")
        ]);
    }
}
