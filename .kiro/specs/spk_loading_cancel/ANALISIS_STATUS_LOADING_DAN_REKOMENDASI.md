# Analisis Penggunaan Status Loading Delivery & Rekomendasi

## 🔍 TEMUAN: Modul yang Menggunakan `loading_delivery`

Berdasarkan grep search di seluruh codebase, berikut modul-modul yang menggunakan tabel `loading_delivery`:

### 1. **Modul Loading** (Owner)
**File:** `application/modules/loading/controllers/Loading.php`
**File:** `application/modules/loading/models/Loading_model.php`

**Penggunaan Status:**
```php
// Status yang digunakan:
// 0 = Draft
// 1 = Confirm QTY
// 2 = Confirm Berat (Waiting Approval)
// 3 = Approved
// -1 = Cancelled (NEW - baru ditambahkan)
```

**Query:**
- `WHERE status = 0` → Filter Draft untuk edit/cancel
- `WHERE status = 2` → Filter untuk approval
- `WHERE status = 3` → Filter untuk approved (bisa dibuat surat jalan)

---

### 2. **Modul Surat Jalan** ⚠️ **PENTING!**
**File:** `application/modules/surat_jalan/controllers/Surat_jalan.php`

**Penggunaan Status:**
```php
// Line 67
WHERE l.status = 3  // ← HANYA AMBIL LOADING YANG APPROVED
AND l.pengiriman = 'Gudang'
AND EXISTS (...)
```

**Keterangan:**
- ✅ Modul ini **HANYA membaca status = 3** (Approved)
- ✅ Status Cancelled (-1) **TIDAK AKAN MUNCUL** di surat jalan
- ✅ **AMAN** dari status baru

**JOIN (tidak cek status):**
```php
// Line 431 & 874
->join('loading_delivery ld', 'sj.no_loading = ld.no_loading', 'left')
```
- Hanya JOIN untuk ambil nopol, tidak filter status
- ✅ **AMAN**

---

### 3. **Modul Surat Jalan Pabrik**
**File:** `application/modules/surat_jalan_pabrik/controllers/Surat_jalan_pabrik.php`

**Penggunaan Status:**
```php
// Line 387 & 807
->join('loading_delivery ld', 'sj.no_loading = ld.no_loading', 'left')
```

**Keterangan:**
- ✅ Tidak ada filter status
- ✅ Hanya JOIN untuk ambil nopol
- ✅ **AMAN** dari status baru

---

## 📊 KESIMPULAN DAMPAK STATUS BARU (-1 = Cancelled)

### ✅ **TIDAK ADA DAMPAK KE MODUL LAIN**

| Modul | Filter Status? | Query | Dampak? |
|-------|----------------|-------|---------|
| Loading | ✅ Ya (0,2,3) | Filter status spesifik | ✅ Aman - status -1 tidak akan muncul di list |
| Surat Jalan | ✅ Ya (3 only) | `WHERE status = 3` | ✅ Aman - hanya ambil Approved |
| Surat Jalan Pabrik | ❌ Tidak | LEFT JOIN saja | ✅ Aman - tidak filter status |

### 🎯 **ALASAN AMAN:**

1. **Surat Jalan hanya ambil status = 3 (Approved)**
   - Loading yang Cancelled (status = -1) tidak akan muncul di dropdown
   - ✅ Tidak ada surat jalan yang bisa dibuat dari loading cancelled

2. **Modul lain hanya LEFT JOIN**
   - Tidak ada filter WHERE status
   - Hanya ambil data nopol/tanggal_muat
   - ✅ Tidak peduli status berapa

3. **Loading sendiri sudah filter per status**
   - Draft → status = 0
   - Waiting Approval → status = 2
   - Approved → status = 3
   - Cancelled → status = -1
   - ✅ Masing-masing ter-isolasi

---

## 🤔 SOFT DELETE vs HARD DELETE: Analisis & Rekomendasi

### **OPSI 1: SOFT DELETE** (Current Implementation) ✅ REKOMENDASI

#### **Cara Kerja:**
```php
// Header: Update status jadi -1
UPDATE loading_delivery SET status = -1 WHERE no_loading = 'XXX';

// Detail: HAPUS PERMANEN
DELETE FROM loading_delivery_detail WHERE no_loading = 'XXX';
```

#### **Kelebihan:**
✅ **Audit Trail** - Data header masih ada untuk tracking
✅ **History** - Bisa lihat loading apa saja yang pernah di-cancel
✅ **Report** - Bisa generate laporan loading yang di-cancel
✅ **Reversible** - Bisa di-restore jika ada kesalahan (walaupun perlu effort)
✅ **Foreign Key Safe** - Tidak break jika ada tabel lain yang reference ke loading_delivery

