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

            <!-- Tabel Jurnal -->
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr bgcolor='#9acfea'>
                                    <th><center>Tanggal</center></th>
                                    <th><center>Tipe</center></th>
                                    <th><center>No. COA</center></th>
                                    <th><center>Nama. COA</center></th>
                                    <th><center>Debit</center></th>
                                    <th><center>Kredit</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr bgcolor='#DCDCDC'>
                                    <td><input type="date" id="tgl_jurnal1" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                    <td><input type="text" id="type1" name="type[]" value="JV" class="form-control" readonly /></td>
                                    <td><input type="text" id="no_coa1" name="no_coa[]" value="1104-01-02" class="form-control" readonly /></td>
                                    <td><input type="text" id="nama_coa1" name="nama_coa[]" value="Persediaan Barang In Transit" class="form-control" readonly /></td>
                                    <td><input type="hidden" id="debet1" name="debet[]" value="" class="form-control text-right" readonly />
                                        <input type="text" id="debet21" name="debet2[]" value="" class="form-control text-right" readonly />
                                    </td>
                                    <td><input type="hidden" id="kredit1" name="kredit[]" value="0" class="form-control text-right" readonly />
                                        <input type="text" id="kredit21" name="kredit2[]" value="0" class="form-control text-right" readonly />
                                    </td>
                                </tr>
                                <tr bgcolor='#DCDCDC'>
                                    <td><input type="date" id="tgl_jurnal2" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                    <td><input type="text" id="type2" name="type[]" value="JV" class="form-control" readonly /></td>
                                    <td><input type="text" id="no_coa2" name="no_coa[]" value="1104-01-01" class="form-control" readonly /></td>
                                    <td><input type="text" id="nama_coa2" name="nama_coa[]" value="Persediaan Barang Warehouse" class="form-control" readonly /></td>
                                    <td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control text-right" readonly />
                                        <input type="text" id="debet22" name="debet2[]" value="0" class="form-control text-right" readonly />
                                    </td>
                                    <td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control text-right" readonly />
                                        <input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control text-right" readonly />
                                    </td>
                                </tr>
                                <tr bgcolor='#DCDCDC'>
                                    <td colspan="4" align="right"><b>TOTAL</b></td>
                                    <td align="right"><input type="hidden" id="total" name="total" value="" class="form-control text-right" readonly />
                                        <input type="text" id="total31" name="total3" value="" class="form-control text-right" readonly />
                                    </td>
                                    <td align="right"><input type="hidden" id="total2" name="total2" value="" class="form-control text-right" readonly />
                                        <input type="text" id="total41" name="total4" value="" class="form-control text-right" readonly />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

        // Disable save button by default
        $('#save').prop('disabled', true);

        $('#spk_delivery').on('change', function() {
            const no_delivery = $(this).val();

            if (!no_delivery) {
                $('#tableSpk tbody').empty();
                $('#delivery_address').val('');
                $('#no_so').val('');
                $('#no_delivery').val('');
                $('#save').prop('disabled', true);
                return;
            }

            // SweetAlert konfirmasi PO sudah incoming
            swal({
                title: "Konfirmasi",
                text: "Apakah PO untuk SPK ini sudah di-incoming?",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ya, sudah incoming",
                cancelButtonText: "Belum",
                closeOnConfirm: true,
                closeOnCancel: true
            }, function(isConfirm) {
                if (isConfirm) {
                    // PO sudah incoming — load data dan aktifkan save
                    loadSpkDetail(no_delivery);
                } else {
                    // PO belum incoming — kosongkan tabel dan disable save
                    $('#tableSpk tbody').empty();
                    $('#delivery_address').val('');
                    $('#no_so').val('');
                    $('#no_delivery').val('');
                    $('#save').prop('disabled', true);
                    swal("Info", "Silakan incoming PO terlebih dahulu sebelum membuat Surat Jalan.", "info");
                }
            });
        });

        function loadSpkDetail(no_delivery) {
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
                        $('#save').prop('disabled', true);
                        return;
                    }

                    // Set data ke form
                    $('#delivery_address').val(data.header.delivery_address || '');
                    $('#pengiriman').val(data.header.pengiriman || '');
                    $('#no_delivery').val(data.header.no_delivery || '');
                    $('#no_so').val(data.header.no_so || '');

                    let html = '';
                    let totalCostbook = 0;

                    data.detail.forEach((item, index) => {
                        let costbook = parseFloat(item.costbook) || 0;
                        let qty = parseFloat(item.qty_spk) || 0;
                        totalCostbook += (qty * costbook);

                        html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <input type="hidden" name="detail[${index}][id]" value="${item.id}">
                            <input type="hidden" name="detail[${index}][id_product]" value="${item.id_product}">
                            <input type="hidden" name="detail[${index}][id_so_det]" value="${item.id_so_det}">
                            <input type="hidden" name="detail[${index}][costbook]" value="${costbook}">
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

                    // Set jurnal values
                    $('#debet1').val(number_format(totalCostbook));
                    $('#debet21').val(number_format(totalCostbook));
                    $('#kredit2').val(number_format(totalCostbook));
                    $('#kredit22').val(number_format(totalCostbook));
                    $('#total31').val(number_format(totalCostbook));
                    $('#total41').val(number_format(totalCostbook));

                    // Aktifkan tombol save
                    $('#save').prop('disabled', false);
                },
                error: function() {
                    swal("Error", "Gagal mengambil data SPK!", "error");
                    $('#save').prop('disabled', true);
                }
            });
        }

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

    function number_format(number, decimals, dec_point, thousands_sep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
</script>