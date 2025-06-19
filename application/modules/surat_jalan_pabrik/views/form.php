<!-- FORM -->
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name="id" value="<?= isset($sj['id']) ? $sj['id'] : '' ?>">
            <input type="hidden" name="no_surat_jalan" value="<?= isset($sj['no_surat_jalan']) ? $sj['no_surat_jalan'] : '' ?>">
            <input type="hidden" name="no_delivery" id="no_delivery" value="<?= isset($sj['no_delivery']) ? $sj['no_delivery'] : '' ?>">
            <input type="hidden" name="no_so" id="no_so" value="<?= isset($sj['no_so']) ? $sj['no_so'] : '' ?>">
            <input type="hidden" name="pengiriman" id="pengiriman" value="<?= isset($sj['pengiriman']) ? $sj['pengiriman'] : '' ?>">

            <div class="form-group row">
                <div class="col-md-6">
                    <!-- Pilih SPK Delivery -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="spk_delivery">SPK Delivery <span class="text-red">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <select name="spk_delivery" id="spk_delivery" class="form-control select2">
                                <option value="">-- Pilih SPK --</option>
                                <?php foreach ($spk_delivery as $spk): ?>
                                    <option value="<?= $spk['no_delivery'] ?>">
                                        <?= $spk['no_delivery'] . ' - ' . $spk['customer'] . ' - ' . date('d/M/Y', strtotime($spk['tanggal_spk'])) ?>
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
                            <label>Tanggal Kirim <span class="text-red">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <input type="date" name="delivery_date" id="delivery_date" class="form-control"
                                value="<?= isset($sj['delivery_date']) ? date('Y-m-d', strtotime($sj['delivery_date'])) : '' ?>" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel SPK -->
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="tableSpk">
                            <thead class="bg-blue">
                                <tr>
                                    <th>No</th>
                                    <th>No SPK</th>
                                    <th>No SO</th>
                                    <th>Customer</th>
                                    <th>Produk</th>
                                    <th>Qty</th>
                                    <th>Berat(Kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Diisi otomatis via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alamat dan Driver -->
            <div class="form-group row">
                <div class="col-md-6">
                    <!-- Alamat Pengiriman -->
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="delivery_address">Alamat Pengiriman</label>
                        </div>
                        <div class="col-md-8">
                            <textarea name="delivery_address" id="delivery_address" class="form-control"><?= isset($sj['delivery_address']) ? $sj['delivery_address'] : '' ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
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

            <!-- Tombol Simpan -->
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
        $('.select2').select2({
            width: '100%'
        });

        $('#spk_delivery').on('change', function() {
            const no_delivery = $(this).val();

            if (!no_delivery) {
                $('#tableSpk tbody').empty();
                $('#delivery_address').val('');
                $('#no_so').val('');
                $('#no_delivery').val('');
                return;
            }

            $.ajax({
                url: siteurl + 'surat_jalan_pabrik/get_spk_detail',
                type: 'GET',
                data: {
                    no_delivery
                },
                success: function(res) {
                    const data = JSON.parse(res);

                    if (!data.header || data.detail.length === 0) {
                        swal("Peringatan", "SPK tidak memiliki detail atau sudah digunakan.", "warning");
                        return;
                    }

                    // ✅ Set data ke form
                    $('#delivery_address').val(data.header.delivery_address || '');
                    $('#pengiriman').val(data.header.pengiriman || '');
                    $('#no_delivery').val(data.header.no_delivery || '');
                    $('#no_so').val(data.header.no_so || '');

                    let html = '';


                    data.detail.forEach((item, index) => {
                        html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <input type="hidden" name="detail[${index}][id]" value="${item.id}">
                            <input type="hidden" name="detail[${index}][id_product]" value="${item.id_product}">
                            <input type="hidden" name="detail[${index}][id_so_det]" value="${item.id_so_det}">
                            <input type="text" class="form-control" name="detail[${index}][no_delivery]" value="${item.no_delivery}" readonly>
                        </td>
                        <td><input type="text" class="form-control" name="detail[${index}][no_so]" value="${item.no_so}" readonly></td>
                        <td><input type="text" class="form-control" name="detail[${index}][customer]" value="${item.customer}" readonly></td>
                        <td><input type="text" class="form-control" name="detail[${index}][product]" value="${item.product}" readonly></td>
                        <td><input type="text" class="form-control" name="detail[${index}][qty]" value="${item.qty_spk}" readonly></td>
                        <td>
                            <input type="text" class="form-control" name="detail[${index}][total_berat]" value="${parseFloat(item.total_berat).toFixed(2)}" readonly>
                            <input type="hidden" class="form-control" name="detail[${index}][weight]" value="${parseFloat(item.weight).toFixed(2)}" readonly>
                        </td>
                    </tr>
                `;
                    });

                    $('#tableSpk tbody').html(html);
                },
                error: function() {
                    swal("Error", "Gagal mengambil data SPK!", "error");
                }
            });
        });

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
                        var baseurl = siteurl + 'surat_jalan_pabrik' + '/save';
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