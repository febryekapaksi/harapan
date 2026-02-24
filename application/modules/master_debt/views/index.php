<form id="form-master-debt">
    <div class="box box-primary">
        <div class="box-body">
            <div class="row" style="margin-bottom:15px;">
                <div class="col-md-2">
                    <label>Pilih Tahun</label>
                    <select name="tahun" id="pilih_tahun" class="form-control input-sm">
                        <?php for ($t = date('Y') - 1; $t <= date('Y') + 1; $t++): ?>
                            <option value="<?= $t ?>" <?= ($tahun == $t) ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-10 text-right" style="padding-top: 25px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-condensed" style="min-width: 1200px;">
                    <thead>
                        <tr class="bg-blue">
                            <th class="text-center" style="vertical-align: middle; width: 180px;">Nama Sales</th>
                            <th class="text-center" style="vertical-align: middle; width: 150px;">Target Debt</th>
                            <?php foreach ($bulan as $b): ?>
                                <th class="text-center" style="min-width: 75px;"><?= $b['bulan'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td rowspan="2" style="vertical-align: middle; font-weight: bold;"><?= strtoupper($s['nm_karyawan']) ?></td>
                                <td class="bg-gray">Target % Late Debt</td>
                                <?php foreach ($bulan as $b):
                                    $val_late = $rekap[$s['id']][$b['bulan_no']]['late'] ?? 0;
                                ?>
                                    <td>
                                        <input type="number" step="0.01" class="form-control input-sm text-center"
                                            name="target[<?= $s['id'] ?>][<?= $b['bulan_no'] ?>][late]"
                                            value="<?= $val_late ?>">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="bg-gray">Target % Bad Debt</td>
                                <?php foreach ($bulan as $b):
                                    $val_bad = $rekap[$s['id']][$b['bulan_no']]['bad'] ?? 0;
                                ?>
                                    <td>
                                        <input type="number" step="0.01" class="form-control input-sm text-center"
                                            name="target[<?= $s['id'] ?>][<?= $b['bulan_no'] ?>][bad]"
                                            value="<?= $val_bad ?>">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
    // Auto-refresh saat ganti tahun
    $('#pilih_tahun').change(function() {
        window.location.href = siteurl + active_controller + '?tahun=' + $(this).val();
    });

    // Ajax Save
    $('#form-master-debt').submit(function(e) {
        e.preventDefault();
        swal({
            title: "Simpan Perubahan?",
            text: "Semua target persentase di tabel ini akan diperbarui.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            closeOnConfirm: false
        }, function() {
            $.ajax({
                url: siteurl + active_controller + 'save',
                type: "POST",
                data: $('#form-master-debt').serialize(),
                dataType: 'json',
                success: function(result) {
                    if (result.status == 1) {
                        swal("Berhasil!", result.pesan, "success");
                    } else {
                        swal("Gagal!", result.pesan, "error");
                    }
                }
            });
        });
    });
</script>