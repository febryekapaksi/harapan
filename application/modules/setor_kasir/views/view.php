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
                            : <?= date('d F Y', strtotime($header->tgl_setor)) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="col-md-4">
                            <label>No Rekening Bank</label>
                        </div>
                        <div class="col-md-8">
                            : <?= $header->bank_name ?> (<?= $header->bank ?>)
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="tablePenerimaan" class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-blue">
                                        <th>No Pembayaran</th>
                                        <th>Customer</th>
                                        <th>No Invoice</th>
                                        <th class="text-right">Total Invoice</th>
                                        <th class="text-right">Total Penerimaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $grand_penerimaan = 0;
                                    foreach ($setor_kasir as $sk):
                                    ?>
                                        <tr style="background:#f3f3f3;font-weight:bold;">
                                            <td colspan="5">
                                                ID Setor: <?= $sk->id ?> |
                                                Sales: <?= $sk->sales ?> |
                                                Tgl Kasir: <?= date('d/m/Y', strtotime($sk->created_at)) ?>
                                            </td>
                                        </tr>

                                        <?php if (!empty($detail_kasir[$sk->id])): ?>
                                            <?php foreach ($detail_kasir[$sk->id] as $d):
                                                $grand_penerimaan += $d->total_penerimaan;
                                            ?>
                                                <tr>
                                                    <td><?= $d->kd_pembayaran ?></td>
                                                    <td><?= $d->name_customer ?></td>
                                                    <td><?= $d->no_invoice ?></td>
                                                    <td class="text-right"><?= number_format($d->total_invoice) ?></td>
                                                    <td class="text-right"><?= number_format($d->total_penerimaan) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total Piutang Kasir</th>
                                        <th class="text-right">
                                            <?= number_format($grand_penerimaan + ($header->sisa_piutang_sebelum ?? 0)) ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">Nilai yang Disetorkan</th>
                                        <th class="text-right" style="color: green; font-size: 1.2em;">
                                            <?= number_format($header->total_setoran) ?>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-right">Sisa Piutang (Menggantung)</th>
                                        <th class="text-right">
                                            <?= number_format($header->sisa_piutang) ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <a href="<?= base_url('setor_bank') ?>" class="btn btn-danger">Kembali</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>