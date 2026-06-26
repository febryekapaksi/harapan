<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <form id="form-retur" enctype="multipart/form-data">
            <!-- HEADER -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Retur</label>
                        <input type="text" class="form-control" value="(Auto Generate)" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Supplier <span class="text-red">*</span></label>
                        <select name="id_supplier" id="id_supplier" class="form-control select2" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>" data-nama="<?= $s['nama'] ?>"><?= $s['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nama_supplier" id="nama_supplier">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>No. Incoming <span class="text-red">*</span></label>
                        <select name="no_invoice" id="no_invoice" class="form-control select2" required>
                            <option value="">-- Pilih Incoming --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>No. PO</label>
                        <select name="no_po" id="no_po" class="form-control select2">
                            <option value="">-- Pilih PO --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tgl Pembelian</label>
                        <input type="text" class="form-control" name="tgl_pembelian" id="tgl_pembelian" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Tanggal Retur <span class="text-red">*</span></label>
                        <input type="date" class="form-control" name="tgl_retur" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <hr>
            <!-- SECTION 1: PRODUK -->
            <h4><i class="fa fa-cube"></i> Produk Retur</h4>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-produk">
                    <thead class="bg-light-blue">
                        <tr>
                            <th width="30">No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th width="70">Satuan</th>
                            <th width="80">Qty Beli</th>
                            <th width="80">Qty Retur</th>
                            <th width="120">Harga Satuan</th>
                            <th width="120">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-produk">
                        <tr><td colspan="8" class="text-center text-muted">Pilih invoice terlebih dahulu</td></tr>
                    </tbody>
                    <tfoot class="bg-gray">
                        <tr>
                            <th colspan="7" class="text-right">Nilai Retur</th>
                            <th><span id="nilai_retur_display">0</span></th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-right">PPN (11%)</th>
                            <th><span id="ppn_display">0</span></th>
                        </tr>
                        <tr>
                            <th colspan="7" class="text-right"><strong>TOTAL RETUR</strong></th>
                            <th><strong><span id="total_retur_display">0</span></strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr>
            <!-- SECTION 2: PINALTI/CLAIM -->
            <h4><i class="fa fa-exclamation-triangle"></i> Pinalti / Claim (Opsional)</h4>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-pinalti">
                    <thead>
                        <tr>
                            <th width="30">No</th>
                            <th>Nilai</th>
                            <th>Keterangan</th>
                            <th width="50">
                                <button type="button" class="btn btn-xs btn-success" onclick="addPinalti()"><i class="fa fa-plus"></i></button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pinalti"></tbody>
                </table>
            </div>

            <hr>
            <!-- SECTION 3: OPSI -->
            <h4><i class="fa fa-cog"></i> Opsi</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kembalikan Barang?</label><br>
                        <label class="radio-inline"><input type="radio" name="kembalikan_barang" value="Ya"> Ya</label>
                        <label class="radio-inline"><input type="radio" name="kembalikan_barang" value="Tidak" checked> Tidak</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nota Retur?</label><br>
                        <label class="radio-inline"><input type="radio" name="nota_retur" value="Ya"> Ya</label>
                        <label class="radio-inline"><input type="radio" name="nota_retur" value="Tidak" checked> Tidak</label>
                    </div>
                </div>
            </div>

            <hr>
            <!-- SECTION 4: ALASAN RETUR -->
            <h4><i class="fa fa-comment"></i> Alasan Retur</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kategori Alasan</label>
                        <select name="kategori_alasan" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="Barang Rusak / Cacat Produksi">Barang Rusak / Cacat Produksi</option>
                            <option value="Salah Kirim">Salah Kirim</option>
                            <option value="Tidak Sesuai Spesifikasi">Tidak Sesuai Spesifikasi</option>
                            <option value="Kelebihan Kirim">Kelebihan Kirim</option>
                            <option value="Expired / Kadaluarsa">Expired / Kadaluarsa</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan_alasan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Upload Berita Acara (PDF/JPG/PNG, max 2MB)</label>
                        <input type="file" name="file_ba" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>

            <hr>
            <!-- BUTTONS -->
            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="saveDraft()"><i class="fa fa-save"></i> Save Draft</button>
                <button type="button" class="btn btn-success" onclick="saveAndAjukan()"><i class="fa fa-check"></i> Ajukan</button>
                <a href="<?= site_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-reply"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script>