#### **Kekurangan:**
❌ **Detail tetap hilang** - List SPK yang di-cancel tidak tersimpan
❌ **Database bloat** - Data header cancelled tetap ada di tabel (minor)
❌ **Query perlu filter** - Harus WHERE status != -1 jika tidak mau ambil cancelled

#### **Recommended For:**
- ✅ Sistem yang perlu audit trail
- ✅ Sistem dengan compliance requirement
- ✅ Sistem yang sering ada dispute/komplain
- ✅ Sistem yang butuh reporting lengkap

---

### **OPSI 2: HARD DELETE**

#### **Cara Kerja:**
```php
// Header: HAPUS PERMANEN
DELETE FROM loading_delivery WHERE no_loading = 'XXX';

// Detail: HAPUS PERMANEN
DELETE FROM loading_delivery_detail WHERE no_loading = 'XXX';
```

#### **Kelebihan:**
✅ **Clean Database** - Tidak ada data sampah
✅ **Simple Query** - Tidak perlu filter status != -1
✅ **Performance** - Tabel lebih kecil (minor)

#### **Kekurangan:**
❌ **No Audit Trail** - Data hilang permanen
❌ **No History** - Tidak bisa track loading yang di-cancel
❌ **Irreversible** - Tidak bisa di-restore jika salah cancel
❌ **Foreign Key Issue** - Bisa error jika ada tabel lain yang reference
❌ **Reporting** - Tidak bisa generate laporan cancelled loading

#### **Recommended For:**
- ✅ Sistem simple tanpa compliance
- ✅ Data loading yang tidak penting
- ✅ Yakin 100% tidak perlu history

---

### **OPSI 3: SOFT DELETE + DETAIL HISTORY** ✨ BEST PRACTICE

#### **Cara Kerja:**
```php
// 1. Copy detail ke tabel history
INSERT INTO loading_delivery_detail_history 
SELECT *, NOW() as cancelled_at, user_id as cancelled_by 
FROM loading_delivery_detail 
WHERE no_loading = 'XXX';

// 2. Header: Update status jadi -1
UPDATE loading_delivery SET status = -1 WHERE no_loading = 'XXX';

// 3. Detail: HAPUS
DELETE FROM loading_delivery_detail WHERE no_loading = 'XXX';
```

#### **Kelebihan:**
✅ **Full Audit Trail** - Header DAN detail tersimpan
✅ **Bisa tampilkan SPK** - List SPK yang di-cancel bisa ditampilkan lagi
✅ **History lengkap** - Semua informasi tersimpan
✅ **Clean Active Table** - Tabel utama tetap bersih (detail dihapus)
✅ **Best of both worlds** - Gabungan soft delete + history

#### **Kekurangan:**
❌ **Need History Table** - Harus buat tabel baru
❌ **Extra Code** - Logic sedikit lebih kompleks
❌ **Storage** - Butuh space untuk tabel history (minor)

#### **Schema Tabel History:**
```sql
CREATE TABLE loading_delivery_detail_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    -- Copy semua kolom dari loading_delivery_detail
    no_loading VARCHAR(50),
    no_delivery VARCHAR(50),
    id_spk_detail INT,
    no_so VARCHAR(50),
    customer VARCHAR(255),
    id_product VARCHAR(50),
    product VARCHAR(255),
    qty_muat INT,
    jumlah_berat DECIMAL(10,2),
    keterangan TEXT,
    -- Extra kolom untuk history
    cancelled_at DATETIME,
    cancelled_by VARCHAR(50),
    INDEX idx_no_loading (no_loading)
);
```

---

### **OPSI 4: SOFT DELETE DETAIL (Flag)** 

#### **Cara Kerja:**
```php
// Header: Update status jadi -1
UPDATE loading_delivery SET status = -1 WHERE no_loading = 'XXX';

// Detail: FLAG saja, tidak hapus
UPDATE loading_delivery_detail 
SET is_cancelled = 1, cancelled_at = NOW(), cancelled_by = user_id
WHERE no_loading = 'XXX';
```

#### **Kelebihan:**
✅ **Full Audit Trail** - Semua data masih ada
✅ **Bisa tampilkan SPK** - Tinggal query is_cancelled = 1
✅ **Reversible** - Bisa di-unrevert dengan update flag
✅ **No New Table** - Tidak perlu tabel history

