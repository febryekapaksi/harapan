# Dokumentasi Fitur Cancel Loading

## 📋 Overview

Fitur untuk membatalkan (cancel) seluruh loading yang masih berstatus **Draft (status = 0)** dari halaman index Muat Kendaraan.

## 🎯 Business Rules

### Kondisi Cancel DIIZINKAN:

- ✅ Status loading = **Draft (0)**
- ✅ Belum ada Surat Jalan terkait

### Kondisi Cancel TIDAK DIIZINKAN:

- ❌ Status loading = Confirm QTY (1), Confirm Berat (2), atau Approved (3)
- ❌ Sudah ada Surat Jalan yang menggunakan no_loading tersebut
- ❌ Status loading = Cancelled (-1)

## 🔄 Flow Proses Cancel

### 1. User Action

- User buka halaman **Loading → Muat Kendaraan**
- Pada list loading dengan status **Draft**, ada tombol merah dengan icon ban (🚫)
- User klik tombol Cancel
- Muncul konfirmasi SweetAlert
- User klik "Ya, Cancel Loading!"

### 2. Backend Process

1. **Validasi:**
   - Cek status = 0 (Draft)
   - Cek tidak ada Surat Jalan terkait
2. **Restore Qty SPK Detail:**

   ```
   Untuk setiap item di loading_delivery_detail:
   - qty_belum_muat = qty_belum_muat + qty_muat (dari loading)
   - qty_muat = qty_muat - qty_muat (dari loading)
   ```

3. **Update Status SPK Header:**
   - Jika tidak ada loading lain yang pakai SPK ini → status = `NOT YET DELIVER`
   - Jika masih ada loading lain → status = `LOADING`

4. **Hapus Loading Detail:**
   - DELETE semua row dari `loading_delivery_detail`

5. **Update Loading Header:**
   - Set status = -1 (Cancelled)
   - Set updated_by dan updated_at

6. **History Log:**
   - Catat ke history: "Cancel Loading: {no_loading}"

### 3. Result

- ✅ Loading status berubah jadi **Cancelled**
- ✅ Semua SPK kembali muncul di modal "Pilih SPK" dengan status **Waiting Loading**
- ✅ Qty SPK sudah di-restore
- ✅ DataTable di-reload otomatis

## 🗂️ Database Changes

### Tabel yang Terpengaruh:

1. **loading_delivery**
   - UPDATE: `status = -1, cancelled_spk_list, cancelled_by, cancelled_at, updated_by, updated_at`

2. **loading_delivery_detail**
   - DELETE: Semua row dengan no_loading tersebut

3. **spk_delivery_detail**
   - UPDATE: `qty_belum_muat`, `qty_muat`

4. **spk_delivery**
   - UPDATE: `status` (NOT YET DELIVER / LOADING)

## 🎨 UI Changes

### File: `application/modules/loading/views/index.php`

- Tambah event handler JavaScript untuk button `.cancel-loading-btn`
- Konfirmasi menggunakan SweetAlert
- Auto reload DataTable setelah sukses

### File: `application/modules/loading/models/Loading_model.php`

- Tambah button Cancel di kolom action untuk status Draft
- Tambah status badge "Cancelled" (merah) untuk status -1
- Button hanya muncul jika user punya permission `Loading.Delete`

## 🔐 Permission

- Permission yang digunakan: **`Loading.Delete`**
- Hanya user dengan permission ini yang bisa melihat dan menggunakan tombol Cancel

## 📊 Status Loading

| Status        | Nilai  | Label         | Warna   | Action Buttons                 |
| ------------- | ------ | ------------- | ------- | ------------------------------ |
| Draft         | 0      | Draft         | Yellow  | Print, Confirm QTY, **Cancel** |
| Confirm QTY   | 1      | Confirm QTY   | Aqua    | Print, Confirm Berat           |
| Confirm Berat | 2      | Confirm Berat | Blue    | Print                          |
| Approved      | 3      | Approved      | Green   | Print                          |
| **Cancelled** | **-1** | **Cancelled** | **Red** | _None_                         |

## 🚨 Error Messages

| Kondisi               | Pesan Error                                                    |
| --------------------- | -------------------------------------------------------------- |
| ID tidak ditemukan    | "ID tidak ditemukan"                                           |
| Data tidak ada        | "Data loading tidak ditemukan"                                 |
| Status bukan Draft    | "Loading tidak bisa di-cancel. Status bukan Draft."            |
| Sudah ada Surat Jalan | "Loading tidak bisa di-cancel. Sudah ada Surat Jalan terkait." |
| Detail kosong         | "Detail loading kosong"                                        |
| Gagal simpan          | "Gagal cancel loading"                                         |

## ✅ Success Message

```
"Loading berhasil di-cancel. SPK dikembalikan ke status Waiting Loading."
```

## 🧪 Testing Scenarios

### Test Case 1: Cancel Draft Loading (Success)

1. Buat loading baru dengan 3 SPK
2. Simpan (status = Draft)
3. Klik Cancel di list
4. **Expected:** Loading cancelled, SPK kembali ke Waiting Loading

### Test Case 2: Cancel Setelah Confirm QTY (Fail)

1. Buat loading dan confirm qty (status = 1)
2. Klik Cancel
3. **Expected:** Tombol Cancel tidak muncul

### Test Case 3: Cancel Loading yang Sudah Ada Surat Jalan (Fail)

1. Buat loading dan approve
2. Buat Surat Jalan dari loading tersebut
3. Ubah status loading ke Draft (manual)
4. Klik Cancel
5. **Expected:** Error "Sudah ada Surat Jalan terkait"

### Test Case 4: Cancel Loading dengan Multiple SPK (Success)

1. Buat loading dengan 5 SPK berbeda
2. Cancel loading
3. Cek semua SPK kembali ke Waiting Loading
4. **Expected:** Semua SPK status = Waiting Loading

### Test Case 5: Permission Check

1. Login sebagai user tanpa permission `Loading.Delete`
2. Buka halaman loading
3. **Expected:** Tombol Cancel tidak muncul di status Draft

## 📝 Code Changes Summary

### Files Modified:

1. ✅ `application/modules/loading/controllers/Loading.php`
   - Tambah method `cancel_loading()`

2. ✅ `application/modules/loading/models/Loading_model.php`
   - Update method `data_side_loading()` untuk tambah button Cancel
   - Tambah status Cancelled (-1)

3. ✅ `application/modules/loading/views/index.php`
   - Tambah JavaScript handler untuk button Cancel

### Files Added:

- None

### Database Schema Changes:

- None (menggunakan kolom status existing dengan nilai baru: -1)

## 🔧 Configuration

Tidak ada konfigurasi tambahan yang diperlukan.

## 📌 Notes

- Loading yang di-cancel **TIDAK DIHAPUS** dari database, hanya status berubah jadi -1
- Jika ingin hapus permanen, uncomment baris delete di controller
- History log dicatat untuk audit trail
- SweetAlert digunakan untuk konfirmasi user-friendly

## 👤 Author

- Date: 2026-08-21
- Requested by: BA Team
- Implemented by: Kiro AI Assistant

## 🔄 Version History

- v1.0 (2026-08-21): Initial implementation - Cancel loading dari index

---

**END OF DOCUMENTATION**
