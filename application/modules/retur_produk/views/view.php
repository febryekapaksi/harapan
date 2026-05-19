<div class="box box-primary">
    <div class="box-body">
        <div class="form-group row">
            <div class="col-md-12">

                <!-- Baris 1: No Retur & No SO -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Nomor Retur</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= strtoupper($retur['no_retur']) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Nomor SO</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= strtoupper($retur['no_so']) ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Baris 2: No Surat Jalan & Tanggal Retur -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>No. Surat Jalan</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= strtoupper($retur['no_surat_jalan']) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Tanggal Retur</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= !empty($retur['tgl_retur']) ? date('d/m/Y', strtotime($retur['tgl_retur'])) : '-' ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Baris 3: Customer & Status -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Customer</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="<?= strtoupper($retur['nm_customer']) ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Status</label>
                        </div>
                        <div class="col-md-8">
                            <?php
                            if ($retur['status'] == 1) {
                                echo "<span class='badge bg-yellow' style='font-size:13px;padding:6px 10px;'>Proses Retur</span>";
                            } elseif ($retur['status'] == 2) {
                                echo "<span class='badge bg-green' style='font-size:13px;padding:6px 10px;'>On Loading</span>";
                            } else {
                                echo "<span class='badge bg-blue' style='font-size:13px;padding:6px 10px;'>Belum Proses</span>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Baris 4: Alasan -->
                <div class="col-md-6">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Alasan</label>
                        </div>
                        <div class="col-md-8">
                            <textarea class="form-control" rows="3" readonly><?= $retur['alasan'] ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tabel Detail Produk -->
            <div class="col-md-12">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-blue">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>ID Produk</th>
                                    <th>Nama Produk</th>
                                    <th class="text-center">Qty Retur</th>
                                    <th class="text-right">Harga Satuan</th>
                                    <th class="text-right">Total Harga</th>
                                    <th>Alasan Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 0;
                                $grand_total = 0;
                                foreach ($detail as $dt) {
                                    $no++;
                                    $grand_total += $dt['total'];
                                ?>
                                    <tr>
                                        <td class="text-center"><?= $no ?></td>
                                        <td><?= $dt['id_product'] ?></td>
                                        <td><?= $dt['nm_product'] ?></td>
                                        <td class="text-center"><?= $dt['qty_retur'] ?></td>
                                        <td class="text-right"><?= number_format($dt['harga'], 0, ',', '.') ?></td>
                                        <td class="text-right"><?= number_format($dt['total'], 0, ',', '.') ?></td>
                                        <td><?= $dt['alasan'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <th colspan="5" class="text-right">Grand Total</th>
                                    <th class="text-right"><?= number_format($grand_total, 0, ',', '.') ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 text-center" style="margin-top:10px;">
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>