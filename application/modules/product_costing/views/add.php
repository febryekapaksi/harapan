<?php
$costing_map = [];
foreach ($costing as $c) {
    $costing_map[$c['element_costing']] = $c['rate'];
}

$costing_rate = [];
$grouped_costing = [];

$current_group = null;
$total_rows = count($costing);

for ($i = 0; $i < $total_rows; $i++) {
    $item = $costing[$i];
    $judul = trim($item['judul']);
    $element = trim(strip_tags($item['element_costing']));
    $rate = floatval($item['rate']);

    // Jika bukan sub-item (bukan diawali a., b., dst), anggap sebagai grup
    if (!preg_match('/^[a-e]\./i', $element)) {
        $current_group = $element;

        // Lihat apakah item berikutnya adalah sub-item
        $next = isset($costing[$i + 1]) ? $costing[$i + 1] : null;
        $next_is_sub = $next && preg_match('/^[a-e]\./i', trim(strip_tags($next['element_costing'])));

        if (!$next_is_sub) {
            // Langsung punya rate sendiri (tanpa sub)
            $costing_rate[$current_group] = $rate;
            $grouped_costing[$current_group] = [$element => $rate];
            $current_group = null; // Reset agar tidak menangkap item lain
        } else {
            // Grup akan diisi oleh sub-item setelah ini
            $costing_rate[$current_group] = 0;
            $grouped_costing[$current_group] = [];
        }
    } elseif ($current_group) {
        // Sub-item dari grup aktif
        $grouped_costing[$current_group][$element] = $rate;
        $costing_rate[$current_group] += $rate;
    }
}

