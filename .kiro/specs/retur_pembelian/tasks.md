# Tasks: Modul Retur Pembelian

## Referensi
#[[file:requirement.md]]
#[[file:design.md]]

---

## Task 1: Setup Database Schema

### Deskripsi
Buat semua tabel yang dibutuhkan untuk modul retur pembelian.

### Sub-tasks
- [x] 1.1 Buat file SQL `sql/create_tables_retur_pembelian.sql`
- [x] 1.2 Buat tabel `tr_retur_pembelian` (header)
- [x] 1.3 Buat tabel `tr_retur_pembelian_detail` (detail produk)
- [x] 1.4 Buat tabel `tr_retur_pembelian_pinalti` (pinalti/claim)
- [x] 1.5 Buat tabel `tr_retur_pembelian_settlement` (penerimaan uang)
- [x] 1.6 Tambahkan indexes untuk performance

### Acceptance Criteria
- Semua tabel berhasil di-create tanpa error
- Kolom, tipe data, dan constraint sesuai design.md
- Charset utf8mb4 untuk support karakter unicode

---

## Task 2: Buat Module Structure dan Base Files

### Deskripsi
Setup folder HMVC dan file dasar controller + model.

### Sub-tasks
- [x] 2.1 Buat folder `application/modules/retur_pembelian/controllers/`
- [x] 2.2 Buat folder `application/modules/retur_pembelian/models/`
- [x] 2.3 Buat folder `application/modules/retur_pembelian/views/`
- [x] 2.4 Buat file `Retur_pembelian.php` (controller) dengan constructor dan permission
- [x] 2.5 Buat file `Retur_pembelian_model.php` (model) dengan base config
- [x] 2.6 Buat file `Jurnal_retur_model.php` (model jurnal)

### Acceptance Criteria
- Folder structure sesuai HMVC pattern project
- Controller extends Admin_Controller dengan 4 permission properties
- Model extends BF_Model dengan table_name dan key

---

## Task 3: Implementasi Model - Data Retrieval

### Deskripsi
Implementasi method-method model untuk pengambilan data.

### Sub-tasks
- [ ] 3.1 Method `generate_no_retur()` - auto-generate nomor RTR-YYYY-NNNNN
- [ ] 3.2 Method `get_invoice_by_supplier($id_supplier)` - ambil list invoice per supplier
- [ ] 3.3 Method `get_detail_invoice($no_invoice)` - ambil detail produk dari invoice
- [ ] 3.4 Method `get_by_id($id)` - ambil data retur lengkap (header + detail + pinalti)
- [ ] 3.5 Method `get_settlement($id)` - ambil history settlement per retur
- [ ] 3.6 Method `data_serverside($request)` - server-side DataTable processing

### Acceptance Criteria
- generate_no_retur menghasilkan nomor sequential per tahun
- get_invoice_by_supplier hanya menampilkan invoice yang sudah di-receive
- data_serverside mendukung search, sort, dan pagination

---

## Task 4: Implementasi Model - CRUD Operations

### Deskripsi
Implementasi method-method model untuk create, update, dan delete data retur.

### Sub-tasks
- [ ] 4.1 Method `save_retur($header, $details, $pinaltis)` - simpan retur baru (Draft)
- [ ] 4.2 Method `update_retur($id, $header, $details, $pinaltis)` - update retur Draft
- [ ] 4.3 Method `ajukan($id)` - submit retur (Draft -> Process) + jurnal + stok
- [ ] 4.4 Method `cancel($id)` - cancel retur + jurnal balik jika Process
- [ ] 4.5 Method `save_settlement($id, $data)` - simpan penerimaan uang
- [ ] 4.6 Method `terima_nota($id)` - update status nota retur diterima

### Acceptance Criteria
- Semua method menggunakan db transaction (trans_start/trans_complete)
- Status guard di setiap method (cek status sebelum operasi)
- Rollback otomatis jika terjadi error
- Kalkulasi total otomatis (nilai_retur, ppn, total_retur, sisa_retur)

---

## Task 5: Implementasi Jurnal Akuntansi

### Deskripsi
Implementasi Jurnal_retur_model untuk semua proses jurnal.

