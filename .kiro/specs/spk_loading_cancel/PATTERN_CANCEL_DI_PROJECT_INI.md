# Pattern Cancel/Batal di Project Ini

## 🔍 ANALISIS PATTERN CANCEL

Saya sudah menelusuri 8 modul yang memiliki fitur cancel/batal. Berikut temuan saya:

---

## 📊 SUMMARY PATTERN CANCEL

| Modul | Method | Header | Detail | Status/Flag | Restore Data? | Jurnal Balik? |
|-------|--------|--------|--------|-------------|---------------|---------------|
| **SPK Delivery** | `cancel_spk()` | ❌ HARD DELETE | ❌ HARD DELETE | - | ✅ Yes (SO) | ❌ No |
| **Sales Order** | `cancel_so()` | ✅ SOFT (status=CLOSED) | ✅ UPDATE (qty_cancelled) | `cancel_reason`, `cancelled_by`, `cancelled_at` | ✅ Yes (booking) | ❌ No |
| **Retur Pembelian** | `cancel()` | ✅ SOFT (status=0) | ✅ TETAP ADA | `updated_by`, `updated_date` | ✅ Yes (stok) | ✅ Yes |
| **Invoice Produk** | `cancel_invoice()` | ✅ SOFT (is_cancel=1) | ✅ TETAP ADA | `is_cancel`, `updated_by`, `updated_on` | ❌ No | ❌ No |
| **Purchase Order** | `cancelPO()` | ✅ SOFT (status) | ✅ UPDATE | - | ✅ Yes (booking) | ❌ No |
| **Setor Kasir** | `cancel()` | ✅ SOFT | ✅ TETAP ADA | - | - | ✅ Yes (likely) |
| **Setor Bank** | `cancel()` | ✅ SOFT | ✅ TETAP ADA | - | - | ✅ Yes (likely) |
| **Loading** (NEW) | `cancel_loading()` | ✅ SOFT (status=-1) | ❌ HARD DELETE | `updated_by`, `updated_at` | ✅ Yes (SPK qty) | ❌ No |

---

## 🎯 PATTERN DOMINAN: **SOFT DELETE**

### **Pattern Umum di Project Ini:**

```
✅ SOFT DELETE = 7 modul (87.5%)
❌ HARD DELETE = 1 modul (12.5%) - hanya SPK Delivery
```

---

## 📋 DETAIL IMPLEMENTASI PER MODUL

### **1. SPK DELIVERY** ⚠️ **EXCEPTION - HARD DELETE**

**File:** `application/modules/spk_delivery/controllers/Spk_delivery.php`

```php
public function cancel_spk()
{
    // 1. Restore qty di sales_order_detail
    // 2. Update status_spk di sales_order
    // 3. DELETE spk_delivery_detail  ← HARD DELETE
    // 4. DELETE spk_delivery          ← HARD DELETE
    
    history("Cancel SPK: " . $no_delivery . " | No SO: " . $no_so);
}
```

**Karakteristik:**
- ❌ Header & Detail DIHAPUS PERMANEN
- ❌ Tidak ada flag cancelled
- ❌ Tidak ada audit trail di tabel
- ✅ Restore qty ke SO
- ✅ Ada history log

**Kenapa Hard Delete?**
- SPK adalah dokumen sementara/intermediary
- SO (parent) masih ada sebagai history
- Bisa di-create ulang dari SO

---

### **2. SALES ORDER** ✅ **SOFT DELETE + DETAIL UPDATE**

**File:** `application/modules/sales_order/controllers/Sales_order.php`

```php
public function cancel_so()
{
    foreach ($so_details as $det) {
        // Update sales_order_detail - SOFT DELETE
        $this->db->update('sales_order_detail', [
            'qty_order'       => $qty_spk,         // kurangi
            'qty_belum_spk'   => 0,
            'qty_cancelled'   => $sisa,            // ← CATAT QTY CANCELLED
            'status_planning' => 1,
        ], ['id' => $det['id']]);
        
        // Kembalikan booking warehouse
        // Catat di kartu_stok: 'Batal SO'
    }
    
    // Update header SO - SOFT DELETE
    $this->db->update('sales_order', [
        'status_so'      => 'CLOSED',              // ← STATUS
        'cancel_reason'  => $reason,               // ← ALASAN
        'cancel_qty'     => $total_cancelled,      // ← JUMLAH
        'cancelled_by'   => $this->auth->user_id(), // ← USER
        'cancelled_at'   => date('Y-m-d H:i:s'),   // ← TIMESTAMP
    ], ['no_so' => $no_so]);
}
```

