<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="id" value="<?= $sj['id'] ?>">
            <input type="hidden" name="no_surat_jalan" value="<?= $sj['no_surat_jalan'] ?>">
            <input type="hidden" name="no_delivery" value="<?= $sj['no_delivery'] ?>">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Nomor Surat Jalan</label>
                            </div>
                            <div class="col-auto">
                                <p>:&emsp;<?= $sj['no_surat_jalan'] ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Nomor SPK Delivery</label>
                            </div>
                            <div class="col-auto">
                                <p>:&emsp;<?= $sj['no_delivery'] ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Nomor Sales Order</label>
                            </div>
                            <div class="col-auto">
                                <p>:&emsp;<?= $sj['no_so'] ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Nomor Penawaran</label>
                            </div>
                            <div class="col-sm-auto">
                                <p>:&emsp;<?= $sj['id_penawaran'] ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Customer</label>
                            </div>
                            <div class="col-sm-auto">
                                <p>:&emsp;<?= $sj['name_customer'] ?></p>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Alamat Pengiriman</label>
                            </div>
                            <div class="col-sm-auto">
                                <p>:&emsp;<?= $sj['delivery_address'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Tanggal Pengiriman</label>
                            </div>
                            <div class="col-sm-8">
                                <input type="date" name="delivery_date" class="form-control" readonly value="<?= date('Y-m-d', strtotime($sj['delivery_date'])) ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Tanggal Diterima <span class="text-red">*</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="date" name="tgl_diterima" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Diterima Oleh <span class="text-red">*</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="text" name="penerima" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label>Upload Dokumen <span class="text-red">*</span></label>
                            </div>
                            <div class="col-sm-8">
                                <input type="file" name="file_dokumen" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-sm-12">
                        <hr>
                        <h4>List Product</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="bg-blue">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Order</th>
                                        <th class="text-center" style="min-width: 100px;">Qty SPK</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Delivery</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Terkirim</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Retur</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Hilang</th>
                                        <th class="text-center" style="min-width: 200px;">Keterangan Retur/Hilang</th>
                                        <th class="text-center" style="min-width: 200px;">Upload Bukti Retur/Hilang</th>
                                        <th class="text-center" style="min-width: 100px;">Qty Lebih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detail as $i => $row): ?>
                                        <tr>
                                            <td align="center"><?= $i + 1; ?></td>
                                            <td style="min-width: 500px;"><?= $row['product']; ?></td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center" value="<?= $row['qty_so']; ?>" readonly>
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center" value="<?= $row['qty_spk']; ?>" readonly>
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center qty-delivery" name="detail[<?= $i ?>][qty_delivery]" value="<?= $row['qty']; ?>" readonly>
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center qty-terkirim" name="detail[<?= $i ?>][qty_terkirim]" min="0" value="<?= $row['qty']; ?>" readonly>
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center qty-retur" name="detail[<?= $i ?>][qty_retur]" min="0" value="0" oninput="validateQty(this)">
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center qty-hilang" name="detail[<?= $i ?>][qty_hilang]" min="0" value="0" oninput="validateQty(this)">
                                            </td>
                                            <td align="center">
                                                <textarea class="form-control reason-retur" name="detail[<?= $i ?>][reason]" disabled></textarea>
                                            </td>
                                            <td align="center">
                                                <input type="file" class="form-control file-retur" name="detail[<?= $i ?>][file_bukti]" disabled>
                                            </td>
                                            <td align="center">
                                                <input type="number" class="form-control text-center qty-lebih" name="detail[<?= $i ?>][qty_lebih]" min="0" value="0">
                                            </td>
                                            <input type="hidden" name="detail[<?= $i ?>][id_detail]" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][id_so_det]" value="<?= $row['id_so_det'] ?>">
                                            <input type="hidden" name="detail[<?= $i ?>][id_product]" value="<?= $row['id_product'] ?>">
                                            <input type="hidden" class="costbook" name="detail[<?= $i ?>][costbook]" value="<?= $row['costbook']; ?>">
                                            <input type="hidden" class="total-costbook" name="detail[<?= $i ?>][total_costbook]">
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <input type="hidden" id="grandTotal">
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <hr>
                        <div class="table-responsive">
                            <h5>Informasi Jurnal</h5>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr bgcolor='#9acfea'>
                                        <th>
                                            <center>Tanggal</center>
                                        </th>
                                        <th>
                                            <center>Tipe</center>
                                        </th>
                                        <th>
                                            <center>No. COA</center>
                                        </th>
                                        <th>
                                            <center>Nama. COA</center>
                                        </th>
                                        <th>
                                            <center>Debit</center>
                                        </th>
                                        <th>
                                            <center>Kredit</center>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr bgcolor='#DCDCDC'>
                                        <td><input type="date" id="tgl_jurnal1" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                        <td><input type="text" id="type1" name="type[]" value="JV" class="form-control" readonly /></td>
                                        <td><input type="text" id="no_coa1" name="no_coa[]" value="1104-01-03" class="form-control" readonly /></td>
                                        <td><input type="text" id="nama_coa1" name="nama_coa[]" value="Persediaan Barang In Customer" class="form-control" readonly /></td>
                                        <td><input type="hidden" id="debet1" name="debet[]" value="" class="form-control" readonly />
                                            <input type="text" id="debet21" name="debet2[]" value="" class="form-control" readonly />
                                        </td>
                                        <td><input type="hidden" id="kredit1" name="kredit[]" value="0" class="form-control" readonly />
                                            <input type="text" id="kredit21" name="kredit2[]" value="0" class="form-control" readonly />
                                        </td>

                                    </tr>
                                    <tr bgcolor='#DCDCDC'>
                                        <td><input type="date" id="tgl_jurnal2" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                        <td><input type="text" id="type2" name="type[]" value="JV" class="form-control" readonly /></td>
                                        <td><input type="text" id="no_coa2" name="no_coa[]" value="1104-01-02" class="form-control" readonly /></td>
                                        <td><input type="text" id="nama_coa2" name="nama_coa[]" value="Persediaan Barang In Transit" class="form-control" readonly /></td>
                                        <td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control" readonly />
                                            <input type="text" id="debet22" name="debet2[]" value="0" class="form-control" readonly />
                                        </td>
                                        <td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control" readonly />
                                            <input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control" readonly />
                                        </td>

                                    </tr>
                                    <tr bgcolor='#DCDCDC'>
                                        <td colspan="4" align="right"><b>TOTAL</b></td>
                                        <td align="right"><input type="hidden" id="total" name="total" value="" class="form-control" readonly />
                                            <input type="text" id="total31" name="total3" value="" class="form-control" readonly />
                                        </td>
                                        <td align="right"><input type="hidden" id="total2" name="total2" value="" class="form-control" readonly />
                                            <input type="text" id="total41" name="total4" value="" class="form-control" readonly />
                                        </td>
                                    </tr>
                            </table>
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
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('tr').each(function() {
            recalcRow($(this));
        });
        recalcGrandTotal();
        $('#data-form').submit(function(e) {
            e.preventDefault();

            let isValid = true;
            let firstInvalidRow = null;

            $('.qty-delivery').each(function(index) {
                const row = $(this).closest('tr');
                const qtyDelivery = parseInt($(this).val()) || 0;
                const qtyTerkirim = parseInt(row.find('.qty-terkirim').val()) || 0;
                const qtyRetur = parseInt(row.find('.qty-retur').val()) || 0;
                const qtyHilang = parseInt(row.find('.qty-hilang').val()) || 0;
                const total = qtyTerkirim + qtyRetur + qtyHilang;

                if (total !== qtyDelivery) {
                    isValid = false;
                    if (!firstInvalidRow) {
                        firstInvalidRow = row.find('td:nth-child(2)').text().trim(); // kolom ke-2 = product name
                    }
                    // Highlight warning
                    row.find('.qty-terkirim, .qty-retur, .qty-hilang').css('background-color', '#fff3cd');
                } else {
                    row.find('.qty-terkirim, .qty-retur, .qty-hilang').css('background-color', '');
                }

                if (qtyRetur > 0) {
                    const reason = row.find('.reason-retur').val().trim();
                    if (!reason) {
                        isValid = false;
                        firstInvalidRow = row.find('td:nth-child(2)').text().trim();
                        swal("Peringatan", "Produk \"" + firstInvalidRow + "\" memiliki retur tapi alasan belum diisi.", "warning");
                        return;
                    }
                }
            });

            if (!isValid) {
                swal("Peringatan", "Jumlah Terkirim + Retur + Hilang untuk produk \"" + firstInvalidRow + "\" tidak sama dengan Qty Delivery.", "warning");
                return;
            }

            $('tr').each(function() {
                recalcRow($(this));
            });
            recalcGrandTotal();

            // Lanjut swal konfirmasi jika valid
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
            }, function(isConfirm) {
                if (isConfirm) {
                    var formData = new FormData($('#data-form')[0]);
                    var baseurl = siteurl + 'surat_jalan_pabrik/confirm';

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
                                window.location.href = base_url + active_controller;
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
                                title: "Error Message!",
                                text: "An Error Occurred During Process. Please try again..",
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


    // hitung ulang total untuk satu baris
    function recalcRow(row) {
        const qtyTerkirim = parseFloat(row.find('.qty-terkirim').val()) || 0;
        const costbook = parseFloat(row.find('.costbook').val()) || 0;
        const lineTotal = qtyTerkirim * costbook;

        row.find('.total-costbook').val(lineTotal);
    }

    // hitung ulang grand total dari seluruh baris
    function recalcGrandTotal() {
        let sum = 0;
        $('.total-costbook').each(function() {
            sum += parseFloat($(this).val()) || 0;
        });
        $('#grandTotal').val(sum);
        $('#debet1').val(sum);
        $('#debet21').val(sum);
        $('#kredit2').val(sum);
        $('#kredit22').val(sum);
        $('#total31').val(sum);
        $('#total41').val(sum);
    }

    function validateQty(input) {
        const row = input.closest('tr');

        const qtyDelivery = parseInt(row.querySelector('.qty-delivery').value) || 0;
        const qtyRetur = parseInt(row.querySelector('.qty-retur').value) || 0;
        const qtyHilang = parseInt(row.querySelector('.qty-hilang').value) || 0;
        const totalReturHilang = qtyRetur + qtyHilang;

        const reasonInput = row.querySelector('.reason-retur');
        const fileInput = row.querySelector('.file-retur');

        // Validasi nilai negatif
        if (qtyRetur < 0 || qtyHilang < 0) {
            swal("Peringatan", "Qty Retur atau Hilang tidak boleh negatif.", "warning");
            input.value = 0;
            return;
        }

        // Validasi melebihi delivery
        if (totalReturHilang > qtyDelivery) {
            swal("Peringatan", `Jumlah Retur + Hilang (${totalReturHilang}) melebihi Qty Delivery (${qtyDelivery})`, "warning");
            input.value = 0;
            return;
        }

        // Hitung Qty Terkirim
        const qtyTerkirimBaru = qtyDelivery - totalReturHilang;
        row.querySelector('.qty-terkirim').value = qtyTerkirimBaru;

        const $row = $(row);
        recalcRow($row);
        recalcGrandTotal();

        // Aktifkan input reason dan file jika ada retur
        if (qtyRetur > 0 || qtyHilang > 0) {
            reasonInput.disabled = false;
            fileInput.disabled = false;
            reasonInput.required = true;
            fileInput.required = true;
        } else {
            reasonInput.disabled = true;
            fileInput.disabled = true;
            reasonInput.required = false;
            fileInput.required = false;
            reasonInput.value = "";
            fileInput.value = "";
        }

        // Highlight warning
        const highlight = (totalReturHilang < qtyDelivery);
        [row.querySelector('.qty-terkirim'), row.querySelector('.qty-retur'), row.querySelector('.qty-hilang')].forEach(el => {
            el.style.backgroundColor = highlight ? '#fff3cd' : '';
        });
    }
</script>