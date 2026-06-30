-- ============================================================
-- Migration: Tanda Terima Nota Retur
-- Description: Tabel untuk menyimpan data tanda terima nota retur pembelian
-- ============================================================

-- Header Tanda Terima Nota Retur
CREATE TABLE IF NOT EXISTS `tr_tanda_terima_nota_retur` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_retur` INT(11) UNSIGNED NOT NULL COMMENT 'FK ke tr_retur_pembelian.id',
    `no_retur` VARCHAR(50) NOT NULL COMMENT 'No Retur dari tr_retur_pembelian',
    `no_invoice` VARCHAR(100) DEFAULT NULL COMMENT 'No Invoice',
    `id_supplier` INT(11) UNSIGNED DEFAULT NULL,
    `nama_supplier` VARCHAR(255) DEFAULT NULL,
    `tgl_retur` DATE DEFAULT NULL,
    `no_sj_retur` VARCHAR(100) DEFAULT NULL COMMENT 'No Surat Jalan Retur',
    `no_faktur_pajak_retur` VARCHAR(100) DEFAULT NULL COMMENT 'No Faktur Pajak Retur (CN)',
    `no_nota_retur_supplier` VARCHAR(100) DEFAULT NULL COMMENT 'No Nota Retur dari Supplier',
    `nilai_barang` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Total nilai barang sebelum PPn',
    `ppn` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'PPn 11%',
    `total` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Nilai barang + PPn',
    `metode_retur` ENUM('Terima Uang','Potong Tagihan') NOT NULL DEFAULT 'Potong Tagihan',
    `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Created/Draft, 2=Final',
    `created_by` INT(11) UNSIGNED DEFAULT NULL,
    `created_date` DATETIME DEFAULT NULL,
    `updated_by` INT(11) UNSIGNED DEFAULT NULL,
    `updated_date` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_id_retur` (`id_retur`),
    KEY `idx_no_retur` (`no_retur`),
    KEY `idx_id_supplier` (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tanda Terima Nota Retur Pembelian';

-- Detail Tanda Terima Nota Retur
CREATE TABLE IF NOT EXISTS `tr_tanda_terima_nota_retur_detail` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_tanda_terima` INT(11) UNSIGNED NOT NULL COMMENT 'FK ke tr_tanda_terima_nota_retur.id',
    `keterangan` VARCHAR(255) DEFAULT NULL COMMENT 'Nama barang / keterangan',
    `qty` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `harga_satuan` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_nilai` DECIMAL(15,2) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_id_tanda_terima` (`id_tanda_terima`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Detail Tanda Terima Nota Retur';

-- Tambah kolom status_nota_retur dan tgl_terima_nota di tr_retur_pembelian (jika belum ada)
-- ALTER TABLE `tr_retur_pembelian` ADD COLUMN `status_nota_retur` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Belum, 1=Sudah diterima' AFTER `status`;
-- ALTER TABLE `tr_retur_pembelian` ADD COLUMN `tgl_terima_nota` DATE DEFAULT NULL AFTER `status_nota_retur`;
