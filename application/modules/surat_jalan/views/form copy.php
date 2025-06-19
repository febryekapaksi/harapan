<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name="id" value="<?= isset($sj['id']) ? $sj['id'] : '' ?>">
            <input type="hidden" name="no_surat_jalan" value="<?= isset($sj['no_surat_jalan']) ? $sj['no_surat_jalan'] : '' ?>">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Nomor Muat Kendaraan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">No Muat Kendaraan</label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_loading" id="no_loading" class="form-control select2" onchange="get_so()">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($loading as $load): ?>
                                        <option value="<?= $load['no_loading'] ?>" <?= (isset($sj['no_loading']) && $sj['no_loading'] == $load['no_loading']) ? 'selected' : '' ?>>
                                            <?= $load['no_loading'] . " - " . $load['nopol'] . " - " . date('d/M/Y', strtotime($load['tanggal_muat'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Daftar SO Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Sales Order</label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_so" id="no_so" class="form-control select2" onchange="get_spk()">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($sales_order as $so): ?>
                                        <option value="<?= $so['no_so'] ?>" <?= (isset($sj['no_so']) && $so['no_so'] == $sj['no_so']) ? 'selected' : '' ?>>
                                            <?= $so['no_so'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Daftar SPK -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">SPK Deliverey</label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_delivery" id="no_delivery" class="form-control select2" onchange="get_detail()">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($spk_list as $spk): ?>
                                        <option value="<?= $spk['no_delivery'] ?>" <?= (isset($sj['no_delivery']) && $spk['no_delivery'] == $sj['no_delivery']) ? 'selected' : '' ?>>
                                            <?= $spk['no_delivery'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Tanggal Kirim -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Kirim</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control"
                                    value="<?= isset($sj['delivery_date']) ? date('Y-m-d', strtotime($sj['delivery_date'])) : '' ?>" required>
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="delivery_address">Alamat Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="delivery_address" id="delivery_address" class="form-control"><?= isset($sj['delivery_address']) ? $sj['delivery_address'] : '' ?></textarea>
                            </div>
                        </div>

                        <!-- Driver -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Driver</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="driver_name" id="driver_name" class="form-control"
                                    value="<?= isset($sj['driver_name']) ? $sj['driver_name'] : '' ?>" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <!-- <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="detSO"><i class="fa fa-plus"></i> Detail SO</a>
                    </div> -->
                    <hr>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tableDetail">
                                <thead class="">
                                    <tr>
                                        <th width='5%' class='text-center'>#</th>
                                        <th width='15%'>ID PRODUCT</th>
                                        <th>PRODUCT</th>
                                        <th width='15%' class='text-center'>QTY ORDER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($detail)) : ?>
                                        <?php foreach ($detail as $i => $row) : ?>
                                            <tr>
                                                <td class="text-center"><?= $i + 1 ?></td>
                                                <td><?= $row['id_product'] ?></td>
                                                <td><?= $row['product'] ?></td>
                                                <td class="text-center"><?= $row['qty'] ?></td>

                                                <!-- Hidden input untuk keperluan POST saat submit -->
                                                <input type="hidden" name="detail[<?= $i ?>][id_product]" value="<?= $row['id_product'] ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][product]" value="<?= $row['product'] ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][qty]" value="<?= $row['qty'] ?>">
                                                <input type="hidden" name="detail[<?= $i ?>][id_so_det]" value="<?= $row['id_so_det']  ?>">
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada detail.</td>
                                        </tr>
                                    <?php endif; ?>
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
        $('#no_so').on('change', function() {
            var alamat = $(this).find(':selected').data('alamat') || '';
            $('#delivery_address').val(alamat);
        });

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
                        var baseurl = siteurl + 'surat_jalan' + '/save';
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

    function get_so() {
        const no_loading = $("#no_loading").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'surat_jalan/get_so',
            data: {
                no_loading: no_loading
            },
            success: function(html) {
                $("#no_so").html(html);
            }
        });
    }

    function get_spk() {
        const no_so = $("#no_so").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'surat_jalan/get_spk',
            data: {
                no_so: no_so
            },
            success: function(html) {
                $("#no_delivery").html(html);
            }
        });
    }

    function get_detail() {
        const no_delivery = $("#no_delivery").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'surat_jalan/get_detail',
            data: {
                no_delivery: no_delivery
            },
            success: function(html) {
                $('#tableDetail tbody').html(html);
            }
        });
    }
</script>