<div class="box box-warning">
    <div class="box-body">
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            Ini adalah <strong>request retur</strong>. Setelah disimpan, departemen <strong>Gudang</strong> akan membuat Surat Jalan Retur,
            kemudian departemen <strong>Finance</strong> akan memproses Credit Note.
        </div>

        <form id="data-form">
            <input type="hidden" name="id_invoice" value="<?= !empty($inv) ? $inv['id_invoice'] : '' ?>">
            <input type="hidden" name="id_billing" value="<?= !empty($inv) ? $inv['id_billing'] : '' ?>">
            <input type="hidden" name="pengiriman" value="<?= !empty($inv) ? $inv['pengiriman'] : '' ?>">
            <input type="hidden" name="id_so" value="<?= !empty($inv) ? $inv['id_so'] : '' ?>">
            <input type="hidden" name="id_customer" value="<?= !empty($inv) ? $inv['id_customer'] : '' ?>">
            <input type="hidden" name="nm_customer" value="<?= !empty($inv) ? $inv['nm_customer'] : '' ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>No. Invoice</label>
                        <input type="text" class="form-control" value="<?= !empty($inv) ? $inv['id_invoice'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>No. SO</label>
                        <input type="text" class="form-control" value="<?= !empty($inv) ? $inv['id_so'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Request Retur <span class="text-red">*</span></label>
                        <input type="date" class="form-control" name="tgl_retur" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Customer</label>
                        <input type="text" class="form-control" value="<?= !empty($inv) ? $inv['nm_customer'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>No. Surat Jalan Asal</label>
                        <input type="text" class="form-control" value="<?= !empty($inv) ? $inv['id_billing'] : '' ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Alasan Retur <span class="text-red">*</span></label>
                        <textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan retur..."></textarea>
                    </div>
                </div>
            </div>

            <hr>
            <h4>Detail Item Invoice</h4>
            <p class="text-muted"><small>Qty retur final akan ditentukan oleh Gudang saat membuat Surat Jalan Retur.</small></p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="bg-blue">
                        <tr>
                            <th>No</th>
                            <th>ID Produk</th>
                            <th>Nama Produk</th>
                            <th class="text-center">Qty Delivery</th>
                            <th class="text-right">Harga Satuan</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 0;
                        foreach ($detail as $dt): $no++; ?>
                            <tr>
                                <td class="text-center"><?= $no ?></td>
                                <td>
                                    <input type="hidden" name="detail[<?= $no ?>][id_produk]" value="<?= $dt['id_produk'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][nm_produk]" value="<?= $dt['nm_produk'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][id_so_det]" value="<?= $dt['id_so_det'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][qty]" value="<?= $dt['qty'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][harga_raw]" value="<?= $dt['harga'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][harga_beli]" value="<?= $dt['harga_beli'] ?>">
                                    <input type="hidden" name="detail[<?= $no ?>][total_raw]" value="<?= $dt['total'] ?>">
                                    <?= $dt['id_produk'] ?>
                                </td>
                                <td><?= $dt['nm_produk'] ?></td>
                                <td class="text-center"><?= $dt['qty'] ?></td>
                                <td class="text-right"><?= number_format($dt['harga'], 0, ',', '.') ?></td>
                                <td class="text-right"><?= number_format($dt['total'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-warning"><i class="fa fa-paper-plane"></i> Kirim Request Retur</button>
                <a class="btn btn-default" onclick="window.history.back(); return false;"><i class="fa fa-reply"></i> Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#data-form').submit(function(e) {
            e.preventDefault();
            swal({
                title: "Kirim Request Retur?",
                text: "Request akan diteruskan ke Gudang untuk dibuatkan Surat Jalan Retur.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Kirim",
                confirmButtonColor: "#f39c12",
                cancelButtonText: "Batal"
            }, function(confirm) {
                if (!confirm) return;
                var formData = new FormData($('#data-form')[0]);
                $.ajax({
                    url: siteurl + 'retur_credit_note/save_request',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            swal({
                                title: 'Berhasil!',
                                text: res.pesan,
                                type: 'success',
                                timer: 4000
                            });
                            setTimeout(function() {
                                window.location.href = siteurl + 'retur_credit_note';
                            }, 1500);
                        } else {
                            swal('Gagal', res.pesan, 'warning');
                        }
                    },
                    error: function() {
                        swal('Error', 'Terjadi kesalahan.', 'error');
                    }
                });
            });
        });
    });
</script>