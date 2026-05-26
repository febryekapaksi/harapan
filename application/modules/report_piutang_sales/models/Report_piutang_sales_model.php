<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_piutang_sales_model extends BF_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ENABLE_ADD     = has_permission('Report_Piutang_Sales.Add');
        $this->ENABLE_MANAGE  = has_permission('Report_Piutang_Sales.Manage');
        $this->ENABLE_VIEW    = has_permission('Report_Piutang_Sales.View');
        $this->ENABLE_DELETE  = has_permission('Report_Piutang_Sales.Delete');
    }

    /**
     * Ringkasan piutang per sales untuk halaman index.
     * Saldo piutang = SUM(nilai penerimaan - nilai yg sudah disetor ke kasir)
     * per sales, hanya penerimaan yang belum lunas disetor.
     *
     * Relasi sales: tr_invoice_payment.created_by → users.id_user
     * (sales yang input penerimaan cash)
     *
     * @param  string|null $tanggal  cut-off (YYYY-MM-DD), null = semua
     * @return array
     */
    public function get_piutang_per_sales($tanggal = null)
    {
        $sql = "
            SELECT
                u.id_user,
                u.nm_lengkap,
                SUM(
                    p.jumlah_pembayaran_idr
                    - COALESCE(skd.total_penerimaan, 0)
                ) AS saldo_piutang
            FROM tr_invoice_payment p
            JOIN users u
                ON u.id_user = p.created_by
            LEFT JOIN tr_setor_kasir_detail skd
                ON skd.kd_pembayaran = p.kd_pembayaran
            WHERE u.department_id = 2
              AND (p.is_cancel IS NULL OR p.is_cancel != 'YES')
        ";

        $params = [];
        if (!empty($tanggal)) {
            $sql     .= " AND DATE(p.tgl_pembayaran) <= ? ";
            $params[] = $tanggal;
        }

        $sql .= "
            GROUP BY u.id_user, u.nm_lengkap
            HAVING saldo_piutang > 0
            ORDER BY u.nm_lengkap ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }

    /**
     * Detail piutang per sales — sesuai konsep:
     * Unit baris = 1 kode penerimaan cash (tr_invoice_payment).
     *
     * Kolom:
     *   Tanggal penerimaan | Kode penerimaan cash | Invoice | Nilai Penerimaan
     *   | Customer | Tanggal setor | Kode setor | Setor kasir penjualan
     *   | Saldo (Penerimaan - Setor)
     *
     * Saldo per baris = jumlah_pembayaran_idr - COALESCE(total_penerimaan di setor, 0)
     * Hanya tampilkan baris yang saldonya > 0 (belum penuh disetor).
     *
     * @param  int         $id_user  users.id_user
     * @param  string|null $tanggal  cut-off (YYYY-MM-DD)
     * @return array
     */
    public function get_detail_piutang($id_user, $tanggal = null)
    {
        $sql = "
            SELECT
                p.tgl_pembayaran,
                p.kd_pembayaran,
                p.no_invoice,
                p.jumlah_pembayaran_idr             AS nilai_penerimaan,
                p.nm_customer,
                sk.tgl_setor,
                sk.id                               AS kode_setor,
                COALESCE(skd.total_penerimaan, 0)   AS setor_kasir_penjualan,
                (p.jumlah_pembayaran_idr - COALESCE(skd.total_penerimaan, 0)) AS saldo
            FROM tr_invoice_payment p
            LEFT JOIN tr_setor_kasir_detail skd
                ON skd.kd_pembayaran = p.kd_pembayaran
            LEFT JOIN tr_setor_kasir sk
                ON sk.id = skd.id_setor_kasir
            WHERE p.created_by = ?
              AND (p.is_cancel IS NULL OR p.is_cancel != 'YES')
              AND p.tipe_bayar = 'CASH'
        ";

        $params = [$id_user];

        if (!empty($tanggal)) {
            $sql     .= " AND DATE(p.tgl_pembayaran) <= ? ";
            $params[] = $tanggal;
        }

        // Hanya tampilkan yang masih ada saldo (belum penuh disetor)
        $sql .= "
            HAVING saldo > 0
            ORDER BY p.tgl_pembayaran ASC, p.kd_pembayaran ASC
        ";

        return $this->db->query($sql, $params)->result_array();
    }
}
