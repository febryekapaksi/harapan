<form id="form-master-margin">
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
                    <span class="label label-warning">Kuning = input manual</span>
                    <span class="label label-default">Abu-abu = kalkulasi otomatis</span>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">
                        <i class="fa fa-save"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-condensed" style="min-width: 1400px;">
                    <thead>
                        <tr class="bg-blue">
                            <th class="text-center" style="vertical-align: middle; width: 180px;">Nama Sales</th>
                            <?php foreach ($bulan as $b): ?>
                                <th class="text-center" style="min-width: 75px;"><?= $b['bulan'] ?></th>
                            <?php endforeach; ?>
                            <th class="text-center" style="min-width: 80px;">Total</th>
                            <th class="text-center" style="min-width: 80px;">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // total & rata-rata per kolom bulan (untuk baris rekap di bawah)
                        $total_per_bulan = [];
                        foreach ($bulan as $b) {
                            $total_per_bulan[$b['bulan_no']] = 0;
                        }
                        $jumlah_sales = count($sales);
                        ?>
                        <?php foreach ($sales as $s): ?>
                            <tr>
                                <td style="vertical-align: middle; font-weight: bold;"><?= strtoupper($s['nm_karyawan']) ?></td>
                                <?php
                                $total_row = 0;
                                foreach ($bulan as $b):
                                    $val = $rekap[$s['id']][$b['bulan_no']] ?? 0;
                                    $total_row += (float) $val;
                                    $total_per_bulan[$b['bulan_no']] += (float) $val;
                                ?>
                                    <td class="bg-warning">
                                        <input type="number" step="0.01" class="form-control input-sm text-center input-target-margin"
                                            data-id-sales="<?= $s['id'] ?>"
                                            data-bulan="<?= $b['bulan_no'] ?>"
                                            name="target[<?= $s['id'] ?>][<?= $b['bulan_no'] ?>]"
                                            value="<?= $val ?>">
                                    </td>
                                <?php endforeach; ?>
                                <td class="bg-gray text-center total-row" data-id-sales="<?= $s['id'] ?>"><?= number_format($total_row, 1) ?>%</td>
                                <td class="bg-gray text-center rata-row" data-id-sales="<?= $s['id'] ?>"><?= number_format($total_row / 12, 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray">
                            <th class="text-right">TOTAL / RATA-RATA PER BULAN</th>
                            <?php foreach ($bulan as $b): ?>
                                <th class="text-center total-bulan" data-bulan="<?= $b['bulan_no'] ?>">
                                    <?= $jumlah_sales > 0 ? number_format($total_per_bulan[$b['bulan_no']] / $jumlah_sales, 1) : '0.0' ?>%
                                </th>
                            <?php endforeach; ?>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
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

    // Hitung ulang Total / Rata-rata / Total per bulan secara real-time saat input diubah
    $('.input-target-margin').on('input', function() {
        var idSales = $(this).data('id-sales');

        // total & rata-rata per baris (sales)
        var totalRow = 0;
        $('.input-target-margin[data-id-sales="' + idSales + '"]').each(function() {
            totalRow += parseFloat($(this).val()) || 0;
        });
        $('.total-row[data-id-sales="' + idSales + '"]').text(totalRow.toFixed(1) + '%');
        $('.rata-row[data-id-sales="' + idSales + '"]').text((totalRow / 12).toFixed(1) + '%');

        // total & rata-rata per kolom (bulan)
        var jumlahSales = <?= $jumlah_sales ?>;
        $('.total-bulan').each(function() {
            var bulan = $(this).data('bulan');
            var totalBulan = 0;
            $('.input-target-margin[data-bulan="' + bulan + '"]').each(function() {
                totalBulan += parseFloat($(this).val()) || 0;
            });
            var rataBulan = jumlahSales > 0 ? (totalBulan / jumlahSales) : 0;
            $(this).text(rataBulan.toFixed(1) + '%');
        });
    });

    // Ajax Save
    $('#form-master-margin').submit(function(e) {
        e.preventDefault();
        swal({
            title: "Simpan Perubahan?",
            text: "Semua target margin di tabel ini akan diperbarui.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Simpan!",
            closeOnConfirm: false
        }, function() {
            $.ajax({
                url: siteurl + active_controller + 'save',
                type: "POST",
                data: $('#form-master-margin').serialize(),
                dataType: 'json',
                success: function(result) {
                    if (result.status == 1) {
                        swal("Berhasil!", result.pesan, "success");
                    } else {
                        swal("Gagal!", result.pesan, "error");
                    }
                },
                error: function() {
                    swal("Gagal!", "Terjadi kesalahan pada server.", "error");
                }
            });
        });
    });
</script>
