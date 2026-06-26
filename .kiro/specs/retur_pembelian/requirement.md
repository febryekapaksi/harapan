# Requirement: Retur Pembelian (Purchase Return)

## Overview
Modul Retur Pembelian digunakan untuk mengelola proses pengembalian barang ke supplier atas pembelian yang sudah diterima (receive invoice). Modul ini mencakup pembuatan retur, pengembalian barang fisik (Surat Jalan), penerimaan nota retur dari supplier, dan penerimaan uang pengembalian dari supplier.

---

## 1. Requirements

### 1.1 Functional Requirements

#### FR-01: Index / List Retur Pembelian
- Menampilkan daftar semua retur pembelian dalam bentuk tabel.
- Kolom yang ditampilkan:
  - No (urut)
  - No. Retur (format: `RTR-YYYY-NNNNN`, contoh: `RTR-2026-00891`)
  - No. Invoice (dari receive invoice)
  - Nama Supplier
  - Tgl Retur
  - Total Nilai Retur
  - Settlement Retur (total yang sudah diselesaikan)
  - Sisa Retur (Total Nilai Retur - Settlement Retur)
  - Status (Draft, Process, Cancel, Selesai)
  - Action
- Tombol **Add New** untuk membuat retur baru → mengarah ke Form Retur.
- Action yang tersedia per row:
  - **EDIT** — hanya jika status = Draft
  - **Print SJ** — muncul jika dipilih "Kembalikan Barang = Ya"
  - **Terima Uang** — muncul jika Sisa Retur > 0
  - **View** — selalu tersedia
  - **Cancel** — hanya jika status = Draft atau Process

#### FR-02: Form Retur Pembelian (Add/Edit)
- **Header:**
  - No. Retur — auto-generate oleh sistem (format: `RTR-YYYY-NNNNN`)
  - Supplier — pilih dari dropdown (master supplier)
  - No. Invoice — pilih dari daftar invoice yang sudah di-receive dari supplier terpilih
  - Tgl Pembelian — otomatis terisi dari data invoice yang dipilih
  - Tanggal Retur — input manual (default: hari ini)
  - Status — otomatis (DRAFT saat save, DALAM PROSES saat ajukan)

- **Section 1: Produk**
  - Tabel produk dari invoice yang dipilih, dengan kolom:
    - No
    - Kode Barang
    - Nama Barang
    - Satuan
    - Qty Beli (dari invoice)
    - Qty Retur (input manual, max = Qty Beli)
    - Harga Satuan (dari invoice)
    - Total Nilai (Qty Retur × Harga Satuan)
  - Summary:
    - NILAI RETUR (sum Total Nilai)
    - PPn (11% dari Nilai Retur)
    - TOTAL RETUR (Nilai Retur + PPn)

- **Section 2: Pinalti/Claim**
  - Tabel opsional untuk menambahkan biaya pinalti/claim:
    - No
    - Nilai (input manual)
  - Diisi jika ada complain pinalti atau biaya jasa.

- **Section 3: Opsi Pengembalian**
  - **Kembalikan Barang?** — Ya/Tidak
    - Jika **Ya**: saat "Ajukan", data masuk ke inbox Pengembalian Barang untuk Print Surat Jalan.
  - **Nota Retur?** — Ya/Tidak
    - Jika **Ya**: data muncul di menu Tanda Terima Nota Retur.

- **Section 4: Alasan Retur**
  - Kategori Alasan — dropdown (contoh: Barang Rusak / Cacat Produksi, Salah Kirim, Tidak Sesuai Spesifikasi, dll.)
  - Keterangan — textarea (deskripsi lengkap alasan)
  - Upload BA (Berita Acara) — file upload (opsional)

- **Tombol Aksi:**
  - **SAVE DRAFT** — simpan sebagai draft, masih bisa diedit
  - **AJUKAN** — submit retur, terbentuk jurnal akuntansi, tidak bisa diedit lagi, status berubah ke "Process"
  - **CANCEL** — batalkan form

#### FR-03: Logika Saat AJUKAN (Submit)
- Status berubah dari Draft → Process
- Terbentuk jurnal akuntansi otomatis:
  - **Jika Retur Produk:**
    - Debit: Hutang Dagang (Retur) = Total Retur (termasuk PPn)
    - Kredit: Inventori = Nilai Retur (tanpa PPn)
    - Kredit: PPn Masukan = Nilai PPn
  - **Jika ada Pinalti/Claim:**
    - Debit: Hutang Dagang = Nilai Pinalti
    - Kredit: Biaya Cost of Poor Quality = Nilai Pinalti
- Ledger Inventori terupdate (Out):
  - Setiap produk yang diretur mengurangi stok dengan nilai costbook.
