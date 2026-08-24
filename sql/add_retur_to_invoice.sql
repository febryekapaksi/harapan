-- Migration: Tambah kolom retur di tabel tr_invoice_po
-- Jalankan SQL ini untuk menambahkan field retur pada receive invoice

ALTER TABLE `tr_invoice_po` 
ADD COLUMN `nilai_retur` DECIMAL(15,2) DEFAULT 0 COMMENT 'Nilai retur yang dipotong dari invoice' AFTER `kurs`,
ADD COLUMN `total_tagihan` DECIMAL(15,2) DEFAULT 0 COMMENT 'Total Tagihan = Total Invoice - Nilai Retur' AFTER `nilai_retur`,
ADD COLUMN `id_retur_pembelian` INT(11) DEFAULT NULL COMMENT 'ID referensi ke tr_retur_pembelian' AFTER `total_tagihan`;
