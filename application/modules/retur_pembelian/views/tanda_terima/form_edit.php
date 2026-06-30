<?php $h = $tanda_terima['header']; $detail = $tanda_terima['detail']; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">EDIT TANDA TERIMA NOTA RETUR</h3>
    </div>
    <div class="box-body">
        <form id="form-tanda-terima">
            <input type="hidden" name="id" value="<?= $h['id'] ?>">

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
                        <input type="text" name="no_faktur_pajak_retur" class="form-control" value="<?= $h['no_faktur_pajak_retur'] ?>" placeholder="Contoh: CN-001">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. SJ Retur</label>
                        <input type="text" name="no_sj_retur" class="form-control" value="<?= $h['no_sj_retur'] ?>" placeholder="Contoh: SJ-RTR-001">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Nota Retur Supplier</label>
                        <input type="text" name="no_nota_retur_supplier" class="form-control" value="<?= $h['no_nota_retur_supplier'] ?>" placeholder="Contoh: RTR-PPN001">
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
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Metode Retur <span class="text-red">*</span></label>
                        <select name="metode_retur" class="form-control" required>
                            <option value="Potong Tagihan" <?= $h['metode_retur'] == 'Potong Tagihan' ? 'selected' : '' ?>>Potong Tagihan</option>
                            <option value="Terima Uang" <?= $h['metode_retur'] == 'Terima Uang' ? 'selected' : '' ?>>Terima Uang</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <!-- DETAIL BARANG -->
            <h4><i class="fa fa-list"></i> Detail Barang Retur</h4>
            <div class="table-responsive">
                <table class="table table-bordered" id="table-detail">
                    <thead class="bg-light-blue">
                        <tr>
                            <th width="30">No</th>
                            <th>Keterangan</th>
                            <th width="80">Qty</th>
                            <th width="150">Harga Satuan</th>
                            <th width="150">Total Nilai</th>
                            <th width="50">
                                <button type="button" class="btn btn-xs btn-success" onclick="addRow()"><i class="fa fa-plus"></i></button>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="tbody-detail">
                        <?php $no = 0; foreach ($detail as $d): $no++; ?>
                        <tr id="row-<?= $no ?>">
                            <td class="text-center row-no"><?= $no ?></td>
                            <td>
                                <input type="text" name="detail[<?= $no ?>][keterangan]" class="form-control input-sm" value="<?= $d['keterangan'] ?>">
                            </td>
                            <td>
                                <input type="number" name="detail[<?= $no ?>][qty]" class="form-control input-sm text-center qty-input" value="<?= (int)$d['qty'] ?>" min="1" onchange="calculateRow(this)">
                            </td>
                            <td>
                                <input type="text" name="detail[<?= $no ?>][harga_satuan]" class="form-control input-sm text-right harga-input" value="<?= number_format($d['harga_satuan'], 0, '', '') ?>" onchange="calculateRow(this)" onkeyup="formatInputNumber(this)">
                            </td>
                            <td class="text-right total-row"><?= number_format($d['total_nilai'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-danger" onclick="removeRow(<?= $no ?>)"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray">
                        <tr>
                            <th colspan="4" class="text-right">NILAI</th>
                            <th class="text-right"><span id="nilai_display">0</span></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right">PPn (11%)</th>
                            <th class="text-right"><span id="ppn_display">0</span></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th colspan="4" class="text-right"><strong>Total</strong></th>
                            <th class="text-right"><strong><span id="total_display">0</span></strong></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <hr>
            <!-- PREVIEW JURNAL -->
            <h4><i class="fa fa-book"></i> Jurnal</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-gray">
                        <tr>
                            <th width="120">Kode Akun</th>
                            <th>Nama Akun</th>
                            <th width="150">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2101-01-01</td>
                            <td>Hutang Dagang</td>
                            <td>Nilai barang + PPn</td>
                        </tr>
                        <tr>
                            <td>1107-01-06</td>
                            <td>PPN Dibayar Dimuka</td>
                            <td>PPn Retur</td>
                        </tr>
                        <tr>
                            <td>1104-01-02</td>
                            <td>Persediaan Barang In Transit</td>
                            <td>Nilai barang</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr>
            <!-- BUTTONS -->
            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="updateForm()"><i class="fa fa-save"></i> Update</button>
                <a href="<?= site_url('retur_pembelian/tanda_terima') ?>" class="btn btn-default"><i class="fa fa-reply"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
var rowCount = <?= $no ?>;

$(document).ready(function() {
    calculateAll();
});

function addRow() {
    rowCount++;
    var html = '<tr id="row-' + rowCount + '">';
    html += '<td class="text-center row-no">' + rowCount + '</td>';
    html += '<td><input type="text" name="detail[' + rowCount + '][keterangan]" class="form-control input-sm" placeholder="Nama barang / keterangan"></td>';
    html += '<td><input type="number" name="detail[' + rowCount + '][qty]" class="form-control input-sm text-center qty-input" value="1" min="1" onchange="calculateRow(this)"></td>';
    html += '<td><input type="text" name="detail[' + rowCount + '][harga_satuan]" class="form-control input-sm text-right harga-input" value="0" onchange="calculateRow(this)" onkeyup="formatInputNumber(this)"></td>';
    html += '<td class="text-right total-row">0</td>';
    html += '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="removeRow(' + rowCount + ')"><i class="fa fa-trash"></i></button></td>';
    html += '</tr>';
    $('#tbody-detail').append(html);
    reNumber();
}

function removeRow(idx) {
    $('#row-' + idx).remove();
    reNumber();
    calculateAll();
}

function reNumber() {
    var no = 0;
    $('#tbody-detail tr').each(function() {
        no++;
        $(this).find('.row-no').text(no);
    });
}

function calculateRow(el) {
    var $row = $(el).closest('tr');
    var qty = parseFloat($row.find('.qty-input').val()) || 0;
    var harga = parseFloat($row.find('.harga-input').val().replace(/\./g, '').replace(/,/g, '')) || 0;
    var total = qty * harga;
    $row.find('.total-row').text(formatNumber(total));
    calculateAll();
}

function calculateAll() {
    var nilai = 0;
    $('#tbody-detail tr').each(function() {
        var qty = parseFloat($(this).find('.qty-input').val()) || 0;
        var harga = parseFloat($(this).find('.harga-input').val().replace(/\./g, '').replace(/,/g, '')) || 0;
        nilai += (qty * harga);
    });

    var ppn = Math.round(nilai * 0.11);
    var total = nilai + ppn;

    $('#nilai_display').text(formatNumber(nilai));
    $('#ppn_display').text(formatNumber(ppn));
    $('#total_display').text(formatNumber(total));
}

function formatInputNumber(el) {
    var val = el.value.replace(/\D/g, '');
    el.value = val;
}

function updateForm() {
    var hasDetail = false;
    $('#tbody-detail tr').each(function() {
        var qty = parseFloat($(this).find('.qty-input').val()) || 0;
        var harga = parseFloat($(this).find('.harga-input').val().replace(/\./g, '').replace(/,/g, '')) || 0;
        if (qty > 0 && harga > 0) hasDetail = true;
    });

    if (!hasDetail) {
        swal("Error", "Minimal 1 detail barang harus diisi dengan Qty dan Harga > 0", "warning");
        return;
    }

    swal({
        title: "Update Tanda Terima?",
        text: "Data Tanda Terima Nota Retur akan diupdate.",
        type: "info",
        showCancelButton: true,
        confirmButtonText: "Ya, Update",
        cancelButtonText: "Batal",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: siteurl + 'retur_pembelian/update_tanda_terima/<?= $h['id'] ?>',
            type: 'POST',
            data: $('#form-tanda-terima').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal({ title: "Berhasil", text: res.pesan, type: "success", timer: 3000 });
                    setTimeout(function() { window.location.href = siteurl + 'retur_pembelian/tanda_terima'; }, 1500);
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() {
                swal("Error", "Terjadi kesalahan, coba lagi.", "error");
            }
        });
    });
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