### Sub-tasks
- [ ] 5.1 Method `generate_no_jurnal()` - generate nomor jurnal
- [ ] 5.2 Method `create_jurnal_retur($retur, $details)` - jurnal saat ajukan
  - D: Hutang Dagang = total_retur
  - K: Inventori = nilai_retur
  - K: PPN Masukan = ppn
- [ ] 5.3 Method `create_jurnal_pinalti($retur)` - jurnal pinalti/claim
  - D: Hutang Dagang = nilai_pinalti
  - K: Biaya COPQ = nilai_pinalti
- [ ] 5.4 Method `create_jurnal_settlement($settlement)` - jurnal terima uang
  - D: Kas/Bank = jumlah
  - K: Hutang Dagang Retur = jumlah
- [ ] 5.5 Method `create_jurnal_balik($retur)` - reverse journal saat cancel
- [ ] 5.6 Integrasi update warehouse_stock (stok Out saat ajukan, In saat cancel)

### Acceptance Criteria
- Jurnal balance (total debit == total kredit)
- Insert ke tabel tr_jurnal dengan format sesuai existing (no_jurnal, tgl_jurnal, tipe, coa, nm_coa, debit, kredit, keterangan, no_transaksi, jenis_transaksi)
- warehouse_stock terupdate dengan benar

---

## Task 6: Implementasi Controller - Halaman dan AJAX

### Deskripsi
Implementasi semua method controller untuk routing dan handling request.

### Sub-tasks
- [ ] 6.1 Method `index()` - render halaman index
- [ ] 6.2 Method `data()` - handle DataTable AJAX request
- [ ] 6.3 Method `add()` - render form tambah (load supplier dropdown)
- [ ] 6.4 Method `get_invoice_by_supplier()` - AJAX response list invoice
- [ ] 6.5 Method `get_detail_invoice()` - AJAX response detail produk
- [ ] 6.6 Method `save()` - handle POST save draft (termasuk file upload BA)
- [ ] 6.7 Method `edit($id)` - render form edit (guard: Draft only)
- [ ] 6.8 Method `update($id)` - handle POST update
- [ ] 6.9 Method `ajukan($id)` - handle POST ajukan
- [ ] 6.10 Method `view($id)` - render detail view
- [ ] 6.11 Method `print_sj($id)` - render print surat jalan
- [ ] 6.12 Method `nota_retur()` - render index nota retur
- [ ] 6.13 Method `data_nota_retur()` - handle DataTable nota retur
- [ ] 6.14 Method `terima_nota($id)` - handle POST terima nota
- [ ] 6.15 Method `settlement($id)` - render form terima uang
- [ ] 6.16 Method `save_settlement($id)` - handle POST save settlement
- [ ] 6.17 Method `cancel($id)` - handle POST cancel

### Acceptance Criteria
- Setiap method memiliki permission check
- Response JSON untuk AJAX calls: {status: 0/1, pesan: '...'}
- File upload BA: validasi tipe (pdf,jpg,png) dan ukuran (max 2MB)
- History log tercatat untuk setiap aksi penting

---

## Task 7: Buat View - Index (List Retur)

### Deskripsi
Halaman utama list retur pembelian dengan DataTable server-side.

### Sub-tasks
- [ ] 7.1 Layout box-primary dengan header dan tombol Add New
- [ ] 7.2 Tabel DataTable dengan kolom: No, No Retur, No Invoice, Supplier, Tgl Retur, Total, Settlement, Sisa, Status, Action
- [ ] 7.3 Action buttons dengan logic visibility per status
- [ ] 7.4 Status badge styling (bg-yellow, bg-blue, bg-green, bg-red)
- [ ] 7.5 JavaScript DataTable initialization (server-side)
- [ ] 7.6 JavaScript function cancelRetur() dengan SweetAlert konfirmasi
- [ ] 7.7 JavaScript function ajukanRetur() dengan SweetAlert konfirmasi
- [ ] 7.8 Number formatting untuk kolom currency (Total, Settlement, Sisa)

### Acceptance Criteria
- DataTable responsive dan searchable
- Action buttons muncul sesuai kondisi status
- Konfirmasi SweetAlert sebelum cancel/ajukan
- Format angka menggunakan separator ribuan

