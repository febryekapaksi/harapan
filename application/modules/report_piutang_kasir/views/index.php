<div class="box box-primary">

    <div class="box-body">


        <!-- Filter -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-5">
                <label>Period (Bulan)</label>
                <div class="input-group">
                    <input type="month" id="inputBulan" class="form-control input-sm bg-select"
                        value="<?= htmlspecialchars($bulan ?? '') ?>">
                    <span class="input-group-btn">
                        <button class="btn btn-primary btn-sm" id="btnFilter">
                            <i class="fa fa-search"></i> Tampilkan
                        </button>
                        <button class="btn btn-default btn-sm" id="btnReset">
                            <i class="fa fa-times"></i> Reset
                        </button>
                        <?php if (!empty($bulan)): ?>
                            <a href="<?= base_url('report_piutang_kasir/export_excel?bulan=' . urlencode($bulan)) ?>"
                                class="btn btn-success btn-sm">
                                <i class="fa fa-file-excel-o"></i> Excel
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if (!empty($bulan)): ?>

                <!-- Summary -->
                <div class="col-md-5">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td style="width:160px;">Setoran Sales</td>
                                <td class="text-right" style="width:160px;">
                                    <strong><?= number_format($total_setoran_sales, 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td>Setoran Bank</td>
                                <td class="text-right">
                                    <strong><?= number_format($total_setoran_bank, 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Piutang Kasir</strong></td>
                                <td class="text-right bg-formula">
                                    <strong style="font-size:14px; color:<?= $piutang_kasir > 0 ? '#c0392b' : '#27ae60' ?>;">
                                        <?= number_format($piutang_kasir, 0, ',', '.') ?>
                                    </strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (!empty($rows)):

                    // Pre-process: hitung rowspan & tandai baris pertama tiap grup bank
                    $bank_rowspan = [];
                    $bank_first   = [];
                    foreach ($rows as $i => $r) {
                        $bid = $r['id_setor_bank'] ?? null;
                        if (!empty($bid)) {
                            if (!isset($bank_rowspan[$bid])) {
                                $bank_rowspan[$bid] = 0;
                                $bank_first[$bid]   = $i;
                            }
                            $bank_rowspan[$bid]++;
                        }
                    }
                ?>

                    <!-- Tabel Gabungan -->
                    <div class="col-md-12">
                        <div class="table-responsive" style="max-height:700px; overflow-y:auto;">
                            <table class="table table-striped table-bordered" id="tbl-piutang">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="4">
                                            Setoran Sales ke Kasir
                                        </th>
                                        <th class="text-center" colspan="3">
                                            Setoran Kasir ke Bank
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Kode Trans Kasir</th>
                                        <th class="text-center">Setoran Sales ke Kasir</th>
                                        <th class="text-center">Sales</th>
                                        <th class="text-center">Kode Trans Bank</th>
                                        <th class="text-center">Tanggal Bank</th>
                                        <th class="text-center">Setor Kasir Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $i => $r):
                                        $bid      = $r['id_setor_bank'] ?? null;
                                        $is_first = !empty($bid) && isset($bank_first[$bid]) && $bank_first[$bid] === $i;
                                        $rowspan  = !empty($bid) ? $bank_rowspan[$bid] : 0;
                                        $no_bank  = empty($bid);
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($r['tgl_kasir'])) ?></td>
                                            <td><?= htmlspecialchars($r['id_kasir']) ?></td>
                                            <td class="text-right"><?= number_format($r['setoran_sales'], 0, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($r['sales']) ?></td>

                                            <?php if ($no_bank): ?>
                                                <td class="col-sep"></td>
                                                <td class="bg-db"></td>
                                                <td class="bg-db"></td>
                                            <?php elseif ($is_first): ?>
                                                <td rowspan="<?= $rowspan ?>">
                                                    <?= htmlspecialchars($r['id_setor_bank']) ?>
                                                </td>
                                                <td class="text-center" rowspan="<?= $rowspan ?>">
                                                    <?= date('d/m/Y', strtotime($r['tgl_bank'])) ?>
                                                </td>
                                                <td class="text-right" rowspan="<?= $rowspan ?>">
                                                    <?= number_format($r['total_bank'], 0, ',', '.') ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-primary">
                                    <tr>
                                        <td colspan="2" class="text-right"><b>Total Setoran Sales</b></td>
                                        <td class="text-right"><b><?= number_format($total_setoran_sales, 0, ',', '.') ?></b></td>
                                        <td></td>
                                        <td colspan="2" class="text-right"><b>Total Setoran Bank</b></td>
                                        <td class="text-right"><b><?= number_format($total_setoran_bank, 0, ',', '.') ?></b></td>
                                    </tr>

                                </tfoot>
                            </table>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-circle"></i>
                            Tidak ada data setoran kasir untuk periode ini.
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
            <!-- jika $bulan kosong: notifikasi ditangani swal di JS -->
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {


        $('#btnFilter').on('click', function() {
            var bulan = $('#inputBulan').val();
            if (!bulan) {
                swal({
                    title: 'Peringatan',
                    text: 'Silakan pilih bulan terlebih dahulu.',
                    type: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f39c12'
                });
                return;
            }
            window.location.href = siteurl + 'report_piutang_kasir?bulan=' + encodeURIComponent(bulan);
        });

        $('#btnReset').on('click', function() {
            window.location.href = siteurl + 'report_piutang_kasir';
        });

        $('#inputBulan').on('keypress', function(e) {
            if (e.which === 13) $('#btnFilter').trigger('click');
        });

    });
</script>