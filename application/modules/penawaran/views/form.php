<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" autocomplete="off">
            <input type="hidden" name="id_penawaran" value="<?= isset($penawaran['id_penawaran']) ? $penawaran['id_penawaran'] : '' ?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-sm-6">

                        <!-- No Penawaran -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="no_penawaran">No Penawaran</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_penawaran" id="no_penawaran"
                                    value="<?= isset($penawaran['id_penawaran']) ? $penawaran['id_penawaran'] : 'Automatic' ?>"
                                    placeholder="Automatic" readonly>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="id_customer">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <select name="id_customer" id="id_customer" class="form-control select2">
                                    <option value="">-- Pilih ---</option>
                                    <?php foreach ($customers as $ctm): ?>
                                        <option
                                            value="<?= $ctm['id_customer']; ?>"
                                            data-sales="<?= $ctm['id_karyawan'] ?>"
                                            data-email="<?= $ctm['email'] ?>"
                                            data-toko="<?= $ctm['kategori_toko']; ?>"
                                            <?= isset($penawaran['id_customer']) && $penawaran['id_customer'] == $ctm['id_customer'] ? 'selected' : '' ?>>
                                            <?= $ctm['name_customer']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Sales -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="sales">Sales</label>
                            </div>
                            <div class="col-md-8">
                                <input type="hidden" name="id_karyawan" id="id_karyawan">
                                <input type="text" class="form-control" name="sales" id="sales"
                                    value="<?= isset($penawaran['sales']) ? $penawaran['sales'] : '' ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="email">Email</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email" id="email"
                                    value="<?= isset($penawaran['email']) ? $penawaran['email'] : '' ?>">
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-6">

                        <!-- Term of Payment -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="payment_term">Term Of Payment</label>
                            </div>
                            <div class="col-md-8">
                                <select id="payment_term" name="payment_term" class="form-control select2" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($payment_terms as $term): ?>
                                        <option value="<?= htmlspecialchars($term['id']) ?>"
                                            <?= isset($penawaran['payment_term']) && $penawaran['payment_term'] == $term['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($term['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Quotation Date -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="quotation_date">Quotation Date</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="quotation_date" id="quotation_date"
                                    value="<?= isset($penawaran['quotation_date']) ? date('Y-m-d', strtotime($penawaran['quotation_date'])) : '' ?>">
                            </div>
                        </div>

                        <!-- Tipe Bayar -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="tipe_bayar">Tipe Bayar</label>
                            </div>
                            <div class="col-md-8">
                                <select name="tipe_bayar" id="tipe_bayar" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                    <option value="cash" <?= isset($penawaran['tipe_bayar']) && $penawaran['tipe_bayar'] == 'cash' ? 'selected' : '' ?>>Cash</option>
                                    <option value="tempo" <?= isset($penawaran['tipe_bayar']) && $penawaran['tipe_bayar'] == 'tempo' ? 'selected' : '' ?>>Tempo</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <hr>
            <div class="form-group row">
                <div class="col-md-12">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr class='bg-blue'>
                                <td align='center' style="width: 25%;"><b>Nama Produk</b></td>
                                <td align='center' style="width: 100px"><b>Qty</b></td>
                                <td align='center' style="width: 100px"><b>Free Stok</b></td>
                                <td align='center'><b>Price List</b></td>
                                <td align='center'><b>Harga Penawaran</b></td>
                                <td align='center'><b>% Discount</b></td>
                                <td align='center'><b>Total Harga Penawaran</b></td>
                                <td style="width: 50px;" align='center'>
                                    <?php
                                    echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-product'));
                                    ?>
                                </td>
                            </tr>
                        </thead>
                        <tbody id="list_product">
                            <?php
                            $loop = 0;
                            if (!empty($penawaran_detail)) {
                                foreach ($penawaran_detail as $dp) {
                                    $loop++;
                            ?>
                                    <tr id="tr_<?= $loop ?>">
                                        <td>
                                            <select name="product[<?= $loop ?>][id_product]" class="form-control product-select select2" data-loop="<?= $loop ?>">
                                                <option value="">-- Pilih Produk --</option>
                                                <?php foreach ($products as $item): ?>
                                                    <option value="<?= $item['id'] ?>"
                                                        data-price="<?= $item['propose_price'] ?>"
                                                        data-product="<?= $item['product_name'] ?>"
                                                        <?= $item['id'] == $dp['id_product'] ? 'selected' : '' ?>>
                                                        <?= $item['product_name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td hidden><input type="hidden" name="product[<?= $loop ?>][product_name]" id="product_name_<?= $loop ?>" value="<?= $dp['product_name'] ?>"></td>
                                        <td><input type="number" class="form-control qty-input" name="product[<?= $loop ?>][qty]" id="qty_<?= $loop ?>" value="<?= $dp['qty'] ?>"></td>
                                        <td><input type="text" class="form-control" name="product[<?= $loop ?>][stok]" id="stok_<?= $loop ?>" readonly></td>
                                        <td><input type=" text" class="form-control divide price-list" name="product[<?= $loop ?>][price_list]" id="price_<?= $loop ?>" value="<?= $dp['price_list'] ?>" readonly></td>
                                        <td><input type="text" class="form-control penawaran divide" name="product[<?= $loop ?>][harga_penawaran]" id="penawaran_<?= $loop ?>" value="<?= $dp['harga_penawaran'] ?>"></td>
                                        <td><input type="text" class="form-control diskon" name="product[<?= $loop ?>][diskon]" id="diskon_<?= $loop ?>" value="<?= $dp['diskon'] ?>" readonly></td>
                                        <td><input type="text" class="form-control divide total-harga" name="product[<?= $loop ?>][total]" id="total_<?= $loop ?>" value="<?= $dp['total'] ?>" readonly></td>
                                        <td align="center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="DelProduct(<?= $loop ?>)"><i class="fa fa-trash-o"></i></button>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                // default 1 baris kosong
                                $loop = 1;
                                ?>
                                <tr id="tr_1">
                                    <td>
                                        <select name="product[1][id_product]" class="form-control product-select select2" data-loop="1">
                                            <option value="">-- Pilih Produk --</option>
                                            <?php foreach ($products as $item): ?>
                                                <option value="<?= $item['id'] ?>" data-price="<?= $item['propose_price'] ?>"
                                                    data-product="<?= $item['product_name'] ?>">
                                                    <?= $item['product_name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td hidden><input type="hidden" name="product[1][product_name]" id="product_name_1"></td>
                                    <td><input type="number" class="form-control qty-input" name="product[1][qty]" id="qty_1"></td>
                                    <td><input type="text" class="form-control" name="product[1][stok]" id="stok_1" readonly></td>
                                    <td><input type="text" class="form-control divide price-list" name="product[1][price_list]" id="price_1" readonly></td>
                                    <td><input type="text" class="form-control penawaran divide" name="product[1][harga_penawaran]" id="penawaran_1"></td>
                                    <td><input type="text" class="form-control diskon" name="product[1][diskon]" id="diskon_1" readonly></td>
                                    <td><input type="text" class="form-control divide total-harga" name="product[1][total]" id="total_1" readonly></td>
                                    <td align="center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="DelProduct(1)"><i class="fa fa-trash-o"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total Harga Penawaran</strong></td>
                                <td colspan="2"><input type="text" class="form-control divide" name="total_penawaran" id="total_penawaran" value="<?= isset($penawaran['total_penawaran']) ? $penawaran['total_penawaran'] : '' ?>" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total Harga Price List</strong></td>
                                <td colspan="2"><input type="text" class="form-control divide" name="total_price_list" id="total_price_list" value="<?= isset($penawaran['total_price_list']) ? $penawaran['total_price_list'] : '' ?>" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Total % Discount</strong></td>
                                <td colspan="2"><input type="text" class="form-control" name="total_diskon_persen" id="total_diskon_persen" value="<?= isset($penawaran['total_diskon_persen']) ? $penawaran['total_diskon_persen'] : '' ?>" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><strong>DPP</strong></td>
                                <td colspan="2"><input type="text" class="form-control divide" name="dpp" id="dpp" value="<?= isset($penawaran['dpp']) ? $penawaran['dpp'] : '' ?>" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><strong>PPn</strong></td>
                                <td colspan="2"><input type="text" class="form-control divide" name="ppn" id="ppn" value="<?= isset($penawaran['ppn']) ? $penawaran['ppn'] : '' ?>" readonly></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-right"><strong>Grand Total</strong></td>
                                <td colspan="2"><input type="text" class="form-control divide" name="grand_total" id="grand_total" value="<?= isset($penawaran['grand_total']) ? $penawaran['grand_total'] : '' ?>" readonly></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <?php if ($mode == 'add' || $mode == 'edit'): ?>
                        <button type="submit" class="btn btn-primary" name="save" id="save">
                            <i class="fa fa-save"></i> Save
                        </button>
                    <?php elseif ($mode == 'approval_manager' || $mode == 'approval_direksi'): ?>
                        <button type="submit" class="btn btn-success" name="approve" id="approve" data-role="<?= $mode ?>">
                            <i class="fa fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" id="reject">
                            <i class="fa fa-times"></i> Reject
                        </button>
                    <?php endif; ?>
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
        $('.divide').divide();


        // TAMBAH LIST PRODUCT
        let products = <?= json_encode($products) ?>; // kirim dari PHP
        let loop = $('#list_product tr').length; // inisialisasi dari jumlah baris awal
        $('#add-product').click(function() {
            loop++;

            let options = '<option value="">-- Pilih Produk --</option>';
            products.forEach(item => {
                options += `<option value="${item.id}" data-price="${item.propose_price}" data-product="${item.product_name}">${item.product_name}</option>`;
            });

            let row = `
                <tr id="tr_${loop}">
                    <td>
                        <select name="product[${loop}][id_product]" class="form-control product-select select2" data-loop="${loop}">
                            ${options}
                        </select>
                    </td>
                    <td hidden><input type="hidden" name="product[${loop}][product_name]" id="product_name_${loop}"></td>
                    <td><input type="number" class="form-control qty-input" name="product[${loop}][qty]" id="qty_${loop}"></td>
                    <td><input type="text" class="form-control" name="product[${loop}][stok]" id="stok_${loop}" readonly></td>
                    <td><input type="text" class="form-control divide price-list" name="product[${loop}][price_list]" id="price_${loop}" readonly></td>
                    <td><input type="text" class="form-control penawaran divide" name="product[${loop}][harga_penawaran]" id="penawaran_${loop}"></td>
                    <td><input type="text" class="form-control diskon" name="product[${loop}][diskon]" id="diskon_${loop}" readonly></td>
                    <td><input type="text" class="form-control divide total-harga" name="product[${loop}][total]" id="total_${loop}" readonly></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="DelProduct(${loop})"><i class="fa fa-trash-o"></i></button>
                    </td>
                </tr>`;
            $('#list_product').append(row);
            $(`#tr_${loop} .select2`).select2({
                width: '100%'
            });
            $(`#tr_${loop} .divide`).divide();
        });

        // saat produk dipilih ambil harga dan stok 
        $(document).on('change', '.product-select', function() {
            const loop = $(this).data('loop');
            const selected = $(this).find(':selected');
            const price = selected.data('price') || 0;
            const stock = selected.data('stock') || 0;
            const product = selected.data('product');

            $(`#price_${loop}`).val(price);
            $(`#stok_${loop}`).val(stock);
            $(`#product_name_${loop}`).val(product);

            hitungTotal(loop);
        });

        // Trigger hitung diskon, total, dan seluruh total
        $(document).on('input', '.penawaran, .qty-input', function() {
            const loop = $(this).closest('tr').attr('id').split('_')[1];
            hitungTotal(loop);
            hitungAllTotal();
        });

        // Trigger untuk mengambil nama sales
        $('#id_customer').change(function() {
            const idKaryawan = $(this).find(':selected').data('sales');
            const email = $(this).find(':selected').data('email');

            if (idKaryawan) {
                $.ajax({
                    url: '<?= base_url('penawaran/get_nama_sales') ?>',
                    type: 'POST',
                    data: {
                        id_karyawan: idKaryawan
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.error) {
                            $('#sales').val('');
                            alert(res.message);
                        } else {
                            $('#sales').val(res.nama_sales);
                            $('#id_karyawan').val(idKaryawan);
                            $('#email').val(email);
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil nama sales.');
                    }
                });
            } else {
                $('#sales').val('');
            }
        });

        // Trigger untuk mengambil data toko dan tipe bayar
        $('#id_customer, #tipe_bayar').change(function() {
            $('.product-select').each(function() {
                const loopIndex = $(this).data('loop');
                hitungHarga(loopIndex);
            });
        });

        // Trigger untuk mengambil harga dari product costing sebagai price list 
        $(document).on('change', '.product-select', function() {
            const loopIndex = $(this).data('loop');
            hitungHarga(loopIndex);
        });

        // SAVE PENAWARAN
        $('#save').click(function(e) {
            e.preventDefault();
            var customer = $('#id_customer').val();

            if (customer == '') {
                swal({
                    title: "Error Message!",
                    text: 'Customer empty, select first ...',
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
                        var baseurl = base_url + active_controller + '/save'
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
                                        timer: 3000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {

                                    if (data.status == 2) {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    }

                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        return false;
                    }
                });
        });

        // APPROVE DIGABUNG
        $('#approve').click(function(e) {
            e.preventDefault();

            var customer = $('#id_customer').val();
            if (customer == '') {
                swal({
                    title: "Error Message!",
                    text: 'Customer empty, select first ...',
                    type: "warning"
                });
                return false;
            }

            var role = $(this).data('role');
            var actionUrl = base_url + active_controller + 'save_' + (role === 'approval_direksi' ? 'approval_direksi' : 'approval_manager');
            console.log(actionUrl)
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
                        $.ajax({
                            url: actionUrl,
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
                                        timer: 3000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller + (role === 'approval_direksi' ? 'approval_direksi' : 'approval_manager');
                                } else {

                                    if (data.status == 2) {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    } else {
                                        swal({
                                            title: "Save Failed!",
                                            text: data.pesan,
                                            type: "warning",
                                            timer: 3000,
                                            showCancelButton: false,
                                            showConfirmButton: false,
                                            allowOutsideClick: false
                                        });
                                    }

                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
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


    //fungsi hapus baris
    function DelProduct(id) {
        $('#list_product #tr_' + id).remove();
    }

    //fungsi hitung seluruh total 
    function hitungAllTotal() {
        let totalPenawaran = 0;
        let totalPriceList = 0;
        let totalDiskon = 0;

        $('.total-harga').each(function() {
            const val = parseFloat($(this).val().replaceAll(',', '')) || 0;
            totalPenawaran += val;
        });

        $('.price-list').each(function() {
            const val = parseFloat($(this).val().replaceAll(',', '')) || 0;
            totalPriceList += val
        });

        $('.diskon').each(function() {
            const val = parseFloat($(this).val()) || 0;
            totalDiskon += val
        })

        const dpp = (11 / 12) * totalPenawaran;
        const ppn = (12 * dpp) / 100;
        const grand_total = dpp + ppn;


        $('#total_penawaran').val(totalPenawaran.toLocaleString('id-ID'));
        $('#total_price_list').val(totalPriceList.toLocaleString('id-ID'));
        $('#total_diskon_persen').val(totalDiskon.toFixed(2));
        $('#dpp').val(Math.floor(dpp));
        $('#ppn').val(Math.floor(ppn));
        $('#grand_total').val(Math.floor(grand_total));
    }

    //fungsi hitung total perbaris
    function hitungTotal(loop) {
        const qty = parseFloat($(`#qty_${loop}`).val()) || 0;
        const price = parseFloat($(`#price_${loop}`).val()) || 0;
        const offer = parseFloat($(`#penawaran_${loop}`).val()) || 0;

        const diskon = offer ? ((offer - price) / price) * 100 : 0;
        const total = qty * offer;

        $(`#diskon_${loop}`).val(diskon.toFixed(2));
        $(`#total_${loop}`).val(total);
    }

    //fungsi hitung harga berantai berdasarkan toko
    function hitungHarga(loopIndex) {
        const productSelect = $(`.product-select[data-loop="${loopIndex}"]`);
        const idProduct = productSelect.val();

        const idCustomer = $('#id_customer').val();
        const kategoriToko = $('#id_customer option:selected').data('toko');
        const tipeBayar = $('#tipe_bayar').val();

        if (idProduct && kategoriToko && tipeBayar) {
            $.ajax({
                url: base_url + 'penawaran/pilih_harga_ajax',
                type: 'POST',
                data: {
                    id_product: idProduct,
                    kategori_toko: kategoriToko,
                    tipe_bayar: tipeBayar
                },
                dataType: 'json',
                success: function(res) {
                    if (res.error) {
                        Swal.fire('Gagal', res.message, 'warning');
                    } else {
                        $(`#price_${loopIndex}`).val(res.harga);
                    }
                },
                error: function() {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat mengambil data harga.', 'error');
                }
            });
        }
    }
</script>