var pinaltiCount = 0;

$(document).ready(function() {
    $('.select2').select2();

    // Event: supplier changed
    $('#id_supplier').on('change', function() {
        var id_supplier = $(this).val();
        var nama = $(this).find(':selected').data('nama') || '';
        $('#nama_supplier').val(nama);
        $('#no_invoice').html('<option value="">-- Pilih Invoice --</option>');
        resetProduk();

        if (!id_supplier) return;

        $.ajax({
            url: siteurl + 'retur_pembelian/get_invoice_by_supplier',
            type: 'POST',
            data: { id_supplier: id_supplier },
            dataType: 'json',
            success: function(data) {
                var opts = '<option value="">-- Pilih Incoming --</option>';
                $.each(data, function(i, inv) {
                    opts += '<option value="' + inv.id_data + '" data-tgl="' + inv.tgl_invoice + '">' + inv.id_incoming + '</option>';
                });
                $('#no_invoice').html(opts).trigger('change.select2');
            }
        });
    });

    // Event: invoice (incoming) changed -> load PO list
    $('#no_invoice').on('change', function() {
        var id_data = $(this).val();
        var tgl = $(this).find(':selected').data('tgl') || '';
        $('#tgl_pembelian').val(tgl);
        $('#no_po').html('<option value="">-- Pilih PO --</option>').trigger('change.select2');
        resetProduk();

        if (!id_data) return;

        $.ajax({
            url: siteurl + 'retur_pembelian/get_po_by_incoming',
            type: 'POST',
            data: { id_data: id_data },
            dataType: 'json',
            success: function(data) {
                var opts = '<option value="">-- Semua PO --</option>';
                $.each(data, function(i, po) {
                    opts += '<option value="' + po.no_po + '">' + po.no_po + '</option>';
                });
                $('#no_po').html(opts).trigger('change.select2');
                // Langsung load semua detail incoming ini
                loadDetail(id_data, '');
            }
        });
    });

    // Event: PO changed -> filter produk by PO
    $('#no_po').on('change', function() {
        var id_data = $('#no_invoice').val();
        var no_po = $(this).val();
        if (!id_data) return;
        loadDetail(id_data, no_po);
    });
});

function bindQtyRetur() {
    $('.qty-retur').off('input').on('input', function() {
        var qty = parseFloat($(this).val()) || 0;
        var max = parseFloat($(this).attr('max')) || 0;
        if (qty > max) { $(this).val(max); qty = max; }
        var harga = parseFloat($(this).data('harga')) || 0;
        var total = qty * harga;
        $(this).closest('tr').find('.total-row').text(formatNumber(total));
        calculateTotals();
    });
}

function loadDetail(id_data, no_po) {
    resetProduk();
    $.ajax({
        url: siteurl + 'retur_pembelian/get_detail_invoice',
        type: 'POST',
        data: { no_invoice: id_data, no_po: no_po },
        dataType: 'json',
        success: function(data) {
            if (!data || data.length == 0) {
                resetProduk();
                return;
            }
            var html = '';
            $.each(data, function(i, d) {
                var no = i + 1;
                html += '<tr>';
                html += '<td class="text-center">' + no + '</td>';
                html += '<td>' + d.kode_barang + '<input type="hidden" name="detail['+no+'][id_product]" value="'+d.id_product+'"><input type="hidden" name="detail['+no+'][kode_barang]" value="'+d.kode_barang+'"><input type="hidden" name="detail['+no+'][nama_barang]" value="'+d.nama_barang+'"><input type="hidden" name="detail['+no+'][satuan]" value="'+(d.satuan||'')+'"><input type="hidden" name="detail['+no+'][qty_beli]" value="'+d.qty_beli+'"><input type="hidden" name="detail['+no+'][harga_satuan]" value="'+d.harga_satuan+'"></td>';
                html += '<td>' + d.nama_barang + '</td>';
                html += '<td class="text-center">' + (d.satuan||'-') + '</td>';
                html += '<td class="text-center">' + formatNumber(d.qty_beli) + '</td>';
                html += '<td><input type="number" name="detail['+no+'][qty_retur]" class="form-control input-sm text-center qty-retur" value="0" min="0" max="'+d.qty_beli+'" data-harga="'+d.harga_satuan+'"></td>';
                html += '<td class="text-right">' + formatNumber(d.harga_satuan) + '</td>';
                html += '<td class="text-right total-row">0</td>';
                html += '</tr>';
            });
            $('#tbody-produk').html(html);
            bindQtyRetur();
        }
    });
}

