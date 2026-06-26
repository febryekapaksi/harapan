<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<?php $h = $retur['header']; ?>

<div class="box box-primary">
    <div class="box-body">
        <form id="form-retur" enctype="multipart/form-data">
            <!-- HEADER -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Retur</label>
                        <input type="text" class="form-control" value="<?= $h['no_retur'] ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Supplier <span class="text-red">*</span></label>
                        <select name="id_supplier" id="id_supplier" class="form-control select2" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['kode_supplier'] ?>" data-nama="<?= $s['nama'] ?>" <?= ($s['kode_supplier'] == $h['id_supplier']) ? 'selected' : '' ?>><?= $s['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nama_supplier" id="nama_supplier" value="<?= $h['nama_supplier'] ?>">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Invoice <span class="text-red">*</span></label>
                        <input type="text" class="form-control" name="no_invoice" value="<?= $h['no_invoice'] ?>" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tgl Pembelian</label>
                        <input type="text" class="form-control" name="tgl_pembelian" value="<?= $h['tgl_pembelian'] ?>" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Retur <span class="text-red">*</span></label>
                        <input type="date" class="form-control" name="tgl_retur" value="<?= $h['tgl_retur'] ?>" required>
                    </div>
                </div>
            </div>

            <hr>
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
                        <?php $no = 0; foreach ($retur['detail'] as $d): $no++; ?>
                        <tr>
                            <td class="text-center"><?= $no ?></td>
                            <td><?= $d['kode_barang'] ?>
                                <input type="hidden" name="detail[<?= $no ?>][id_product]" value="<?= $d['id_product'] ?>">
                                <input type="hidden" name="detail[<?= $no ?>][kode_barang]" value="<?= $d['kode_barang'] ?>">
                                <input type="hidden" name="detail[<?= $no ?>][nama_barang]" value="<?= $d['nama_barang'] ?>">
                                <input type="hidden" name="detail[<?= $no ?>][satuan]" value="<?= $d['satuan'] ?>">
                                <input type="hidden" name="detail[<?= $no ?>][qty_beli]" value="<?= $d['qty_beli'] ?>">
                                <input type="hidden" name="detail[<?= $no ?>][harga_satuan]" value="<?= $d['harga_satuan'] ?>">
                            </td>
                            <td><?= $d['nama_barang'] ?></td>
                            <td class="text-center"><?= $d['satuan'] ?: '-' ?></td>
                            <td class="text-center"><?= number_format($d['qty_beli']) ?></td>
                            <td><input type="number" name="detail[<?= $no ?>][qty_retur]" class="form-control input-sm text-center qty-retur" value="<?= (int)$d['qty_retur'] ?>" min="0" max="<?= $d['qty_beli'] ?>" data-harga="<?= $d['harga_satuan'] ?>"></td>
                            <td class="text-right"><?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                            <td class="text-right total-row"><?= number_format($d['total_nilai'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray">
                        <tr><th colspan="7" class="text-right">Nilai Retur</th><th><span id="nilai_retur_display"><?= number_format($h['nilai_retur'], 0, ',', '.') ?></span></th></tr>
                        <tr><th colspan="7" class="text-right">PPN (11%)</th><th><span id="ppn_display"><?= number_format($h['ppn'], 0, ',', '.') ?></span></th></tr>
                        <tr><th colspan="7" class="text-right"><strong>TOTAL RETUR</strong></th><th><strong><span id="total_retur_display"><?= number_format($h['total_retur'], 0, ',', '.') ?></span></strong></th></tr>
                    </tfoot>
                </table>
            </div>

            <hr>
            <h4><i class="fa fa-exclamation-triangle"></i> Pinalti / Claim (Opsional)</h4>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-pinalti">
                    <thead>
                        <tr>
                            <th width="30">No</th>
                            <th>Nilai</th>
                            <th>Keterangan</th>
                            <th width="50"><button type="button" class="btn btn-xs btn-success" onclick="addPinalti()"><i class="fa fa-plus"></i></button></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pinalti">
                        <?php $pno = 0; foreach ($retur['pinalti'] as $p): $pno++; ?>
                        <tr id="pinalti-row-<?= $pno ?>">
                            <td class="text-center"><?= $pno ?></td>
                            <td><input type="text" name="pinalti[<?= $pno ?>][nilai]" class="form-control input-sm" value="<?= number_format($p['nilai'], 0, '', '') ?>" onchange="calculateTotals()"></td>
                            <td><input type="text" name="pinalti[<?= $pno ?>][keterangan]" class="form-control input-sm" value="<?= $p['keterangan'] ?>"></td>
                            <td><button type="button" class="btn btn-xs btn-danger" onclick="removePinalti(<?= $pno ?>)"><i class="fa fa-trash"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>
            <h4><i class="fa fa-cog"></i> Opsi</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kembalikan Barang?</label><br>
                        <label class="radio-inline"><input type="radio" name="kembalikan_barang" value="Ya" <?= $h['kembalikan_barang'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                        <label class="radio-inline"><input type="radio" name="kembalikan_barang" value="Tidak" <?= $h['kembalikan_barang'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nota Retur?</label><br>
                        <label class="radio-inline"><input type="radio" name="nota_retur" value="Ya" <?= $h['nota_retur'] == 'Ya' ? 'checked' : '' ?>> Ya</label>
                        <label class="radio-inline"><input type="radio" name="nota_retur" value="Tidak" <?= $h['nota_retur'] == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
                    </div>
                </div>
            </div>

            <hr>
            <h4><i class="fa fa-comment"></i> Alasan Retur</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Kategori Alasan</label>
                        <select name="kategori_alasan" class="form-control">
                            <option value="">-- Pilih --</option>
                            <?php
                            $kategori_options = ['Barang Rusak / Cacat Produksi','Salah Kirim','Tidak Sesuai Spesifikasi','Kelebihan Kirim','Expired / Kadaluarsa','Lainnya'];
                            foreach ($kategori_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= ($h['kategori_alasan'] == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan_alasan" class="form-control" rows="3"><?= $h['keterangan_alasan'] ?></textarea>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Upload Berita Acara</label>
                        <?php if ($h['file_ba']): ?>
                        <p><a href="<?= base_url($h['file_ba']) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-file"></i> File saat ini</a></p>
                        <?php endif; ?>
                        <input type="file" name="file_ba" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>

            <hr>
            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="saveUpdate()"><i class="fa fa-save"></i> Update Draft</button>
                <button type="button" class="btn btn-success" onclick="updateAndAjukan()"><i class="fa fa-check"></i> Update & Ajukan</button>
                <a href="<?= site_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-reply"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script>
var pinaltiCount = <?= count($retur['pinalti']) ?>;

$(document).ready(function() {
    $('.select2').select2();
    bindQtyRetur();
});

function bindQtyRetur() {
    $('.qty-retur').off('input').on('input', function() {
        var qty = parseFloat($(this).val()) || 0;
        var max = parseFloat($(this).attr('max')) || 0;
        if (qty > max) { $(this).val(max); qty = max; }
        var harga = parseFloat($(this).data('harga')) || 0;
        $(this).closest('tr').find('.total-row').text(formatNumber(qty * harga));
        calculateTotals();
    });
}

function calculateTotals() {
    var nilai_retur = 0;
    $('.qty-retur').each(function() {
        var qty = parseFloat($(this).val()) || 0;
        var harga = parseFloat($(this).data('harga')) || 0;
        nilai_retur += (qty * harga);
    });
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

function addPinalti() {
    pinaltiCount++;
    var html = '<tr id="pinalti-row-'+pinaltiCount+'"><td class="text-center">'+pinaltiCount+'</td><td><input type="text" name="pinalti['+pinaltiCount+'][nilai]" class="form-control input-sm" onchange="calculateTotals()"></td><td><input type="text" name="pinalti['+pinaltiCount+'][keterangan]" class="form-control input-sm"></td><td><button type="button" class="btn btn-xs btn-danger" onclick="removePinalti('+pinaltiCount+')"><i class="fa fa-trash"></i></button></td></tr>';
    $('#tbody-pinalti').append(html);
}

function removePinalti(idx) { $('#pinalti-row-'+idx).remove(); calculateTotals(); }

function saveUpdate() { submitForm(siteurl + 'retur_pembelian/update/<?= $h['id'] ?>', false); }

function updateAndAjukan() {
    swal({ title: "Update & Ajukan?", text: "Data akan diupdate dan langsung diajukan.", type: "warning", showCancelButton: true, confirmButtonText: "Ya!", closeOnConfirm: true },
    function(isConfirm) { if (isConfirm) submitForm(siteurl + 'retur_pembelian/update/<?= $h['id'] ?>', true); });
}

function submitForm(url, ajukan) {
    var hasQty = false;
    $('.qty-retur').each(function() { if (parseFloat($(this).val()) > 0) hasQty = true; });
    if (!hasQty) { swal("Error", "Minimal 1 produk Qty Retur > 0", "warning"); return; }

    var formData = new FormData($('#form-retur')[0]);
    if (ajukan) formData.append('ajukan', '1');

    $.ajax({
        url: url, type: 'POST', data: formData, dataType: 'json', processData: false, contentType: false,
        success: function(res) {
            if (res.status == 1) {
                if (ajukan) {
                    // Ajukan setelah update
                    $.post(siteurl + 'retur_pembelian/ajukan/<?= $h['id'] ?>', function(r) {
                        var r2 = JSON.parse(r);
                        swal("Berhasil", r2.pesan || res.pesan, "success");
                        setTimeout(function() { window.location.href = siteurl + 'retur_pembelian'; }, 1500);
                    });
                } else {
                    swal({ title: "Berhasil", text: res.pesan, type: "success", timer: 3000 });
                    setTimeout(function() { window.location.href = siteurl + 'retur_pembelian'; }, 1500);
                }
            } else { swal("Gagal", res.pesan, "warning"); }
        },
        error: function() { swal("Error", "Terjadi kesalahan", "error"); }
    });
}

function formatNumber(num) { return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."); }
</script>
