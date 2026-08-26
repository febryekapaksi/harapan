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
                    <?php if (has_permission('Setor_Bank.Delete')): ?>
                        <button type="button" class="btn btn-danger" id="btnCancel" data-id="<?= $header->id ?>">
                            <i class="fa fa-times"></i> Cancel Setoran
                        </button>
                    <?php endif; ?>
                    <a class="btn btn-primary" href="<?= base_url('setor_bank') ?>">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('#btnCancel').on('click', function() {
        var id = $(this).data('id');

        swal({
            title: "Konfirmasi Pembatalan",
            text: "Apakah Anda yakin ingin membatalkan setoran " + id + "? Proses ini akan mengembalikan status penerimaan dan membatalkan jurnal.",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d9534f",
            confirmButtonText: "Ya, Batalkan!",
            cancelButtonText: "Tidak",
            closeOnConfirm: false
        }, function(confirm) {
            if (confirm) {
                $.ajax({
                    type: 'POST',
                    url: siteurl + 'setor_bank/cancel/' + id,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status) {
                            swal({
                                title: 'Berhasil!',
                                text: result.message,
                                type: 'success'
                            }, function() {
                                window.location.href = siteurl + 'setor_bank';
                            });
                        } else {
                            swal('Gagal!', result.message, 'warning');
                        }
                    },
                    error: function() {
                        swal('Error!', 'Terjadi kesalahan, silakan coba lagi.', 'error');
                    }
                });
            }
        });
    });
});
</script>