**Karakteristik:**
- ✅ Header SOFT DELETE (status = CLOSED)
- ✅ Detail TETAP ADA, UPDATE qty_cancelled
- ✅ Simpan: reason, qty, user, timestamp
- ✅ Restore stock booking
- ✅ Catat kartu stok
- ✅ Full audit trail

**Pattern:**
```
Header: status + cancel_reason + cancelled_by + cancelled_at + cancel_qty
Detail: qty_cancelled (tetap ada)
```

---

### **3. RETUR PEMBELIAN** ✅ **SOFT DELETE + JURNAL BALIK**

**File:** `application/modules/retur_pembelian/models/Retur_pembelian_model.php`

```php
public function cancel($id)
{
    $header = $this->db->get_where('tr_retur_pembelian', ['id' => $id])->row_array();
    
    // Jika sudah Process (status=2), buat jurnal balik
    if ($header['status'] == 2) {
        // Jurnal balik
        $this->Jurnal_retur_model->create_jurnal_balik($header, $detail);
        
        // Kembalikan stok
        foreach ($detail as $d) {
            $this->db->set('qty_stock', 'qty_stock + ' . $qty_retur, false);
            $this->db->set('qty_free', 'qty_free + ' . $qty_retur, false);
            $this->db->update('warehouse_stock');
            
            // Catat kartu_stok: 'Cancel Retur Pembelian'
            $this->db->insert('kartu_stok', [...]);
        }
    }
    
    // Update header - SOFT DELETE
    $this->db->update('tr_retur_pembelian', [
        'status'       => 0,                        // ← STATUS 0 = CANCELLED
        'updated_by'   => $this->auth->user_id(),
        'updated_date' => date('Y-m-d H:i:s'),
    ], ['id' => $id]);
}
```

**Karakteristik:**
- ✅ Header SOFT DELETE (status = 0)
- ✅ Detail TETAP ADA
- ✅ Restore stok
- ✅ Jurnal balik (jika sudah process)
- ✅ Catat kartu stok
- ✅ Full audit trail

**Pattern:**
```
Header: status = 0 (cancelled) + updated_by + updated_date
Detail: tetap ada
```

---

### **4. INVOICE PRODUK** ✅ **SOFT DELETE (FLAG)**

**File:** `application/modules/invoice_produk/controllers/Invoice_produk.php`

```php
public function cancel_invoice()
{
    $ArrHeader = [
        'updated_by'  => $this->auth->user_id(),
        'updated_on'  => date('Y-m-d H:i:s'),
        'is_cancel'   => 1                          // ← FLAG CANCEL
    ];
    
    $this->db->update('tr_invoice_sales', $ArrHeader, ['id_invoice' => $id_invoice]);
}
```

**Karakteristik:**
- ✅ Header SOFT DELETE (is_cancel = 1)
- ✅ Detail TETAP ADA
- ❌ Tidak restore data (invoice sifatnya dokumen)
- ✅ Simple & clean

**Pattern:**
```
Header: is_cancel = 1 + updated_by + updated_on
Detail: tetap ada
```

---

## 🏆 KESIMPULAN PATTERN PROJECT INI

### **A. PATTERN UMUM (87.5% modul):**

#### **1. Header: SOFT DELETE**
```php
// Pakai salah satu metode:

// Metode 1: Status (paling umum)
UPDATE table_header SET status = 0 WHERE id = xxx;  // 0 = cancelled

// Metode 2: Flag
UPDATE table_header SET is_cancel = 1 WHERE id = xxx;

// Metode 3: Status string
UPDATE table_header SET status = 'CLOSED' WHERE id = xxx;
```

#### **2. Detail: TETAP ADA / UPDATE**
```php
// Tidak dihapus, hanya update jika perlu
UPDATE table_detail SET qty_cancelled = xxx WHERE id = xxx;
```

#### **3. Audit Trail Columns:**
```sql
-- Kolom yang umum ditambahkan:
cancelled_by     VARCHAR(50)   -- User ID yang cancel
cancelled_at     DATETIME      -- Timestamp cancel
cancel_reason    TEXT          -- Alasan cancel (optional)
cancel_qty       DECIMAL       -- Jumlah yang di-cancel (optional)
```

#### **4. Restore Related Data:**
```php
// Kembalikan data yang terpengaruh:
- Stock warehouse (qty_stock, qty_free, qty_booking)
- Kartu stok (catat transaksi cancel)
- Parent document (update status/qty)
- Jurnal (buat jurnal balik jika perlu)
```

---

### **B. EXCEPTION: SPK DELIVERY (Hard Delete)**

**Kenapa SPK Delivery pakai Hard Delete?**

1. **SPK = Dokumen Intermediary**
   - Bukan dokumen final
   - Parent (SO) masih ada
   - Bisa di-generate ulang dari SO

