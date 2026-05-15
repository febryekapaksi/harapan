<?php
$ENABLE_MANAGE = has_permission('Gl_interface.Manage');
$is_pending    = ($header['status'] === 'pending' || $header['status'] === 'error');
$memo          = !empty($header['memo']) ? json_decode($header['memo'], true) : [];

$total_debet  = 0;
$total_kredit = 0;
foreach ($details as $d) {
    $total_debet  += (float) $d['debet'];
    $total_kredit += (float) $d['kredit'];
}
$is_balance = (round($total_debet) === round($total_kredit));
?>

<div class="row">
    <div class="col-xs-12">

        <!-- BACK BUTTON -->
        <div class="box-tools" style="margin-bottom: 10px;">
            <a href="<?= base_url('gl_interface') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        <!-- HEADER INFO -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <i class="fa fa-file-text-o"></i>
                    Detail GL Interface &mdash;
                    <?php if (!empty($header['nomor'])): ?>
                        <?= htmlspecialchars($header['nomor']) ?>
                    <?php else: ?>
                        <span class="text-muted">Nomor belum di-generate</span>
                    <?php endif; ?>
                </h3>
                <div class="box-tools pull-right">
                    <?php if ($header['status'] === 'posted'): ?>
                        <span class="label label-success" style="font-size:13px;"><?= strtoupper($header['status']) ?></span>
                    <?php elseif ($header['status'] === 'error'): ?>
                        <span class="label label-danger" style="font-size:13px;"><?= strtoupper($header['status']) ?></span>
                    <?php else: ?>
                        <span class="label label-warning" style="font-size:13px;"><?= strtoupper($header['status']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <label>Nomor</label><br>
                        <?php if (!empty($header['nomor'])): ?>
                            <?= htmlspecialchars($header['nomor']) ?>
                        <?php else: ?>
                            <span class="text-warning"><i class="fa fa-clock-o"></i> Akan di-generate saat posting</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Tanggal</label><br>
                        <?= htmlspecialchars($header['tgl']) ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Jenis</label><br>
                        <?= htmlspecialchars($header['jenis']) ?>
                    </div>
                    <div class="col-sm-2">
                        <label>Tipe Transaksi</label><br>
                        <?= ucfirst(htmlspecialchars($header['jenis_transaksi'])) ?>
                    </div>
                    <div class="col-sm-3">
                        <label>User</label><br>
                        <?= htmlspecialchars($header['user_id']) ?>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-sm-6">
                        <label>Keterangan</label><br>
                        <?= htmlspecialchars($header['keterangan']) ?>
                    </div>
                    <?php if (!empty($memo['nama_supplier'])): ?>
                        <div class="col-sm-3">
                            <label>Supplier</label><br>
                            <?= htmlspecialchars($memo['nama_supplier']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($memo['no_reff'])): ?>
                        <div class="col-sm-3">
                            <label>No Reff</label><br>
                            <?= htmlspecialchars($memo['no_reff']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($header['error_msg'])): ?>
                    <div class="alert alert-danger" style="margin-top:10px;">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Error:</strong> <?= htmlspecialchars($header['error_msg']) ?>
                    </div>
                <?php endif; ?>

                <?php if ($header['status'] === 'posted' && !empty($header['posted_at'])): ?>
                    <div class="alert alert-success" style="margin-top:10px;">
                        <i class="fa fa-check-circle"></i>
                        Diposting pada: <?= htmlspecialchars($header['posted_at']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- /.box -->

        <!-- DETAIL JURNAL TABLE -->
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> Detail Jurnal</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr bgcolor="#9acfea">
                            <th width="40">
                                <center>#</center>
                            </th>
                            <th>
                                <center>COA</center>
                            </th>
                            <th>
                                <center>Nama COA</center>
                            </th>
                            <th>
                                <center>Keterangan</center>
                            </th>
                            <th>
                                <center>No Reff</center>
                            </th>
                            <th>
                                <center>No Request</center>
                            </th>
                            <th>
                                <center>Debet</center>
                            </th>
                            <th>
                                <center>Kredit</center>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($details as $d): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td style="white-space:nowrap;"><?= htmlspecialchars($d['no_perkiraan']) ?></td>
                                <td><?= htmlspecialchars($d['nama_coa'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['keterangan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['no_reff'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($d['no_request'] ?? '-') ?></td>
                                <td class="text-right"><?= number_format($d['debet'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($d['kredit'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr bgcolor="#DCDCDC">
                            <td colspan="6" class="text-right"><strong>TOTAL</strong></td>
                            <td class="text-right"><strong><?= number_format($total_debet, 0, ',', '.') ?></strong></td>
                            <td class="text-right"><strong><?= number_format($total_kredit, 0, ',', '.') ?></strong></td>
                        </tr>
                        <?php if (!$is_balance): ?>
                            <tr class="text-danger">
                                <td colspan="6" class="text-right"><strong>SELISIH</strong></td>
                                <td colspan="2" class="text-right"><strong><?= number_format(abs($total_debet - $total_kredit), 0, ',', '.') ?></strong></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>
            </div>
        </div>
        <!-- /.box -->

        <!-- ACTION BUTTONS -->
        <div class="box box-default">
            <div class="box-body">
                <a href="<?= base_url('gl_interface') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>

                <?php if ($ENABLE_MANAGE && $is_pending): ?>
                    <?php if (!$is_balance): ?>
                        <div class="alert alert-warning" style="display:inline-block; margin:0 0 0 10px; padding:6px 12px;">
                            <i class="fa fa-warning"></i> Debet dan Kredit tidak balance. Tidak bisa diposting.
                        </div>
                    <?php else: ?>
                        <button type="button" class="btn btn-success btn-sm" id="btnPostAccounting" style="margin-left:10px;">
                            <i class="fa fa-paper-plane"></i> Post ke Accounting
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- /.box -->

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#btnPostAccounting').on('click', function() {
            var btn = $(this);

            swal({
                title: 'Post ke Accounting?',
                text: 'Nomor: <?= htmlspecialchars($header['nomor']) ?>\nPastikan data jurnal sudah sesuai sebelum diposting.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#00a65a',
                confirmButtonText: 'Ya, Post Sekarang!',
                cancelButtonText: 'Batal',
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

                    $.post('<?= base_url("gl_interface/post") ?>', {
                        id: '<?= $header['id'] ?>'
                    }, function(res) {
                        if (res.status == 1) {
                            swal({
                                title: 'Berhasil!',
                                text: res.pesan,
                                type: 'success',
                                timer: 3000,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                            }, function() {
                                location.reload();
                            });
                        } else {
                            swal({
                                title: 'Gagal',
                                text: res.pesan,
                                type: 'error',
                                timer: 4000,
                                showConfirmButton: false
                            });
                            btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Post ke Accounting');
                        }
                    }, 'json').fail(function() {
                        swal({
                            title: 'Error',
                            text: 'Terjadi kesalahan server',
                            type: 'error',
                            timer: 4000,
                            showConfirmButton: false
                        });
                        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Post ke Accounting');
                    });
                }
            });
        });
    });
</script>