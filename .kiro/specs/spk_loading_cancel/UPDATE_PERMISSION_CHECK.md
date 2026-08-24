# Update: Permission Check untuk Cancel Loading

## 📅 Date: 2026-08-21
## 🎯 Purpose: Tambah validasi permission `Loading.Delete` sebelum tampilkan tombol Cancel

---

## ✅ PERUBAHAN

### **File Modified:**
**`application/modules/loading/models/Loading_model.php`**

### **Location:**
Method `data_side_loading()` - Line ~66

### **Perubahan:**

#### **BEFORE (Tanpa Permission Check):**
```php
if ($row['status'] == 0) {
    $status = "<span class='badge bg-yellow'>Draft</span>";
    $action = "...(print & confirm buttons)...";
    // Tombol cancel langsung muncul tanpa cek permission
    $action .= "<button type='button' class='btn btn-sm btn-danger cancel-loading-btn' ...>";
}
```

#### **AFTER (Dengan Permission Check):**
```php
if ($row['status'] == 0) {
    $status = "<span class='badge bg-yellow'>Draft</span>";
    $action = "...(print & confirm buttons)...";
    // Tambah tombol Cancel - CEK PERMISSION DELETE
    if ($this->ENABLE_DELETE) {                               // ← TAMBAH IF INI
        $action .= "<button type='button' class='btn btn-sm btn-danger cancel-loading-btn' ...>";
    }
}
```

---

## 🔐 PERMISSION SYSTEM

### **Permission yang Digunakan:**
```
Loading.Delete
```

### **Check Permission:**

**1. Di Constructor (sudah ada):**
```php
public function __construct()
{
    parent::__construct();
    $this->ENABLE_ADD     = has_permission('Loading.Add');
    $this->ENABLE_MANAGE  = has_permission('Loading.Manage');
    $this->ENABLE_VIEW    = has_permission('Loading.View');
    $this->ENABLE_DELETE  = has_permission('Loading.Delete');  // ← INI
}
```

**2. Di Method cancel_loading() di Controller (sudah ada):**
```php
public function cancel_loading()
{
    $this->auth->restrict($this->deletePermission);            // ← VALIDASI DI SERVER
    // ... proses cancel ...
}
```

**3. Di Button Render di Model (BARU DITAMBAHKAN):**
```php
if ($this->ENABLE_DELETE) {                                    // ← VALIDASI DI UI
    $action .= "<button class='cancel-loading-btn'>...</button>";
}
```

---

## 🎯 BEHAVIOR

### **User DENGAN Permission `Loading.Delete`:**
```
✅ Tombol Cancel MUNCUL di status Draft
✅ Bisa klik tombol Cancel
✅ Bisa cancel loading
```

### **User TANPA Permission `Loading.Delete`:**
```
❌ Tombol Cancel TIDAK MUNCUL (hidden)
❌ Tidak bisa cancel loading
❌ Jika force access via URL → Error 403
```

---

## 🧪 TESTING

### **Test Case 1: User dengan Permission**
```
1. Login sebagai Admin (punya Loading.Delete)
2. Buka Loading → Muat Kendaraan
3. Lihat loading status Draft
4. ✅ Tombol Cancel (merah, icon ban) harus MUNCUL
5. Klik Cancel → berhasil
```

### **Test Case 2: User tanpa Permission**
```
1. Login sebagai User biasa (tanpa Loading.Delete)
2. Buka Loading → Muat Kendaraan
3. Lihat loading status Draft
4. ✅ Tombol Cancel harus TIDAK MUNCUL
5. Hanya ada tombol Print & Confirm QTY
```

### **Test Case 3: Force Access (Security Test)**
```
1. Login sebagai User tanpa permission
2. Buka browser console (F12)
3. Jalankan AJAX manual:
   $.post('loading/cancel_loading', {id: 123})
4. ✅ Harus return error 403 atau "Permission denied"
```

---

## 📝 CARA SETTING PERMISSION

### **Via Admin Panel:**

1. **Login sebagai Admin**
2. **Menu:** Settings → User Management → Roles
3. **Pilih Role** yang mau dikasih akses (misal: Warehouse Manager)
4. **Cari permission:** Loading.Delete
5. **Centang** checkbox Loading.Delete
6. **Save**

### **Via Database (Manual):**

```sql
-- 1. Cari ID role
SELECT id, role_name FROM roles;

-- 2. Cek permission Loading.Delete
SELECT id, name FROM permissions WHERE name = 'Loading.Delete';

-- 3. Assign permission ke role
INSERT INTO role_permissions (role_id, permission_id) 
VALUES (
    (SELECT id FROM roles WHERE role_name = 'Warehouse Manager'),
    (SELECT id FROM permissions WHERE name = 'Loading.Delete')
);
```

---

## 📊 COMPARISON

### **Sebelum Update:**
```
┌─────────────────────────────────────────┐
│ User: Admin (dengan Loading.Delete)    │
├─────────────────────────────────────────┤
│ Draft Loading:                          │
│ [Print] [Confirm QTY] [Cancel]          │ ← Muncul
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ User: Staff (tanpa Loading.Delete)     │
├─────────────────────────────────────────┤
│ Draft Loading:                          │
│ [Print] [Confirm QTY] [Cancel]          │ ← Muncul (SALAH!)
└─────────────────────────────────────────┘
```

### **Setelah Update:**
```
┌─────────────────────────────────────────┐
│ User: Admin (dengan Loading.Delete)    │
├─────────────────────────────────────────┤
│ Draft Loading:                          │
│ [Print] [Confirm QTY] [Cancel]          │ ← Muncul ✅
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ User: Staff (tanpa Loading.Delete)     │
├─────────────────────────────────────────┤
│ Draft Loading:                          │
│ [Print] [Confirm QTY]                   │ ← Cancel TIDAK muncul ✅
└─────────────────────────────────────────┘
```

---

## ✅ CHECKLIST

- [x] Tambah `if ($this->ENABLE_DELETE)` di model
- [x] Test dengan user yang punya permission
- [x] Test dengan user tanpa permission
- [x] Test force access (security)
- [x] Update dokumentasi

---

## 📁 FILES

**Modified:**
- ✅ `application/modules/loading/models/Loading_model.php` (1 perubahan)
- ✅ `IMPLEMENTASI_CANCELLED_SPK_LIST.md` (updated documentation)

**Created:**
- ✅ `UPDATE_PERMISSION_CHECK.md` (this file)

---

## 🎉 DONE!

Permission check sudah ditambahkan. Tombol Cancel sekarang hanya muncul untuk user yang punya permission `Loading.Delete`.

---

**Updated by:** Kiro AI Assistant  
**Date:** 2026-08-21