- Jika "Kembalikan Barang = Ya" → data masuk ke inbox Print SJ (Surat Jalan pengembalian barang ke supplier).
- Jika "Nota Retur = Ya" → data muncul di menu Tanda Terima Nota Retur.

#### FR-04: Print Surat Jalan (Pengembalian Barang)
- Tersedia jika "Kembalikan Barang = Ya" pada form retur.
- Mencetak surat jalan untuk pengembalian fisik barang ke supplier.
- Data yang tercetak:
  - No. Retur
  - Nama Supplier
  - Tanggal
  - Daftar barang (Kode, Nama, Qty Retur, Satuan)
  - Tanda tangan pengirim & penerima

#### FR-05: Tanda Terima Nota Retur
- Menu terpisah (sub-menu dari Retur Pembelian).
- Menampilkan daftar retur yang memilih "Nota Retur = Ya".
- User bisa mencatat penerimaan nota retur dari supplier.
- Mengonfirmasi bahwa nota retur sudah diterima dari supplier.

#### FR-06: Penerimaan Uang dari Supplier (Terima Uang)
- Tersedia jika Sisa Retur > 0.
- Form untuk mencatat penerimaan pembayaran/pengembalian uang dari supplier.
- Field:
  - Tanggal terima
  - Jumlah yang diterima
  - Metode pembayaran (Transfer, Cash, Giro, dll.)
  - Keterangan
- Setelah terima uang:
  - Settlement Retur bertambah
  - Sisa Retur berkurang
  - Jika Sisa Retur = 0, status otomatis berubah ke **Selesai**
- Jurnal:
  - Debit: Kas/Bank = Jumlah diterima
  - Kredit: Hutang Dagang (Retur) = Jumlah diterima

#### FR-07: Status Flow
| Status | Keterangan |
|--------|------------|
| **Draft** | Retur baru dibuat, masih bisa diedit |
| **Process** | Sudah diajukan, jurnal terbentuk, menunggu settlement |
| **Selesai** | Sisa Retur = 0, semua sudah diselesaikan |
| **Cancel** | Retur dibatalkan |

#### FR-08: Pembatalan (Cancel)
- Hanya bisa dilakukan pada status Draft atau Process.
- Jika status Process (sudah ada jurnal):
  - Buat jurnal balik (reverse journal)
  - Kembalikan stok inventori
- Status berubah ke Cancel.

---

### 1.2 Non-Functional Requirements

#### NFR-01: Nomor Retur Auto-Generate
- Format: `RTR-YYYY-NNNNN`
- Sequential per tahun, reset setiap tahun baru.

#### NFR-02: Validasi Data
- Qty Retur tidak boleh melebihi Qty Beli.
- Invoice yang dipilih harus berstatus sudah diterima (dari receive invoice).
- Supplier harus dipilih terlebih dahulu sebelum memilih invoice.
- Minimal 1 produk harus memiliki Qty Retur > 0.

#### NFR-03: Permission/Hak Akses
- `Retur_pembelian.View` — melihat daftar retur
- `Retur_pembelian.Add` — membuat retur baru
- `Retur_pembelian.Manage` — edit, ajukan, cancel
- `Retur_pembelian.Delete` — cancel/hapus retur

#### NFR-04: Audit Trail
- Setiap aksi (create, ajukan, cancel, terima uang) dicatat dalam history log.
- Menyimpan user yang melakukan aksi dan timestamp.

---

## 2. Design

### 2.1 Database Schema

#### Tabel: `tr_retur_pembelian` (Header)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT AUTO_INCREMENT | Primary Key |
| no_retur | VARCHAR(20) | No. Retur (unique) |
| no_invoice | VARCHAR(30) | No. Invoice (FK ke receive invoice) |
| id_supplier | INT | FK ke master supplier |
| nama_supplier | VARCHAR(150) | Nama supplier (denormalized) |
| tgl_pembelian | DATE | Tanggal pembelian (dari invoice) |
| tgl_retur | DATE | Tanggal retur |
| nilai_retur | DECIMAL(15,2) | Sub-total retur (sebelum PPn) |
| ppn | DECIMAL(15,2) | Nilai PPn |
| total_retur | DECIMAL(15,2) | Total retur (termasuk PPn) |
| pinalti | DECIMAL(15,2) | Total nilai pinalti/claim |
| settlement | DECIMAL(15,2) | Total yang sudah di-settle |
| sisa_retur | DECIMAL(15,2) | Total - Settlement |
| kembalikan_barang | ENUM('Ya','Tidak') | Opsi pengembalian barang |
| nota_retur | ENUM('Ya','Tidak') | Opsi nota retur |
| kategori_alasan | VARCHAR(100) | Kategori alasan retur |
| keterangan_alasan | TEXT | Keterangan detail alasan |
| file_ba | VARCHAR(255) | Path file Berita Acara |
| status | TINYINT | 0=Cancel, 1=Draft, 2=Process, 3=Selesai |
| created_by | INT | User ID pembuat |
| created_date | DATETIME | Tanggal dibuat |
| updated_by | INT | User ID update terakhir |
| updated_date | DATETIME | Tanggal update terakhir |

