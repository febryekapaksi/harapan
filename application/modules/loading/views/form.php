<div class="box box-primary">
    <div class="box-body">
        <form method="post" id="data-form">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Pengiriman  -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <select name="pengiriman" id="pengiriman" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                    <option value="Gudang">Gudang SBF/NBO</option>
                                    <option value="Dropship">Dropship</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Kendaraan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Kendaraan</label>
                            </div>
                            <div class="col-md-8">
                                <select name="kendaraan" id="selectKendaraan" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($kendaraan as $item): ?>
                                        <option data-kapasitas="<?= $item->kapasitas ?>" value="<?= $item->nopol ?>"><?= $item->jenis . ' - ' . $item->nopol ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="selectSpk"><i class="fa fa-plus"></i> Pilih SPK</a>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tableSpk">
                                <thead class="bg-blue">
                                    <tr>
                                        <th>No</th>
                                        <th style="min-width: 100px;" class="text-nowrap">No SPK</th>
                                        <th style="min-width: 100px;" class="text-nowrap">No SO</th>
                                        <th style="min-width: 200px;" class="text-nowrap">Customer</th>
                                        <th style="min-width: 300px;">Produk</th>
                                        <th style="min-width: 20px;" class="text-nowrap">Qty</th>
                                        <th style="min-width: 20px;" class="text-nowrap">Berat (Kg)</th>
                                        <th style="min-width: 20px;" class="text-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-right" colspan="5">Total Berat</td>
                                        <td colspan="2"><input type="text" class="form-control input-sm" name="total_berat" id="totalBerat" readonly></td>
                                    </tr>
                                    <tr>
                                        <td class="text-right" colspan="5">Kapasitas</td>
                                        <td colspan="2"><input type="text" class="form-control input-sm" name="kapasitas" id="kapasitas" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
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
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalSpk" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Detail Sales Order</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="tableModalSpk" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>No SPK</th>
                            <th>No SO</th>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Berat (Kg)</th>
                            <th>Tanggal Kirim</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnPilihSpk">Pilih</button>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        // Tombol Pilih SPK
        $('#selectSpk').on('click', function() {
            const pengiriman = $('#pengiriman').val();
            const kendaraan = $('#kendaraan').val();
            if (!pengiriman) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Pilih Opsi Pengiriman terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }
            if (!kendaraan) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Pilih Opsi Kendaraan terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }

            $.ajax({
                url: siteurl + 'loading/get_spk',
                type: 'GET',
                data: {
                    pengiriman
                },
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';
                    data.forEach((item) => {
                        html += `
                    <tr>
                        <td>${item.no_delivery}</td>
                        <td>${item.no_so}</td>
                        <td>${item.name_customer}</td>
                        <td>${item.nama}</td>
                        <td>${item.qty_spk}</td>
                        <td>${item.jumlah_berat}</td>
                        <td>${item.delivery_date}</td>
                        <td>
                        <input type="checkbox" class="select-row" data-item='${JSON.stringify(item)}'>
                        </td>
                    </tr>`;
                    });
                    $('#modalSpk').modal('show');
                    $('#tableModalSpk tbody').html(html);
                    // tampilkan modal setelah data selesai diisi
                }
            });
        });

        // add spk ke main tableSpk
        $('#btnPilihSpk').on('click', function() {
            let i = $('#tableSpk tbody tr').length;

            $('#tableModalSpk .select-row:checked').each(function() {
                const data = JSON.parse($(this).attr('data-item'));

                const row = `
                           <tr>
                            <td class="text-center">${i + 1}</td>
                            <td>
                                <input type="text" class="form-control" name="detail[${i}][no_delivery]" value="${data.no_delivery}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${i}][no_so]" value="${data.no_so}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${i}][customer]" value="${data.name_customer}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${i}][product]" value="${data.nama}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="detail[${i}][qty_spk]" value="${data.qty_spk}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control jumlah-berat" name="detail[${i}][jumlah_berat]" value="${data.jumlah_berat}" readonly>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>
                            </td>

                            <!-- optional hidden fields if needed -->
                            <td hidden>
                                <input type="hidden" name="detail[${i}][id_product]" value="${data.id_product}">
                                <input type="hidden" name="detail[${i}][id_spk_detail]" value="${data.id}">
                            </td>
                        </tr>`;

                $('#tableSpk tbody').append(row);
                hitungTotalBerat();
                i++;
            });

            $('#modalSpk').modal('hide');
        });

        //Hapus bariss
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            hitungTotalBerat();
        });

        $('#selectKendaraan').on('change', function() {
            const kapasitas = $(this).find(':selected').data('kapasitas') || 0;
            $('#kapasitas').val(kapasitas);
            hitungTotalBerat(); // Cek ulang apakah kapasitas terlampaui
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
                        var baseurl = siteurl + 'loading' + '/save';
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

    function getSpk() {
        const pengiriman = $("#pengiriman").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'loading/get_spk',
            data: {
                pengiriman: pengiriman
            },
            success: function(html) {
                $("#no_so").html(html);
            }
        });
    }

    function hitungTotalBerat() {
        let total = 0;

        $('.jumlah-berat').each(function() {
            const berat = parseFloat($(this).val()) || 0;
            total += berat;
        });

        $('#totalBerat').val(total.toFixed(2));

        const kapasitas = parseFloat($('#kapasitas').val()) || 0;

        if (kapasitas > 0 && total > kapasitas) {
            swal({
                title: "Peringatan!",
                text: `Total berat (${total.toFixed(2)} Kg) melebihi kapasitas kendaraan (${kapasitas} Kg).`,
                type: "warning"
            });
        }
    }
</script>