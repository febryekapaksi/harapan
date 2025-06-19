<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <input type="hidden" name="id" value="<?= isset($sj['id']) ? $sj['id'] : '' ?>">
            <input type="hidden" name="no_surat_jalan" value="<?= isset($sj['no_surat_jalan']) ? $sj['no_surat_jalan'] : '' ?>">
            <input type="hidden" name="no_delivery" id="no_delivery" value="<?= isset($sj['no_delivery']) ? $sj['no_delivery'] : '' ?>">
            <input type="hidden" name="no_so" id="no_so" value="<?= isset($sj['no_so']) ? $sj['no_so'] : '' ?>">
            <input type="hidden" name="pengiriman" id="pengiriman" value="<?= isset($sj['pengiriman']) ? $sj['pengiriman'] : '' ?>">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Nomor Muat Kendaraan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">No Muat Kendaraan <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_loading" id="no_loading" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($loading as $load): ?>
                                        <option value="<?= $load['no_loading'] ?>" <?= (isset($sj['no_loading']) && $sj['no_loading'] == $load['no_loading']) ? 'selected' : '' ?>>
                                            <?= $load['no_loading'] . " - " . $load['nopol'] . " - " . date('d/M/Y', strtotime($load['tanggal_muat'])) ?>
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

                <div class="col-md-12">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success btn-get-spk" id="getSpk"><i class="fa fa-plus"></i> Pilih SPK</a>
                    </div>
                    <hr>
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
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
<div class="modal fade" id="modalViewLoading" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close text-white" data-dismiss="modal"><i class="fa fa-times"></i></button>
                <h4 class="modal-title"><b>Detail Loading</b></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-3">
                    <tr>
                        <th>Pengiriman</th>
                        <td id="view-pengiriman"></td>
                        <th>Kendaraan</th>
                        <td id="view-kendaraan"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Muat</th>
                        <td id="view-tgl-muat"></td>
                        <th>Total Berat</th>
                        <td id="view-total-berat"></td>
                    </tr>
                </table>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr class="bg-light">
                            <th>No SPK</th>
                            <th>No SO</th>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Berat (Kg)</th>
                        </tr>
                    </thead>
                    <tbody id="view-detail-body">
                        <!-- diisi via JS -->
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="pilihSpk">Pilih SPK</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let groupedSpk = {}; // simpan SPK hasil AJAX untuk digunakan saat pilih
        $('.select2').select2({
            width: '100%'
        });

        // Tombol Get SPK
        $(document).on('click', '#getSpk', function() {
            const id = $('#no_loading').val();
            const tgl = $('#delivery_date').val();

            if (!id || !tgl) {
                swal("Peringatan", "Silakan pilih No Muat Kendaraan & Tanggal terlebih dahulu.", "warning");
                return;
            }

            $.ajax({
                url: siteurl + 'surat_jalan/get_spk',
                type: 'GET',
                data: {
                    no_loading: id
                },
                success: function(res) {
                    const data = JSON.parse(res);

                    // Header
                    $('#view-pengiriman').text(data.header.pengiriman);
                    $('#view-kendaraan').text(data.header.nopol);
                    $('#view-tgl-muat').text(data.header.tanggal_muat);
                    $('#view-total-berat').text(parseFloat(data.header.total_berat).toFixed(2));

                    // Grouping
                    groupedSpk = {};
                    const grouped = {};
                    data.detail.forEach(row => {
                        if (!grouped[row.no_delivery]) {
                            grouped[row.no_delivery] = {
                                no_delivery: row.no_delivery,
                                no_so: row.no_so,
                                customer: row.customer,
                                alamat: row.alamat,
                                pengiriman: row.pengiriman,
                                weight: row.weight,
                                items: []
                            };
                        }
                        grouped[row.no_delivery].items.push(row);
                    });
                    groupedSpk = grouped;

                    // Tampilkan ke tabel modal
                    let html = '';
                    Object.keys(grouped).forEach(no_spk => {
                        const group = grouped[no_spk];

                        // Header SPK + radio
                        html += `
                        <tr style="background-color:#f0f0f0; font-weight:bold;">
                            <td colspan="6">SPK : ${no_spk} - ${group.customer}</td>
                            <td>
                                <input type="radio" name="spk_selected" class="select-spk" value="${no_spk}">
                            </td>
                        </tr>
                    `;

                        // Detail produk
                        group.items.forEach(item => {
                            html += `
                            <tr>
                                <td>${item.no_delivery}</td>
                                <td>${item.no_so}</td>
                                <td>${item.customer}</td>
                                <td>${item.product}</td>
                                <td>${item.qty_spk}</td>
                                <td>${parseFloat(item.jumlah_berat).toFixed(2)}</td>
                                <td></td>
                                <td hidden>${item.alamat}</td>
                                <td hidden>${item.pengiriman}</td>
                                <td hidden>${item.weight}</td>
                                <td hidden>${item.id_so_det}</td>
                            </tr>
                        `;
                        });
                    });

                    $('#view-detail-body').html(html);
                    $('#modalViewLoading').modal('show');
                },
                error: function() {
                    swal("Error", "Gagal mengambil data detail.", "error");
                }
            });
        });

        // Tombol pilih SPK
        $('#pilihSpk').on('click', function() {
            const selectedSpk = $('input[name="spk_selected"]:checked').val();

            if (!selectedSpk || !groupedSpk[selectedSpk]) {
                swal("Peringatan", "Silakan pilih salah satu SPK terlebih dahulu.", "warning");
                return;
            }

            // ✅ Set alamat customer ke textarea
            $('#delivery_address').val(groupedSpk[selectedSpk].alamat || '');
            $('#pengiriman').val(groupedSpk[selectedSpk].pengiriman || '');
            $('#no_delivery').val(groupedSpk[selectedSpk].no_delivery || '');
            $('#no_so').val(groupedSpk[selectedSpk].no_so || '');

            let html = '';
            html += `
                <tr style="background-color:#f0f0f0; font-weight:bold;">
                    <td colspan="7" style="background-color:#f0f0f0; font-weight:bold;">
                        SPK : ${selectedSpk} - ${groupedSpk[selectedSpk].customer}
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" id="hapusSpk"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            `;

            groupedSpk[selectedSpk].items.forEach((data, index) => {
                html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <input type="hidden" name="detail[${index}][id]" value="${data.id}">
                                <input type="hidden" name="detail[${index}][id_product]" value="${data.id_product}">
                                <input type="hidden" name="detail[${index}][id_so_det]" value="${data.id_so_det}">
                                <input type="text" class="form-control" name="detail[${index}][no_delivery]" value="${data.no_delivery}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${index}][no_so]" value="${data.no_so}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${index}][customer]" value="${data.customer}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${index}][product]" value="${data.product}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${index}][qty]" value="${data.qty_spk}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${index}][total_berat]" value="${parseFloat(data.total_berat).toFixed(2)}" readonly>
                                <input type="hidden" class="form-control" name="detail[${index}][weight]" value="${parseFloat(data.weight).toFixed(2)}" readonly>
                            </td>
                            <td></td>

                        </tr>
                    `;
            });


            $('#tableSpk tbody').html(html);
            $('#getSpk').prop('disabled', true);
            $('#modalViewLoading').modal('hide');

        });

        // Tombol hapus SPK
        $(document).on('click', '#hapusSpk', function() {
            $('#tableSpk tbody').empty();
            $('#delivery_address').val('');
            $('#getSpk').prop('disabled', false);
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
</script>