<style>
    .text-green {
        color: green;
        font-weight: bold;
    }

    .text-red {
        color: red;
        font-weight: bold;
    }
</style>

<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" autocomplete="off">
            <input type="hidden" name="id_penawaran" value="<?= isset($penawaran['id_penawaran']) ? $penawaran['id_penawaran'] : '' ?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-sm-6">

                        <!-- No SO -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="no_so">No SO</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_so" id="no_so"
                                    value="<?= isset($so['no_so']) ? $so['no_so'] : '' ?>"
                                    readonly>
                            </div>
                        </div>

                        <!-- Term of Payment -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="payment_term">Term Of Payment</label>
                            </div>
                            <div class="col-md-8">
                                <select id="payment_term" name="payment_term" class="form-control select2" required disabled>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($payment_terms as $term): ?>
                                        <option value="<?= htmlspecialchars($term['id']) ?>"
                                            <?= isset($penawaran['payment_term']) && $penawaran['payment_term'] == $term['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($term['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="payment_term" value="<?= isset($penawaran['payment_term']) ? $penawaran['payment_term'] : '' ?>">
                            </div>
                        </div>

                        <!-- SO Date -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="tgl_so">SO Date <span style="color: red;">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tgl_so" id="tgl_so"
                                    value="<?= isset($so['tgl_so']) ? date('Y-m-d', strtotime($so['tgl_so'])) : '' ?>" <?= (isset($mode) && $mode == 'deal') ? 'readonly' : '' ?> required>
                            </div>
                        </div>

                    </div>
                    <div class="col-sm-6">

                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="id_customer">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <select name="id_customer" id="id_customer" class="form-control select2" disabled>
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
                                <input type="hidden" name="id_customer" value="<?= isset($penawaran['id_customer']) ? $penawaran['id_customer'] : '' ?>">
                            </div>
                        </div>

                        <!-- Sales -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="sales">Sales</label>
                            </div>
                            <div class="col-md-8">
                                <input type="hidden" name="id_karyawan" id="id_karyawan">
                                <input type="text" class="form-control" name="sales" id="sales" readonly
                                    value="<?= isset($penawaran['sales']) ? $penawaran['sales'] : '' ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="email">Email</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="email" id="email" readonly
                                    value="<?= isset($penawaran['email']) ? $penawaran['email'] : '' ?>">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <hr>
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class='table table-bordered table-striped'>
                            <thead>
                                <tr class='bg-blue'>
                                    <td align='center' style="min-width: 200px;"><b>Nama Produk</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>Qty</b></td>
                                    <td align='center' style="min-width: 150px;" class="text-nowrap"><b>Harga</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>Free Stok</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>Use Free Stok</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>Selisih</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>Propose PR</b></td>
                                    <td align='center' style="min-width: 100px;" class="text-nowrap"><b>% Discount</b></td>
                                    <td align='center'><b>Pengiriman</b></td>
                                    <td align='center' style="min-width: 160px;" class="text-nowrap"><b>Total Harga</b></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $loop = 0;
                                if (!empty($penawaran_detail)) {
                                    foreach ($penawaran_detail as $dp) {
                                        $loop++;
                                ?>
                                        <tr id="tr_<?= $loop ?>">
                                            <td hidden>
                                                <input type="hidden" name="product[<?= $loop ?>][id_penawaran]" id="id_penawaran_<?= $loop ?>" value="<?= $dp['id_penawaran'] ?>">
                                                <input type="hidden" name="product[<?= $loop ?>][product_name]" id="product_name_<?= $loop ?>" value="<?= $dp['product_name'] ?>">
                                                <input type="hidden" name="product[<?= $loop ?>][harga_beli]" value="<?= $dp['harga_beli'] ?>">
                                            </td>

                                            <td>
                                                <select name="product[<?= $loop ?>][id_product]" class="form-control product-select select2" data-loop="<?= $loop ?>" disabled>
                                                    <option value="">-- Pilih Produk --</option>
                                                    <?php foreach ($products as $item): ?>
                                                        <option value="<?= $item['code_lv4'] ?>"
                                                            data-price="<?= $item['propose_price'] ?>"
                                                            data-product="<?= $item['product_name'] ?>"
                                                            data-code="<?= $item['code_lv4'] ?>"
                                                            <?= $item['code_lv4'] == $dp['id_product'] ? 'selected' : '' ?>>
                                                            <?= $item['product_name'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="product[<?= $loop ?>][id_product]" value="<?= $dp['id_product'] ?>">
                                            </td>
                                            <td hidden><input type="text" class="form-control" name="product[<?= $loop ?>][code_lv4]" id="code_lv4_<?= $loop ?>"></td>
                                            <td><input type="number" class="form-control qty-input" name="product[<?= $loop ?>][qty]" id="qty_<?= $loop ?>" value="<?= $dp['qty'] ?>" readonly></td>
                                            <td><input type="text" class="form-control penawaran moneyFormat" name="product[<?= $loop ?>][harga_penawaran]" id="penawaran_<?= $loop ?>" value="<?= $dp['harga_penawaran'] ?>" readonly></td>
                                            <td><input type="text" class="form-control" name="product[<?= $loop ?>][qty_free]" id="qty_free_<?= $loop ?>" readonly></td>
                                            <td><input type="number" class="form-control" name="product[<?= $loop ?>][use_qty_free]" id="use_qty_free_<?= $loop ?>"></td>
                                            <td><input type="text" class="form-control" name="product[<?= $loop ?>][selisih]" id="selisih_<?= $loop ?>" readonly></td>
                                            <td><input type="number" class="form-control" name="product[<?= $loop ?>][pr]" id="pr_<?= $loop ?>"></td>
                                            <td>
                                                <input type="text" class="form-control diskon" name="product[<?= $loop ?>][diskon]" id="diskon_<?= $loop ?>" value="<?= $dp['diskon'] ?>" readonly>
                                                <input type="hidden" class="form-control diskon_nilai" name="product[<?= $loop ?>][diskon_nilai]" id="diskon_nilai<?= $loop ?>" value="<?= $dp['diskon_nilai'] ?>" readonly>
                                                <input type="hidden" class="form-control price_list" name="product[<?= $loop ?>][price_list]" id="price_list<?= $loop ?>" value="<?= $dp['price_list'] ?>" readonly>
                                                <input type="hidden" class="form-control total_pl" name="product[<?= $loop ?>][total_pl]" id="total_pl<?= $loop ?>" value="<?= $dp['total_pl'] ?>" readonly>
                                            </td>
                                            <td>
                                                <select name="product[<?= $loop ?>][pengiriman]" id="pengiriman_<?= $loop ?>" class="form-control select2" required>
                                                    <option value="">-- Pilih --</option>
                                                    <option value="Pabrik" <?= (isset($penawaran['tipe_penawaran']) && $penawaran['tipe_penawaran'] == 'Dropship') ? 'selected' : '' ?>>Pabrik</option>
                                                    <option value="Gudang">Gudang SBF/NBO</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control moneyFormat total-harga" name="product[<?= $loop ?>][total]" id="total_<?= $loop ?>" value="<?= $dp['total'] ?>" readonly></td>
                                        </tr>
                                    <?php }
                                } elseif (!empty($so_detail)) {
                                    foreach ($so_detail as $sd) {
                                        $loop++;
                                    ?>
                                        <tr id="tr_<?= $loop ?>">
                                            <td hidden>
                                                <input type="hidden" name="product[<?= $loop ?>][id_penawaran]" id="id_penawaran_<?= $loop ?>" value="<?= $sd['id_penawaran'] ?>">
                                                <input type="hidden" name="product[<?= $loop ?>][product_name]" id="product_name_<?= $loop ?>" value="<?= $sd['product'] ?>">
                                                <input type="hidden" name="product[<?= $loop ?>][harga_beli]" id="product_name_<?= $loop ?>" value="<?= $sd['harga_beli'] ?>">
                                            </td>

                                            <td>
                                                <select name="product[<?= $loop ?>][id_product]" class="form-control product-select select2" data-loop="<?= $loop ?>" disabled>
                                                    <option value="">-- Pilih Produk --</option>
                                                    <?php foreach ($products as $item): ?>
                                                        <option value="<?= $item['code_lv4'] ?>"
                                                            data-price="<?= $item['propose_price'] ?>"
                                                            data-product="<?= $item['product_name'] ?>"
                                                            data-code="<?= $item['code_lv4'] ?>"
                                                            <?= $item['code_lv4'] == $sd['id_product'] ? 'selected' : '' ?>>
                                                            <?= $item['product_name'] ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="product[<?= $loop ?>][id_product]" value="<?= $sd['id_product'] ?>">
                                            </td>
                                            <td hidden><input type="text" class="form-control" name="product[<?= $loop ?>][code_lv4]" id="code_lv4_<?= $loop ?>"></td>
                                            <td><input type="number" class="form-control qty-input" name="product[<?= $loop ?>][qty]" id="qty_<?= $loop ?>" value="<?= $sd['qty_order'] ?>" readonly></td>
                                            <td><input type="text" class="form-control penawaran moneyFormat" name="product[<?= $loop ?>][harga_penawaran]" id="penawaran_<?= $loop ?>" value="<?= $sd['harga_penawaran'] ?>" readonly></td>
                                            <td><input type="text" class="form-control" name="product[<?= $loop ?>][qty_free]" id="qty_free_<?= $loop ?>" readonly></td>
                                            <td><input type="number" class="form-control" name="product[<?= $loop ?>][use_qty_free]" id="use_qty_free_<?= $loop ?>" value="<?= $sd['use_qty_free'] ?>" <?= (isset($mode) && $mode == 'deal') ? 'readonly' : '' ?>></td>
                                            <td><input type="text" class="form-control" name="product[<?= $loop ?>][selisih]" id="selisih_<?= $loop ?>" value="<?= $sd['qty_propose'] ?>" readonly></td>
                                            <td><input type="number" class="form-control" name="product[<?= $loop ?>][pr]" id="pr_<?= $loop ?>" value="<?= $sd['qty_propose'] ?>" <?= (isset($mode) && $mode == 'deal') ? 'readonly' : '' ?>></td>
                                            <td>
                                                <input type="text" class="form-control diskon" name="product[<?= $loop ?>][diskon]" id="diskon_<?= $loop ?>" value="<?= $sd['diskon_persen'] ?>" readonly>
                                                <input type="hidden" class="form-control diskon_nilai" name="product[<?= $loop ?>][diskon_nilai]" id="diskon_nilai<?= $loop ?>" value="<?= $sd['diskon_nilai'] ?>" readonly>
                                                <input type="hidden" class="form-control price_list" name="product[<?= $loop ?>][price_list]" id="price_list<?= $loop ?>" value="<?= $sd['price_list'] ?>" readonly>
                                                <input type="hidden" class="form-control total_pl" name="product[<?= $loop ?>][total_pl]" id="total_pl<?= $loop ?>" value="<?= $sd['total_pl'] ?>" readonly>
                                            </td>
                                            <td>
                                                <select name="product[<?= $loop ?>][pengiriman]" id="pengiriman_<?= $loop ?>" class="form-control select2" <?= (isset($mode) && $mode == 'deal') ? 'disabled' : '' ?> required>
                                                    <option value="">-- Pilih --</option>
                                                    <option value="Pabrik" <?= ($sd['pengiriman'] == 'Pabrik') ? 'selected' : '' ?>>Pabrik</option>
                                                    <option value="Gudang" <?= ($sd['pengiriman'] == 'Gudang') ? 'selected' : '' ?>>Gudang SBF/NBO</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control total-harga moneyFormat" name="product[<?= $loop ?>][total]" id="total_<?= $loop ?>" value="<?= $sd['total_harga'] ?>" readonly></td>
                                        </tr>
                                <?php }
                                } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Harga</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="total_penawaran" id="total_penawaran" value="<?= isset($penawaran['total_penawaran']) ? $penawaran['total_penawaran'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Harga Price List</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="total_price_list" id="total_price_list" value="<?= isset($penawaran['total_price_list']) ? $penawaran['total_price_list'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Freight Cost</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="freight" id="freight" value="<?= isset($penawaran['freight']) ? $penawaran['freight'] : ''; ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total % Discount</strong></td>
                                    <td colspan="2"><input type="text" class="form-control" name="total_diskon_persen" id="total_diskon_persen" value="<?= isset($penawaran['total_diskon_persen']) ? $penawaran['total_diskon_persen'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Discount Khusus</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="diskon_khusus" id="diskon_khusus" value="<?= isset($penawaran['diskon_khusus']) ? $penawaran['diskon_khusus'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Harga + Freight</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="total_harga_freight" id="total_harga_freight" value="<?= isset($penawaran['total_harga_freight']) ? $penawaran['total_harga_freight'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Total Harga + Freight (Exclude PPN)</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="total_harga_freight_exppn" id="total_harga_freight_exppn" value="<?= isset($penawaran['total_harga_freight_exppn']) ? $penawaran['total_harga_freight_exppn'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>DPP</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="dpp" id="dpp" value="<?= isset($penawaran['dpp']) ? $penawaran['dpp'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>PPn</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="ppn" id="ppn" value="<?= isset($penawaran['ppn']) ? $penawaran['ppn'] : '' ?>" readonly></td>
                                </tr>
                                <tr>
                                    <td colspan="9" class="text-right"><strong>Grand Total</strong></td>
                                    <td colspan="2"><input type="text" class="form-control moneyFormat" name="grand_total" id="grand_total" value="<?= isset($penawaran['grand_total']) ? $penawaran['grand_total'] : '' ?>" readonly></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <hr>

            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <?php if (isset($mode) && $mode == 'add') : ?>
                        <a href="javascript:void(0)" class="btn btn-danger btn-cancel" data-id="<?= isset($penawaran['id_penawaran']) ? $penawaran['id_penawaran'] : '' ?>"><i class="fa fa-times"></i> Batal SO</a>
                        <button type="submit" class="btn btn-success" name="save" id="save">
                            <i class="fa fa-check"></i> Deal SO
                        </button>
                    <?php endif; ?>

                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>


<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
        moneyFormat('.moneyFormat')

        const grandTotal = $('#grand_total').val();
        $('#total_so').val(grandTotal);

        // SAVE SO
        $('#data-form').submit(function(e) {
            e.preventDefault();
            var tgl_so = $('#tgl_so').val()

            if (tgl_so == '') {
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
                                    setTimeout(() => {
                                        // Arahkan ke controller yang generate link WhatsApp
                                        // window.open(`${base_url + active_controller}send_wa_so/${data.no_so}`, '_blank');
                                        window.location.href = base_url + active_controller;
                                    }, 1000);
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

        // APPROVE SO
        $('#deal').click(function(e) {
            e.preventDefault();

            var actionUrl = base_url + active_controller + 'deal_so';
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
                        formData.append('no_so', $('#no_so').val());
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
                                    window.location.href = base_url + active_controller + 'index';
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

        // Trigger penetuan credit limit
        $('#credit_limit, #total_so, #outstanding').on('input', function() {
            updateCreditStatus();
        });

        //buat ambil free stok
        $('.product-select').each(function() {
            const loop = $(this).data('loop');
            const selected = $(this).find('option:selected');
            const code_lv4 = selected.data('code');

            if (code_lv4) {
                $.ajax({
                    url: '<?= base_url('sales_order/get_free_stok') ?>',
                    type: 'POST',
                    data: {
                        code_lv4: code_lv4
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (!res.error) {
                            $(`#code_lv4_${loop}`).val(code_lv4);
                            $(`#qty_free_${loop}`).val(res.qty_free);
                        } else {
                            $(`#code_lv4_${loop}`).val('');
                            $(`#qty_free_${loop}`).val('0');
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil free stock');
                    }
                });
            }
        });

        // buat hitung selisih dan pr 
        $(document).on('input', '[id^="use_qty_free_"]', function() {
            const loop = $(this).attr('id').split('_').pop(); // ambil loop index dari ID

            const qty = parseFloat($(`#qty_${loop}`).val()) || 0;
            const useFree = parseFloat($(`#use_qty_free_${loop}`).val()) || 0;

            const selisih = qty - useFree;

            // Isi nilai selisih dan pr
            $(`#selisih_${loop}`).val(selisih);
            $(`#pr_${loop}`).val(selisih); // atau bisa pakai rumus lain
        });

        $(document).on('click', '.btn-cancel', function(e) {
            e.preventDefault();

            var id_penawaran = $(this).data('id');
            console.log(id_penawaran)

            swal({
                title: "Are you sure?",
                text: "Batalkan SO ini!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, Batalkan!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + 'sales_order/cancel',
                        type: 'POST',
                        data: {
                            id_penawaran: id_penawaran,
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 1) {
                                swal("Success!", response.pesan, "success");
                                window.location.href = base_url + active_controller;
                            } else {
                                swal("Failed", response.pesan, "warning");
                            }
                        },
                        error: function() {
                            swal("Error", "Something went wrong.", "error");
                        }
                    });
                }
            });
        });
    })

    function moneyFormat(e) {
        $(e).inputmask({
            alias: "decimal",
            digits: 2,
            radixPoint: ".",
            autoGroup: true,
            placeholder: "0",
            rightAlign: false,
            allowMinus: false,
            integerDigits: 13,
            groupSeparator: ",",
            digitsOptional: false,
            showMaskOnHover: true,
        })
    }

    function toNumber(val) {
        return parseFloat((val || "0").replace(/[^0-9.-]+/g, '')) || 0;
    }

    function updateCreditStatus() {
        const creditLimit = toNumber($('#credit_limit').val());
        const totalSO = toNumber($('#total_so').val());
        const outstanding = toNumber($('#outstanding').val());

        const selisih = (totalSO + outstanding) - creditLimit;

        let status = "";

        if (creditLimit === 0) {
            status = "Tidak Overlimit";
            $('#status_credit_limit').text(status).removeClass('text-red').addClass('text-green');
            $('#over_limit').val("0");
        } else if (selisih > 0) {
            status = "Overlimit";
            $('#status_credit_limit').text(status).removeClass('text-green').addClass('text-red');
            $('#over_limit').val(selisih.toFixed(2));
        } else {
            status = "Tidak Overlimit";
            $('#status_credit_limit').text(status).removeClass('text-red').addClass('text-green');
            $('#over_limit').val("0");
        }

        // ✅ Simpan ke input hidden agar ikut terkirim
        $('input[name="status_credit_limit"]').val(status);
    }
</script>