<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <select name="id_customer" id="id_customer" class="form-control select2" onchange="get_so()">
                                    <option value="">-- Pilih Customer --</option>
                                    <?php foreach ($customer as $cs): ?>
                                        <option value="<?= $cs['id_customer'] ?>"><?= $cs['name_customer'] ?></option>
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
                                <select name="no_so" id="no_so" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Tanggal SPK -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal SPK </label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="tanggal_spk" id="tanggal_spk" class="form-control" value="">
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="delivery_address">Alamat Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="detSO"><i class="fa fa-plus"></i> Detail SO</a>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="table-spk-detail">
                                <thead class="bg-blue">
                                    <tr>
                                        <th width='5%' class='text-center'>#</th>
                                        <th>PRODUCT</th>
                                        <th width='15%' class='text-center'>QTY ORDER</th>
                                        <th width='15%' class='text-center'>QTY BOOKING</th>
                                        <th width='15%' class='text-center'>QTY BELUM KIRIM</th>
                                        <th width='15%' class='text-center'>QTY SPK</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalDetailSO" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Detail Sales Order</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="table-detail-so" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID Product</th>
                            <th>Product Price</th>
                            <th>Qty Order</th>
                            <th>Total Harga</th>
                            <th>Pengiriman</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnPilihDetailSO">Pilih</button>
            </div>
        </div>
    </div>
</div>



<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
        });

        // Tombol Detail SO
        $('#detSO').on('click', function() {
            const no_so = $('#no_so').val();
            if (!no_so) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Pilih SO terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }

            $.ajax({
                url: siteurl + 'spk_delivery/get_so_detail',
                type: 'GET',
                data: {
                    no_so
                },
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';
                    data.forEach((item) => {
                        html += `
                    <tr>
                        <td>${item.product}</td>
                        <td class="text-right">${item.product_price}</td>
                        <td class="text-right">${item.qty_order}</td>
                        <td class="text-right">${item.total_harga}</td>
                        <td class="text-right">${item.pengiriman}</td>
                        <td hidden>${item.use_qty_free}</td>
                        <td hidden>${item.qty_belum_spk}</td>
                        <td hidden>${item.id}</td>
                        <td><input type="checkbox" class="select-row" data-item='${JSON.stringify(item)}'></td>
                    </tr>`;
                    });
                    $('#table-detail-so tbody').html(html);
                    // tampilkan modal setelah data selesai diisi
                    $('#modalDetailSO').modal('show');

                    // Reset previous binding
                    $('#table-detail-so').off('change', '.select-row');

                    // Binding baru
                    $('#table-detail-so').on('change', '.select-row', function() {
                        const checkedItems = $('#table-detail-so .select-row:checked');

                        if (checkedItems.length === 1) {
                            const selectedPengiriman = JSON.parse($(this).attr('data-item')).pengiriman;

                            // Disable semua checkbox dengan pengiriman yang berbeda
                            $('#table-detail-so .select-row').each(function() {
                                const item = JSON.parse($(this).attr('data-item'));
                                if (item.pengiriman !== selectedPengiriman && !$(this).is(':checked')) {
                                    $(this).prop('disabled', true);
                                }
                            });

                        } else if (checkedItems.length === 0) {
                            // Kalau tidak ada yang dicentang, aktifkan kembali semua
                            $('#table-detail-so .select-row').prop('disabled', false);
                        }
                    });
                }
            });
        });

        // add detail SO ke main tabel
        $('#btnPilihDetailSO').on('click', function() {
            let i = $('#table-spk-detail tbody tr').length; // hitung baris awal

            $('#table-detail-so .select-row:checked').each(function() {
                const data = JSON.parse($(this).attr('data-item'));

                const row = `
                            <tr>
                                <td class='text-center'>${i + 1}</td>
                                <td>${data.product}</td>
                                <td class='text-center'>${data.qty_order}</td>
                                <td class='text-center'>${data.use_qty_free ?? 0}</td>
                                <td class='text-center'>${data.qty_belum_spk ?? 0}</td>
                                <td class='text-center'>
                                    <input type='text' name='detail[${i}][qty_spk]' class='form-control input-sm text-center'>
                                </td>
                                <td hidden>
                                    <input type='hidden' name='detail[${i}][id_product]' value='${data.id_product}'>
                                    <input type='hidden' name='detail[${i}][id_so_det]' value='${data.id}'>
                                    <input type='hidden' name='detail[${i}][pengiriman]' value='${data.pengiriman}'>
                                    <input type='hidden' name='detail[${i}][qty_order]' value='${data.qty_order}'>
                                    <input type='hidden' name='detail[${i}][qty_booking]' value='${data.use_qty_free ?? 0}'>
                                    <input type='hidden' name='detail[${i}][qty_belum_spk]' value='${data.qty_belum_spk ?? 0}'>
                                </td>
                            </tr>
                            `;
                $('#table-spk-detail tbody').append(row);
                i++;
            });

            $('#modalDetailSO').modal('hide');
        });

        // button save
        $('#save').click(function(e) {
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
                        var baseurl = siteurl + 'spk_delivery' + '/save';
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


    function get_so() {
        const id_customer = $("#id_customer").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'spk_delivery/get_so',
            data: {
                id_customer: id_customer
            },
            success: function(html) {
                $("#no_so").html(html);
            }
        });
    }
</script>