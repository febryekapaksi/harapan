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
                                <input type="text" id="no_so" name="no_so" class="form-control" value="<?= $so['no_so'] ?>" readonly>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <input type="hidden" name="id_customer" class="form-control" value="<?= $so['id_customer'] ?>">
                                <input type="text" name="customer" class="form-control" value="<?= $so['name_customer'] ?>" readonly>
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
                                <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3"><?= $so['address_office'] ?></textarea>
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
                                        <th width='15%' class='text-center'>QTY BELUM SPK</th>
                                        <th width='15%' class='text-center'>QTY SPK</th>
                                        <th></th>
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
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
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
                <div class="table-responsive">
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
                        <td class="text-right">${item.price_list}</td>
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

                    $('#table-spk-detail tbody tr').each(function() {
                        const id_so_det = $(this).find("input[name*='[id_so_det]']").val();
                        const id_product = $(this).find("input[name*='[id_product]']").val();
                        const rowKey = `${id_so_det}_${id_product}`;

                        $('#table-detail-so .select-row').each(function() {
                            const item = JSON.parse($(this).attr('data-item'));
                            const thisKey = `${item.id}_${item.id_product}`;
                            if (thisKey === rowKey) {
                                $(this).prop('checked', true).prop('disabled', true);
                            }
                        });
                    });
                }
            });
        });

        // add detail SO ke main tabel
        $('#btnPilihDetailSO').on('click', function() {
            let i = $('#table-spk-detail tbody tr').length;

            $('#table-detail-so .select-row:checked').each(function() {
                const data = JSON.parse($(this).attr('data-item'));
                const rowKey = `${data.id}_${data.id_product}`; // kombinasi unik

                // Cegah duplikasi baris
                if ($(`#row-${rowKey}`).length) return;

                const row = `
            <tr id="row-${rowKey}">
                <td class='text-center'>${i + 1}</td>
                <td>${data.product}</td>
                <td class='text-center'>${data.qty_order}</td>
                <td class='text-center'>${data.use_qty_free ?? 0}</td>
                <td class='text-center qty-belum-spk'>${data.qty_belum_spk ?? 0}</td>
                <td class='text-center'>
                    <input type='number' name='detail[${i}][qty_spk]' class='form-control input-sm text-center qty_spk' required>
                </td>
                <td class='text-center'>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" data-key="${rowKey}"><i class="fa fa-trash"></i></button>
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

                // Disable checkbox di modal agar tidak dipilih ulang
                $(this).prop('disabled', true);
                i++;
            });

            $('#modalDetailSO').modal('hide');
        });


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

        $(document).on('click', '.btn-remove-row', function() {
            const key = $(this).data('key');

            // Hapus baris dari table-spk-detail
            $(`#row-${key}`).remove();

            // Aktifkan kembali checkbox di modal
            $('#table-detail-so .select-row').each(function() {
                const data = JSON.parse($(this).attr('data-item'));
                const rowKey = `${data.id}_${data.id_product}`;
                if (rowKey === key) {
                    $(this).prop('checked', false).prop('disabled', false);
                }
            });

            // Re-numbering (opsional)
            $('#table-spk-detail tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        });

        $(document).on('input', '.qty_spk', function() {
            const row = $(this).closest('tr');
            const qtyBelumSpk = parseInt(row.find('.qty-belum-spk').text()) || 0;
            const inputValue = parseInt($(this).val()) || 0;

            if (inputValue > qtyBelumSpk) {
                swal({
                    title: "Error Message !",
                    text: 'Qty SPK tidak boleh melebihi Qty Belum SPK',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: true,
                    allowOutsideClick: true
                });
                $(this).val(qtyBelumSpk);
            }
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