-- =====================================================
-- MIGRATION: Add Cancel Audit Trail to loading_delivery
-- Date: 2026-08-21
-- Purpose: Simpan list SPK yang di-cancel untuk audit
-- =====================================================

-- Tambah 3 kolom di tabel loading_delivery
ALTER TABLE loading_delivery 
ADD COLUMN cancelled_spk_list TEXT NULL COMMENT 'List No SPK Delivery yang di-cancel (separator koma)',
ADD COLUMN cancelled_by VARCHAR(50) NULL COMMENT 'User ID yang melakukan cancel',
ADD COLUMN cancelled_at DATETIME NULL COMMENT 'Timestamp cancel loading';

-- Verifikasi kolom sudah ditambahkan
-- DESC loading_delivery;

-- =====================================================
-- END OF MIGRATION
-- =====================================================
