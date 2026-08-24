# Implementasi: Tambah Kolom cancelled_spk_list

## 📅 Date: 2026-08-21

## 👤 Requested by: User

## 🎯 Purpose: Simpan list SPK yang di-cancel untuk audit trail dan display

---

## ✅ IMPLEMENTASI SELESAI

### **1. DATABASE MIGRATION**

**File:** `database_migration_loading_cancel.sql`

**SQL Script:**

```sql
ALTER TABLE loading_delivery
ADD COLUMN cancelled_spk_list TEXT NULL COMMENT 'List No SPK Delivery yang di-cancel (separator koma)',
ADD COLUMN cancelled_by VARCHAR(50) NULL COMMENT 'User ID yang melakukan cancel',
ADD COLUMN cancelled_at DATETIME NULL COMMENT 'Timestamp cancel loading';
```

**Kolom yang Ditambahkan:**
| Kolom | Type | Nullable | Keterangan |
|-------|------|----------|------------|
| `cancelled_spk_list` | TEXT | YES | List no_delivery yang di-cancel (separator: koma) |
| `cancelled_by` | VARCHAR(50) | YES | User ID yang melakukan cancel |
| `cancelled_at` | DATETIME | YES | Timestamp saat cancel |

**Cara Menjalankan:**

```bash
# Connect ke MySQL
mysql -u username -p database_name

# Run migration
source c:\web_dev\harapan\database_migration_loading_cancel.sql

# Atau copy-paste SQL ke phpMyAdmin / MySQL Workbench
```

---

### **2. CONTROLLER UPDATE**

**File:** `application/modules/loading/controllers/Loading.php`

**Method:** `cancel_loading()`

**Perubahan:**

#### **A. Simpan List SPK (Line ~850)**

```php
// BEFORE (old code)
$details = $this->db->get_where('loading_delivery_detail', ['no_loading' => $no_loading])->result_array();

// AFTER (new code)
$details = $this->db->get_where('loading_delivery_detail', ['no_loading' => $no_loading])->result_array();

if (empty($details)) {
    echo json_encode(['status' => 0, 'pesan' => 'Detail loading kosong']);
    return;
}

// Simpan list no_delivery untuk audit trail
$no_deliveries = array_unique(array_column($details, 'no_delivery'));
$cancelled_spk_list = implode(', ', $no_deliveries);  // "SPK1, SPK2, SPK3"
```

#### **B. Update Header dengan Audit Trail (Line ~920)**

```php
// BEFORE (old code)
$this->db->update(
    'loading_delivery',
    [
        'status'     => -1,
        'updated_by' => $this->auth->user_id(),
        'updated_at' => date('Y-m-d H:i:s')
    ],
    ['no_loading' => $no_loading]
);

// AFTER (new code)
$this->db->update(
    'loading_delivery',
    [
        'status'              => -1,
        'cancelled_spk_list'  => $cancelled_spk_list,      // ← NEW
        'cancelled_by'        => $this->auth->user_id(),    // ← NEW
        'cancelled_at'        => date('Y-m-d H:i:s'),      // ← NEW
        'updated_by'          => $this->auth->user_id(),
        'updated_at'          => date('Y-m-d H:i:s')
    ],
    ['no_loading' => $no_loading]
);
```

---

### **3. MODEL UPDATE**

**File:** `application/modules/loading/models/Loading_model.php`

**Method:** `data_side_loading()` & `get_query_json_loading()`

**Perubahan:**

#### **A. Add SELECT kolom cancelled_spk_list (Line ~210)**

```php
// BEFORE
SELECT
    l.id,
    l.no_loading,
    l.pengiriman,
    l.nopol,
    l.kapasitas,
    l.total_berat,
    l.tanggal_muat,
    l.status,
    COALESCE(s.list_spk, '') AS list_spk
FROM loading_delivery l
...

// AFTER
SELECT
    l.id,
    l.no_loading,
    l.pengiriman,
    l.nopol,
    l.kapasitas,
    l.total_berat,
    l.tanggal_muat,
    l.status,
    l.cancelled_spk_list,                    // ← NEW
    COALESCE(s.list_spk, '') AS list_spk
FROM loading_delivery l
...
```

#### **B. Update Display Logic (Line ~60)**

