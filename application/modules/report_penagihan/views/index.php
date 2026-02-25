<div class="box box-primary">
    <div class="box-body">
        <div class="row" style="margin-bottom: 15px;">
            <form method="GET" action="<?= site_url('report_penagihan') ?>">
                <div class="col-md-2">
                    <label>Pilih Tahun</label>
                    <select name="tahun" class="form-control input-sm" onchange="this.form.submit()">
                        <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                            <option value="<?= $i ?>" <?= ($tahun_pilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2" style="margin-top: 25px;">
                    <a target="_blank" href="<?= base_url($this->uri->segment(1) . '/export_excel?tahun=' . $tahun_pilih) ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o"></i> Export Excel
                    </a>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr class="bg-blue">
                        <th class="text-center" style="vertical-align:middle; min-width: 150px;">Nama Sales</th>
                        <th class="text-center" style="vertical-align:middle; min-width: 150px;">Keterangan</th>
                        <?php foreach ($bulan as $b): ?>
                            <th class="text-center" style="min-width: 100px;"><?= substr($b['bulan'], 0, 3) ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">T Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total_tagihan = array_fill(1, 12, 0);
                    $grand_total_penerimaan = array_fill(1, 12, 0);

                    foreach ($sales as $s):
                        $row_t_tagihan = 0;
                        $row_t_penerimaan = 0;
                    ?>
                        <tr>
                            <td rowspan="2" style="vertical-align:middle; font-weight:bold;"><?= ucwords($s['nm_karyawan']) ?></td>
                            <td>Total Tagihan</td>
                            <?php foreach ($bulan as $b):
                                $val = $rekap[$s['id']][$b['bulan_no']]['tagihan'] ?? 0;
                                $row_t_tagihan += $val;
                                $grand_total_tagihan[$b['bulan_no']] += $val;
                            ?>
                                <td class="text-right"><?= number_format($val) ?></td>
                            <?php endforeach; ?>
                            <td class="text-right"><b><?= number_format($row_t_tagihan) ?></b></td>
                        </tr>
                        <tr>
                            <td>Total Penerimaan</td>
                            <?php foreach ($bulan as $b):
                                $val = $rekap[$s['id']][$b['bulan_no']]['penerimaan'] ?? 0;
                                $row_t_penerimaan += $val;
                                $grand_total_penerimaan[$b['bulan_no']] += $val;
                            ?>
                                <td class="text-right"><?= number_format($val) ?></td>
                            <?php endforeach; ?>
                            <td class="text-right"><b><?= number_format($row_t_penerimaan) ?></b></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-info">
                        <td rowspan="2" style="vertical-align:middle; font-weight:bold;">Target Cabang</td>
                        <td>Total Tagihan</td>
                        <?php $total_cabang_t = 0;
                        foreach ($grand_total_tagihan as $gt): $total_cabang_t += $gt; ?>
                            <td class="text-right"><b><?= number_format($gt) ?></b></td>
                        <?php endforeach; ?>
                        <td class="text-right"><b><?= number_format($total_cabang_t) ?></b></td>
                    </tr>
                    <tr class="bg-info">
                        <td>Total Penerimaan</td>
                        <?php $total_cabang_p = 0;
                        foreach ($grand_total_penerimaan as $gp): $total_cabang_p += $gp; ?>
                            <td class="text-right"><b><?= number_format($gp) ?></b></td>
                        <?php endforeach; ?>
                        <td class="text-right"><b><?= number_format($total_cabang_p) ?></b></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>