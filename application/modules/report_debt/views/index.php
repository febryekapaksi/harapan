<div class="box box-primary">
    <div class="box-body">
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-2">
                <label>Pilih Tahun Report</label>
                <select id="filter_tahun" class="form-control input-sm">
                    <?php
                    $thn_skrg = date('Y');
                    for ($i = $thn_skrg - 2; $i <= $thn_skrg + 1; $i++):
                    ?>
                        <option value="<?= $i ?>" <?= ($tahun_pilih == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2" style="margin-top: 25px;">
                <a target="_blank" href="<?= base_url($this->uri->segment(1) . '/export_excel?tahun=' . $tahun_pilih) ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr class="bg-primary">
                        <th class="text-center" style="vertical-align:middle; min-width: 180px;">Nama Sales</th>
                        <th class="text-center" style="vertical-align:middle; min-width: 100px;">Keterangan</th>
                        <?php foreach ($bulan as $b): ?>
                            <th class="text-center" style="min-width: 100px;"><?= $b['bulan'] ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">T Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $s): ?>
                        <tr>
                            <td rowspan="9" style="vertical-align:middle; font-weight:bold;"><?= ucwords($s['nm_karyawan']) ?></td>
                            <td>Total piutang per akhir bulan</td>
                            <?php $t_piutang = 0;
                            foreach ($bulan as $b):
                                $val = $realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0;
                                $t_piutang += $val;
                                $bln_no = $b['bulan_no'];
                            ?>
                                <td class="text-right">
                                    <a href="<?= site_url('report_debt/export_detail?tahun=' . $tahun_pilih . '&bulan=' . $bln_no . '&id_sales=' . $s['id'] . '&tipe=total') ?>" title="Download detail total piutang" style="color:inherit; text-decoration:underline; cursor:pointer;">
                                        <?= number_format($val) ?>
                                    </a>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-right"><b><?= number_format($t_piutang) ?></b></td>
                        </tr>
                        <tr>
                            <td class="bg-info">Target % late debt</td>
                            <?php foreach ($bulan as $b):
                                $p_late = $target[$s['id']][$b['bulan_no']]['late_p'] ?? 0;
                            ?>
                                <td class="text-center bg-info"><?= $p_late ?>%</td>
                            <?php endforeach; ?>
                            <td class="text-center">-</td>
                        </tr>
                        <tr style="background-color: #FFFF99;">
                            <td><b>Target amount late debt (amount)</b></td>
                            <?php $t_late_amount = 0;
                            foreach ($bulan as $b):
                                $piutang_bln = (float)($realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                                $p_late = (float)($target[$s['id']][$b['bulan_no']]['late_p'] ?? 0);
                                $val = $piutang_bln * ($p_late / 100);
                                $t_late_amount += $val;
                            ?>
                                <td class="text-right" style="background-color: #FFFF99;"><b><?= number_format($val) ?></b></td>
                            <?php endforeach; ?>
                            <td class="text-right" style="background-color: #FFFF99;"><b><?= number_format($t_late_amount) ?></b></td>
                        </tr>
                        <tr style="background-color: #FFFF99;">
                            <td>Actual amount late debt</td>
                            <?php $t_1530 = 0;
                            foreach ($bulan as $b):
                                $val = $realisasi[$s['id']][$b['bulan_no']]['aging_15_30'] ?? 0;
                                $t_1530 += $val;
                                $bln_no = $b['bulan_no'];
                            ?>
                                <td class="text-right" style="background-color: #FFFF99;">
                                    <a href="<?= site_url('report_debt/export_detail?tahun=' . $tahun_pilih . '&bulan=' . $bln_no . '&id_sales=' . $s['id'] . '&tipe=late') ?>" title="Download detail late debt (15-30 hari)" style="color:inherit; text-decoration:underline; cursor:pointer;">
                                        <?= number_format($val) ?>
                                    </a>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-right" style="background-color: #FFFF99;"><?= number_format($t_1530) ?></td>
                        </tr>
                        <tr>
                            <td>Persentase actual amount</td>
                            <?php foreach ($bulan as $b):
                                $piutang_bln = (float)($realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                                $aging_late = (float)($realisasi[$s['id']][$b['bulan_no']]['aging_15_30'] ?? 0);
                                $pct = $piutang_bln > 0 ? ($aging_late / $piutang_bln) * 100 : 0;
                            ?>
                                <td class="text-center" style="color:#FF0000;"><?= number_format($pct, 2) ?>%</td>
                            <?php endforeach; ?>
                            <?php $pct_total_late = $t_piutang > 0 ? ($t_1530 / $t_piutang) * 100 : 0; ?>
                            <td class="text-center" style="color:#FF0000;"><?= number_format($pct_total_late, 2) ?>%</td>
                        </tr>
                        <tr>
                            <td class="bg-info">Target bad debt %</td>
                            <?php foreach ($bulan as $b):
                                $p_bad = $target[$s['id']][$b['bulan_no']]['bad_p'] ?? 0;
                            ?>
                                <td class="text-center bg-info"><?= $p_bad ?>%</td>
                            <?php endforeach; ?>
                            <td class="text-center">-</td>
                        </tr>
                        <tr style="background-color: #FFFF99;">
                            <td><b>Target amount bad debt (amount)</b></td>
                            <?php $t_bad_amount = 0;
                            foreach ($bulan as $b):
                                $piutang_bln = (float)($realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                                $p_bad = (float)($target[$s['id']][$b['bulan_no']]['bad_p'] ?? 0);
                                $val = $piutang_bln * ($p_bad / 100);
                                $t_bad_amount += $val;
                            ?>
                                <td class="text-right" style="background-color: #FFFF99;"><b><?= number_format($val) ?></b></td>
                            <?php endforeach; ?>
                            <td class="text-right" style="background-color: #FFFF99;"><b><?= number_format($t_bad_amount) ?></b></td>
                        </tr>
                        <tr style="background-color: #FFFF99;">
                            <td>Actual amount bad debt</td>
                            <?php $t_30up = 0;
                            foreach ($bulan as $b):
                                $val = $realisasi[$s['id']][$b['bulan_no']]['aging_30_up'] ?? 0;
                                $t_30up += $val;
                                $bln_no = $b['bulan_no'];
                            ?>
                                <td class="text-right" style="background-color: #FFFF99;">
                                    <a href="<?= site_url('report_debt/export_detail?tahun=' . $tahun_pilih . '&bulan=' . $bln_no . '&id_sales=' . $s['id'] . '&tipe=bad') ?>" title="Download detail bad debt (> 30 hari)" style="color:inherit; text-decoration:underline; cursor:pointer;">
                                        <?= number_format($val) ?>
                                    </a>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-right" style="background-color: #FFFF99;"><?= number_format($t_30up) ?></td>
                        </tr>
                        <tr>
                            <td>Persentase actual amount bad debt</td>
                            <?php foreach ($bulan as $b):
                                $piutang_bln = (float)($realisasi[$s['id']][$b['bulan_no']]['total_piutang'] ?? 0);
                                $aging_bad = (float)($realisasi[$s['id']][$b['bulan_no']]['aging_30_up'] ?? 0);
                                $pct = $piutang_bln > 0 ? ($aging_bad / $piutang_bln) * 100 : 0;
                            ?>
                                <td class="text-center" style="color:#FF0000;"><?= number_format($pct, 2) ?>%</td>
                            <?php endforeach; ?>
                            <?php $pct_total_bad = $t_piutang > 0 ? ($t_30up / $t_piutang) * 100 : 0; ?>
                            <td class="text-center" style="color:#FF0000;"><?= number_format($pct_total_bad, 2) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#filter_tahun').on('change', function() {
            var thn = $(this).val();
            window.location.href = siteurl + active_controller + '?tahun=' + thn;
        });
    });
</script>