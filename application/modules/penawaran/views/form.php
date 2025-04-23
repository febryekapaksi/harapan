<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" autocomplete="off">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-sm-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">No Penawaran</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_penawaran" id="no_penawaran" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Customer</label>
                            </div>
                            <div class="col-md-8">
                                <select name="id_customer" id="id_customer" class="form-control select2">
                                    <option value="">-- Pilih ---</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Sales</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="id_karyawan" id="id_karyawan">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Term Of Payment</label>
                            </div>
                            <div class="col-md-8">
                                <select id="payment_term" name="payment_term" class="form-control select2" required>
                                    <option value="">--Pilih--</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Quotation Date</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="quotation_date" id="quotation_date">
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
                                <td align='center'><b>Free Stok</b></td>
                                <td align='center'><b>Price List</b></td>
                                <td align='center'><b>Harga Penawaran</b></td>
                                <td align='center'><b>% Discount</b></td>
                                <td align='center'><b>Total Harga penawaran</b></td>
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
                            if (!empty($detail_penawaran)) {
                                foreach ($detail_penawaran as $dp) {
                                    $loop++;
                            ?>
                                    <tr id="tr_<?= $loop ?>">
                                        <td>
                                            <select name="product[<?= $loop ?>][id]" class="form-control product-select select2" data-loop="<?= $loop ?>">
                                                <option value="">-- Pilih Produk --</option>
                                                <?php foreach ($products as $item): ?>
                                                    <option value="<?= $item['id'] ?>"
                                                        data-price="<?= $item['price_ref'] ?>"
                                                        data-stock="<?= $item['stok'] ?>"
                                                        <?= $item['id'] == $dp['product_id'] ? 'selected' : '' ?>>
                                                        <?= $item['nama'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control qty-input" name="product[<?= $loop ?>][qty]" id="qty_<?= $loop ?>" value="<?= $dp['qty'] ?>"></td>
                                        <td><input type="text" class="form-control" name="product[<?= $loop ?>][stok]" id="stok_<?= $loop ?>" value="<?= $dp['stok'] ?>" readonly></td>
                                        <td><input type="text" class="form-control" name="product[<?= $loop ?>][price_list]" id="price_<?= $loop ?>" value="<?= $dp['price_list'] ?>" readonly></td>
                                        <td><input type="number" class="form-control penawaran" name="product[<?= $loop ?>][harga_penawaran]" id="penawaran_<?= $loop ?>" value="<?= $dp['harga_penawaran'] ?>"></td>
                                        <td><input type="text" class="form-control" name="product[<?= $loop ?>][diskon]" id="diskon_<?= $loop ?>" value="<?= $dp['diskon'] ?>" readonly></td>
                                        <td><input type="text" class="form-control" name="product[<?= $loop ?>][total]" id="total_<?= $loop ?>" value="<?= $dp['total'] ?>" readonly></td>
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
                                        <select name="product[1][id]" class="form-control product-select select2" data-loop="1">
                                            <option value="">-- Pilih Produk --</option>
                                            <?php foreach ($products as $item): ?>
                                                <option value="<?= $item['code_lv4'] ?>" data-price="<?= $item['price_ref'] ?>" data-stock="<?= $item['max_stok'] ?>">
                                                    <?= $item['nama'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input type="number" class="form-control qty-input" name="product[1][qty]" id="qty_1"></td>
                                    <td><input type="text" class="form-control" name="product[1][stok]" id="stok_1" readonly></td>
                                    <td><input type="text" class="form-control" name="product[1][price_list]" id="price_1" readonly></td>
                                    <td><input type="number" class="form-control penawaran" name="product[1][harga_penawaran]" id="penawaran_1"></td>
                                    <td><input type="text" class="form-control" name="product[1][diskon]" id="diskon_1" readonly></td>
                                    <td><input type="text" class="form-control" name="product[1][total]" id="total_1" readonly></td>
                                    <td align="center">
                                        <button type="button" class="btn btn-sm btn-danger" onclick="DelProduct(1)"><i class="fa fa-trash-o"></i></button>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
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

        // TAMBAH LIST PRODUCT
        let products = <?= json_encode($products) ?>; // kirim dari PHP
        let loop = $('#list_product tr').length; // inisialisasi dari jumlah baris awal

        $('#add-product').click(function() {
            loop++;

            let options = '<option value="">-- Pilih Produk --</option>';
            products.forEach(item => {
                options += `<option value="${item.id}" data-price="${item.price_ref}" data-stock="${item.max_stok}">${item.nama}</option>`;
            });

            let row = `
                <tr id="tr_${loop}">
                    <td>
                        <select name="product[${loop}][id]" class="form-control product-select select2" data-loop="${loop}">
                            ${options}
                        </select>
                    </td>
                    <td><input type="number" class="form-control qty-input" name="product[${loop}][qty]" id="qty_${loop}"></td>
                    <td><input type="text" class="form-control" name="product[${loop}][stok]" id="stok_${loop}" readonly></td>
                    <td><input type="text" class="form-control" name="product[${loop}][price_list]" id="price_${loop}" readonly></td>
                    <td><input type="number" class="form-control penawaran" name="product[${loop}][harga_penawaran]" id="penawaran_${loop}"></td>
                    <td><input type="text" class="form-control" name="product[${loop}][diskon]" id="diskon_${loop}" readonly></td>
                    <td><input type="text" class="form-control" name="product[${loop}][total]" id="total_${loop}" readonly></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="DelProduct(${loop})"><i class="fa fa-trash-o"></i></button>
                    </td>
                </tr>`;
            $('#list_product').append(row);
            $(`#tr_${loop} .select2`).select2({
                width: '100%'
            });
        });

        // fungsi hapus baris
        function DelProduct(id) {
            $('#tr_' + id).remove();
        }

        // saat produk dipilih
        $(document).on('change', '.product-select', function() {
            const loop = $(this).data('loop');
            const selected = $(this).find(':selected');
            const price = selected.data('price') || 0;
            const stock = selected.data('stock') || 0;

            $(`#price_${loop}`).val(price);
            $(`#stok_${loop}`).val(stock);

            hitungTotal(loop);
        });

        // hitung diskon dan total
        $(document).on('input', '.penawaran, .qty-input', function() {
            const loop = $(this).closest('tr').attr('id').split('_')[1];
            hitungTotal(loop);
        });

        function hitungTotal(loop) {
            const qty = parseFloat($(`#qty_${loop}`).val()) || 0;
            const price = parseFloat($(`#price_${loop}`).val()) || 0;
            const offer = parseFloat($(`#penawaran_${loop}`).val()) || 0;

            const diskon = price ? ((price - offer) / price) * 100 : 0;
            const total = qty * offer;

            $(`#diskon_${loop}`).val(diskon.toFixed(2));
            $(`#total_${loop}`).val(total.toFixed(2));
        }


    });

    function DelProduct(id) {
        $('#list_product #tr_' + id).remove();
    }
</script>