#### **Kekurangan:**
❌ **Bloat Detail Table** - Data cancelled tetap di tabel aktif
❌ **Query selalu butuh filter** - WHERE is_cancelled = 0
❌ **Performance** - Tabel detail jadi lebih besar (minor)
❌ **Schema change** - Harus alter table tambah kolom

#### **Schema Changes:**
```sql
ALTER TABLE loading_delivery_detail 
ADD COLUMN is_cancelled TINYINT(1) DEFAULT 0,
ADD COLUMN cancelled_at DATETIME NULL,
ADD COLUMN cancelled_by VARCHAR(50) NULL,
ADD INDEX idx_cancelled (is_cancelled);
```

---

## 🏆 REKOMENDASI FINAL

### **Untuk Sistem Anda, Saya Rekomendasikan:**

### **🥇 PILIHAN 1: SOFT DELETE (Current) + SIMPAN NO_DELIVERY DI HEADER**

**Implementasi Simple:**
```php
// Saat cancel, simpan list SPK ke kolom baru
$no_deliveries = implode(', ', array_unique(array_column($details, 'no_delivery')));

UPDATE loading_delivery 
SET status = -1, 
    cancelled_spk_list = $no_deliveries,  // ← Kolom baru
    cancelled_at = NOW(),
    cancelled_by = user_id
WHERE no_loading = 'XXX';

DELETE FROM loading_delivery_detail WHERE no_loading = 'XXX';
```

**Schema Change:**
```sql
ALTER TABLE loading_delivery 
ADD COLUMN cancelled_spk_list TEXT NULL,
ADD COLUMN cancelled_at DATETIME NULL,
ADD COLUMN cancelled_by VARCHAR(50) NULL;
```

**Display di List:**
```php
if ($row['status'] == -1 && !empty($row['cancelled_spk_list'])) {
    $nestedData[] = "<span class='text-muted'><del>" . 
                    $row['cancelled_spk_list'] . 
                    "</del></span>";
} else if (!empty($mapDelivery[$row['no_loading']])) {
    // ... normal display
} else {
    $nestedData[] = '-';
}
```

#### **Keuntungan Solusi Ini:**
✅ **Minimal Change** - Hanya tambah 3 kolom
✅ **Audit Trail** - Header + list SPK tersimpan
✅ **Display SPK** - Bisa tampilkan SPK yang di-cancel (strikethrough)
✅ **No New Table** - Tidak perlu tabel history
✅ **Performance** - Tidak impact query existing

---

### **🥈 PILIHAN 2: TETAP SEPERTI SEKARANG (Paling Simple)**

**Status Quo:**
- Header soft delete (status = -1)
- Detail hard delete
- Display "-" untuk cancelled loading

#### **Keuntungan:**
✅ **No Change Needed** - Sudah jalan
✅ **Simple** - Code paling sederhana
✅ **Clean Detail Table** - Detail dihapus

#### **Kekurangan:**
❌ **No SPK History** - List SPK hilang
❌ **Display "-"** - Kurang informatif

**Cocok Jika:**
- Data loading cancelled tidak terlalu penting
- Tidak butuh detail history
- User OK dengan display "-"

---

### **🥉 PILIHAN 3: SOFT DELETE FULL (History Table)**

**Hanya jika:**
- ✅ Butuh full audit trail untuk compliance
- ✅ Ada requirement reporting cancelled loading
- ✅ Sering ada dispute yang perlu check detail
- ✅ Budget development cukup (lebih banyak coding)

---

## 📋 KESIMPULAN

### **Status Baru (-1 = Cancelled):**
✅ **AMAN** - Tidak impact modul lain
✅ **ISOLATED** - Modul lain tidak filter status -1
✅ **NO BREAKING CHANGE** - Surat jalan tetap jalan normal

### **Rekomendasi Delete Strategy:**

| Kriteria | Rekomendasi |
|----------|-------------|
| **Simple & Quick** | ✅ Keep current (status quo) |
| **Need SPK List** | ✅ Soft delete + save SPK to header (BEST) |
| **Full Compliance** | ✅ Soft delete + history table |
| **Minimal Storage** | Hard delete (NOT recommended) |

### **My Top Recommendation:**
**🏆 Soft Delete + Simpan List SPK di Header Loading**

Alasan:
1. ✅ Balance antara simplicity vs functionality
2. ✅ Minimal schema change (3 kolom saja)
3. ✅ Bisa tampilkan SPK yang di-cancel
4. ✅ Audit trail tetap ada
5. ✅ No new table needed

---

**Pilihan Anda?** Beritahu saya mau pakai yang mana, nanti saya implementasikan! 🙏