```php
// BEFORE
if (!empty($mapDelivery[$row['no_loading']])) {
    $ul = "<ul style='padding-left:16px;margin:0'>";
    foreach ($mapDelivery[$row['no_loading']] as $spk) {
        $ul .= "<li>" . htmlspecialchars($spk) . "</li>";
    }
    $ul .= "</ul>";
    $nestedData[] = $ul;
} else {
    $nestedData[] = '-';
}

// AFTER
if ($row['status'] == -1 && !empty($row['cancelled_spk_list'])) {
    // Untuk cancelled loading, tampilkan dari kolom cancelled_spk_list
    $nestedData[] = "<span class='text-muted'><del>" .
                    htmlspecialchars($row['cancelled_spk_list']) .
                    "</del> <small class='text-danger'>(Cancelled)</small></span>";
} else if (!empty($mapDelivery[$row['no_loading']])) {
    $ul = "<ul style='padding-left:16px;margin:0'>";
    foreach ($mapDelivery[$row['no_loading']] as $spk) {
        $ul .= "<li>" . htmlspecialchars($spk) . "</li>";
    }
    $ul .= "</ul>";
    $nestedData[] = $ul;
} else {
    $nestedData[] = '-';
}
```

---

## 🎨 TAMPILAN HASIL

### **Before (Lama):**

```
No. SPK Delivery: -
```

### **After (Baru):**

```
No. SPK Delivery: ~~SPK251002021, SPK251002022, SPK251002023~~ (Cancelled)
```

**Style:**

- Text strikethrough (~~text~~)
- Warna abu-abu (text-muted)
- Label "(Cancelled)" warna merah

---

## 🔄 FLOW LENGKAP

### **1. User Cancel Loading:**

```
1. User klik tombol Cancel (status = Draft)
2. Konfirmasi SweetAlert
3. AJAX ke controller cancel_loading()
```

### **2. Backend Process:**

```
1. Validasi status = 0 & tidak ada SJ
2. Ambil semua detail loading
3. Extract list no_delivery: ["SPK1", "SPK2", "SPK3"]
4. Gabung jadi string: "SPK1, SPK2, SPK3"
5. Restore qty di spk_delivery_detail
6. Update status spk_delivery
7. DELETE loading_delivery_detail
8. UPDATE loading_delivery:
   ├─ status = -1
   ├─ cancelled_spk_list = "SPK1, SPK2, SPK3"  ← SIMPAN DI SINI
   ├─ cancelled_by = user_id
   ├─ cancelled_at = timestamp
   └─ updated_by, updated_at
9. Commit transaction
10. History log
```

### **3. Display di List:**

```
Query SELECT dengan JOIN:
├─ Ambil kolom cancelled_spk_list
├─ Check status == -1
└─ Render dengan strikethrough + label
```

---

## 📊 DATA FLOW

```
┌─────────────────────────────────────────────────┐
│ SEBELUM CANCEL                                  │
├─────────────────────────────────────────────────┤
│ loading_delivery:                               │
│   no_loading: MK2608004                         │
│   status: 0 (Draft)                             │
│   cancelled_spk_list: NULL                      │
│                                                 │
│ loading_delivery_detail:                        │
│   Row 1: SPK251002021 - Product A               │
│   Row 2: SPK251002021 - Product B               │
│   Row 3: SPK251002022 - Product C               │
│   Row 4: SPK251002023 - Product D               │
└─────────────────────────────────────────────────┘
                    ↓ CANCEL
┌─────────────────────────────────────────────────┐
│ SETELAH CANCEL                                  │
├─────────────────────────────────────────────────┤
│ loading_delivery:                               │
│   no_loading: MK2608004                         │
│   status: -1 (Cancelled)                        │
│   cancelled_spk_list: "SPK251002021, SPK251002022, SPK251002023" ← SAVED
│   cancelled_by: "user123"                       │
│   cancelled_at: "2026-08-21 15:00:00"          │
│                                                 │
│ loading_delivery_detail:                        │
│   (EMPTY - ALL DELETED)                         │
└─────────────────────────────────────────────────┘
```

---

## 🔐 PERMISSION CHECK

### **Loading.Delete Permission:**

Tombol Cancel **hanya muncul** jika user memiliki permission `Loading.Delete`.

**Implementasi di Model:**

```php
// Constructor - Check permission
public function __construct()
{
    parent::__construct();
    $this->ENABLE_ADD     = has_permission('Loading.Add');
    $this->ENABLE_MANAGE  = has_permission('Loading.Manage');
    $this->ENABLE_VIEW    = has_permission('Loading.View');
    $this->ENABLE_DELETE  = has_permission('Loading.Delete');  // ← CHECK INI
}

// Action button - Conditional render
if ($row['status'] == 0) {
    $action = "... print & confirm buttons ...";

    // Tombol Cancel hanya muncul jika ada permission Delete
    if ($this->ENABLE_DELETE) {                               // ← IF INI
        $action .= "<button class='cancel-loading-btn'>...</button>";
    }
}
```

**Implementasi di Controller:**

```php
public function cancel_loading()
{
    // Validasi permission di method
    $this->auth->restrict($this->deletePermission);           // ← RESTRICT INI

    // ... proses cancel ...
}
```

### **Cara Setting Permission:**