// Untuk itungan rate dropship
$rate_gaji = 0;
if (isset($grouped_costing['A2. Cabang'])) {
    foreach ($grouped_costing['A2. Cabang'] as $label => $rate) {
        if (stripos($label, 'b.') === 0) {
            $rate_gaji = $rate;
            break;
        }
    }
}
?>
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" autocomplete="off">
            <input type="hidden" name="id" value="<?= (!empty($procost->id)) ? $procost->id : '' ?>">
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Product <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <select name="product_id" id="productSelect" class="form-control select">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($product as $item) {
                            $code_lv4 = (!empty($procost->code_lv4)) ? $procost->code_lv4 : '';
                            $selected = ($item['code_lv4'] == $code_lv4) ? 'selected' : '';
                        ?>
                            <option value="<?= $item['code_lv4'] ?>" data-harga="<?= $item['price_ref'] ?>" <?= $selected ?>>
                                <?= $item['nama'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Harga Beli</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat harga_beli" name="harga_beli" value="<?= (!empty($procost->harga_beli)) ? $procost->harga_beli : '' ?>" readonly>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Import</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_import" name="biaya_import" value="<?= (!empty($procost->biaya_import)) ? $procost->biaya_import : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Cabang</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_cabang" name="biaya_cabang" value="<?= (!empty($procost->biaya_cabang)) ? $procost->biaya_cabang : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Logistik</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_logistik" name="biaya_logistik" value="<?= (!empty($procost->biaya_logistik)) ? $procost->biaya_logistik : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya HO</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_ho" name="biaya_ho" value="<?= (!empty($procost->biaya_ho)) ? $procost->biaya_ho : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Marketing</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_marketing" name="biaya_marketing" value="<?= (!empty($procost->biaya_marketing)) ? $procost->biaya_marketing : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Interest</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_interest" name="biaya_interest" value="<?= (!empty($procost->biaya_interest)) ? $procost->biaya_interest : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Biaya Profit</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat biaya_profit" name="biaya_profit" value="<?= (!empty($procost->biaya_profit)) ? $procost->biaya_profit : '' ?>" readonly>
                </div>
            </div>
            <hr>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Product Costing</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat price" name="price" value="<?= (!empty($procost->price)) ? $procost->price : '' ?>" readonly>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Dropship Price Cash</label>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control moneyFormat dropship_price" name="dropship_price" value="<?= (!empty($procost->dropship_price)) ? $procost->dropship_price : '' ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label for="">Dropship Price Tempo</label>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control moneyFormat dropship_tempo" name="dropship_tempo" value="<?= (!empty($procost->dropship_tempo)) ? $procost->dropship_tempo : '' ?>" readonly>
                </div>
            </div>
            <hr>

            <div class="form-group row">
                <div class="col-md-12">
                    <table class='table table-bordered table-striped'>
                        <thead>
                            <tr class='bg-blue'>
                                <td align='center' style="width: 60%;"><b>Nama Kompetitor</b></td>
                                <td align='center' style="width: 30%;"><b>Harga</b></td>
                                <td style="width: 50px;" align='center'>
                                    <?php
                                    echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-kompetitor'));
                                    ?>
                                </td>
                            </tr>
                        </thead>
                        <tbody id='list_kompetitor'>
                            <?php
                            if (isset($kompetitor)) {
                                $loop = 0;
                                foreach ($kompetitor as $kp) {
                                    $loop++;
                                    echo "<tr id='tr_$loop'>";
                                    echo "<td align='left'><input type='text' class='form-control input-sm' name='kompetitor[" . $loop . "][nama]' value='$kp->nama' id='kompetitor" . $loop . "_nama'></td>";
                                    echo "<td align='left'><input type='text' class='form-control moneyFormat input-sm' name='kompetitor[" . $loop . "][harga]' value='$kp->harga' id='kompetitor" . $loop . "_harga'></td>";
                                    echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' data-role='qtip' onClick='return DelKompetitor(" . $loop . ");'><i class='fa fa-trash-o'></i></button></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>

            <div class="form-group row">
                <div class="col-md-3">
                    <label for="">Propose Costing</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control moneyFormat propose_price" value="<?= (!empty($procost->propose_price)) ? $procost->propose_price : '' ?>" name="propose_price">
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select').select2({
            width: '100%',
            dropdownParent: $('#dialog-popup'),
        });
        moneyFormat('.moneyFormat')

        const ImportRate = <?= isset($costing_map['Biaya Import']) ? $costing_map['Biaya Import'] : 0 ?>;
        const CabangRate = <?= isset($costing_rate['A2. Cabang']) ? $costing_rate['A2. Cabang'] : 0 ?>;
        const LogistikRate = <?= isset($costing_rate['A3. Logistik']) ? $costing_rate['A3. Logistik'] : 0 ?>;
        const HORate = <?= isset($costing_rate['B1. Biaya HO']) ? $costing_rate['B1. Biaya HO'] : 0 ?>;
        const MarketingRate = <?= isset($costing_rate['B2. Biaya Marketing']) ? $costing_rate['B2. Biaya Marketing'] : 0 ?>;
        const InterestRate = <?= isset($costing_rate['B3. Interest']) ? $costing_rate['B3. Interest'] : 0 ?>;
        const ProfitRate = <?= isset($costing_rate['B4. Profit']) ? $costing_rate['B4. Profit'] : 0 ?>;
        const GajiTunjanganRate = <?= isset($rate_gaji) ? $rate_gaji  : 0 ?>

        $('#productSelect').on('change', function() {
            const harga = $(this).find(':selected').data('harga') || 0;
            $('.harga_beli').val(harga);

            // Hitung biaya import otomatis
            const biayaImport = (harga * ImportRate) / 100;
            const biayaCabang = (harga * CabangRate) / 100;
            const biayaLogistik = (harga * LogistikRate) / 100;
            const biayaHO = (harga * HORate) / 100;
            const biayaMarketing = (harga * MarketingRate) / 100;
            const biayaInterest = (harga * InterestRate) / 100;
            const biayaProfit = (harga * ProfitRate) / 100;
            const productCosting = harga + biayaImport + biayaCabang + biayaLogistik + biayaHO + biayaMarketing + biayaInterest + biayaProfit;

            // untuk biaya khusus (dropship)
            const biayaGajiTunjangan = (harga * GajiTunjanganRate) / 100;
            const rawDropshipPrice = harga + biayaGajiTunjangan + biayaHO + biayaMarketing;
            const dropshipPrice = Math.ceil(rawDropshipPrice / 100) * 100;
            const rawDropshipTempo = (dropshipPrice * (2 / 100)) + dropshipPrice;
            const dropshipTempo = Math.ceil(rawDropshipTempo / 100) * 100;

            $('.biaya_import').val(biayaImport).trigger('change');
            $('.biaya_cabang').val(biayaCabang).trigger('change');
            $('.biaya_logistik').val(biayaLogistik).trigger('change');
            $('.biaya_ho').val(biayaHO).trigger('change');
            $('.biaya_marketing').val(biayaMarketing).trigger('change');
            $('.biaya_interest').val(biayaInterest).trigger('change');
            $('.biaya_profit').val(biayaProfit).trigger('change');
            $('.price').val(productCosting).trigger('change');
            $('.dropship_price').val(dropshipPrice).trigger('change');
            $('.dropship_tempo').val(dropshipTempo).trigger('change');
        });

        $('#add-kompetitor').click(function() {
            var jumlah = $('#list_kompetitor').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_kompetitor tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control" name="kompetitor[' + loop + '][nama]" id="kompetitor_' + loop + '_nama">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control moneyFormat" name="kompetitor[' + loop + '][harga]" id="kompetitor_' + loop + '_harga">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelKompetitor(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_kompetitor').append(Template);
            moneyFormat('.moneyFormat')
        });

        $('#save').click(function(e) {
            e.preventDefault();
            var product = $('#productSelect').val();

            if (product == '') {
                swal({
                    title: "Error Message!",
                    text: 'Product empty, select first ...',
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
    });

    function DelKompetitor(id) {
        $('#list_kompetitor #tr_' + id).remove();
    }

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
</script>