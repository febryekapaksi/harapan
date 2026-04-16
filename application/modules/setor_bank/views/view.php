<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label>Tanggal Setor</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= date('d M Y', strtotime($header->tgl_setor)) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label>Bank Tujuan</label>
                        </div>
                        <div class="col-md-8">
                            <?php
                            // Mencari nama bank berdasarkan norek yang tersimpan
                            $nama_bank = "";
                            foreach ($bank as $b) {
                                if ($b->no_perkiraan == $header->norek) $nama_bank = $b->nama;
                            }
                            ?>
                            <input type="text" class="form-control" value="<?= $nama_bank . " - " . $header->norek ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="col-md-12">
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tablePn">
                            <thead class="bg-blue">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Kode Penerimaan</th>
                                    <th>Nama Customer</th>
                                    <th>No Invoice</th>
                                    <th class="text-right">Total Invoice</th>
                                    <th class="text-right">Total Penerimaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($detail)): ?>
                                    <?php $n = 0;
                                    foreach ($detail as $d): $n++; ?>
                                        <tr>
                                            <td><?= $n ?></td>
                                            <td><?= $d->kd_pembayaran ?></td>
                                            <td><?= $d->name_customer ?></td>
                                            <td><?= $d->no_invoice ?></td>
                                            <td class="text-right"><?= number_format($d->total_invoice, 2) ?></td>
                                            <td class="text-right"><?= number_format($d->total_penerimaan, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Data tidak ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total Penerimaan (Total Piutang Sales)</th>
                                    <th class="text-right">
                                        <input type="text" class="form-control text-right" value="<?= number_format($header->total_penerimaan, 2) ?>" readonly>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="5" class="text-right">Nilai Setor</th>
                                    <th class="text-right">
                                        <input type="text" class="form-control text-right" value="<?= number_format($header->total_setoran, 2) ?>" readonly style="font-weight:bold; color: blue;">
                                    </th>
                                </tr>
                                <?php if ($header->sisa_piutang > 0): ?>
                                    <tr>
                                        <th colspan="5" class="text-right">Sisa Piutang (Pending)</th>
                                        <th class="text-right">
                                            <input type="text" class="form-control text-right" value="<?= number_format($header->sisa_piutang, 2) ?>" readonly style="color: red;">
                                        </th>
                                    </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <a class="btn btn-primary" href="<?= base_url('setor_bank') ?>">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>