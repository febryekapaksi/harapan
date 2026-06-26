-- ============================================================
-- RETUR PEMBELIAN - Database Schema
-- Module: retur_pembelian
-- Created: 2026-06-26
-- ============================================================

-- ------------------------------------------------------------
-- 1. Tabel Header: tr_retur_pembelian
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_retur_pembelian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_retur` varchar(20) NOT NULL COMMENT 'Format: RTR-YYYY-NNNNN',
  `no_invoice` varchar(30) NOT NULL COMMENT 'FK ke receive invoice',
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(150) DEFAULT NULL,
  `tgl_pembelian` date DEFAULT NULL,
  `tgl_retur` date NOT NULL,
  `nilai_retur` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Sub-total sebelum PPN',
  `ppn` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_retur` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Nilai Retur + PPN + Pinalti',
  `pinalti` decimal(15,2) NOT NULL DEFAULT 0.00,
  `settlement` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total yang sudah di-settle',
  `sisa_retur` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Total - Settlement',
  `kembalikan_barang` enum('Ya','Tidak') NOT NULL DEFAULT 'Tidak',
  `nota_retur` enum('Ya','Tidak') NOT NULL DEFAULT 'Tidak',
  `status_nota_retur` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Belum Diterima, 1=Sudah Diterima',
  `tgl_terima_nota` date DEFAULT NULL COMMENT 'Tanggal terima nota retur dari supplier',
  `kategori_alasan` varchar(100) DEFAULT NULL,
  `keterangan_alasan` text DEFAULT NULL,
  `file_ba` varchar(255) DEFAULT NULL COMMENT 'Path file Berita Acara',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Cancel, 1=Draft, 2=Process, 3=Selesai',
  `created_by` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_no_retur` (`no_retur`),
  KEY `idx_no_invoice` (`no_invoice`),
  KEY `idx_id_supplier` (`id_supplier`),
  KEY `idx_status` (`status`),
  KEY `idx_tgl_retur` (`tgl_retur`),
  KEY `idx_kembalikan_barang` (`kembalikan_barang`),
  KEY `idx_nota_retur` (`nota_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Header Retur Pembelian';

-- ------------------------------------------------------------
-- 2. Tabel Detail Produk: tr_retur_pembelian_detail
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_retur_pembelian_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL,
  `no_retur` varchar(20) NOT NULL,
  `id_product` int(11) DEFAULT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `qty_beli` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_retur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_nilai` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'qty_retur x harga_satuan',
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`),
  KEY `idx_id_product` (`id_product`),
  CONSTRAINT `fk_detail_retur` FOREIGN KEY (`id_retur`) REFERENCES `tr_retur_pembelian` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Detail Produk Retur Pembelian';

-- ------------------------------------------------------------
-- 3. Tabel Pinalti/Claim: tr_retur_pembelian_pinalti
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_retur_pembelian_pinalti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL,
  `no_retur` varchar(20) NOT NULL,
  `nilai` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`),
  CONSTRAINT `fk_pinalti_retur` FOREIGN KEY (`id_retur`) REFERENCES `tr_retur_pembelian` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pinalti/Claim Retur Pembelian';

-- ------------------------------------------------------------
-- 4. Tabel Settlement: tr_retur_pembelian_settlement
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tr_retur_pembelian_settlement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL,
  `no_retur` varchar(20) NOT NULL,
  `tgl_terima` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT 0.00,
  `metode` varchar(50) DEFAULT NULL COMMENT 'Transfer/Cash/Giro',
  `no_referensi` varchar(50) DEFAULT NULL COMMENT 'No. Giro / No. Transfer',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`),
  KEY `idx_tgl_terima` (`tgl_terima`),
  CONSTRAINT `fk_settlement_retur` FOREIGN KEY (`id_retur`) REFERENCES `tr_retur_pembelian` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Settlement/Penerimaan Uang Retur Pembelian';

-- ------------------------------------------------------------
-- 5. Insert Permission
-- Sesuaikan id_permission dan id_menu dengan urutan terakhir di DB
-- ------------------------------------------------------------
INSERT INTO `permissions` (`nm_permission`, `ket`, `id_menu`, `nm_menu`, `created_on`, `created_by`) VALUES
('Retur_pembelian.View', 'View', 0, 'Retur Pembelian', NOW(), '1'),
('Retur_pembelian.Add', 'Add', 0, 'Retur Pembelian', NOW(), '1'),
('Retur_pembelian.Manage', 'Manage', 0, 'Retur Pembelian', NOW(), '1'),
('Retur_pembelian.Delete', 'Delete', 0, 'Retur Pembelian', NOW(), '1');
