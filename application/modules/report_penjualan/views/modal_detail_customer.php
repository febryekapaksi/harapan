<style>
    tr+tr[style*="background:#e7f1ff"] td {
        border-top: 2px solid #ccc;
    }
</style>
<div style="max-height: 400px; overflow-y: auto;">
    <table class="table table-bordered table-hover">
        <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
            <tr class="bg-info">
                <th>No Invoice / Pembayaran</th>
                <th>Tanggal Invoice / Tanggal Bayar</th>
                <th class="text-right">Nilai Invoice / Bayar Invoice</th>
                <th class="text-right">Sisa Invoice</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($grouped as $inv => $item): ?>

                <!-- HEADER INVOICE -->
                <tr style="background:#e7f1ff; font-weight:bold;">
                    <td><?= $inv ?></td>
                    <td><?= date('d/m/Y', strtotime($item['tgl_invoice'])) ?></td>
                    <td class="text-right"><?= number_format($item['grand_total']) ?></td>
                    <td style="background: <?= $item['last_sisa'] > 0 ? '#fff3f3' : '#e7f1ff' ?>;" class="text-right text-danger"><?= number_format($item['last_sisa']) ?></td>
                </tr>

                <!-- DETAIL PEMBAYARAN -->
                <?php if (!empty($item['details'])): ?>
                    <?php foreach ($item['details'] as $d): ?>
                        <tr>
                            <td style="padding-left:30px;">
                                ↳ <?= $d->kd_pembayaran ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($d->tgl_bayar)) ?></td>
                            <td class="text-right"><?= number_format($d->nilai_bayar) ?></td>
                            <td class="text-right"><?= number_format($d->sisa_piutang) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding-left:30px;">
                            ↳ Belum ada pembayaran
                        </td>
                    </tr>
                <?php endif; ?>

            <?php endforeach; ?>

        </tbody>

        <tfoot>
            <tr class="bg-warning">
                <th colspan="3" class="text-right">Total Piutang Customer</th>
                <th class="text-right"><?= number_format($grand_total_sisa) ?></th>
            </tr>
        </tfoot>
    </table>
</div>