function calculateTotals() {
    var nilai_retur = 0;
    $('.qty-retur').each(function() {
        var qty = parseFloat($(this).val()) || 0;
        var harga = parseFloat($(this).data('harga')) || 0;
        nilai_retur += (qty * harga);
    });

    // Tambah pinalti
    var pinalti_total = 0;
    $('input[name^="pinalti"]').filter('[name$="[nilai]"]').each(function() {
        pinalti_total += parseFloat($(this).val().replace(/,/g, '')) || 0;
    });

    var ppn = nilai_retur * 0.11;
    var total = nilai_retur + ppn + pinalti_total;

    $('#nilai_retur_display').text(formatNumber(nilai_retur));
    $('#ppn_display').text(formatNumber(ppn));
    $('#total_retur_display').text(formatNumber(total));
}

function resetProduk() {
    $('#tbody-produk').html('<tr><td colspan="8" class="text-center text-muted">Pilih invoice terlebih dahulu</td></tr>');
    $('#nilai_retur_display, #ppn_display, #total_retur_display').text('0');
}

function addPinalti() {
    pinaltiCount++;
    var html = '<tr id="pinalti-row-'+pinaltiCount+'">';
    html += '<td class="text-center">'+pinaltiCount+'</td>';
    html += '<td><input type="text" name="pinalti['+pinaltiCount+'][nilai]" class="form-control input-sm" onchange="calculateTotals()"></td>';
    html += '<td><input type="text" name="pinalti['+pinaltiCount+'][keterangan]" class="form-control input-sm"></td>';
    html += '<td><button type="button" class="btn btn-xs btn-danger" onclick="removePinalti('+pinaltiCount+')"><i class="fa fa-trash"></i></button></td>';
    html += '</tr>';
    $('#tbody-pinalti').append(html);
}

function removePinalti(idx) {
    $('#pinalti-row-' + idx).remove();
    calculateTotals();
}

function saveDraft() {
    submitForm(siteurl + 'retur_pembelian/save');
}

function saveAndAjukan() {
    swal({
        title: "Ajukan Retur?",
        text: "Data akan disimpan dan langsung diajukan. Jurnal akan terbentuk otomatis.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Ajukan!",
        cancelButtonText: "Batal",
        closeOnConfirm: true
    }, function(isConfirm) {
        if (isConfirm) {
            submitForm(siteurl + 'retur_pembelian/save', true);
        }
    });
}

function submitForm(url, ajukan) {
    // Validasi minimal
    if (!$('#id_supplier').val()) { swal("Error", "Pilih supplier terlebih dahulu", "warning"); return; }
    if (!$('#no_invoice').val()) { swal("Error", "Pilih invoice terlebih dahulu", "warning"); return; }

    var hasQty = false;
    $('.qty-retur').each(function() { if (parseFloat($(this).val()) > 0) hasQty = true; });
    if (!hasQty) { swal("Error", "Minimal 1 produk harus memiliki Qty Retur > 0", "warning"); return; }

    var formData = new FormData($('#form-retur')[0]);
    if (ajukan) formData.append('ajukan', '1');

    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.status == 1) {
                swal({ title: "Berhasil", text: res.pesan, type: "success", timer: 3000 });
                setTimeout(function() { window.location.href = siteurl + 'retur_pembelian'; }, 1500);
            } else {
                swal("Gagal", res.pesan, "warning");
            }
        },
        error: function() {
            swal("Error", "Terjadi kesalahan, coba lagi.", "error");
        }
    });
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
