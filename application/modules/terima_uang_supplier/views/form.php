<?php $h = $retur['header']; ?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-money"></i> Terima Uang dari Supplier</h3>
    </div>
    <div class="box-body">
        <form id="form-terima-uang">
            <input type="hidden" name="id_retur" value="<?= $h['id'] ?>">

            <!-- HEADER INFO -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Supplier</label>
                        <input type="text" class="form-control" value="<?= $h['nama_supplier'] ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Faktur Pajak Retur</label>
                        <input type="text" name="no_faktur_pajak_retur" class="form-control" placeholder="Contoh: CN-001">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. SJ Retur</label>
                        <input type="text" name="no_sj_retur" class="form-control" placeholder="Contoh: SJ-RTR-001">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Nota Retur Supplier</label>
                        <input type="text" name="no_nota_retur_supplier" class="form-control" placeholder="Contoh: RTR-PPN001">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Invoice</label>
                        <input type="text" class="form-control" value="<?= $h['no_invoice'] ?>" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tanggal Retur</label>
                        <input type="text" class="form-control" value="<?= date('d F Y', strtotime($h['tgl_retur'])) ?>" readonly>
                    </div>
                </div>
            </div>

            <!-- INFO SISA RETUR -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-aqua">
                        <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Retur</span>
                            <span class="info-box-number"><?= number_format($h['total_retur'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-green">
                        <span class="info-box-icon"><i class="fa fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sudah Diterima</span>
                            <span class="info-box-number"><?= number_format($h['settlement'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-red">
                        <span class="info-box-icon"><i class="fa fa-exclamation"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sisa Retur</span>
                            <span class="info-box-number"><?= number_format($h['sisa_retur'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <!-- TANGGAL TERIMA -->
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal Terima Uang <span class="text-red">*</span></label>
                        <input type="date" name="tgl_terima" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <hr>

            <!-- DETAIL ITEMS -->
            <h4><i class="fa fa-list"></i> Detail Penerimaan</h4>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-items">
                    <thead class="bg-light-blue">
                        <tr>
                            <th width="30">No</th>
                            <th>Keterangan</th>
                            <th width="100">Qty</th>
                            <th width="150">Harga Satuan</th>
                            <th width="150">Total Nilai</th>
                            <th width="50">
                                <button type="button" class="btn btn-xs btn-success" onclick="addRow()">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tbody-items">
                        <?php if (!empty($detail)): ?>
                            <?php $no = 0; foreach ($detail as $d): $no++; ?>
                            <tr>
                                <td class="text-center row-num"><?= $no ?></td>
                                <td>
                                    <input type="text" name="items[<?= $no-1 ?>][keterangan]" class="form-control input-sm" value="<?= $d['nama_barang'] ?>">
                                </td>
                                <td>
                                    <input type="text" name="items[<?= $no-1 ?>][qty]" class="form-control input-sm text-right input-qty" value="<?= $d['qty_retur'] ?>" onkeyup="hitungBaris(this)">
                                </td>
                                <td>
                                    <input type="text" name="items[<?= $no-1 ?>][harga_satuan]" class="form-control input-sm text-right input-harga" value="<?= number_format($d['harga_satuan'], 0, '', '') ?>" onkeyup="hitungBaris(this)">
                                </td>
                                <td class="text-right total-baris"><?= number_format($d['qty_retur'] * $d['harga_satuan'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center row-num">1</td>
                                <td>
                                    <input type="text" name="items[0][keterangan]" class="form-control input-sm" placeholder="Nama barang / keterangan">
                                </td>
                                <td>
                                    <input type="text" name="items[0][qty]" class="form-control input-sm text-right input-qty" value="1" onkeyup="hitungBaris(this)">
                                </td>
                                <td>
                                    <input type="text" name="items[0][harga_satuan]" class="form-control input-sm text-right input-harga" value="0" onkeyup="hitungBaris(this)">
                                </td>
                                <td class="text-right total-baris">0</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-gray">
                        <tr>
                            <th colspan="4" class="text-right">NILAI</th>
                            <th class="text-right"><span id="total_nilai">0</span></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right">PPN (11%)</th>
                            <th class="text-right">
                                <input type="text" name="ppn" id="ppn" class="form-control input-sm text-right" value="0" onkeyup="hitungTotal()">
                            </th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right"><strong>TOTAL</strong></th>
                            <th class="text-right"><strong><span id="grand_total">0</span></strong></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr>

            <!-- JURNAL PREVIEW -->
            <h4><i class="fa fa-book"></i> Jurnal</h4>
            <table class="table table-bordered table-condensed">
                <thead class="bg-gray">
                    <tr>
                        <th>Keterangan</th>
                        <th>Akun</th>
                        <th width="150">Debet</th>
                        <th width="150">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sesuai bank terima</td>
                        <td>BANK (1102-01-01)</td>
                        <td class="text-right"><span id="jurnal_debet">0</span></td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>2101-01-01</td>
                        <td>Hutang Dagang</td>
                        <td class="text-right">-</td>
                        <td class="text-right"><span id="jurnal_kredit">0</span></td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <!-- BUTTON -->
            <div class="text-center">
                <button type="button" class="btn btn-primary btn-lg" onclick="saveReceive()">
                    <i class="fa fa-check"></i> Receive
                </button>
                <a href="<?= site_url('terima_uang_supplier') ?>" class="btn btn-default btn-lg">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </form>

        <!-- HISTORY PENERIMAAN -->
        <?php if (!empty($history)): ?>
        <hr>
        <h4><i class="fa fa-history"></i> History Penerimaan</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Faktur Pajak</th>
                    <th>No. Nota Retur</th>
                    <th>Nilai</th>
                    <th>PPN</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0; foreach ($history as $hist): $no++; ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td><?= date('d/m/Y', strtotime($hist['tgl_terima'])) ?></td>
                    <td><?= $hist['no_faktur_pajak_retur'] ?: '-' ?></td>
                    <td><?= $hist['no_nota_retur_supplier'] ?: '-' ?></td>
                    <td class="text-right"><?= number_format($hist['nilai'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($hist['ppn'], 0, ',', '.') ?></td>
                    <td class="text-right"><strong><?= number_format($hist['total'], 0, ',', '.') ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
var rowIndex = <?= !empty($detail) ? count($detail) : 1 ?>;

$(document).ready(function() {
    hitungSemua();
});

function addRow() {
    var html = '<tr>' +
        '<td class="text-center row-num">' + (rowIndex + 1) + '</td>' +
        '<td><input type="text" name="items[' + rowIndex + '][keterangan]" class="form-control input-sm" placeholder="Nama barang / keterangan"></td>' +
        '<td><input type="text" name="items[' + rowIndex + '][qty]" class="form-control input-sm text-right input-qty" value="1" onkeyup="hitungBaris(this)"></td>' +
        '<td><input type="text" name="items[' + rowIndex + '][harga_satuan]" class="form-control input-sm text-right input-harga" value="0" onkeyup="hitungBaris(this)"></td>' +
        '<td class="text-right total-baris">0</td>' +
        '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button></td>' +
        '</tr>';
    $('#tbody-items').append(html);
    rowIndex++;
    reNumber();
}

function removeRow(btn) {
    var tbody = $('#tbody-items');
    if (tbody.find('tr').length <= 1) {
        swal("Warning", "Minimal harus ada 1 baris item", "warning");
        return;
    }
    $(btn).closest('tr').remove();
    reNumber();
    hitungSemua();
}

function reNumber() {
    $('#tbody-items tr').each(function(i) {
        $(this).find('.row-num').text(i + 1);
    });
}

function hitungBaris(el) {
    var row = $(el).closest('tr');
    var qty = parseFloat(row.find('.input-qty').val().replace(/\./g, '').replace(',', '.')) || 0;
    var harga = parseFloat(row.find('.input-harga').val().replace(/\./g, '').replace(',', '.')) || 0;
    var total = qty * harga;
    row.find('.total-baris').text(formatNumber(total));
    hitungSemua();
}

function hitungSemua() {
    var totalNilai = 0;
    $('#tbody-items tr').each(function() {
        var qty = parseFloat($(this).find('.input-qty').val().replace(/\./g, '').replace(',', '.')) || 0;
        var harga = parseFloat($(this).find('.input-harga').val().replace(/\./g, '').replace(',', '.')) || 0;
        totalNilai += (qty * harga);
    });

    $('#total_nilai').text(formatNumber(totalNilai));

    // Auto calculate PPN 11%
    var ppn = Math.round(totalNilai * 0.11);
    $('#ppn').val(ppn);

    hitungTotal();
}

function hitungTotal() {
    var totalNilai = 0;
    $('#tbody-items tr').each(function() {
        var qty = parseFloat($(this).find('.input-qty').val().replace(/\./g, '').replace(',', '.')) || 0;
        var harga = parseFloat($(this).find('.input-harga').val().replace(/\./g, '').replace(',', '.')) || 0;
        totalNilai += (qty * harga);
    });

    var ppn = parseFloat($('#ppn').val().replace(/\./g, '').replace(',', '.')) || 0;
    var grandTotal = totalNilai + ppn;

    $('#grand_total').text(formatNumber(grandTotal));
    $('#jurnal_debet').text(formatNumber(grandTotal));
    $('#jurnal_kredit').text(formatNumber(grandTotal));
}

function saveReceive() {
    var grandTotal = parseFloat($('#grand_total').text().replace(/\./g, '').replace(',', '.')) || 0;
    var sisaRetur = <?= $h['sisa_retur'] ?>;

    if (grandTotal <= 0) {
        swal("Error", "Total penerimaan harus lebih dari 0", "warning");
        return;
    }

    if (grandTotal > sisaRetur) {
        swal("Error", "Total penerimaan melebihi sisa retur (" + formatNumber(sisaRetur) + ")", "warning");
        return;
    }

    swal({
        title: "Simpan Penerimaan?",
        text: "Konfirmasi penerimaan uang dari supplier sebesar " + formatNumber(grandTotal) + "\nJurnal akan terbentuk otomatis: D: Bank, K: Hutang Dagang",
        type: "info",
        showCancelButton: true,
        confirmButtonClass: "btn-primary",
        confirmButtonText: "Ya, Simpan",
        cancelButtonText: "Batal",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;

        $.ajax({
            url: siteurl + 'terima_uang_supplier/save',
            type: 'POST',
            data: $('#form-terima-uang').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal({
                        title: "Berhasil",
                        text: res.pesan,
                        type: "success",
                        timer: 3000
                    });
                    setTimeout(function() {
                        window.location.href = siteurl + 'terima_uang_supplier';
                    }, 1500);
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() {
                swal("Error", "Terjadi kesalahan server", "error");
            }
        });
    });
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
