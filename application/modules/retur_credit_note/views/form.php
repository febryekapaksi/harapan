<?php
// Cek apakah invoice sudah pernah ada pembayaran
$total_sudah_bayar = 0;
$riwayat_bayar = [];
if (!empty($inv['id_invoice'])) {
    $riwayat_bayar = $this->db
        ->select('p.kd_pembayaran, p.tgl_pembayaran, p.tipe_bayar, d.total_bayar_idr')
        ->from('tr_invoice_payment_detail d')
        ->join('tr_invoice_payment p', 'p.kd_pembayaran = d.kd_pembayaran', 'left')
        ->where('d.no_invoice', $inv['id_invoice'])
        ->order_by('p.tgl_pembayaran', 'ASC')
        ->get()->result_array();
    foreach ($riwayat_bayar as $rb) {
        $total_sudah_bayar += $rb['total_bayar_idr'];
    }
}
$grand_total_inv = 0;
foreach ($detail as $dt) {
    $grand_total_inv += $dt['total'];
}
$sisa_piutang = $grand_total_inv - $total_sudah_bayar;
?>

<div class="box box-primary">
    <div class="box-body">

        <?php if ($total_sudah_bayar > 0): ?>
            <div class="alert alert-warning">
                <strong><i class="fa fa-exclamation-triangle"></i> Perhatian!</strong>
                Invoice ini sudah pernah menerima pembayaran sebesar <strong>Rp <?= number_format($total_sudah_bayar, 0, ',', '.') ?></strong>.
                Sisa piutang saat ini: <strong>Rp <?= number_format($sisa_piutang, 0, ',', '.') ?></strong>.
                Credit note hanya akan mengurangi nilai piutang yang tersisa.
            </div>
        <?php endif; ?>

        <form id="data-form">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Nomor Retur</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="no_retur" readonly>
                                <input type="hidden" name="id_invoice" value="<?= !empty($inv) ? $inv['id_invoice'] : '' ?>">
                                <input type="hidden" name="id_billing" value="<?= !empty($inv) ? $inv['id_billing'] : '' ?>">
                                <input type="hidden" name="pengiriman" value="<?= !empty($inv) ? $inv['pengiriman'] : '' ?>">
                                <input type="hidden" name="grand_total_asli" id="grand_total_asli" value="<?= $grand_total_inv ?>">
                                <input type="hidden" name="total_sudah_bayar" id="total_sudah_bayar" value="<?= $total_sudah_bayar ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Nomor SO</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="id_so" value="<?= !empty($inv) ? $inv['id_so'] : '' ?>" required readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Retur</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tgl_retur" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Customer</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="nm_customer" value="<?= !empty($inv) ? $inv['nm_customer'] : '' ?>" required readonly>
                                <input type="hidden" name="id_customer" value="<?= !empty($inv) ? $inv['id_customer'] : '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Alasan</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="alasan" class="form-control" id=""></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-blue">
                                        <th>No</th>
                                        <th>ID Produk</th>
                                        <th>Nama Produk</th>
                                        <th class="text-center">Qty Delivery</th>
                                        <th class="text-center">Qty Retur <span class="text-red">*</span></th>
                                        <th class="text-center">Qty Sisa</th>
                                        <th class="text-right">Harga Satuan</th>
                                        <th class="text-right">Total Retur</th>
                                        <th>Alasan Retur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 0;
                                    $grand_total = 0;
                                    foreach ($detail as $dt) {
                                        $no++;
                                        $total_item = $dt['qty'] * $dt['harga'];
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= $no ?></td>
                                            <td style="width: 130px;">
                                                <input type="text" name="detail[<?= $no ?>][id_produk]" class="form-control input-sm" value="<?= $dt['id_produk'] ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="detail[<?= $no ?>][nm_produk]" class="form-control input-sm" value="<?= $dt['nm_produk'] ?>" readonly>
                                            </td>
                                            <td style="width: 90px;" class="text-center">
                                                <input type="text" class="form-control input-sm text-center qty_delivery" value="<?= $dt['qty'] ?>" readonly>
                                                <input type="hidden" name="detail[<?= $no ?>][qty_delivery]" value="<?= $dt['qty'] ?>">
                                            </td>
                                            <td style="width: 90px;">
                                                <input type="number" name="detail[<?= $no ?>][qty_retur]" class="form-control input-sm text-center qty_retur"
                                                    value="<?= $dt['qty'] ?>" min="0" max="<?= $dt['qty'] ?>" step="1" required>
                                            </td>
                                            <td style="width: 90px;">
                                                <input type="text" class="form-control input-sm text-center qty_sisa" value="0" readonly>
                                            </td>
                                            <td style="width: 140px;">
                                                <input type="text" name="detail[<?= $no ?>][harga]" class="form-control input-sm text-right" value="<?= number_format($dt['harga'], 0, ',', '.') ?>" readonly>
                                                <input type="hidden" name="detail[<?= $no ?>][harga_raw]" value="<?= $dt['harga'] ?>">
                                            </td>
                                            <td style="width: 140px;">
                                                <input type="text" name="detail[<?= $no ?>][total]" class="form-control input-sm text-right total_retur" value="<?= number_format($total_item, 0, ',', '.') ?>" readonly>
                                                <input type="hidden" name="detail[<?= $no ?>][total_raw]" class="total_retur_raw" value="<?= $total_item ?>">
                                            </td>
                                            <td>
                                                <textarea name="detail[<?= $no ?>][alasan_retur]" class="form-control input-sm"></textarea>
                                            </td>
                                            <input type="hidden" name="detail[<?= $no ?>][id_so_det]" value="<?= $dt['id_so_det'] ?>">
                                        </tr>
                                    <?php
                                        $grand_total += $total_item;
                                    } ?>
                                </tbody>
                                <tfoot class="bg-gray">
                                    <tr>
                                        <th colspan="7" class="text-right">Total Nilai Retur</th>
                                        <th>
                                            <input type="text" class="form-control input-sm text-right" id="grand_total_display" readonly value="<?= number_format($grand_total, 0, ',', '.') ?>">
                                            <input type="hidden" name="grand_total" id="grand_total" value="<?= $grand_total ?>">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right">Nilai Invoice Asal</th>
                                        <th>
                                            <input type="text" class="form-control input-sm text-right bg-yellow" readonly value="<?= number_format($grand_total_inv, 0, ',', '.') ?>">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right">Sudah Dibayar</th>
                                        <th>
                                            <input type="text" class="form-control input-sm text-right bg-green" readonly value="<?= number_format($total_sudah_bayar, 0, ',', '.') ?>">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right">Nilai Invoice Baru (Sisa)</th>
                                        <th>
                                            <input type="text" class="form-control input-sm text-right bg-aqua" id="nilai_inv_baru_display" readonly value="<?= number_format($grand_total_inv - $grand_total, 0, ',', '.') ?>">
                                            <input type="hidden" name="nilai_inv_baru" id="nilai_inv_baru" value="<?= $grand_total_inv - $grand_total ?>">
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-12 text-center" style="margin-top:15px;">
                        <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Simpan Credit Note</button>
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

        // Hitung ulang saat qty retur berubah
        $(document).on('input change', '.qty_retur', function() {
            var $row = $(this).closest('tr');
            var qty_delivery = parseFloat($row.find('.qty_delivery').val()) || 0;
            var qty_retur = parseFloat($(this).val()) || 0;

            // Validasi tidak boleh melebihi qty delivery
            if (qty_retur > qty_delivery) {
                qty_retur = qty_delivery;
                $(this).val(qty_retur);
            }
            if (qty_retur < 0) {
                qty_retur = 0;
                $(this).val(0);
            }

            var qty_sisa = qty_delivery - qty_retur;
            $row.find('.qty_sisa').val(qty_sisa);

            var harga = parseFloat($row.find('input[name$="[harga_raw]"]').val()) || 0;
            var total_retur = qty_retur * harga;

            $row.find('.total_retur').val(number_format_id(total_retur));
            $row.find('.total_retur_raw').val(total_retur);

            hitungGrandTotal();
        });

        // Inisialisasi qty sisa saat load
        $('.qty_retur').each(function() {
            var $row = $(this).closest('tr');
            var qty_delivery = parseFloat($row.find('.qty_delivery').val()) || 0;
            var qty_retur = parseFloat($(this).val()) || 0;
            $row.find('.qty_sisa').val(qty_delivery - qty_retur);
        });

        // Submit form
        $('#data-form').submit(function(e) {
            e.preventDefault();

            // Validasi: minimal 1 item harus ada qty retur > 0
            var ada_retur = false;
            $('.qty_retur').each(function() {
                if (parseFloat($(this).val()) > 0) ada_retur = true;
            });
            if (!ada_retur) {
                swal('Peringatan', 'Minimal satu item harus memiliki qty retur > 0.', 'warning');
                return false;
            }

            swal({
                    title: "Konfirmasi Credit Note",
                    text: "Proses credit note ini tidak dapat dibatalkan. Lanjutkan?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Ya, Proses!",
                    cancelButtonText: "Batal",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        $.ajax({
                            url: siteurl + 'retur_credit_note/save',
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Berhasil!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 5000
                                    });
                                    setTimeout(function() {
                                        window.location.href = siteurl + 'retur_credit_note';
                                    }, 1500);
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: data.pesan,
                                        type: "warning"
                                    });
                                }
                            },
                            error: function() {
                                swal("Error", 'Terjadi kesalahan. Silakan coba lagi.', "warning");
                            }
                        });
                    } else {
                        swal("Dibatalkan", "Proses credit note dibatalkan.", "error");
                    }
                });
        });
    });

    function hitungGrandTotal() {
        var total = 0;
        $('.total_retur_raw').each(function() {
            total += parseFloat($(this).val()) || 0;
        });

        var grand_total_asli = parseFloat($('#grand_total_asli').val()) || 0;

        $('#grand_total_display').val(number_format_id(total));
        $('#grand_total').val(total);

        var nilai_baru = grand_total_asli - total;
        $('#nilai_inv_baru_display').val(number_format_id(nilai_baru));
        $('#nilai_inv_baru').val(nilai_baru);
    }

    function number_format_id(num) {
        return parseFloat(num).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
</script>