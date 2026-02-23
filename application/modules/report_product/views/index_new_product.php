<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-2">
                <label>Tahun</label>
                <select id="tahun" class="form-control input-sm">
                    <?php for ($t = date('Y') - 3; $t <= date('Y') + 1; $t++): ?>
                        <option value="<?= $t ?>" <?= ($t == $tahun_pilih) ? 'selected' : '' ?>>
                            <?= $t ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3" style="padding-top:25px;">
                <button class="btn btn-primary btn-sm" id="btnFilter">Filter</button>
                <button class="btn btn-default btn-sm" onclick="window.location.href='<?= base_url('controller_anda') ?>'">Reset</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="min-width: 150px;">Actual</th>
                        <?php foreach ($months as $m): ?>
                            <th class="text-center" style="min-width: 250px;"><?= $m ?></th>
                        <?php endforeach; ?>
                        <th style="min-width: 100px;">T score</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>Qty</b></td>
                        <?php
                        $total_all = 0;
                        foreach ($months as $m):
                            $total_all += $data_qty[$m];
                        ?>
                            <td class="text-right"><?= ($data_qty[$m] > 0) ? number_format($data_qty[$m]) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-right" rowspan="<?= $max_rows + 1 ?>">
                            <?= number_format($total_all) ?>
                        </td>
                    </tr>

                    <?php for ($i = 0; $i < $max_rows; $i++): ?>
                        <tr>
                            <?php if ($i === 0): ?>
                                <td rowspan="<?= $max_rows ?>"><b>Nama Produk</b></td>
                            <?php endif; ?>

                            <?php foreach ($months as $m): ?>
                                <td><?= isset($data_produk[$m][$i]) ? $data_produk[$m][$i] : '' ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('btnFilter').addEventListener('click', function() {
        var thn = document.getElementById('tahun').value;
        window.location.href = "<?= base_url('report_product') ?>?tahun=" + thn;
    });
</script>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>