1. Login sebagai **Admin**
2. Menu: **Settings → User Management → Role**
3. Pilih role yang mau dikasih akses
4. Cari permission: **Loading.Delete**
5. ✅ Centang permission tersebut
6. Save

### **Testing Permission:**

**User DENGAN permission `Loading.Delete`:**

- ✅ Tombol Cancel **MUNCUL** di status Draft
- ✅ Bisa klik & cancel loading

**User TANPA permission `Loading.Delete`:**

- ❌ Tombol Cancel **TIDAK MUNCUL**
- ❌ Jika force access via URL → Error 403 (restricted)

---

## ✅ TESTING CHECKLIST

### **Manual Testing:**

- [ ] 1. Run SQL migration
- [ ] 2. Verifikasi kolom sudah ada: `DESC loading_delivery`
- [ ] 3. **Test dengan user yang punya permission `Loading.Delete`:**
  - [ ] Login sebagai admin/user dengan permission Delete
  - [ ] Buka menu Loading → Muat Kendaraan
  - [ ] Buat loading baru dengan 3 SPK (status Draft)
  - [ ] **Tombol Cancel (merah) harus MUNCUL** di kolom Option
  - [ ] Klik tombol Cancel
  - [ ] Konfirmasi cancel
  - [ ] Check tabel loading_delivery:
    - [ ] status = -1
    - [ ] cancelled_spk_list = "SPK1, SPK2, SPK3"
    - [ ] cancelled_by = user_id
    - [ ] cancelled_at = timestamp
  - [ ] Check display di list loading:
    - [ ] Kolom "No. SPK Delivery" tampil dengan strikethrough
    - [ ] Ada label "(Cancelled)" warna merah
  - [ ] Check SPK Delivery:
    - [ ] Status kembali ke "Waiting Loading" (NOT YET DELIVER)
    - [ ] qty_belum_muat sudah di-restore
- [ ] 4. **Test dengan user yang TIDAK punya permission `Loading.Delete`:**
  - [ ] Login sebagai user tanpa permission Delete
  - [ ] Buka menu Loading → Muat Kendaraan
  - [ ] Lihat loading yang status Draft
  - [ ] **Tombol Cancel harus TIDAK MUNCUL** (hidden)
  - [ ] Test force access: buka console, jalankan AJAX cancel
  - [ ] **Harus error 403** atau "You don't have permission"

### **Edge Cases:**

- [ ] Cancel loading dengan 1 SPK
- [ ] Cancel loading dengan 10+ SPK (panjang list)
- [ ] Cancel loading yang sudah pernah di-cancel (should error)
- [ ] Cancel loading status Confirm QTY (should error)

---

## 📁 FILE SUMMARY

### **Files Created:**

1. ✅ `database_migration_loading_cancel.sql` - SQL migration script
2. ✅ `IMPLEMENTASI_CANCELLED_SPK_LIST.md` - Dokumentasi implementasi (this file)

### **Files Modified:**

1. ✅ `application/modules/loading/controllers/Loading.php` - Method cancel_loading()
2. ✅ `application/modules/loading/models/Loading_model.php` - Display logic & query (with permission check)
3. ✅ `FITUR_CANCEL_LOADING.md` - Updated documentation

### **Permission Required:**

- ✅ `Loading.Delete` - User harus punya permission ini untuk melihat & menggunakan tombol Cancel

### **Files NOT Modified (No Changes):**

- `application/modules/loading/views/index.php` - JavaScript handler sudah OK
- `application/modules/surat_jalan/**` - Not affected (only query status = 3)
- Other modules - Not affected

---

## 🚀 DEPLOYMENT STEPS

### **Development:**

```bash
# 1. Run SQL migration
mysql -u root -p harapan_db < database_migration_loading_cancel.sql

# 2. Clear CI cache (if any)
rm -rf application/cache/*

# 3. Test di browser
```

### **Production:**

```bash
# 1. Backup database first!
mysqldump -u user -p database_name > backup_before_migration.sql

# 2. Run migration
mysql -u user -p database_name < database_migration_loading_cancel.sql

# 3. Deploy code files:
#    - controllers/Loading.php
#    - models/Loading_model.php

# 4. Clear application cache

# 5. Test cancel functionality
```

---

## 📝 NOTES

1. **Migration harus dijalankan DULU** sebelum deploy code
2. **Kolom nullable** - tidak akan break data existing
3. **Backward compatible** - loading lama (status 0,1,2,3) tetap jalan normal
4. **Forward compatible** - loading baru yang di-cancel akan punya audit trail
5. **No breaking change** - modul lain tidak terpengaruh

---

## 🎉 DONE!

Implementasi selesai. Silakan:

1. Jalankan SQL migration
2. Test fitur cancel
3. Verifikasi tampilan list SPK

---

**Implemented by:** Kiro AI Assistant  
**Date:** 2026-08-21  
**Version:** 1.0
