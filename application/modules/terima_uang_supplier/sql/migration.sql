-- =============================================
-- TERIMA UANG DARI SUPPLIER - Database Migration
-- =============================================

-- Table: tr_terima_uang_supplier (Header penerimaan uang)
CREATE TABLE IF NOT EXISTS `tr_terima_uang_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL COMMENT 'FK ke tr_retur_pembelian.id',
  `no_retur` varchar(50) DEFAULT NULL,
  `no_invoice` varchar(100) DEFAULT NULL,
  `id_supplier` varchar(50) DEFAULT NULL,
  `nama_supplier` varchar(255) DEFAULT NULL,
  `no_sj_retur` varchar(100) DEFAULT NULL COMMENT 'No. Surat Jalan Retur',
  `no_faktur_pajak_retur` varchar(100) DEFAULT NULL COMMENT 'No. Faktur Pajak Retur (CN)',
  `no_nota_retur_supplier` varchar(100) DEFAULT NULL COMMENT 'No. Nota Retur dari Supplier',
  `tgl_terima` date DEFAULT NULL,
  `nilai` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai sebelum PPN',
  `ppn` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai + PPN',
  `created_by` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`),
  KEY `idx_id_supplier` (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: tr_terima_uang_supplier_detail (Detail items penerimaan)
CREATE TABLE IF NOT EXISTS `tr_terima_uang_supplier_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_terima_uang` int(11) NOT NULL COMMENT 'FK ke tr_terima_uang_supplier.id',
  `id_retur` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_nilai` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_id_terima_uang` (`id_terima_uang`),
  KEY `idx_id_retur` (`id_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