2. **Tidak Butuh Audit Trail di Level SPK**
   - History ada di SO
   - SO tidak dihapus
   - Focus audit di SO, bukan SPK

3. **Keep Table Clean**
   - SPK yang di-cancel tidak ada nilai bisnis
   - Menghindari data sampah
   - Performance query lebih baik

---

## 🎯 REKOMENDASI UNTUK LOADING

### **Mengikuti Pattern Project:**

**Loading itu seperti SPK atau seperti SO?**

| Aspek | SPK Delivery | Loading | Sales Order |
|-------|--------------|---------|-------------|
| **Tipe Dokumen** | Intermediary | **Intermediary** | Final |
| **Parent Exists?** | ✅ SO | ✅ SPK Delivery | ❌ None |
| **Bisa Regenerate?** | ✅ Yes (dari SO) | ✅ Yes (dari SPK) | ❌ No |
| **Need Audit Detail?** | ❌ Low | **🤔 Medium** | ✅ High |
| **Used in Reports?** | ❌ No | **🤔 Maybe** | ✅ Yes |

### **Loading Lebih Mirip SPK atau SO?**

**🔍 Analisis:**
- Loading adalah **intermediary** antara SPK dan Surat Jalan
- Parent (SPK Delivery) masih ada
- Bisa di-create ulang dari SPK yang sama
- **TAPI:** Loading mungkin dipakai di report/monitoring

---

## 💡 REKOMENDASI FINAL

### **OPSI 1: Ikuti Pattern SPK (Hard Delete)** ❌ **CURRENT**

```php
// Header: Soft delete (status = -1)
UPDATE loading_delivery SET status = -1 WHERE ...;

// Detail: Hard delete
DELETE FROM loading_delivery_detail WHERE ...;
```

**Pros:**
- ✅ Mirip dengan SPK (parent-nya)
- ✅ Table detail tetap clean
- ✅ Simple implementation

**Cons:**
- ❌ List SPK hilang (tidak bisa ditampilkan)
- ❌ Audit trail partial (header only)

---

### **OPSI 2: Ikuti Pattern SO (Full Soft Delete)** ✅ **RECOMMENDED**

```php
// Header: Soft delete dengan audit trail
UPDATE loading_delivery SET 
    status = -1,
    cancelled_spk_list = 'SPK251002021, SPK251002022',  // ← LIST SPK
    cancelled_by = user_id,
    cancelled_at = NOW()
WHERE ...;

// Detail: Hard delete (ATAU soft delete dengan flag)
DELETE FROM loading_delivery_detail WHERE ...;
```

**Pros:**
- ✅ Bisa tampilkan list SPK yang di-cancel
- ✅ Full audit trail
- ✅ Cocok untuk report/monitoring
- ✅ Balance antara SPK pattern vs SO pattern

**Cons:**
- 🟡 Butuh tambah 3 kolom
- 🟡 Sedikit lebih kompleks

---

### **OPSI 3: Hybrid SPK + Minimal Audit** 🥈 **ALTERNATIVE**

```php
// Header: Soft delete minimal
UPDATE loading_delivery SET 
    status = -1,
    updated_by = user_id,
    updated_at = NOW()
WHERE ...;

// Detail: Hard delete
DELETE FROM loading_delivery_detail WHERE ...;
```

**Pros:**
- ✅ Mengikuti pattern SPK
- ✅ Simple
- ✅ No schema change

**Cons:**
- ❌ List SPK hilang
- ❌ Audit trail minimal

---

## 🏁 KESIMPULAN

### **Pattern di Project Ini:**

**87.5% modul pakai SOFT DELETE:**
- Header: Update status/flag
- Detail: Tetap ada atau update
- Audit: cancelled_by, cancelled_at, cancel_reason
- Restore: Yes (stock, booking, etc)

**12.5% modul pakai HARD DELETE:**
- Hanya SPK Delivery
- Alasan: Intermediary document, parent masih ada

### **Loading saat ini (Hybrid):**
- Header: SOFT DELETE (status = -1) ✅
- Detail: HARD DELETE ✅
- Audit: Minimal (updated_by, updated_at) 🟡
- **Issue:** List SPK hilang (tampil "-") ❌

### **Rekomendasi:**

**🥇 BEST: Tambah kolom `cancelled_spk_list`**
- Simpan list SPK di header
- Bisa ditampilkan dengan strikethrough
- Minimal schema change (3 kolom)
- Balance antara simplicity vs audit

**🥈 OK: Keep current (status quo)**
- Sudah mengikuti pattern SPK
- Simple & clean
- Trade-off: list SPK hilang

**🥉 OVERKILL: Full soft delete dengan history table**
- Hanya jika butuh compliance ketat
- Too complex untuk loading

---

**Pilihan Anda?** 🙏