#### Tabel: `tr_retur_pembelian_detail` (Detail Produk)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT AUTO_INCREMENT | Primary Key |
| id_retur | INT | FK ke tr_retur_pembelian.id |
| no_retur | VARCHAR(20) | No. Retur |
| kode_barang | VARCHAR(30) | Kode barang |
| nama_barang | VARCHAR(150) | Nama barang |
| satuan | VARCHAR(20) | Satuan |
| qty_beli | INT | Qty dari invoice |
| qty_retur | INT | Qty yang diretur |
| harga_satuan | DECIMAL(15,2) | Harga satuan dari invoice |
| total_nilai | DECIMAL(15,2) | Qty Retur × Harga Satuan |

#### Tabel: `tr_retur_pembelian_pinalti` (Pinalti/Claim)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT AUTO_INCREMENT | Primary Key |
| id_retur | INT | FK ke tr_retur_pembelian.id |
| no_retur | VARCHAR(20) | No. Retur |
| nilai | DECIMAL(15,2) | Nilai pinalti |
| keterangan | VARCHAR(255) | Keterangan pinalti |

#### Tabel: `tr_retur_pembelian_settlement` (Penerimaan Uang)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT AUTO_INCREMENT | Primary Key |
| id_retur | INT | FK ke tr_retur_pembelian.id |
| no_retur | VARCHAR(20) | No. Retur |
| tgl_terima | DATE | Tanggal terima uang |
| jumlah | DECIMAL(15,2) | Jumlah yang diterima |
| metode | VARCHAR(50) | Metode pembayaran |
| keterangan | VARCHAR(255) | Keterangan |
| created_by | INT | User ID |
| created_date | DATETIME | Tanggal dibuat |

### 2.2 Module Structure (HMVC)
```
application/modules/retur_pembelian/
├── controllers/
│   └── Retur_pembelian.php
├── models/
│   └── Retur_pembelian_model.php
└── views/
    ├── index.php            (list/index retur)
    ├── form.php             (form add/edit retur)
    ├── view.php             (detail view retur)
    ├── print_sj.php         (print surat jalan)
    ├── nota_retur.php       (tanda terima nota retur)
    └── settlement.php       (form terima uang)
```

### 2.3 Controller Methods
| Method | HTTP | Keterangan |
|--------|------|------------|
| index() | GET | Halaman index list retur |
| data() | POST | AJAX DataTable server-side |
| add() | GET | Form tambah retur baru |
| get_invoice_by_supplier($id_supplier) | GET | AJAX: ambil list invoice per supplier |
| get_detail_invoice($no_invoice) | GET | AJAX: ambil detail produk dari invoice |
| save() | POST | Simpan draft retur |
| edit($id) | GET | Form edit retur (status Draft) |
| update($id) | POST | Update data retur |
| ajukan($id) | POST | Submit/ajukan retur → buat jurnal |
| view($id) | GET | View detail retur |
| print_sj($id) | GET | Print surat jalan pengembalian |
| nota_retur() | GET | Index tanda terima nota retur |
| terima_nota($id) | POST | Konfirmasi terima nota retur |
| settlement($id) | GET | Form terima uang |
| save_settlement($id) | POST | Simpan penerimaan uang |
| cancel($id) | POST | Cancel retur |

---

## 3. Tasks

### Task 1: Setup Database
- [ ] Buat migration / SQL untuk tabel `tr_retur_pembelian`
- [ ] Buat migration / SQL untuk tabel `tr_retur_pembelian_detail`
- [ ] Buat migration / SQL untuk tabel `tr_retur_pembelian_pinalti`
- [ ] Buat migration / SQL untuk tabel `tr_retur_pembelian_settlement`

### Task 2: Buat Module Structure
- [ ] Buat folder `application/modules/retur_pembelian/controllers/`
- [ ] Buat folder `application/modules/retur_pembelian/models/`
- [ ] Buat folder `application/modules/retur_pembelian/views/`

