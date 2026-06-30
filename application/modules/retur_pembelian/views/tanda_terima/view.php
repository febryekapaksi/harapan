<?php $h = $tanda_terima['header']; $detail = $tanda_terima['detail']; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">TANDA TERIMA NOTA RETUR</h3>
        <div class="box-tools pull-right">
            <?php if ($h['status'] == 1): ?>
            <a href="<?= site_url('retur_pembelian/edit_tanda_terima/' . $h['id']) ?>" class="btn btn-info btn-sm">
                <i class="fa fa-edit"></i> Edit
            </a>
            <?php endif; ?>
            <a href="<?= site_url('retur_pembelian/tanda_terima') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-reply"></i> Kembali
            </a>
        </div>
    </div>
    <div class="box-body">
        <!-- HEADER INFO -->
        <div class="row">
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr>
                        <th width="180">Supplier</th>
                        <td><?= $h['nama_supplier'] ?></td>
                    </tr>
                    <tr>
                        <th>No. SJ Retur</th>
                        <td><?= $h['no_sj_retur'] ?: '-' ?></td>
                    </tr>
                    <tr>
                        <th>No. Invoice</th>
                        <td><?= $h['no_invoice'] ?></td>
                    </tr>
                    <tr>
                        <th>Tanggal Retur</th>
                        <td><?= date('d F Y', strtotime($h['tgl_retur'])) ?></td>
                    </tr>
                    <tr>
                        <th>Metode Retur</th>
                        <td>
                            <?php if ($h['metode_retur'] == 'Terima Uang'): ?>
                                <span class="badge bg-green">Terima Uang</span>
                            <?php else: ?>
                                <span class="badge bg-blue">Potong Tagihan</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-condensed">
                    <tr>
                        <th width="180">No. Faktur Pajak Retur</th>
                        <td><?= $h['no_faktur_pajak_retur'] ?: '-' ?></td>
                    </tr>
                    <tr>
                        <th>No. Nota Retur Supplier</th>
                        <td><?= $h['no_nota_retur_supplier'] ?: '-' ?></td>
                    </tr>
                    <tr>
                        <th>No. Retur</th>
                        <td><strong><?= $h['no_retur'] ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>
        <!-- DETAIL BARANG -->
        <h4><i class="fa fa-list"></i> Detail Barang Retur</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light-blue">
                    <tr>
                        <th width="30">No</th>
                        <th>Keterangan</th>
                        <th width="80" class="text-center">Qty</th>
                        <th width="150" class="text-right">Harga Satuan</th>
                        <th width="150" class="text-right">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0; foreach ($detail as $d): $no++; ?>
                    <tr>
                        <td class="text-center"><?= $no ?></td>
                        <td><?= $d['keterangan'] ?></td>
                        <td class="text-center"><?= number_format($d['qty'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($d['harga_satuan'], 0, ',', '.') ?></td>
                        <td class="text-right"><?= number_format($d['total_nilai'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray">
                    <tr>
                        <th colspan="4" class="text-right">NILAI</th>
                        <th class="text-right"><?= number_format($h['nilai_barang'], 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right">PPn (11%)</th>
                        <th class="text-right"><?= number_format($h['ppn'], 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-right"><strong>Total</strong></th>
                        <th class="text-right"><strong><?= number_format($h['total'], 0, ',', '.') ?></strong></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <hr>
        <!-- JURNAL -->
        <h4><i class="fa fa-book"></i> Jurnal</h4>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-gray">
                    <tr>
                        <th width="120">Kode Akun</th>
                        <th>Nama Akun</th>
                        <th width="150" class="text-right">Debet</th>
                        <th width="150" class="text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2101-01-01</td>
                        <td>Hutang Dagang</td>
                        <td class="text-right"><?= number_format($h['total'], 0, ',', '.') ?></td>
                        <td class="text-right">-</td>
                    </tr>
                    <tr>
                        <td>1107-01-06</td>
                        <td>PPN Dibayar Dimuka</td>
                        <td class="text-right">-</td>
                        <td class="text-right"><?= number_format($h['ppn'], 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>1104-01-02</td>
                        <td>Persediaan Barang In Transit</td>
                        <td class="text-right">-</td>
                        <td class="text-right"><?= number_format($h['nilai_barang'], 0, ',', '.') ?></td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray">
                    <tr>
                        <th colspan="2" class="text-right"><strong>TOTAL</strong></th>
                        <th class="text-right"><strong><?= number_format($h['total'], 0, ',', '.') ?></strong></th>
                        <th class="text-right"><strong><?= number_format($h['total'], 0, ',', '.') ?></strong></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
