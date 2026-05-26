<div class="box box-primary">

    <div class="box-body">
        <div class="box-tools pull-right">
            <a href="<?= base_url('report_piutang_sales' . (!empty($tanggal) ? '?tanggal=' . $tanggal : '')) ?>"
                class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
        <!-- Info Header -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-6">
                <table class="table table-condensed" style="margin-bottom:0; width:auto;">
                    <tr>
                        <td style="width:50px;"><strong>Sales</strong></td>
                        <td style="width:20px;">:</td>
                        <td><?= ucfirst(htmlspecialchars($sales['nm_lengkap'])) ?></td>
                        <td style="padding-left:30px;"><strong>Piutang per Tanggal</strong></td>
                        <td style="width:20px;">:</td>
                        <td>
                            <?= !empty($tanggal)
                                ? date('d/m/Y', strtotime($tanggal))
                                : '<em class="text-muted">Semua</em>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Tabel Detail sesuai konsep -->
        <div class="table-responsive" style="max-height:500px; overflow-y:auto;">
            <table class="table table-bordered table-striped table-condensed" id="tblDetail"
                style="margin-bottom:0;">
                <thead>
                    <tr class="bg-blue" style="color:#fff;">
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Kode Penerimaan Cash</th>
                        <th class="text-center">Invoice</th>
                        <th class="text-right">Nilai Penerimaan</th>
                        <th>Customer</th>
                        <th class="text-center">Tanggal<br><small>(Setor)</small></th>
                        <th class="text-center">Kode Setor</th>
                        <th class="text-right">Setor Kasir Penjualan</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td class="text-center">
                                    <?= !empty($row['tgl_pembayaran'])
                                        ? date('d/m/Y', strtotime($row['tgl_pembayaran']))
                                        : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?= htmlspecialchars($row['kd_pembayaran']) ?>
                                </td>
                                <td class="text-center">
                                    <?= htmlspecialchars($row['no_invoice']) ?>
                                </td>
                                <td class="text-right">
                                    <?= number_format($row['nilai_penerimaan'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['nm_customer']) ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($row['tgl_setor'])
                                        ? date('d/m/Y', strtotime($row['tgl_setor']))
                                        : '' ?>
                                </td>
                                <td class="text-center">
                                    <?= !empty($row['kode_setor'])
                                        ? htmlspecialchars($row['kode_setor'])
                                        : '' ?>
                                </td>
                                <td class="text-right">
                                    <?= $row['setor_kasir_penjualan'] > 0
                                        ? number_format($row['setor_kasir_penjualan'], 0, ',', '.')
                                        : '0' ?>
                                </td>
                                <td class="text-right">
                                    <strong><?= number_format($row['saldo'], 0, ',', '.') ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fa fa-info-circle"></i> Tidak ada data piutang
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-light-blue-active">
                        <td colspan="8" class="text-right">
                            <strong>Saldo Piutang</strong>
                        </td>
                        <td class="text-right">
                            <strong style="font-size:14px;">
                                <?= number_format($saldo_piutang ?? 0, 0, ',', '.') ?>
                            </strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>