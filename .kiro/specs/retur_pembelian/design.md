# Design: Modul Retur Pembelian (Purchase Return)

## Referensi
#[[file:requirement.md]]

---

## 1. Arsitektur dan Pola Desain

### 1.1 Arsitektur HMVC
Modul ini mengikuti pola HMVC CodeIgniter yang sudah ada di project:
- Controller extends Admin_Controller (permission-based access control)
- Model extends BF_Model (Bonfire base model dengan CRUD helper)
- Views dengan template engine AdminLTE (box-primary pattern)
- DataTable server-side processing untuk list data
- AJAX untuk interaksi form dinamis
- SweetAlert untuk konfirmasi dan notifikasi

### 1.2 Struktur Module

```
application/modules/retur_pembelian/
+-- controllers/
|   +-- Retur_pembelian.php
+-- models/
|   +-- Retur_pembelian_model.php
|   +-- Jurnal_retur_model.php
+-- views/
    +-- index.php
    +-- form.php
    +-- form_edit.php
    +-- view.php
    +-- print_sj.php
    +-- nota_retur.php
    +-- settlement.php
```

---

## 2. Database Design

### 2.1 ERD (Entity Relationship)

```
tr_retur_pembelian (1) --- (N) tr_retur_pembelian_detail
tr_retur_pembelian (1) --- (N) tr_retur_pembelian_pinalti
tr_retur_pembelian (1) --- (N) tr_retur_pembelian_settlement
tr_retur_pembelian (N) --- (1) mtr_supplier
tr_retur_pembelian (N) --- (1) receive_invoice (via no_invoice)
tr_retur_pembelian (1) --- (N) tr_jurnal (via no_transaksi)
```


### 2.2 DDL - SQL Schema

#### Tabel Header: tr_retur_pembelian

