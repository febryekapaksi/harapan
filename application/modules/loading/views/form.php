<?php
$isConfirm = (isset($mode) && $mode == 'confirm_qty');
$isConfirmBerat = (isset($mode) && $mode == 'confirm_berat');
$isApproval = (isset($mode) && $mode == 'approval');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <form method="post" id="data-form">
            <input type="hidden" name="id" id="id" value="<?= isset($loading['id']) ? $loading['id'] : '' ?>">
            <input type="hidden" name="id_loading" value="<?= isset($loading['no_loading']) ? $loading['no_loading'] : '' ?>">
            <div class="col-md-12">
                <div class="row">
                    <!-- <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <select name="pengiriman" id="pengiriman" class="form-control select" <?= $isConfirm || $isConfirmBerat || $isApproval ? 'disabled' : '' ?>>
                                    <option value="">-- Pilih --</option>
                                    <option value="Gudang" <?= (isset($loading['pengiriman']) && $loading['pengiriman'] == "Gudang") ? 'selected' : '' ?>>Gudang SBF/NBO</option>
                                    <option value="Pabrik" <?= (isset($loading['pengiriman']) && $loading['pengiriman'] == "Pabrik") ? 'selected' : '' ?>>Pabrik</option>
                                </select>
                                <?php if ($isConfirm || $isConfirmBerat): ?>
                                    <input type="hidden" name="pengiriman" value="<?= isset($loading['pengiriman']) ? $loading['pengiriman'] : '' ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div> -->
                    <div class="col-md-6">
                        <!-- Kendaraan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Kendaraan</label>
                            </div>
                            <div class="col-md-8">
                                <select name="kendaraan" id="selectKendaraan" class="form-control select" <?= $isConfirm || $isConfirmBerat || $isApproval ? 'disabled' : '' ?>>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($kendaraan as $item): ?>
                                        <option data-kapasitas="<?= $item->kapasitas ?>" value="<?= $item->nopol ?>"
                                            <?= (isset($loading['nopol']) == $item->nopol) ? 'selected' : '' ?>><?= $item->jenis . ' - ' . $item->nopol ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($isConfirm || $isConfirmBerat): ?>
                                    <!-- Hidden input untuk tetap mengirimkan value saat disabled -->
                                    <input type="hidden" name="kendaraan" value="<?= isset($loading['nopol']) ? $loading['nopol'] : '' ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Tanggal Muat -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Muat</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="tanggal_muat" id="tanggal_muat" class="form-control" value="<?= isset($loading['tanggal_muat']) ? date('Y-m-d', strtotime($loading['tanggal_muat'])) : '' ?>" required <?= $isConfirm || $isConfirmBerat || $isApproval ? 'readonly' : '' ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success <?= $isConfirm || $isConfirmBerat || $isApproval ? 'disabled' : '' ?>" id="selectSpk"><i class="fa fa-plus"></i> Pilih SPK</a>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tableSpk">
                                <thead class="bg-blue">
                                    <tr>
                                        <th style="min-width: 100px;" class="text-nowrap">No SPK</th>
                                        <th style="min-width: 100px;" class="text-nowrap">No SO</th>
                                        <th style="min-width: 200px;" class="text-nowrap" hidden>Customer</th>
                                        <th style="min-width: 300px;">Produk</th>
                                        <th style="min-width: 20px;" class="text-center">Qty Muat</th>
                                        <th style="min-width: 20px;" class="text-center">Berat (Kg)</th>
                                        <?php if (isset($mode) && $mode == 'confirm_qty') { ?>
                                            <th style="min-width: 20px;" class="text-center">Qty Aktual</th>
                                            <th style="min-width: 20px;" class="text-center">Stock Aktual</th>
                                            <th style="min-width: 20px;" class="text-center">Keterangan</th>
                                        <?php  } else if (isset($mode) && $mode == 'confirm_berat') { ?>
                                            <th style="min-width: 20px;" class="text-center">Berat Aktual</th>
                                            <th style="min-width: 20px;" class="text-center">Keterangan</th>
                                        <?php } else { ?>
                                            <th style="min-width: 20px;" class="text-center"></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (isset($detail)) {
                                        $grouped = [];

                                        // Grouping manual by no_delivery
                                        foreach ($detail as $row) {
                                            $grouped[$row['no_delivery']][] = $row;
                                        }

                                        $i = 0;
                                        foreach ($grouped as $no_delivery => $rows):
                                            $customer_name = $rows[0]['customer'];
                                            $sales = $rows[0]['nama_sales'];
                                    ?>
                                            <!-- Header SPK -->
                                            <tr style="background-color:#f0f0f0; font-weight:bold;">
                                                <td colspan="8">No SPK : <?= $no_delivery ?> - <?= $customer_name ?> / Sales : <?= $sales ?></td>
                                            </tr>

                                            <!-- Baris produk -->
                                            <?php foreach ($rows as $row) :
                                                $key = $row['no_so'] . '|' . $row['no_delivery'];
                                                $isUsed = in_array($key, $usedKeys);
                                                $berat = (float)($row['jumlah_berat'] ?? 0);
                                                $qty   = (float)($row['qty_muat'] ?? 0);
                                                $weight = ($qty > 0) ? ($berat / $qty) : 0;
                                            ?>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="detail[<?= $i ?>][no_delivery]" value="<?= $row['no_delivery'] ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="detail[<?= $i ?>][no_so]" value="<?= $row['no_so'] ?>" readonly>
                                                    </td>
                                                    <td hidden>
                                                        <input type="text" class="form-control" name="detail[<?= $i ?>][customer]" value="<?= $row['customer'] ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="detail[<?= $i ?>][product]" value="<?= $row['product'] ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control text-center qty-muat" name="detail[<?= $i ?>][qty_muat]" value="<?= $row['qty_muat'] ?>" readonly>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control jumlah-berat text-center" name="detail[<?= $i ?>][jumlah_berat]" value="<?= $row['jumlah_berat'] ?>" readonly>
                                                    </td>

                                                    <?php if (isset($mode) && $mode == 'confirm_qty') { ?>
                                                        <td>
                                                            <input type="number" class="form-control text-center qty-aktual" name="detail[<?= $i ?>][qty_aktual]" value="">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control text-center stock-aktual" name="detail[<?= $i ?>][stock_aktual]"
                                                                value="<?= isset($detail[$i]['stock_aktual']) ? (float)$detail[$i]['stock_aktual'] : 0 ?>" readonly>
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="detail[<?= $i ?>][keterangan]"></textarea>
                                                        </td>
                                                    <?php } else if (isset($mode) && $mode == 'confirm_berat') { ?>
                                                        <td>
                                                            <input type="number" class="form-control text-center berat-aktual" name="detail[<?= $i ?>][berat_aktual]" value="">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control" name="detail[<?= $i ?>][keterangan]"><?= $row['keterangan'] ?></textarea>
                                                        </td>
                                                    <?php } else { ?>
                                                        <td class="text-center">
                                                            <button type=" button" class="btn btn-danger btn-sm remove-row" <?= $isUsed ? 'disabled' : '' ?>><i class="fa fa-trash"></i></button>
                                                        </td>
                                                    <?php } ?>

                                                    <!-- hidden fields -->
                                                    <td hidden>
                                                        <input type="hidden" name="detail[<?= $i ?>][id]" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="detail[<?= $i ?>][id_spk_detail]" value="<?= $row['id_spk_detail'] ?>">
                                                        <input type="hidden" name="detail[<?= $i ?>][id_product]" value="<?= $row['id_product'] ?>">
                                                        <input type="hidden" name="detail[<?= $i ?>][no_loading]" value="<?= $row['no_loading'] ?>">
                                                        <input type="hidden" name="detail[<?= $i ?>][weight]" value="<?= $weight ?>">
                                                    </td>
                                                </tr>
                                    <?php
                                                $i++;
                                            endforeach;
                                        endforeach;
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-right" colspan="4">Total Berat</td>
                                        <td colspan="3"><input type="text" class="form-control input-sm" name="total_berat" id="totalBerat" value="" readonly></td>
                                    </tr>
                                    <tr>
                                        <td class="text-right" colspan="4">Kapasitas</td>
                                        <td colspan="3"><input type="text" class="form-control input-sm" name="kapasitas" id="kapasitas" value="<?= isset($loading['kapasitas']) ? number_format($loading['kapasitas']) : '' ?>" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12 text-center">
                        <?php if (isset($mode) && $mode == 'confirm_qty') { ?>
                            <button type="button" class="btn btn-success" name="confirm" id="confirm"><i class="fa fa-save"></i> Confirm</button>
                        <?php } elseif (isset($mode) && $mode == 'confirm_berat') { ?>
                            <button type="button" class="btn btn-success" name="confirm_berat" id="confirm_berat"><i class="fa fa-save"></i> Confirm</button>
                        <?php } elseif (isset($mode) && $mode == 'approval') { ?>
                            <button type="button" class="btn btn-success" name="approve" id="approve"><i class="fa fa-check-square-o"></i> Approve</button>
                            <a class="btn btn-danger reject" name="reject" id="reject" onclick="Reject()"><i class="fa fa-ban"></i> Reject</a>
                        <?php } else { ?>
                            <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                        <?php } ?>
                        <a class="btn btn-default" onclick="window.history.back(); return false;">
                            <i class="fa fa-reply"></i> Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<style>
    .modal-content {
        display: flex;
        flex-direction: column;
        height: 90vh;
    }

    .modal-body {
        flex: 1 1 auto;
        overflow-y: auto;
    }
</style>
<!-- Modal -->
<div class="modal fade" id="modalSpk" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Detail SPK Delivery</h4>
            </div>
            <div class="modal-body">
                <div class="form-group text-right">
                    <div class="input-group" style="width: 250px; float: right;">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" id="searchSpk" class="form-control" placeholder="Cari No SO / Customer ...">
                    </div>
                </div>
                <br>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tableModalSpk" style="width: 100%;">
                        <thead class="bg-blue">
                            <tr>
                                <th>No SO</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th>Qty Belum Muat</th>
                                <th>Berat (Kg)</th>
                                <th>Tanggal SPK</th>
                                <th hidden></th>
                                <th hidden></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnPilihSpk">Pilih</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="reject-modal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-reject">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title" id="rejectModalLabel">Alasan Penolakan</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reject_id">
                    <div class="form-group">
                        <label for="reason">Alasan:</label>
                        <textarea id="reason" name="reason" class="form-control" rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Tolak</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        let selectedIds = [];
        hitungTotalBerat();

        $('.select').select2({
            width: '100%'
        });

        // Filter manual untuk tabel modal
        $('#searchSpk').on('keyup', function() {
            const kw = $(this).val().toLowerCase().trim();
            const $rows = $('#tableModalSpk tbody tr');

            let $currentHeader = null;
            let groupHasMatch = false;

            $rows.each(function() {
                const $tr = $(this);
                const isHeader = $tr.hasClass('spk-header');

                if (isHeader) {
                    // Tutup grup sebelumnya: tampilkan/hilangkan header sesuai hasil
                    if ($currentHeader) $currentHeader.toggle(groupHasMatch);

                    // Mulai grup baru
                    $currentHeader = $tr;
                    groupHasMatch = false;

                    // Sembunyikan dulu header; akan ditampilkan bila ada detail yang match
                    $tr.hide();
                    return;
                }

                // Baris detail: cek kolom Customer (index 1)
                const customer = $tr.children('td').eq(1).text().toLowerCase();
                const match = kw === '' || customer.includes(kw);

                $tr.toggle(match);
                if (match) groupHasMatch = true;
            });

            // Finalisasi grup terakhir
            if ($currentHeader) $currentHeader.toggle(groupHasMatch);

            // Jika input kosong, tampilkan semua header lagi
            if (kw === '') {
                $('#tableModalSpk tbody tr.spk-header').show();
            }
        });

        // Tombol Pilih SPK
        $('#selectSpk').on('click', function() {
            // const pengiriman = $('#pengiriman').val();
            const kendaraan = $('#selectKendaraan').val();
            const tanggal = $('#tanggal_muat').val();
            if (!tanggal) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Tentukan Tanggal Muat terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: true
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
                    allowOutsideClick: true
                });
                return;
            }


            $('#tableSpk tbody tr').each(function() {
                const id_spk_detail = $(this).find('input[name*="[id_spk_detail]"]').val();
                if (id_spk_detail) {
                    selectedIds.push(id_spk_detail);
                }
            });

            $.ajax({
                url: siteurl + 'loading/get_spk',
                type: 'GET',
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';
                    let currentSpk = '';

                    data.forEach((item) => {
                        // Skip jika id_spk_detail sudah pernah dipilih
                        if (selectedIds.includes(item.id.toString())) return;

                        if (item.no_delivery !== currentSpk) {
                            html += `
                                    <tr class="spk-header" data-spk="${item.no_delivery}" style="background-color:#f0f0f0; font-weight:bold;">
                                        <td colspan="8">No SPK : ${item.no_delivery} / Sales : ${item.nama_sales}</td>
                                    </tr>
                                `;
                            currentSpk = item.no_delivery;
                        }

                        html += `
                                <tr>
                                    <td>${item.no_so}</td>
                                    <td>${item.name_customer}</td>
                                    <td>${item.nama}</td>
                                    <td>${item.qty_belum_muat}</td>
                                    <td>${item.jumlah_berat}</td>
                                    <td>${item.tanggal_spk}</td>
                                    <td hidden>${item.id}</td>
                                    <td hidden>${item.weight}</td>
                                    <td class="text-center">
                                        <input type="checkbox" class="select-row" data-item='${JSON.stringify(item)}'>
                                    </td>
                                </tr>
                            `;
                    });

                    $('#modalSpk').modal('show');
                    $('#tableModalSpk tbody').html(html);
                }

            });
        });

        // add spk ke main tableSpk
        $('#btnPilihSpk').on('click', function() {
            const grouped = {};
            let detailIndex = $('#tableSpk tbody input[name*="[id_spk_detail]"]').length; // hanya hitung baris produk

            // Grouping data berdasarkan no_delivery (SPK)
            $('#tableModalSpk .select-row:checked').each(function() {
                const data = JSON.parse($(this).attr('data-item'));
                if (!grouped[data.no_delivery]) {
                    grouped[data.no_delivery] = [];
                }
                grouped[data.no_delivery].push(data);
            });

            // Render hasil grouping ke dalam tabel
            Object.keys(grouped).forEach((no_delivery) => {
                const group = grouped[no_delivery];
                const customer = group[0].name_customer;
                const sales = group[0].nama_sales;

                // Baris header SPK
                let groupRow = `
            <tr style="background-color:#f0f0f0; font-weight:bold;">
                <td colspan="8">No SPK : ${no_delivery} - ${customer} / Sales : ${sales}</td>
            </tr>
        `;
                $('#tableSpk tbody').append(groupRow);

                // Baris detail produk
                group.forEach((data) => {
                    let row = `
                <tr>
                    <td hidden>
                        <input type="text" class="form-control" name="detail[${detailIndex}][no_delivery]" value="${data.no_delivery}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="detail[${detailIndex}][no_so]" value="${data.no_so}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="detail[${detailIndex}][customer]" value="${data.name_customer}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="detail[${detailIndex}][product]" value="${data.nama}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control qty-muat" name="detail[${detailIndex}][qty_muat]" value="${data.qty_belum_muat}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control jumlah-berat" name="detail[${detailIndex}][jumlah_berat]" value="${data.jumlah_berat}" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>
                    </td>
                    <td hidden>
                        <input type="hidden" name="detail[${detailIndex}][id_product]" value="${data.id_product}">
                        <input type="hidden" name="detail[${detailIndex}][id_spk_detail]" value="${data.id}">
                        <input type="hidden" name="detail[${detailIndex}][weight]" value="${data.weight}">
                    </td>
                </tr>
            `;
                    $('#tableSpk tbody').append(row);
                    detailIndex++;
                });
            });

            hitungTotalBerat();
            $('#modalSpk').modal('hide');
        });

        //Hapus bariss
        $(document).on('click', '.remove-row', function() {
            const tr = $(this).closest('tr');
            const no_delivery = tr.find('input[name*="[no_delivery]"]').val();
            const id = tr.find('input[name*="[id]"]').val();

            // Kirim permintaan untuk hapus dari loading_delivery_detail
            if (id) {
                $.ajax({
                    url: siteurl + 'loading/delete_detail',
                    type: 'POST',
                    data: {
                        id
                    },
                    success: function(res) {
                        try {
                            const response = JSON.parse(res);
                            if (response.status === 'success') {
                                swal({
                                    title: "Sukses!",
                                    text: "Data berhasil dihapus.",
                                    type: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                setTimeout(function() {
                                    location.reload(); // Reload halaman agar get_spk tampilkan data terbaru
                                }, 2100);
                            } else {
                                swal({
                                    title: "Gagal!",
                                    text: response.message || "Terjadi kesalahan saat menghapus data.",
                                    type: "error"
                                });
                            }
                        } catch (e) {
                            swal({
                                title: "Error!",
                                text: "Respon server tidak valid.",
                                type: "error"
                            });
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error!",
                            text: "Gagal terhubung ke server.",
                            type: "error"
                        });
                    }
                });
            }

            // Hapus baris produk dari DOM
            tr.remove();

            // Hapus baris header SPK jika tidak ada sisa baris
            const remainingRows = $('#tableSpk tbody tr').filter(function() {
                const val = $(this).find('input[name*="[no_delivery]"]').val();
                return val === no_delivery;
            });
            if (remainingRows.length === 0) {
                $('#tableSpk tbody tr').filter(function() {
                    return $(this).text().includes(no_delivery) && $(this).find('input').length === 0;
                }).remove();
            }

            hitungTotalBerat();
        });

        $('#selectKendaraan').on('change', function() {
            const kapasitas = $(this).find(':selected').data('kapasitas') || 0;
            $('#kapasitas').val(kapasitas);
            hitungTotalBerat(); // Cek ulang apakah kapasitas terlampaui
        });

        // button save
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
                                    let id_loading = data.id_loading;
                                    if (id_loading) {
                                        window.open(`${base_url}${active_controller}print/${id_loading}`, '_blank');
                                    }
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

        //button confirm 
        $('#confirm').on('click', function(e) {
            e.preventDefault();

            var qty_aktual = $('.qty-aktual').val()

            if (qty_aktual == '') {
                swal({
                    title: "Error Message!",
                    text: 'Qty Aktual kosong, isi terlebih dulu ...',
                    type: "warning"
                });

                $('#confirm').prop('disabled', false);
                return false;
            }

            // Jika valid, lanjut swal konfirmasi
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
                    var baseurl = siteurl + 'loading/save_confirm_qty';

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
                                text: 'An Error Occurred During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be processed again :)", "error");
                    return false;
                }
            });
        });

        $('#confirm_berat').on('click', function(e) {
            e.preventDefault();

            var berat_aktual = $('.berat-aktual').val()

            if (berat_aktual == '') {
                swal({
                    title: "Error Message!",
                    text: 'Berat Aktual kosong, isi terlebih dulu ...',
                    type: "warning"
                });

                $('#confirm_berat').prop('disabled', false);
                return false;
            }

            // Jika valid, lanjut swal konfirmasi
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
                    var baseurl = siteurl + 'loading/save_confirm_berat';

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
                                text: 'An Error Occurred During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be processed again :)", "error");
                    return false;
                }
            });
        })

        //button approval
        $('#approve').on('click', function(e) {
            // Jika valid, lanjut swal konfirmasi
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
                    var baseurl = siteurl + 'loading/approve';

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
                                text: 'An Error Occurred During Process. Please try again..',
                                type: "warning",
                                timer: 7000
                            });
                        }
                    });
                } else {
                    swal("Cancelled", "Data can be processed again :)", "error");
                    return false;
                }
            });
        })

        $('#form-reject').submit(function(e) {
            e.preventDefault();

            const id = $('#reject_id').val();
            const reason = $('#reason').val().trim();

            if (reason === '') {
                alert('Alasan penolakan harus diisi.');
                return;
            }

            // Konfirmasi kedua pakai SweetAlert
            swal({
                title: "Konfirmasi Penolakan",
                text: "Yakin ingin menolak data dengan alasan berikut?\n\n" + reason,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Tolak",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (!isConfirm) return;

                // Sembunyikan modal input
                $('#reject-modal').modal('hide');

                // Kirim AJAX ke backend
                $.ajax({
                    url: base_url + active_controller + 'reject/' + id,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        reason
                    },
                    success: function(res) {
                        if (res.save == 1) {
                            swal({
                                title: "Ditolak!",
                                text: "Data berhasil ditolak.",
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                window.location.href = base_url + active_controller;
                            }, 1500);
                        } else {
                            swal("Gagal", res.message || "Penolakan gagal disimpan.", "error");
                        }
                    },
                    error: function() {
                        swal("Error", "Terjadi kesalahan saat memproses data.", "error");
                    }
                });
            });
        });

        $(document).on('click', '.btn-req-print', function(e) {
            e.preventDefault();

            var id_loading = $(this).data('id');
            console.log(id_loading)

            swal({
                title: "Are you sure?",
                text: "Print Draft Muatan!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-succes",
                confirmButtonText: "Yes, Lanjutkan!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + 'loading/request_print',
                        type: 'POST',
                        data: {
                            id_loading: id_loading,
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 1) {
                                swal("Success!", response.pesan, "success");
                                window.open(`${base_url + active_controller}print/${id_loading}`, '_blank');
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

        $(document).on('input', '.qty-aktual', function(e) {
            const row = $(this).closest('tr');

            const qtyAktual = parseFloat($(this).val()) || 0;
            const qtySpk = parseInt(row.find('.qty-muat').val()) || 0;
            const stockActual = parseInt(row.find('.stock-aktual').val()) || 0;

            if (qtyAktual > qtySpk) {
                swal({
                    title: "Error Message !",
                    text: 'Qty Aktual tidak boleh melebihi Qty SPK',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: true,
                    allowOutsideClick: true
                }, () => {
                    $(e.target).val(qtySpk);
                });
                return; // Hentikan perhitungan lanjut
            }

            if (qtyAktual < 1) {
                swal({
                    title: "Error Message !",
                    text: 'Qty Aktual tidak boleh 0',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: true,
                    allowOutsideClick: true
                }, () => {
                    $(e.target).val(qtySpk);
                });
                return; // Hentikan perhitungan lanjut
            }

            if (qtyAktual > stockActual) {
                swal({
                    title: "Error Message !",
                    text: 'Qty Aktual melebihi Stock Gudang',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: true,
                    allowOutsideClick: true
                }, () => {
                    $(e.target).val(qtySpk);
                });
                return;
            }

            const beratPerUnit = parseFloat(row.find('input[name*="[weight]"]').val()) || 0;
            const beratTotal = qtyAktual * beratPerUnit;

            // Update jumlah berat di baris tersebut
            row.find('input[name*="[jumlah_berat]"]').val(beratTotal.toFixed(2));

            // Hitung ulang total seluruh berat aktual
            hitungTotalBeratAktual();
        })

        $(document).on('input', '.berat-aktual', function(e) {
            const row = $(this).closest('tr');

            const beratAktual = parseFloat($(this).val()) || 0;
            const beratSpk = parseInt(row.find('.jumlah-berat').val()) || 0;

            if (beratAktual < 1) {
                swal({
                    title: "Error Message !",
                    text: 'Berat Aktual tidak boleh 0',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: true,
                    allowOutsideClick: true
                }, () => {
                    $(e.target).val(beratSpk);
                });
                return; // Hentikan perhitungan lanjut
            }

            // Update jumlah berat di baris tersebut
            row.find('input[name*="[jumlah_berat]"]').val(beratAktual.toFixed(2));

            // Hitung ulang total seluruh berat aktual
            hitungTotalBeratAktual();
        })
    });
    //fungsi reject
    function Reject() {
        const id = $('#id').val(); // ambil id dari input hidden
        $('#reject_id').val(id); // simpan ke form modal
        $('#reason').val(''); // reset alasan
        $('#reject-modal').modal('show'); // tampilkan modal
    }

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

        const kapasitas = ($('#kapasitas').val()) || 0;

        if (kapasitas > 0 && total > kapasitas) {
            swal({
                title: "Peringatan!",
                text: `Total berat (${total.toFixed(2)} Kg) melebihi kapasitas kendaraan (${kapasitas} Kg).`,
                type: "warning"
            });
        }
    }

    function hitungTotalBeratAktual() {
        let total = 0;

        $('.jumlah-berat').each(function() {
            const berat = parseFloat($(this).val()) || 0;
            total += berat;
        });

        $('#totalBerat').val(total.toFixed(2));

        const kapasitas = ($('#kapasitas').val()) || 0;

        if (kapasitas > 0 && total > kapasitas) {
            swal({
                title: "Peringatan!",
                text: `Total berat (${total.toFixed(2)} Kg) melebihi kapasitas kendaraan (${kapasitas} Kg).`,
                type: "warning"
            });
        }
    }
</script>