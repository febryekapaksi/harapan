<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Jurnal_retur_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Jurnal saat Ajukan Retur Produk
     * D: Hutang Dagang = total_retur (nilai_retur + ppn)
     * K: Inventori = nilai_retur
     * K: PPN Masukan = ppn
     */
    public function create_jurnal_retur($header, $details)
    {
        $tgl_jurnal  = date('Y-m-d');
        $keterangan  = 'Retur Pembelian - ' . $header['no_retur'] . ' - ' . $header['nama_supplier'];
        $no_retur    = $header['no_retur'];

        $COA_HUTANG_DAGANG = '2101-01-01';
        $COA_INVENTORI     = '1104-01-01';
        $COA_PPN_MASUKAN   = '1107-01-01';

        $nilai_adjustment = floatval($header['nilai_retur']) + floatval($header['ppn']);

        // Insert gl_interface header
        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $nilai_adjustment,
            'kdcab'            => '101',
            'jenis'            => 'JV',
            'jenis_transaksi'  => 'retur_pembelian',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'memo'             => json_encode([
                'no_retur'      => $no_retur,
                'no_invoice'    => $header['no_invoice'],
                'id_supplier'   => $header['id_supplier'],
                'nilai_retur'   => $header['nilai_retur'],
                'ppn'           => $header['ppn'],
                'total_retur'   => $header['total_retur'],
            ]),
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            // Debit: Hutang Dagang
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => $nilai_adjustment,
                'kredit'          => 0,
            ]);

            // Kredit: Inventori
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_INVENTORI,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => 0,
                'kredit'          => floatval($header['nilai_retur']),
            ]);

            // Kredit: PPN Masukan
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_PPN_MASUKAN,
                'keterangan'      => $keterangan,
                'no_reff'         => $no_retur,
                'debet'           => 0,
                'kredit'          => floatval($header['ppn']),
            ]);
        }
    }

    /**
     * Jurnal Pinalti/Claim
     * D: Hutang Dagang = nilai pinalti
     * K: Biaya COPQ = nilai pinalti
     */
    public function create_jurnal_pinalti($header)
    {
        $tgl_jurnal = date('Y-m-d');
        $keterangan = 'Pinalti Retur - ' . $header['no_retur'] . ' - ' . $header['nama_supplier'];
        $nilai      = floatval($header['pinalti']);

        $COA_HUTANG_DAGANG = '2101-01-01';
        $COA_COPQ          = '5101-01-04';

        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $nilai,
            'kdcab'            => '101',
            'jenis'            => 'JV',
            'jenis_transaksi'  => 'retur_pembelian_pinalti',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => $nilai,
                'kredit'          => 0,
            ]);

            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_COPQ,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => 0,
                'kredit'          => $nilai,
            ]);
        }
    }

    /**
     * Jurnal Settlement (Terima Uang)
     * D: Kas/Bank = jumlah
     * K: Hutang Dagang = jumlah
     */
    public function create_jurnal_settlement($header, $jumlah, $metode)
    {
        $tgl_jurnal = date('Y-m-d');
        $keterangan = 'Settlement Retur - ' . $header['no_retur'] . ' - ' . $header['nama_supplier'];

        $COA_HUTANG_DAGANG = '2101-01-01';
        $COA_BANK          = '1102-01-01'; // default Bank
        if (strtolower($metode) == 'cash') {
            $COA_BANK = '1101-01-01'; // Kas
        }

        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $jumlah,
            'kdcab'            => '101',
            'jenis'            => 'BM',
            'jenis_transaksi'  => 'retur_pembelian_settlement',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'BM',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_BANK,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => $jumlah,
                'kredit'          => 0,
            ]);

            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'BM',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => 0,
                'kredit'          => $jumlah,
            ]);
        }
    }

    /**
     * Jurnal Balik (saat Cancel dari status Process)
     * Reverse: swap debit/kredit
     */
    public function create_jurnal_balik($header, $details)
    {
        $tgl_jurnal = date('Y-m-d');
        $keterangan = 'REVERSAL Retur - ' . $header['no_retur'] . ' - ' . $header['nama_supplier'];
        $nilai      = floatval($header['nilai_retur']) + floatval($header['ppn']);

        $COA_HUTANG_DAGANG = '2101-01-01';
        $COA_INVENTORI     = '1104-01-01';
        $COA_PPN_MASUKAN   = '1107-01-01';

        $this->db->insert('gl_interface', [
            'tgl'              => $tgl_jurnal,
            'jml'              => $nilai,
            'kdcab'            => '101',
            'jenis'            => 'JV',
            'jenis_transaksi'  => 'retur_pembelian_reversal',
            'keterangan'       => $keterangan,
            'bulan'            => date('n'),
            'tahun'            => date('Y'),
            'user_id'          => $this->auth->user_id(),
            'status'           => 'pending',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
        $id_gl = $this->db->insert_id();

        if ($id_gl) {
            // Kredit: Hutang Dagang (reverse)
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_HUTANG_DAGANG,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => 0,
                'kredit'          => $nilai,
            ]);

            // Debit: Inventori (reverse)
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_INVENTORI,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => floatval($header['nilai_retur']),
                'kredit'          => 0,
            ]);

            // Debit: PPN Masukan (reverse)
            $this->db->insert('gl_interface_detail', [
                'id_gl_interface' => $id_gl,
                'tipe'            => 'JV',
                'tanggal'         => $tgl_jurnal,
                'no_perkiraan'    => $COA_PPN_MASUKAN,
                'keterangan'      => $keterangan,
                'no_reff'         => $header['no_retur'],
                'debet'           => floatval($header['ppn']),
                'kredit'          => 0,
            ]);
        }

        // Reversal pinalti jika ada
        if ($header['pinalti'] > 0) {
            $COA_COPQ = '5101-01-04';
            $nilai_p  = floatval($header['pinalti']);

            $this->db->insert('gl_interface', [
                'tgl'              => $tgl_jurnal,
                'jml'              => $nilai_p,
                'kdcab'            => '101',
                'jenis'            => 'JV',
                'jenis_transaksi'  => 'retur_pembelian_pinalti_reversal',
                'keterangan'       => 'REVERSAL Pinalti - ' . $header['no_retur'],
                'bulan'            => date('n'),
                'tahun'            => date('Y'),
                'user_id'          => $this->auth->user_id(),
                'status'           => 'pending',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
            $id_gl2 = $this->db->insert_id();

            if ($id_gl2) {
                $this->db->insert('gl_interface_detail', [
                    'id_gl_interface' => $id_gl2,
                    'tipe'            => 'JV',
                    'tanggal'         => $tgl_jurnal,
                    'no_perkiraan'    => $COA_COPQ,
                    'keterangan'      => 'REVERSAL Pinalti - ' . $header['no_retur'],
                    'no_reff'         => $header['no_retur'],
                    'debet'           => $nilai_p,
                    'kredit'          => 0,
                ]);

                $this->db->insert('gl_interface_detail', [
                    'id_gl_interface' => $id_gl2,
                    'tipe'            => 'JV',
                    'tanggal'         => $tgl_jurnal,
                    'no_perkiraan'    => $COA_HUTANG_DAGANG,
                    'keterangan'      => 'REVERSAL Pinalti - ' . $header['no_retur'],
                    'no_reff'         => $header['no_retur'],
                    'debet'           => 0,
                    'kredit'          => $nilai_p,
                ]);
            }
        }
    }
}