---

## Task 8: Buat View - Form Retur (Add/Edit)

### Deskripsi
Form untuk membuat dan mengedit retur pembelian.

### Sub-tasks
- [ ] 8.1 Header form: No Retur (readonly), Supplier (Select2), No Invoice (Select2), Tgl Pembelian (readonly), Tgl Retur (date input), Status (readonly)
- [ ] 8.2 Section Produk: tabel dinamis yang terisi dari invoice
- [ ] 8.3 Section Pinalti: tabel dengan add/remove row dinamis
- [ ] 8.4 Section Opsi: radio button Kembalikan Barang dan Nota Retur
- [ ] 8.5 Section Alasan: dropdown kategori, textarea keterangan, file upload BA
- [ ] 8.6 Summary: Nilai Retur, PPN (11%), Total Retur (auto-calculate)
- [ ] 8.7 Tombol: Save Draft, Ajukan, Cancel/Back
- [ ] 8.8 JS: AJAX load invoice saat supplier berubah
- [ ] 8.9 JS: AJAX load detail produk saat invoice dipilih
- [ ] 8.10 JS: Auto-calculate total per row (qty_retur x harga)
- [ ] 8.11 JS: Auto-sum nilai retur, ppn, total
- [ ] 8.12 JS: Dynamic add/remove row pinalti
- [ ] 8.13 JS: Form submit via AJAX (FormData) dengan validasi
- [ ] 8.14 JS: Validasi qty_retur <= qty_beli

### Acceptance Criteria
- Select2 berfungsi untuk dropdown supplier dan invoice
- Produk tabel otomatis terisi saat pilih invoice
- Kalkulasi real-time saat input qty_retur
- File upload BA berfungsi (preview filename)
- Validasi client-side sebelum submit
- Form edit: data ter-populate dari database

---

## Task 9: Buat View - Detail View

### Deskripsi
Halaman view detail retur (readonly).

### Sub-tasks
- [ ] 9.1 Panel header info: No Retur, Supplier, Invoice, Tanggal, Status badge
- [ ] 9.2 Tabel detail produk (readonly)
- [ ] 9.3 Tabel pinalti (jika ada)
- [ ] 9.4 Info alasan retur + link download file BA
- [ ] 9.5 Opsi: Kembalikan Barang dan Nota Retur
- [ ] 9.6 Summary: Nilai Retur, PPN, Total, Settlement, Sisa
- [ ] 9.7 History settlement (tabel: No, Tgl, Jumlah, Metode, Keterangan, Oleh)
- [ ] 9.8 Audit trail: Created by/date, Updated by/date
- [ ] 9.9 Tombol Back

### Acceptance Criteria
- Semua data ditampilkan dengan format yang rapi
- Currency terformat dengan separator ribuan
- File BA bisa didownload jika ada
- History settlement muncul jika ada data

---

## Task 10: Buat View - Print Surat Jalan

### Deskripsi
Layout print-friendly untuk surat jalan pengembalian barang ke supplier.

### Sub-tasks
- [ ] 10.1 Layout A4 print-friendly (CSS @media print)
- [ ] 10.2 Header: Logo perusahaan + Nama + Alamat
- [ ] 10.3 Info: No Retur, No SJ, Tanggal, Kepada (Supplier + Alamat)
- [ ] 10.4 Tabel barang: No, Kode, Nama Barang, Satuan, Qty Retur
- [ ] 10.5 Area tanda tangan: Pengirim, Penerima, Diketahui
- [ ] 10.6 CSS: hide sidebar/navbar saat print
- [ ] 10.7 Tombol Print (window.print())

### Acceptance Criteria
- Layout bersih saat di-print (tanpa navigation)
- Tabel barang terformat dengan border
- Area TTD memiliki garis dan label

---

## Task 11: Buat View - Settlement (Terima Uang)

### Deskripsi
Form untuk mencatat penerimaan uang dari supplier.

