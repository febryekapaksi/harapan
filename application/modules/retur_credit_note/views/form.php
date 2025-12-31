<div class="box box-primary">
    <div class="box-body">
        <form id="data-form">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Nomor Retur</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_retur" readonly>
                                <input type="hidden" name="id_invoice" value="<?= !empty($inv) ? $inv['id_invoice'] : '' ?>">
                                <input type="hidden" name="id_billing" value="<?= !empty($inv) ? $inv['id_billing'] : '' ?>">
                                <input type="hidden" name="pengiriman" value="<?= !empty($inv) ? $inv['pengiriman'] : '' ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Nomor SO</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="id_so" value="<?= !empty($inv) ? $inv['id_so'] : '' ?>" required readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Retur</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tgl_retur" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Customer</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="nm_customer" value="<?= !empty($inv) ? $inv['nm_customer'] : '' ?>" required readonly>
                                <input type="hidden" name="id_customer" value="<?= !empty($inv) ? $inv['id_customer'] : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Alasan</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="alasan" class="form-control" id=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>ID Produk</th>
                                        <th>Nama Produk</th>
                                        <th>Qty Delivery</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Alasan Retur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 0;
                                    $grand_total = 0;
                                    foreach ($detail as $dt) {
                                        $no++ ?>
                                        <tr>
                                            <td class="text-center"><?= $no ?></td>
                                            <td style="width: 150px;"><input type="text" name="detail[<?= $no ?>][id_produk]" class="form-control input-sm" value="<?= $dt['id_produk'] ?>" readonly></td>
                                            <td><input type="text" name="detail[<?= $no ?>][nm_produk]" class="form-control input-sm" value="<?= $dt['nm_produk'] ?>" readonly></td>
                                            <td style="width: 100px;"><input type="text" name="detail[<?= $no ?>][qty]" class="form-control input-sm text-center" value="<?= $dt['qty'] ?>" readonly></td>
                                            <td style="width: 150px;"><input type="text" name="detail[<?= $no ?>][harga]" class="form-control input-sm text-right" value="<?= number_format($dt['harga']) ?>" readonly></td>
                                            <td style="width: 150px;"><input type="text" name="detail[<?= $no ?>][total]" class="form-control input-sm text-right" value="<?= number_format($dt['total']) ?>" readonly></td>
                                            <td><textarea name="detail[<?= $no ?>][alasan_retur]" class="form-control input-sm"></textarea></td>
                                            <input type="hidden" name="detail[<?= $no ?>][id_so_det]" value="<?= $dt['id_so_det'] ?>">
                                        </tr>
                                    <?php
                                        $grand_total += $dt['total'];
                                    } ?>
                                </tbody>
                                <tfoot class="bg-gray">
                                    <tr>
                                        <th colspan="5" class="text-right">Grand Total</th>
                                        <th><input type="text" class="form-control input-sm text-right" name="grand_total" value="<?= number_format($grand_total) ?>"></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                        <a class="btn btn-default" onclick="window.history.back(); return false;">
                            <i class="fa fa-reply"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        //SAVE SURAT JALAN
        $('#data-form').submit(function(e) {
            e.preventDefault();
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + 'retur_credit_note' + '/save';
                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000
                                    });
                                    window.location.href = base_url + active_controller
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            },
                            error: function() {

                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        return false;
                    }
                });
        })
    });
</script>