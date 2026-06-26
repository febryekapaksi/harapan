<?php $h = $retur['header']; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Detail Retur Pembelian</h3>
        <div class="box-tools">
            <?php
            $status_label = '';
            switch ($h['status']) {
                case 0: $status_label = '<span class="badge bg-red">Cancel</span>'; break;
                case 1: $status_label = '<span class="badge bg-yellow">Draft</span>'; break;
                case 2: $status_label = '<span class="badge bg-blue">Process</span>'; break;
                case 3: $status_label = '<span class="badge bg-green">Selesai</span>'; break;
            }
            echo $status_label;
            ?>
        </div>
    </div>
    <div class="box-body">
        <!-- HEADER INFO -->
        <div class="row">
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr><th width="150">No. Retur</th><td><?= $h['no_retur'] ?></td></tr>
                    <tr><th>No. Invoice</th><td><?= $h['no_invoice'] ?></td></tr>
                    <tr><th>Supplier</th><td><?= $h['nama_supplier'] ?></td></tr>
                    <tr><th>Tgl Pembelian</th><td><?= $h['tgl_pembelian'] ? date('d/m/Y', strtotime($h['tgl_pembelian'])) : '-' ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr><th width="150">Tgl Retur</th><td><?= date('d/m/Y', strtotime($h['tgl_retur'])) ?></td></tr>
                    <tr><th>Kembalikan Barang</th><td><?= $h['kembalikan_barang'] ?></td></tr>
                    <tr><th>Nota Retur</th><td><?= $h['nota_retur'] ?> <?= ($h['nota_retur'] == 'Ya' && $h['status_nota_retur'] == 1) ? '<span class="badge bg-green">Sudah Diterima</span>' : '' ?></td></tr>
                    <tr><th>Kategori Alasan</th><td><?= $h['kategori_alasan'] ?: '-' ?></td></tr>
                </table>
            </div>
        </div>

        <?php if (!empty($h['keterangan_alasan'])): ?>
        <div class="row">
            <div class="col-md-12">
                <strong>Keterangan Alasan:</strong>
                <p class="well well-sm"><?= nl2br(htmlspecialchars($h['keterangan_alasan'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($h['file_ba'])): ?>
        <div class="row">
            <div class="col-md-12">
                <strong>File Berita Acara:</strong>
                <a href="<?= base_url($h['file_ba']) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Download</a>
            </div>
        </div>
        <br>
        <?php endif; ?>

        <hr>
        <!-- DETAIL PRODUK -->
        <h4><i class="fa fa-cube"></i> Detail Produk</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light-blue">
                    <tr>
                        <th width="30">No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th class="text-center">Qty Beli</th>
                        <th class="text-center">Qty Retur</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0; foreach ($retur['detail'] as $d): $no++; ?>
                    <tr>
                        <td class="text-center"><?= $no ?></td>
                        <td><?= $d['kode_barang'] ?></td>
                        <td><?= $d['nama_barang'] ?></td>
                        <td class="text-center"><?= $d['satuan'] ?: '-' ?></td>
                        <td class="text-center"><?= number_format($d['qty_beli']) ?></td>
                        <td class="text-center"><?= number_format($d['qty_retur']) ?></td>
                        <td class="text-right"><?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($d['total_nilai'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray">
                    <tr><th colspan="7" class="text-right">Nilai Retur</th><th class="text-right"><?= number_format($h['nilai_retur'], 0, ',', '.') ?></th></tr>
                    <tr><th colspan="7" class="text-right">PPN (11%)</th><th class="text-right"><?= number_format($h['ppn'], 0, ',', '.') ?></th></tr>
                    <?php if ($h['pinalti'] > 0): ?>
                    <tr><th colspan="7" class="text-right">Pinalti/Claim</th><th class="text-right"><?= number_format($h['pinalti'], 0, ',', '.') ?></th></tr>
                    <?php endif; ?>
                    <tr><th colspan="7" class="text-right"><strong>TOTAL RETUR</strong></th><th class="text-right"><strong><?= number_format($h['total_retur'], 0, ',', '.') ?></strong></th></tr>
                    <tr><th colspan="7" class="text-right">Settlement</th><th class="text-right"><?= number_format($h['settlement'], 0, ',', '.') ?></th></tr>
                    <tr><th colspan="7" class="text-right">Sisa Retur</th><th class="text-right"><strong><?= number_format($h['sisa_retur'], 0, ',', '.') ?></strong></th></tr>
                </tfoot>
            </table>
        </div>

        <!-- PINALTI -->
        <?php if (!empty($retur['pinalti'])): ?>
        <hr>
        <h4><i class="fa fa-exclamation-triangle"></i> Pinalti / Claim</h4>
        <table class="table table-bordered">
            <thead><tr><th>No</th><th>Nilai</th><th>Keterangan</th></tr></thead>
            <tbody>
                <?php $no = 0; foreach ($retur['pinalti'] as $p): $no++; ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td class="text-right"><?= number_format($p['nilai'], 0, ',', '.') ?></td>
                    <td><?= $p['keterangan'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- SETTLEMENT HISTORY -->
        <?php if (!empty($settlements)): ?>
        <hr>
        <h4><i class="fa fa-money"></i> History Settlement</h4>
        <table class="table table-bordered table-striped">
            <thead><tr><th>No</th><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>No. Referensi</th><th>Keterangan</th></tr></thead>
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

        <hr>
        <small class="text-muted">
            Dibuat oleh: <?= $h['created_by'] ?> pada <?= $h['created_date'] ?>
            <?php if ($h['updated_by']): ?> | Diupdate oleh: <?= $h['updated_by'] ?> pada <?= $h['updated_date'] ?><?php endif; ?>
        </small>

        <div class="text-center" style="margin-top:15px;">
            <a href="<?= site_url('retur_pembelian') ?>" class="btn btn-default"><i class="fa fa-reply"></i> Kembali</a>
        </div>
    </div>
</div>
