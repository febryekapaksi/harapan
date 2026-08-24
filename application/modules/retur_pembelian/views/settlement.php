<?php $h = $retur['header']; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Penerimaan Uang dari Supplier</h3>
    </div>
    <div class="box-body">
        <!-- INFO RETUR -->
        <div class="row">
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr><th width="130">No. Retur</th><td><?= $h['no_retur'] ?></td></tr>
                    <tr><th>Supplier</th><td><?= $h['nama_supplier'] ?></td></tr>
                    <tr><th>Total Retur</th><td class="text-right"><strong><?= number_format($h['total_retur'], 0, ',', '.') ?></strong></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr><th width="130">Settlement</th><td class="text-right"><?= number_format($h['settlement'], 0, ',', '.') ?></td></tr>
                    <tr><th>Sisa Retur</th><td class="text-right"><strong class="text-red"><?= number_format($h['sisa_retur'], 0, ',', '.') ?></strong></td></tr>
                </table>
            </div>
        </div>

        <hr>
        <!-- FORM -->
        <form id="form-settlement">
            <input type="hidden" name="id_retur" value="<?= $h['id'] ?>">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tanggal Terima <span class="text-red">*</span></label>
                        <input type="date" name="tgl_terima" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Jumlah <span class="text-red">*</span> (max: <?= number_format($h['sisa_retur'], 0, ',', '.') ?>)</label>
                        <input type="text" name="jumlah" id="jumlah" class="form-control" required placeholder="0">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Metode <span class="text-red">*</span></label>
                        <select name="metode" class="form-control" required>
                            <option value="">-- Pilih --</option>
                            <option value="Transfer">Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Giro">Giro</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>No. Referensi</label>
                        <input type="text" name="no_referensi" class="form-control" placeholder="No. Giro / Transfer">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control">
                    </div>
                </div>
            </div>
            <div class="text-center">
                <button type="button" class="btn btn-primary" onclick="saveSettlement()"><i class="fa fa-save"></i> Simpan</button>
                <a href="<?= site_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-reply"></i> Kembali</a>
            </div>
        </form>

        <!-- HISTORY -->
        <?php if (!empty($settlements)): ?>
        <hr>
        <h4>History Settlement</h4>
        <table class="table table-bordered table-striped">
            <thead><tr><th>No</th><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>No. Ref</th><th>Keterangan</th></tr></thead>
            <tbody>
                <?php $no = 0; foreach ($settlements as $s): $no++; ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td><?= date('d/m/Y', strtotime($s['tgl_terima'])) ?></td>
                    <td class="text-right"><?= number_format($s['jumlah'], 0, ',', '.') ?></td>
                    <td><?= $s['metode'] ?></td>
                    <td><?= $s['no_referensi'] ?: '-' ?></td>
                    <td><?= $s['keterangan'] ?: '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function saveSettlement() {
    var jumlah = parseFloat($('#jumlah').val().replace(/\./g, '').replace(',', '.')) || 0;
    var sisa = <?= $h['sisa_retur'] ?>;

    if (jumlah <= 0) { swal("Error", "Jumlah harus lebih dari 0", "warning"); return; }
    if (jumlah > sisa) { swal("Error", "Jumlah tidak boleh melebihi sisa retur", "warning"); return; }

    swal({
        title: "Simpan Settlement?",
        text: "Konfirmasi penerimaan uang sebesar " + formatNumber(jumlah),
        type: "info",
        showCancelButton: true,
        confirmButtonText: "Ya, Simpan",
        cancelButtonText: "Batal",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: siteurl + 'retur_pembelian/save_settlement/<?= $h['id'] ?>',
            type: 'POST',
            data: $('#form-settlement').serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal({ title: "Berhasil", text: res.pesan, type: "success", timer: 3000 });
                    setTimeout(function() { window.location.href = siteurl + 'retur_pembelian'; }, 1500);
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() { swal("Error", "Terjadi kesalahan", "error"); }
        });
    });
}

function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>