```sql
CREATE TABLE `tr_retur_pembelian` (
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
  `status_nota_retur` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Belum, 1=Sudah Diterima',
  `kategori_alasan` varchar(100) DEFAULT NULL,
  `keterangan_alasan` text DEFAULT NULL,
  `file_ba` varchar(255) DEFAULT NULL COMMENT 'Path file Berita Acara',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=Cancel, 1=Draft, 2=Process, 3=Selesai',
  `created_by` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_retur` (`no_retur`),
  KEY `idx_no_invoice` (`no_invoice`),
  KEY `idx_id_supplier` (`id_supplier`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Tabel Detail Produk: tr_retur_pembelian_detail

```sql
CREATE TABLE `tr_retur_pembelian_detail` (
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
  `total_nilai` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Tabel Pinalti/Claim: tr_retur_pembelian_pinalti

```sql
CREATE TABLE `tr_retur_pembelian_pinalti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL,
  `no_retur` varchar(20) NOT NULL,
  `nilai` decimal(15,2) NOT NULL DEFAULT 0.00,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Tabel Settlement: tr_retur_pembelian_settlement

```sql
CREATE TABLE `tr_retur_pembelian_settlement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_retur` int(11) NOT NULL,
  `no_retur` varchar(20) NOT NULL,
  `tgl_terima` date NOT NULL,
  `jumlah` decimal(15,2) NOT NULL DEFAULT 0.00,
  `metode` varchar(50) DEFAULT NULL COMMENT 'Transfer/Cash/Giro',
  `no_referensi` varchar(50) DEFAULT NULL COMMENT 'No. Giro/No. Transfer',
  `keterangan` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_retur` (`id_retur`),
  KEY `idx_no_retur` (`no_retur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```


---

## 3. Detail Controller Design

### 3.1 Class Definition

```php
class Retur_pembelian extends Admin_Controller
{
    protected $viewPermission   = 'Retur_pembelian.View';
    protected $addPermission    = 'Retur_pembelian.Add';
    protected $managePermission = 'Retur_pembelian.Manage';
    protected $deletePermission = 'Retur_pembelian.Delete';
}
```

### 3.2 Method Routing Table

| Method | Route | Request | Response | Keterangan |
|--------|-------|---------|----------|------------|
| index() | GET /retur_pembelian | - | HTML | Render halaman index + DataTable |
| data() | POST /retur_pembelian/data | DataTable params | JSON | Server-side DataTable |
| add() | GET /retur_pembelian/add | - | HTML | Form tambah retur |
| get_invoice_by_supplier() | POST | id_supplier | JSON | AJAX: list invoice |
| get_detail_invoice() | POST | no_invoice | JSON | AJAX: detail produk |
| save() | POST /retur_pembelian/save | FormData | JSON | Simpan draft |
| edit($id) | GET /retur_pembelian/edit/{id} | - | HTML | Form edit (Draft only) |
| update($id) | POST /retur_pembelian/update/{id} | FormData | JSON | Update draft |
| ajukan($id) | POST /retur_pembelian/ajukan/{id} | - | JSON | Submit + buat jurnal |
| view($id) | GET /retur_pembelian/view/{id} | - | HTML | Detail view |
| print_sj($id) | GET /retur_pembelian/print_sj/{id} | - | HTML | Print surat jalan |
| nota_retur() | GET /retur_pembelian/nota_retur | - | HTML | Index nota retur |
| data_nota_retur() | POST | DataTable params | JSON | DataTable nota retur |
| terima_nota($id) | POST | tgl_terima | JSON | Konfirmasi terima nota |
| settlement($id) | GET /retur_pembelian/settlement/{id} | - | HTML | Form terima uang |
| save_settlement($id) | POST | FormData | JSON | Simpan settlement |
| cancel($id) | POST /retur_pembelian/cancel/{id} | alasan | JSON | Cancel retur |


---

## 4. Detail Model Design

### 4.1 Retur_pembelian_model extends BF_Model

```php
class Retur_pembelian_model extends BF_Model
{
    protected $table_name = 'tr_retur_pembelian';
    protected $key        = 'id';
}
```

#### Method Specifications:

**generate_no_retur()**
- Logic: SELECT MAX(no_retur) WHERE no_retur LIKE 'RTR-{YYYY}-%'
- Format: RTR-YYYY-NNNNN (5 digit sequential per tahun)
- Return: string no_retur baru

**get_invoice_by_supplier($id_supplier)**
- Query: SELECT dari tabel receive invoice
- Filter: id_supplier = param AND status sudah diterima
- Filter: invoice belum pernah diretur sepenuhnya
- Return: array of invoice (no_invoice, tgl_invoice, total)

**get_detail_invoice($no_invoice)**
- Query: SELECT detail produk dari invoice
- Return: array of products (kode, nama, satuan, qty, harga)

**data_serverside($request)**
- Pattern: sama dengan Retur_produk_model data_side_retur()
- Join: tr_retur_pembelian + mtr_supplier
- Search: no_retur, no_invoice, nama_supplier
- Return: JSON DataTable format

**save_retur($header, $details, $pinaltis)**
- Transaction based
- Insert header ke tr_retur_pembelian (status=1/Draft)
- Insert batch detail ke tr_retur_pembelian_detail
- Insert batch pinalti ke tr_retur_pembelian_pinalti (jika ada)
- Hitung: nilai_retur, ppn, total_retur, sisa_retur
- Return: boolean + id

**update_retur($id, $header, $details, $pinaltis)**
- Guard: status == 1 (Draft)
- Delete existing detail + pinalti, re-insert
- Recalculate totals

**ajukan($id)**
- Guard: status == 1 (Draft)
- Update status = 2 (Process)
- Buat jurnal akuntansi
- Update inventory ledger (stock out)

**cancel($id)**
- Guard: status == 1 atau 2
- Jika status == 2: buat jurnal balik + kembalikan stok
- Update status = 0 (Cancel)

**save_settlement($id, $data)**
- Guard: sisa_retur > 0 dan jumlah <= sisa_retur
- Insert ke tr_retur_pembelian_settlement
- Update settlement dan sisa_retur di header
- Jika sisa_retur == 0: status = Selesai
- Buat jurnal penerimaan uang

### 4.2 Jurnal_retur_model extends CI_Model

**create_jurnal_retur($retur_data, $details)**
- Jurnal Retur Produk:
  - D: Hutang Dagang (2101) = total_retur
  - K: Inventori (1105) = nilai_retur
  - K: PPN Masukan (1107) = ppn
- Jurnal Pinalti (jika ada):
  - D: Hutang Dagang (2101) = nilai_pinalti
  - K: Biaya COPQ (5xxx) = nilai_pinalti
- Insert batch ke tr_jurnal
- Fields: no_jurnal, tgl_jurnal, tipe, coa, nm_coa, debit, kredit, keterangan, no_transaksi, jenis_transaksi, created_by, created_date

**create_jurnal_settlement($settlement_data)**
- D: Kas/Bank (1102/1103) = jumlah
- K: Hutang Dagang Retur (2101) = jumlah

**create_jurnal_balik($retur_data)**
- Reverse semua jurnal saat ajukan (swap debit/kredit)

**generate_no_jurnal()**
- Sequential number generation


---

## 5. View Design

### 5.1 index.php - Halaman List Retur

**Layout:**
- Box primary dengan DataTable
- Tombol Add New (di atas tabel, kanan)
- Kolom: No, No Retur, No Invoice, Supplier, Tgl Retur, Total, Settlement, Sisa, Status, Action

**Action Buttons Logic:**
```
if status == 1 (Draft):
    EDIT + VIEW + CANCEL
if status == 2 (Process):
    VIEW + CANCEL
    if kembalikan_barang == 'Ya': + PRINT_SJ
    if sisa_retur > 0: + TERIMA_UANG
if status == 3 (Selesai):
    VIEW only
if status == 0 (Cancel):
    VIEW only
```

**Status Badge:**
- Draft = badge bg-yellow
- Process = badge bg-blue
- Selesai = badge bg-green
- Cancel = badge bg-red

### 5.2 form.php - Form Tambah Retur

**Layout Structure:**
```
+---------------------------------------------------+
| HEADER                                            |
| No. Retur [auto]       | Supplier [select2]       |
| No. Invoice [select2]  | Tgl Pembelian [readonly] |
| Tanggal Retur [date]   | Status [readonly]        |
+---------------------------------------------------+
| SECTION 1: PRODUK (tabel dinamis dari invoice)    |
| # | Kode | Nama | Sat | QBeli | QRetur | Harga   |
|   |      |      |     |       | [input]| Total   |
| Nilai Retur: xxx  | PPN: xxx  | TOTAL: xxx       |
+---------------------------------------------------+
| SECTION 2: PINALTI/CLAIM (dynamic rows)           |
| # | Nilai [input] | Keterangan | [+Add] [-Remove]|
+---------------------------------------------------+
| SECTION 3: OPSI                                   |
| Kembalikan Barang? [radio Ya/Tidak]               |
| Nota Retur? [radio Ya/Tidak]                      |
+---------------------------------------------------+
| SECTION 4: ALASAN RETUR                           |
| Kategori: [dropdown]                              |
| Keterangan: [textarea]                            |
| Upload BA: [file input]                           |
+---------------------------------------------------+
| [SAVE DRAFT]     [AJUKAN]     [CANCEL/BACK]       |
+---------------------------------------------------+
```

**JavaScript Interactions:**
1. Pilih Supplier -> AJAX load invoice dropdown (Select2)
2. Pilih Invoice -> AJAX load detail produk ke tabel
3. Input Qty Retur -> auto calculate Total Nilai per row
4. Auto sum Nilai Retur, PPN (11%), Total Retur
5. Tambah/Hapus row Pinalti secara dinamis
6. Submit form via AJAX (FormData untuk file upload)

### 5.3 view.php - Detail View

Menampilkan semua data readonly:
- Header info (No Retur, Supplier, Invoice, Tanggal, Status badge)
- Tabel detail produk (readonly)
- Tabel pinalti (jika ada)
- Info alasan retur + file BA (downloadable link)
- History Settlement (tabel: tgl, jumlah, metode, oleh)
- Audit trail (created/updated by + date)

### 5.4 print_sj.php - Surat Jalan

Layout print-friendly (A4):
- Header: Logo + Nama Perusahaan + Alamat
- Info: No Retur, Tanggal, Tujuan (Nama + Alamat Supplier)
- Tabel: No, Kode Barang, Nama Barang, Satuan, Qty Retur
- Footer: TTD Pengirim | TTD Penerima | TTD Diketahui
- CSS @media print { hide nav, sidebar }

### 5.5 settlement.php - Form Terima Uang

Layout:
- Panel info (readonly): No Retur, Supplier, Total Retur, Settlement, Sisa
- Form input: Tanggal, Jumlah (max=sisa), Metode [dropdown], No Referensi, Keterangan
- History settlement sebelumnya (tabel)
- Tombol: SIMPAN + BATAL

### 5.6 nota_retur.php - Tanda Terima Nota Retur

Layout:
- DataTable: No Retur, Supplier, Tgl Retur, Total, Status Nota
- Action: Konfirmasi Terima (jika belum diterima)
- Filter: hanya yang nota_retur = 'Ya'


---

## 6. Alur Proses (Sequence Diagrams)

### 6.1 Flow: Buat Retur Baru

```
User -> Klik Add New
     -> Pilih Supplier (AJAX get invoices)
     -> Pilih Invoice (AJAX get detail products)
     -> Isi Qty Retur per produk
     -> Isi Pinalti (opsional)
     -> Pilih opsi Kembalikan Barang / Nota Retur
     -> Isi Alasan + Upload BA
     -> Klik SAVE DRAFT
         -> Controller save()
         -> Model generate_no_retur()
         -> Model save_retur(header, detail, pinalti)
         -> Response JSON {status:1, pesan:'...'}
         -> Redirect ke index
```

### 6.2 Flow: Ajukan Retur

```
User -> Klik AJUKAN (dari form atau index)
     -> SweetAlert konfirmasi
     -> POST /retur_pembelian/ajukan/{id}
         -> Controller ajukan($id)
         -> Model ajukan($id):
             1. Cek status == Draft
             2. Update status = Process
             3. Jurnal_retur_model->create_jurnal_retur()
                - Insert jurnal hutang dagang (Debit)
                - Insert jurnal inventori (Kredit)
                - Insert jurnal PPN (Kredit)
                - Insert jurnal pinalti (jika ada)
             4. Update warehouse_stock (qty -= qty_retur)
         -> Response JSON success
         -> history() log
```

### 6.3 Flow: Terima Uang (Settlement)

```
User -> Klik TERIMA UANG di index
     -> GET /retur_pembelian/settlement/{id}
     -> Render form dengan info sisa retur
     -> User isi form (jumlah, metode, tgl)
     -> POST /retur_pembelian/save_settlement/{id}
         -> Model save_settlement():
             1. Validasi jumlah <= sisa_retur
             2. Insert tr_retur_pembelian_settlement
             3. Update header: settlement += jumlah
             4. Update header: sisa_retur -= jumlah
             5. Jika sisa_retur == 0: status = Selesai
             6. Jurnal_retur_model->create_jurnal_settlement()
         -> Response JSON success
```

### 6.4 Flow: Cancel Retur

```
User -> Klik CANCEL di index
     -> SweetAlert konfirmasi + input alasan
     -> POST /retur_pembelian/cancel/{id}
         -> Model cancel($id):
             1. Cek status == 1 atau 2
             2. Jika status == 2 (sudah ada jurnal):
                - Jurnal_retur_model->create_jurnal_balik()
                - Kembalikan stok (warehouse_stock += qty_retur)
             3. Update status = 0 (Cancel)
         -> Response JSON success
```


---

## 7. Integrasi dengan Sistem Existing

### 7.1 Tabel yang Di-referensi (Read Only)
- `mtr_supplier` - Master supplier (dropdown)
- `receive_invoice` / tabel incoming - Invoice yang sudah diterima
- `receive_invoice_detail` - Detail produk dari invoice
- `warehouse_stock` - Current stock (untuk validasi)
- `COA` (database accounting) - Chart of Account

### 7.2 Tabel yang Di-update (Write)
- `tr_retur_pembelian` - Header retur (CRUD)
- `tr_retur_pembelian_detail` - Detail produk retur
- `tr_retur_pembelian_pinalti` - Data pinalti
- `tr_retur_pembelian_settlement` - Data settlement
- `tr_jurnal` - Jurnal akuntansi
- `warehouse_stock` - Update stok (qty_stock Out/In)

### 7.3 Jurnal Akuntansi - COA Mapping

| Aksi | Akun (COA) | Debit | Kredit |
|------|------------|-------|--------|
| Ajukan - Retur Produk | Hutang Dagang (2101-xx) | total_retur | - |
| Ajukan - Retur Produk | Inventori (1105-xx) | - | nilai_retur |
| Ajukan - Retur Produk | PPN Masukan (1107-xx) | - | ppn |
| Ajukan - Pinalti | Hutang Dagang (2101-xx) | nilai_pinalti | - |
| Ajukan - Pinalti | Biaya COPQ (5xxx) | - | nilai_pinalti |
| Settlement | Kas/Bank (1102/1103) | jumlah | - |
| Settlement | Hutang Dagang Retur (2101-xx) | - | jumlah |
| Cancel (Balik) | Inventori (1105-xx) | nilai_retur | - |
| Cancel (Balik) | PPN Masukan (1107-xx) | ppn | - |
| Cancel (Balik) | Hutang Dagang (2101-xx) | - | total_retur |

### 7.4 Inventory Ledger Impact

**Saat Ajukan:**
- warehouse_stock: qty_stock -= qty_retur (per produk per gudang)
- Record history: tipe = OUT, keterangan = Retur Pembelian

**Saat Cancel (dari Process):**
- warehouse_stock: qty_stock += qty_retur (kembalikan)
- Record history: tipe = IN, keterangan = Cancel Retur

---

## 8. Keamanan dan Validasi

### 8.1 Server-Side Validation
- Permission check pada setiap method controller
- Status guard: edit hanya Draft, ajukan hanya Draft, cancel hanya Draft/Process
- Qty Retur: > 0 AND <= Qty Beli
- Settlement jumlah: > 0 AND <= sisa_retur
- File upload: tipe (pdf,jpg,png), max 2MB
- XSS filtering via CI input class
- CSRF protection (CI built-in)

### 8.2 Client-Side Validation
- Required fields check sebelum submit
- Qty Retur max validation (HTML5 + JS)
- Number formatting validation
- File size check sebelum upload
- Disable submit button saat processing (prevent double-submit)

### 8.3 Transaction Safety
- Semua operasi write: db->trans_start() / trans_complete()
- Rollback otomatis jika gagal
- Check trans_status() sebelum commit

---

## 9. UI/UX Conventions

### 9.1 CSS Framework
- AdminLTE 2.x (Bootstrap 3)
- Box container: box-primary
- Badge: bg-yellow(Draft), bg-blue(Process), bg-green(Selesai), bg-red(Cancel)
- Button: btn-primary(save), btn-success(ajukan), btn-danger(cancel/delete), btn-warning(view), btn-info(action)

### 9.2 JavaScript Libraries
- jQuery DataTable (server-side processing)
- SweetAlert (konfirmasi dan notifikasi)
- Select2 (dropdown supplier dan invoice)
- Number formatting manual (number_format pattern)

### 9.3 AJAX Pattern

```javascript
$.ajax({
    url: siteurl + 'retur_pembelian/method',
    type: 'POST',
    data: formData,
    dataType: 'json',
    processData: false,
    contentType: false,
    success: function(data) {
        if (data.status == 1) {
            swal("Berhasil", data.pesan, "success");
        } else {
            swal("Gagal", data.pesan, "warning");
        }
    },
    error: function() {
        swal("Error", "Terjadi kesalahan", "error");
    }
});
```

### 9.4 Response JSON Standard

```json
{"status": 1, "pesan": "Data berhasil disimpan."}
{"status": 0, "pesan": "Gagal menyimpan data."}
```

### 9.5 DataTable Server-Side Pattern

```javascript
$('#table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: siteurl + 'retur_pembelian/data',
        type: 'POST'
    },
    columns: [...],
    order: [[1, 'desc']]
});
```