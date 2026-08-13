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
                        <th class="text-center" style="vertical-align:middle; min-width: 180px;">Keterangan</th>
                        <?php foreach ($bulan as $b): ?>
                            <th class="text-center" style="min-width: 100px;"><?= substr($b['bulan'], 0, 3) ?></th>
                        <?php endforeach; ?>
                        <th class="text-center">T Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total_target = array_fill(1, 12, 0);
                    $grand_total_realisasi = array_fill(1, 12, 0);

                    foreach ($sales as $s):
                        $row_t_target = 0;
                        $row_t_realisasi = 0;
                    ?>
                        <tr>
                            <td rowspan="2" style="vertical-align:middle; font-weight:bold;"><?= ucwords($s['nm_karyawan']) ?></td>
                            <td>Rencana Penagihan</td>
                            <?php foreach ($bulan as $b):
                                $bln_no = (int)$b['bulan_no'];
                                if ($tahun_pilih == $tahun_sekarang && $bln_no > $bulan_sekarang):
                            ?>
                                <td class="text-center text-muted">-</td>
                            <?php else:
                                $val = $rekap_target[$s['id']][$bln_no] ?? 0;
                                $row_t_target += $val;
                                $grand_total_target[$bln_no] += $val;
                            ?>
                                <td class="text-right">
                                    <a href="<?= site_url('report_penagihan/export_detail?tahun=' . $tahun_pilih . '&bulan=' . $bln_no . '&id_sales=' . $s['id'] . '&tipe=target') ?>" title="Download detail Rencana Penagihan" style="color:inherit; text-decoration:underline; cursor:pointer;">
                                        <?= number_format($val) ?>
                                    </a>
                                </td>
                            <?php endif; endforeach; ?>
                            <td class="text-right"><b><?= number_format($row_t_target) ?></b></td>
                        </tr>
                        <tr>
                            <td>Realisasi Tagihan</td>
                            <?php foreach ($bulan as $b):
                                $bln_no = (int)$b['bulan_no'];
                                if ($tahun_pilih == $tahun_sekarang && $bln_no > $bulan_sekarang):
                            ?>
                                <td class="text-center text-muted">-</td>
                            <?php else:
                                $val = $rekap_realisasi[$s['id']][$bln_no] ?? 0;
                                $row_t_realisasi += $val;
                                $grand_total_realisasi[$bln_no] += $val;
                            ?>
                                <td class="text-right">
                                    <a href="<?= site_url('report_penagihan/export_detail?tahun=' . $tahun_pilih . '&bulan=' . $bln_no . '&id_sales=' . $s['id'] . '&tipe=realisasi') ?>" title="Download detail Realisasi Tagihan" style="color:inherit; text-decoration:underline; cursor:pointer;">
                                        <?= number_format($val) ?>
                                    </a>
                                </td>
                            <?php endif; endforeach; ?>
                            <td class="text-right"><b><?= number_format($row_t_realisasi) ?></b></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-info">
                        <td rowspan="2" style="vertical-align:middle; font-weight:bold;">Target Cabang</td>
                        <td>Rencana Penagihan</td>
                        <?php $total_cabang_t = 0;
                        foreach ($bulan as $b):
                            $bln_no = (int)$b['bulan_no'];
                            if ($tahun_pilih == $tahun_sekarang && $bln_no > $bulan_sekarang):
                        ?>
                            <td class="text-center text-muted">-</td>
                        <?php else:
                            $gt = $grand_total_target[$bln_no];
                            $total_cabang_t += $gt;
                        ?>
                            <td class="text-right"><b><?= number_format($gt) ?></b></td>
                        <?php endif; endforeach; ?>
                        <td class="text-right"><b><?= number_format($total_cabang_t) ?></b></td>
                    </tr>
                    <tr class="bg-info">
                        <td>Realisasi Tagihan</td>
                        <?php $total_cabang_r = 0;
                        foreach ($bulan as $b):
                            $bln_no = (int)$b['bulan_no'];
                            if ($tahun_pilih == $tahun_sekarang && $bln_no > $bulan_sekarang):
                        ?>
                            <td class="text-center text-muted">-</td>
                        <?php else:
                            $gr = $grand_total_realisasi[$bln_no];
                            $total_cabang_r += $gr;
                        ?>
                            <td class="text-right"><b><?= number_format($gr) ?></b></td>
                        <?php endif; endforeach; ?>
                        <td class="text-right"><b><?= number_format($total_cabang_r) ?></b></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
