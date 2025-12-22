<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Daftar SO Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Sales Order</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" id="no_so" name="no_so" class="form-control" value="<?= $retur['no_so'] ?>" readonly>
                                <input type="hidden" name="tipe" value="<?= $retur['tipe'] ?>">
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <input type="hidden" name="id_customer" class="form-control" value="<?= $retur['id_customer'] ?>">
                                <input type="text" name="customer" class="form-control" value="<?= $retur['nm_customer'] ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Tanggal SPK -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal SPK <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="tanggal_spk" id="tanggal_spk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <!-- Tanggal Kirim -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Pengiriman <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="tanggal_kirim" id="tanggal_kirim" class="form-control" required>
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="delivery_address">Alamat Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3"><?= $retur['address_office'] ?></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="notes" value="Barang Retur">
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-spk-detail">
                                <thead class="bg-blue">
                                    <tr>
                                        <th width='5%' class='text-center'>#</th>
                                        <th>PRODUCT</th>
                                        <th width='15%' class='text-center'>QTY RETUR</th>
                                        <th width='15%' class='text-center'>QTY SPK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 0;
                                    foreach ($detail as $dt) {
                                        $no++; ?>
                                        <tr>
                                            <td class="text-center"><?= $no ?></td>
                                            <td><input type="text" class="form-control input-sm" name="detail[<?= $no ?>][nm_product]" value="<?= $dt['nm_product'] ?>" readonly></td>
                                            <td><input type="text" class="form-control input-sm" name="detail[<?= $no ?>][qty_retur]" value="<?= $dt['qty_retur'] ?>" readonly></td>
                                            <td><input type="text" class="form-control input-sm" name="detail[<?= $no ?>][qty_spk]" value="<?= $dt['qty_retur'] ?>" readonly></td>
                                            <input type="hidden" name="detail[<?= $no ?>][id_product]" value="<?= $dt['id_product'] ?>">
                                            <input type="hidden" name="detail[<?= $no ?>][id_so_det]" value="<?= $dt['id_so_det'] ?>">
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // button save
        $('#data-form').submit(function(e) {
            e.preventDefault();

            var tanggal_spk = $('#tanggal_spk').val()

            if (tanggal_spk == '') {
                swal({
                    title: "Error Message!",
                    text: 'Data not complete, completely first ...',
                    type: "warning"
                });

                $('#save').prop('disabled', false);
                return false;
            }

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
                        var baseurl = siteurl + active_controller + '/save_spk';
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
        });

    });
</script>