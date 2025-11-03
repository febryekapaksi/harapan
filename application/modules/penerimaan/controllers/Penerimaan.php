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

        // Ambil data bank dari GL
        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $data_bank = $this->db->get()->result();

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
            DATE_FORMAT(i.tgl_so, "%d/%b/%Y") as tgl_so,
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

            // Rekap ulang total_bayar dari detail
            $sum = $this->db->select('COALESCE(SUM(total_bayar_idr),0) AS total', false)
                ->from('tr_invoice_payment_detail')
                ->where('no_invoice', $row['id_invoice'])
                ->get()->row()->total;

            // Update header: total_bayar, piutang, dan status
            $this->db->set('total_bayar', $sum, false);
            $this->db->set('piutang', $sisa_invoice, false);
            $this->db->set('sts', "CASE WHEN {$sisa_invoice} <= 0 THEN 0 ELSE 1 END", false);
            $this->db->where('id_invoice', $row['id_invoice'])->update('tr_invoice_sales');
        }

        $kd_bayar  = $kd_pembayaran;
        $this->appr_jurnal($kd_bayar);

        echo json_encode([
            'status' => 1,
            'message' => 'Pembayaran berhasil disimpan.',
            // 'redirect_url' => base_url("penerimaan_cash/print_struk/$kd_pembayaran")
        ]);
    }


    function appr_jurnal($kd_bayar)
    {


        $session = $this->session->userdata('app_session');

        $data_bayar =  $this->db->query("SELECT * FROM tr_invoice_payment WHERE kd_pembayaran = '$kd_bayar' ")->row();

        $tgl_byr     = $data_bayar->tgl_pembayaran;
        $kd_invoice        = $data_bayar->no_invoice;
        $kd_bank     = $data_bayar->kd_bank;
        $jenis_pph     = $data_bayar->jenis_pph;
        $nama    = html_escape($data_bayar->nm_customer);
        $jmlpph   = 0;

        $id_cust =  $this->db->query("SELECT * FROM master_customer WHERE name_customer = '$nama'")->row();
        $idcust  = $data_bayar->id_customer;



        $No_Inv  = $kd_bayar;
        $Tgl_Inv = $tgl_byr;
        $Bln             = substr($Tgl_Inv, 6, 2);
        $Thn             = substr($Tgl_Inv, 0, 4);
        $bulan_bayar = date("n", strtotime($Tgl_Inv));
        $tahun_bayar = date("Y", strtotime($Tgl_Inv));
        $keterangan_byr  = $data_bayar->keterangan;
        $jumlah_total    = $data_bayar->jumlah_pembayaran_idr;
        $jumlah_terima   = $data_bayar->jumlah_bank_idr;
        $biaya_admin     = $data_bayar->biaya_admin_idr;
        $biaya_lain     = $data_bayar->biaya_pph_idr;
        $deposit         = $data_bayar->lebih_bayar;
        $jenis_reff      = $kd_bayar;
        $no_reff         = $kd_bayar;
        ## NOMOR JV ##
        $Nomor_BUM                = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $Tgl_Inv);

        //print_r($Nomor_BUM);
        //exit;


        //$Keterangan_INV		    = 'PENERIMAAN MULTI INVOICE A/N '.$nama.' INV NO. '.$No_Inv.
        //' Keterangan :'.$ket_invoice.', Catatan :'.$notes.', No Reff:'.$noreff.', No Pembayaran:'.$kd_pn;

        $Keterangan_INV            = 'PENERIMAAN MULTI INVOICE A/N ' . $nama . ' INV NO. ' . $No_Inv . ' Keterangan :' . $keterangan_byr;

        $dataJARH = array(
            'nomor'             => $Nomor_BUM,
            'kd_pembayaran'        => $kd_bayar,
            'tgl'                 => $Tgl_Inv,
            'jml'                => $jumlah_total,
            'kdcab'                => '101',
            'jenis_reff'        => $jenis_reff,
            'no_reff'            => $no_reff,
            'customer'            => $nama,
            'terima_dari'        => '-',
            'jenis_ar'            => 'V',
            'note'                => $Keterangan_INV,
            'valid'                => $session['id_user'],
            'tgl_valid'            => $Tgl_Inv,
            'user_id'            => $session['id_user'],
            'tgl_invoice'        => $Tgl_Inv,
            'ho_valid'            => '',
            'batal'                => '0'
        );

        $det_Jurnal                = array();
        $det_Jurnal[]            = array(
            'nomor'         => $Nomor_BUM,
            'tanggal'       => $Tgl_Inv,
            'tipe'          => 'BUM',
            'no_perkiraan'  => $kd_bank,
            'keterangan'    => $Keterangan_INV,
            'no_reff'       => $No_Inv,
            'debet'         => $jumlah_terima,
            'kredit'        => 0

        );

        if ($biaya_admin != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '7205-01-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $biaya_admin,
                'kredit'        => 0

            );
        }

        if ($deposit != 0) {
            $det_Jurnal[]            = array(
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '2109-02-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $No_Inv,
                'debet'         => $deposit,
                'kredit'        => 0

            );
        }




        // if ($jumlah_piutang2 > $pembayaran){

        // $det_Jurnal[]			  = array(
        // 'nomor'         => $Nomor_BUM,
        // 'tanggal'       => $Tgl_Inv,
        // 'tipe'          => 'BUM',
        // 'no_perkiraan'  => $no_account,
        // 'keterangan'    => $Keterangan_INV,
        // 'no_reff'       => $No_Inv,
        // 'debet'         => $selisih,
        // 'kredit'        => 0
        // );

        // }
        // else if ($jumlah_piutang2 < $pembayaran){
        // $det_Jurnal[]			  = array(
        // 'nomor'         => $Nomor_BUM,
        // 'tanggal'       => $Tgl_Inv,
        // 'tipe'          => 'BUM',
        // 'no_perkiraan'  => $no_account,
        // 'keterangan'    => $Keterangan_INV,
        // 'no_reff'       => $No_Inv,
        // 'debet'         => 0,
        // 'kredit'        => $selisih
        // );

        // }



        $data_jurnal = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();

        foreach ($data_jurnal as $jr) {
            $jmlbayar   = $jr->total_bayar_idr;
            $invoice2    = $jr->no_invoice;

            $det_Jurnal[]              = array(
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '1102-01-01',
                'keterangan'    => $Keterangan_INV,
                'no_reff'       => $invoice2,
                'debet'         => 0,
                'kredit'        => $jmlbayar,
            );
        }


        ## INSERT JURNAL ##
        $this->db->insert(DBACC . '.jarh', $dataJARH);
        $this->db->insert_batch(DBACC . '.jurnal', $det_Jurnal);

        $Qry_Update_Cabang_acc     = "UPDATE " . DBACC . ".pastibisa_tb_cabang SET nobum=nobum + 1 WHERE nocab='101'";
        $this->db->query($Qry_Update_Cabang_acc);

        //PROSES JURNAL

        $data_jr = $this->db->query("SELECT * FROM tr_invoice_payment_detail WHERE kd_pembayaran = '$kd_bayar' ")->result();

        foreach ($data_jr as $val) {
            $jml   = $val->total_bayar_idr;
            $inv   = $val->no_invoice;

            $Ket_INV            = 'PENERIMAAN MULTI INVOICE A/N ' . $nama . ' INV NO. ' . $inv . ' Keterangan :' . $keterangan_byr;


            $datapiutang = array(
                'tipe'            => 'BUM',
                'nomor'            => $Nomor_BUM,
                'tanggal'        => $Tgl_Inv,
                'no_perkiraan'  => '1102-01-01',
                'keterangan'    => $Ket_INV,
                'no_reff'       => $inv,
                'debet'         => 0,
                'kredit'         => $jml,
                'id_supplier'     => $idcust,
                'nama_supplier'   => $nama,

            );



            $idso = $this->db->insert('tr_kartu_piutang', $datapiutang);
        }
    }
}
