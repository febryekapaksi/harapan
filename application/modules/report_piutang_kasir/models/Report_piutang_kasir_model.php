<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_piutang_kasir_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Report_Piutang_Kasir.Add');
        $this->ENABLE_MANAGE  = has_permission('Report_Piutang_Kasir.Manage');
        $this->ENABLE_VIEW    = has_permission('Report_Piutang_Kasir.View');
        $this->ENABLE_DELETE  = has_permission('Report_Piutang_Kasir.Delete');
    }

    // ─────────────────────────────────────────────────────────────
    // Ambil semua baris setoran kasir pada bulan tertentu,
    // sekaligus join ke tr_setor_bank via tr_setor_bank_detail.id_setor_kasir
    //
    // Setiap baris = 1 record tr_setor_kasir
    // Kolom bank (id_setor_bank, tgl_bank) diisi jika kasir tsb
    // sudah disetor ke bank, NULL jika belum.
    //
    // total_bank diambil dari tr_setor_bank.total_setoran (bukan per-kasir),
    // tapi hanya ditampilkan sekali per id_setor_bank (untuk summary).
    // ─────────────────────────────────────────────────────────────
    public function get_rows($bulan)
    {
        $sql = "
            SELECT
                sk.id              AS id_kasir,
                sk.tgl_setor       AS tgl_kasir,
                sk.sales,
                sk.total_setoran   AS setoran_sales,
                sbd.id_setor_bank,
                sb.tgl_setor       AS tgl_bank,
                sb.total_setoran   AS total_bank
            FROM tr_setor_kasir sk
            LEFT JOIN (
                SELECT DISTINCT id_setor_kasir, id_setor_bank
                FROM tr_setor_bank_detail
            ) sbd ON sbd.id_setor_kasir = sk.id
            LEFT JOIN tr_setor_bank sb ON sb.id = sbd.id_setor_bank
            WHERE DATE_FORMAT(sk.tgl_setor, '%Y-%m') = ?
            ORDER BY sk.tgl_setor ASC, sk.id ASC
        ";

        return $this->db->query($sql, [$bulan])->result_array();
    }
}