### Task 3: Buat Model (`Retur_pembelian_model.php`)
- [ ] Method `get_all()` — ambil semua data retur untuk DataTable
- [ ] Method `get_by_id($id)` — ambil data retur berdasarkan ID
- [ ] Method `get_invoice_by_supplier($id_supplier)` — ambil list invoice per supplier
- [ ] Method `get_detail_invoice($no_invoice)` — ambil detail produk dari invoice
- [ ] Method `generate_no_retur()` — auto-generate nomor retur
- [ ] Method `save($data, $detail, $pinalti)` — simpan retur (draft)
- [ ] Method `update($id, $data, $detail, $pinalti)` — update retur
- [ ] Method `ajukan($id)` — proses ajukan: update status + buat jurnal + update inventori
- [ ] Method `cancel($id)` — cancel retur + jurnal balik jika perlu
- [ ] Method `save_settlement($id, $data)` — simpan penerimaan uang
- [ ] Method `get_settlement($id)` — ambil history settlement per retur

### Task 4: Buat Controller (`Retur_pembelian.php`)
- [ ] Method `index()` — render halaman index
- [ ] Method `data()` — server-side DataTable AJAX
- [ ] Method `add()` — render form tambah
- [ ] Method `get_invoice_by_supplier()` — AJAX response
- [ ] Method `get_detail_invoice()` — AJAX response
- [ ] Method `save()` — handle POST save draft
- [ ] Method `edit($id)` — render form edit
- [ ] Method `update($id)` — handle POST update
- [ ] Method `ajukan($id)` — handle POST ajukan
- [ ] Method `view($id)` — render view detail
- [ ] Method `print_sj($id)` — render print surat jalan
- [ ] Method `nota_retur()` — render index nota retur
- [ ] Method `terima_nota($id)` — handle POST terima nota
- [ ] Method `settlement($id)` — render form settlement
- [ ] Method `save_settlement($id)` — handle POST save settlement
- [ ] Method `cancel($id)` — handle POST cancel

### Task 5: Buat View - Index (`index.php`)
- [ ] Layout tabel DataTable dengan kolom sesuai FR-01
- [ ] Tombol Add New
- [ ] Action buttons dengan logic visibility (sesuai status & kondisi)
- [ ] Filter pencarian

### Task 6: Buat View - Form Retur (`form.php`)
- [ ] Header form (No. Retur, Supplier dropdown, Invoice dropdown, Tanggal)
- [ ] Section produk (tabel dinamis dari invoice)
- [ ] Section pinalti/claim (tabel tambah/hapus row)
- [ ] Section opsi (Kembalikan Barang, Nota Retur)
- [ ] Section alasan retur (dropdown kategori, textarea, file upload)
- [ ] Summary kalkulasi (Nilai Retur, PPn, Total)
- [ ] Tombol Save Draft, Ajukan, Cancel
- [ ] JavaScript: AJAX load invoice saat pilih supplier
- [ ] JavaScript: AJAX load detail produk saat pilih invoice
- [ ] JavaScript: kalkulasi otomatis Qty Retur × Harga

### Task 7: Buat View - Detail View (`view.php`)
- [ ] Tampilkan semua informasi header retur
- [ ] Tabel detail produk
- [ ] Tabel pinalti (jika ada)
- [ ] History settlement (jika ada)
- [ ] Informasi status & audit trail

### Task 8: Buat View - Print Surat Jalan (`print_sj.php`)
- [ ] Layout print-friendly
- [ ] Header (No. Retur, Supplier, Tanggal)
- [ ] Tabel barang yang dikembalikan
- [ ] Area tanda tangan

### Task 9: Buat View - Settlement / Terima Uang (`settlement.php`)
- [ ] Form input (tanggal, jumlah, metode, keterangan)
- [ ] Info sisa retur yang tersisa
- [ ] History settlement sebelumnya
- [ ] Validasi jumlah ≤ sisa retur

### Task 10: Jurnal Akuntansi
- [ ] Implementasi pembuatan jurnal saat ajukan (retur produk)
- [ ] Implementasi pembuatan jurnal pinalti/claim
- [ ] Implementasi jurnal penerimaan uang (settlement)
- [ ] Implementasi jurnal balik saat cancel (jika status sudah Process)
- [ ] Integrasi dengan ledger inventori (update stok Out)

### Task 11: Integrasi & Menu
- [ ] Tambahkan menu "Retur Pembelian" di navigasi (sub-menu Pembelian)
  - Sub: Buat Retur
  - Sub: Tanda Terima Nota Retur
  - Sub: Penerimaan Uang dari Supplier
- [ ] Tambahkan permission di tabel permissions
- [ ] Tambahkan route jika diperlukan

### Task 12: Testing & Validasi
- [ ] Test flow: Create Draft → Edit → Ajukan → Terima Uang → Selesai
- [ ] Test flow: Create Draft → Cancel
- [ ] Test flow: Ajukan → Cancel (dengan jurnal balik)
- [ ] Test validasi Qty Retur tidak melebihi Qty Beli
- [ ] Test auto-generate nomor retur
- [ ] Test kalkulasi PPn dan total
- [ ] Test settlement parsial (bertahap)
- [ ] Test Print Surat Jalan