### Sub-tasks
- [ ] 11.1 Panel info retur (readonly): No Retur, Supplier, Total, Settlement sebelumnya, Sisa
- [ ] 11.2 Form input: Tanggal terima, Jumlah (max=sisa), Metode (dropdown), No Referensi, Keterangan
- [ ] 11.3 Tabel history settlement sebelumnya
- [ ] 11.4 JS: Validasi jumlah tidak melebihi sisa
- [ ] 11.5 JS: Submit via AJAX
- [ ] 11.6 Tombol: Simpan + Batal

### Acceptance Criteria
- Jumlah input tidak bisa melebihi sisa_retur
- History settlement sebelumnya ditampilkan
- Setelah simpan redirect ke index dengan notifikasi sukses

---

## Task 12: Buat View - Nota Retur (Tanda Terima)

### Deskripsi
Halaman sub-menu untuk mengelola tanda terima nota retur dari supplier.

### Sub-tasks
- [ ] 12.1 Layout DataTable: No Retur, Supplier, Tgl Retur, Total, Status Nota
- [ ] 12.2 Filter: hanya data yang nota_retur = 'Ya'
- [ ] 12.3 Status badge: Belum Diterima (bg-yellow), Sudah Diterima (bg-green)
- [ ] 12.4 Action button: Konfirmasi Terima (jika belum)
- [ ] 12.5 JS: AJAX konfirmasi terima nota dengan SweetAlert
- [ ] 12.6 Input tanggal terima saat konfirmasi

### Acceptance Criteria
- Hanya menampilkan retur yang memilih nota_retur = Ya
- Bisa konfirmasi terima nota (update status_nota_retur)
- DataTable reload setelah konfirmasi

---

## Task 13: Integrasi Menu dan Permission

### Deskripsi
Tambahkan menu navigasi dan permission untuk modul retur pembelian.

### Sub-tasks
- [ ] 13.1 Tambahkan menu "Retur Pembelian" di navigasi sidebar (group Pembelian)
  - Sub-menu: Buat Retur (index)
  - Sub-menu: Tanda Terima Nota Retur (nota_retur)
  - Sub-menu: Penerimaan Uang (settlement list)
- [ ] 13.2 Insert permission ke database:
  - Retur_pembelian.View
  - Retur_pembelian.Add
  - Retur_pembelian.Manage
  - Retur_pembelian.Delete
- [ ] 13.3 Assign permission ke role yang sesuai
- [ ] 13.4 Icon menu: fa fa-undo atau fa fa-exchange

### Acceptance Criteria
- Menu muncul di sidebar sesuai permission user
- Sub-menu navigasi berfungsi dengan benar
- Permission mengontrol akses ke setiap fitur

---

## Task 14: Testing dan Validasi End-to-End

### Deskripsi
Testing manual semua flow dan edge cases.

### Sub-tasks
- [ ] 14.1 Test: Create Draft (pilih supplier -> invoice -> isi form -> save)
- [ ] 14.2 Test: Edit Draft (load data -> ubah -> save)
- [ ] 14.3 Test: Ajukan (Draft -> Process, cek jurnal terbentuk, cek stok berkurang)
- [ ] 14.4 Test: Settlement parsial (terima uang < sisa, cek status tetap Process)
- [ ] 14.5 Test: Settlement penuh (terima uang = sisa, cek status jadi Selesai)
- [ ] 14.6 Test: Cancel dari Draft (tanpa jurnal balik)
- [ ] 14.7 Test: Cancel dari Process (cek jurnal balik + stok kembali)
- [ ] 14.8 Test: Print Surat Jalan (layout print benar)
- [ ] 14.9 Test: Nota Retur (konfirmasi terima)
- [ ] 14.10 Test: Validasi qty_retur > qty_beli (harus ditolak)
- [ ] 14.11 Test: Validasi settlement > sisa_retur (harus ditolak)
- [ ] 14.12 Test: Auto-generate nomor retur sequential
- [ ] 14.13 Test: Permission (user tanpa akses tidak bisa masuk)
- [ ] 14.14 Test: File upload BA (pdf, jpg - valid; exe - ditolak)

### Acceptance Criteria
- Semua flow berjalan sesuai requirement
- Tidak ada error 500 atau blank page
- Jurnal balance (debit == kredit) di setiap transaksi
- Stok inventory konsisten setelah semua operasi
- Data integrity terjaga (no orphan records)
