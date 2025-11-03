<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setor_bank extends Admin_Controller
{
    //Permission
    protected $viewPermission   = 'Setor_Bank.View';
    protected $addPermission    = 'Setor_Bank.Add';
    protected $managePermission = 'Setor_Bank.Manage';
    protected $deletePermission = 'Setor_Bank.Delete';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('upload', 'Image_lib'));
        $this->load->model(array(
            'Setor_bank/setor_bank_model',
            'Penerimaan_cash/All_model',
            'Penerimaan_cash/Jurnal_model',
            'Penerimaan_cash/Acc_model'
        ));

        date_default_timezone_set('Asia/Bangkok');
    }

    public function index()
    {
        $this->template->page_icon('fa fa-money');
        $this->template->title('Setor Bank');

        $this->template->render('index');
    }

    public function data_side_setoran_bank()
    {
        $this->setor_bank_model->get_json_setoran_bank();
    }

    public function create()
    {
        $this->template->page_icon('fa fa-sign-in');
        $this->template->title('Input Setoran Bank');

        // Ambil data bank dari GL
        $this->db->from(DBACC . '.coa_master a')
            ->where('a.no_perkiraan LIKE', '%1101-02%')
            ->where('a.level', 5);
        $bank = $this->db->get()->result();

        $data = [
            'bank' => $bank,
        ];

        $this->template->render('form', $data);
    }

    public function save()
    {
        try {
            $post = $this->input->post();

            $tgl_setor = $post['tgl_setor'];
            $bank = $post['bank'];
            //$norek = $post['norek'];
            $nilai_setor = str_replace(",", "", $post['nilai_setor']);
            $total_penerimaan = str_replace(",", "", $post['total_penerimaan']);
            $sisa_piutang = str_replace(",", "", $post['sisa_piutang_sesudah']);

            $id_setoran = $this->setor_bank_model->generateKodeSetoran($tgl_setor);

            $header = [
                'id'                => $id_setoran,
                'tgl_setor'         => $tgl_setor,
                'bank_id'           => $bank,
                // 'norek'             => $norek,
                'total_penerimaan'  => $total_penerimaan,
                'total_setoran'     => $nilai_setor,
                'sisa_piutang'      => $sisa_piutang,
                'created_by'        => $this->auth->user_id(),
                'created_at'        => date('Y-m-d H:i:s'),
                'tipe_setor'        => "LANGSUNG",

            ];

            if (empty($post['detail'])) {
                echo json_encode(['status' => false, 'message' => 'Data penerimaan tidak boleh kosong.']);
                return;
            }

            $this->db->trans_begin();

            $this->db->insert('tr_setor_bank', $header);

            foreach ($post['detail'] as $kd_penerimaan => $item) {
                $detail = [
                    'id_setor_bank'     => $id_setoran,
                    'kd_pembayaran'     => $item['kd_pembayaran'],
                    'id_customer'       => $item['id_customer'],
                    'name_customer'     => $item['name_customer'],
                    'no_invoice'        => $item['no_invoice'],
                    'total_invoice'     => str_replace(",", "", $item['total_invoice']),
                    'total_penerimaan'  => str_replace(",", "", $item['total_invoiced']),
                ];

                $this->db->insert('tr_setor_bank_detail', $detail);

                $this->db->where('kd_pembayaran', $item['kd_pembayaran'])
                    ->update('tr_invoice_payment', ['status_setor' => 1]);
            }

            $kd_bayar  = $id_setoran;
            $this->appr_jurnal($kd_bayar);

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                echo json_encode(['status' => false, 'message' => 'Gagal menyimpan data setoran.']);
            } else {
                $this->db->trans_commit();
                echo json_encode(['status' => true, 'message' => 'Data setoran berhasil disimpan.']);
            }
        } catch (\Throwable $th) {
            $this->db->trans_rollback();
            echo json_encode(['status' => false, 'message' => 'Proses Gagal.' . $th->getMessage()]);
        }
    }

    // fungsi get untuk ajax
    public function get_penerimaan()
    {
        $user_id = $this->auth->user_id(); // ambil user login

        $data = $this->db
            ->select('
            a.kd_pembayaran,
            a.created_on,
            a.created_by,
            a.id_customer,
            m.name_customer,
            c.invoiced,
            c.totalinvoiced,
            c.total_invoice
        ')
            ->from('tr_invoice_payment a')
            ->join("
            (
                SELECT 
                    kd_pembayaran, 
                    GROUP_CONCAT(no_invoice SEPARATOR ',') AS invoiced, 
                    SUM(total_bayar_idr) AS totalinvoiced, 
                    SUM(total_invoice_idr) AS total_invoice 
                FROM tr_invoice_payment_detail 
                GROUP BY kd_pembayaran
            ) c", 'a.kd_pembayaran = c.kd_pembayaran', 'left')
            ->join('master_customers m', 'm.id_customer = a.id_customer', 'left')
            ->where('a.tipe_bayar', 'CASH')
            ->where('a.status_setor', 0)
            ->where('a.created_by', $user_id)
            ->order_by('a.created_on', 'DESC')
            ->get()
            ->result();

        echo json_encode($data);
    }

    public function get_sisa_piutang_sebelumnya()
    {
        $user_id = $this->auth->user_id();

        $last = $this->db->select('sisa_piutang')
            ->from('tr_setor_bank')
            ->where('created_by', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $total = ($last && $last->sisa_piutang > 0) ? $last->sisa_piutang : 0;

        echo json_encode([
            'status' => true,
            'total'  => $total
        ]);
    }


    function appr_jurnal($kd_bayar)
    {


        $session = $this->session->userdata('app_session');

        $data_bayar =  $this->db->query("SELECT * FROM tr_setor_bank WHERE id = '$kd_bayar' ")->row();

        $tgl_byr     = $data_bayar->tgl_setor;
        $kd_invoice        = $data_bayar->id;
        $kd_bank     = $data_bayar->bank_id;
        //$jenis_pph 	= $data_bayar->jenis_pph;
        $nama    = $session['id_user'];
        $jmlpph   = 0;


        $idcust  = $session['id_user'];



        $No_Inv  = $kd_bayar;
        $Tgl_Inv = $tgl_byr;
        $Bln             = substr($Tgl_Inv, 6, 2);
        $Thn             = substr($Tgl_Inv, 0, 4);
        $bulan_bayar = date("n", strtotime($Tgl_Inv));
        $tahun_bayar = date("Y", strtotime($Tgl_Inv));
        $keterangan_byr  = $data_bayar->norek;
        $jumlah_total    = $data_bayar->total_setoran;
        $jumlah_terima   = $data_bayar->total_setoran;
        $biaya_admin     = 0;
        $biaya_lain     = 0;
        $deposit         = 0;
        $jenis_reff      = $kd_bayar;
        $no_reff         = $kd_bayar;
        ## NOMOR JV ##
        $Nomor_BUM                = $this->Jurnal_model->get_Nomor_Jurnal_BUM('101', $Tgl_Inv);

        //print_r($Nomor_BUM);
        //exit;


        //$Keterangan_INV		    = 'PENERIMAAN MULTI INVOICE A/N '.$nama.' INV NO. '.$No_Inv.
        //' Keterangan :'.$ket_invoice.', Catatan :'.$notes.', No Reff:'.$noreff.', No Pembayaran:'.$kd_pn;

        $Keterangan_INV            = 'SETOR PENERIMAAN ' . $nama . 'NO. ' . $No_Inv . ' Keterangan :' . $keterangan_byr;

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

        $data_jurnal = $this->db->query("SELECT * FROM tr_setor_bank_detail WHERE id_setor_bank = '$kd_bayar' ")->result();

        foreach ($data_jurnal as $jr) {
            $jmlbayar   = $jr->total_penerimaan;
            $invoice2    = $jr->kd_pembayaran;

            $det_Jurnal[]              = array(
                'nomor'         => $Nomor_BUM,
                'tanggal'       => $Tgl_Inv,
                'tipe'          => 'BUM',
                'no_perkiraan'  => '1102-01-04',
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

        $data_jr = $this->db->query("SELECT * FROM tr_setor_bank_detail WHERE id_setor_bank = '$kd_bayar' ")->result();

        foreach ($data_jr as $val) {
            $jml   = $val->total_penerimaan;
            $inv   = $val->kd_pembayaran;

            $Ket_INV            = 'SETOR PENERIMAAN A/N ' . $nama . ' NO. ' . $inv . ' Keterangan :' . $keterangan_byr;


            $datapiutang = array(
                'tipe'            => 'BUM',
                'nomor'            => $Nomor_BUM,
                'tanggal'        => $Tgl_Inv,
                'no_perkiraan'  => '1102-01-04',
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
