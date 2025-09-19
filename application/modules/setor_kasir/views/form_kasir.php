<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label>Tanggal Setor <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tgl_setor" id="tgl_setor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label>No Rekening</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="norek" id="norek">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label>Pilih Bank <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control select2" name="bank" id="bank">
                                    <option value="">Pilih</option>
                                    <?php foreach ($bank as $b) : ?>
                                        <option value="<?= $b->no_perkiraan ?>" data-rekening="<?= $b->no_perkiraan ?>" data-nama="<?= $b->nama; ?>">
                                            <?= $b->nama . " - " . $b->no_perkiraan ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" id="bank_name" name="bank_name" value="">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-12">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr class="bg-blue">
                                                <th style="min-width: 50px;">No Penerimaan</th>
                                                <th style="min-width: 100px;">Customer</th>
                                                <th style="min-width: 50px;">No Invoice</th>
                                                <th style="min-width: 50px;">Total Invoice</th>
                                                <th style="min-width: 50px;">Total Penerimaan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $grand_invoice = 0;
                                            $grand_penerimaan = 0;
                                            foreach ($setor_kasir as $sk):
                                            ?>
                                                <tr style="background:#f3f3f3;font-weight:bold;">
                                                    <td colspan="5">
                                                        No Setor Kasir: <?= $sk->id ?> |
                                                        Sales: <?= $sk->sales ?> |
                                                        Tgl Setor: <?= date('d M Y', strtotime($sk->tgl_setor)) ?>
                                                    </td>
                                                </tr>

                                                <?php if (!empty($detail_kasir[$sk->id])): ?>
                                                    <?php
                                                    $total_invoice = 0;
                                                    $total_penerimaan = 0;
                                                    foreach ($detail_kasir[$sk->id] as $d):
                                                        $total_invoice += $d->total_invoice;
                                                        $total_penerimaan += $d->total_penerimaan;
                                                        $kd = $d->kd_pembayaran;
                                                    ?>
                                                        <tr>
                                                            <td hidden>
                                                                <input type="hidden" name="detail[<?= $kd ?>][id_setor_kasir]" value="<?= $sk->id ?>">
                                                                <input type="hidden" name="detail[<?= $kd ?>][id_sales]" value="<?= $sk->id_sales ?>">
                                                                <input type="hidden" name="detail[<?= $kd ?>][sales]" value="<?= $sk->sales ?>">
                                                                <input type="hidden" name="detail[<?= $kd ?>][tgl_setor_kasir]" value="<?= $sk->tgl_setor ?>">
                                                            </td>
                                                            <td><input type="text" name="detail[<?= $kd ?>][kd_pembayaran]" class="form-control input-sm" value="<?= $kd ?>" readonly /></td>
                                                            <td> <input type="hidden" name="detail[<?= $kd ?>][id_customer]" value="<?= $d->id_customer ?>">
                                                                <input type="text" name="detail[<?= $kd ?>][name_customer]" class="form-control input-sm" value="<?= $d->name_customer ?>" readonly />
                                                            </td>
                                                            <td><textarea class="form-control input-sm" name="detail[<?= $kd ?>][no_invoice]" readonly><?= $d->no_invoice ?></textarea></td>
                                                            <td><input type="text" name="detail[<?= $kd ?>][total_invoice]" class="form-control input-sm total-invoice moneyFormat text-right" value="<?= $d->total_invoice ?>" readonly /></td>
                                                            <td><input type="text" name="detail[<?= $kd ?>][total_invoiced]" class="form-control input-sm total-invoiced moneyFormat text-right" value="<?= $d->total_penerimaan ?>" readonly /></td>
                                                        </tr>
                                                    <?php endforeach; ?>

                                                    <?php
                                                    $grand_invoice += $total_invoice;
                                                    $grand_penerimaan += $total_penerimaan;
                                                    ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>

                                        <!-- GLOBAL FOOTER -->
                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">Sisa Piutang Sebelumnya</th>
                                                <th class="text-right">
                                                    <input type="text" name="sisa_piutang_sebelum"
                                                        class="form-control moneyFormat text-right" id="sisaPiutangSebelum"
                                                        value="<?= $sisa_piutang_sebelumnya ?? 0 ?>" readonly>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">Penerimaan Uang Cash</th>
                                                <th class="text-right">
                                                    <input type="text" name="total_penerimaan"
                                                        class="form-control moneyFormat text-right" id="totalPenerimaan"
                                                        value="<?= $grand_penerimaan ?>" readonly>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">Total Piutang Kasir</th>
                                                <th class="text-right">
                                                    <input type="text" name="total_piutang_sales"
                                                        class="form-control moneyFormat text-right" id="totalPiutangSales"
                                                        value="<?= $grand_penerimaan + ($sisa_piutang_sebelumnya ?? 0) ?>" readonly>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">Nilai Setor</th>
                                                <th>
                                                    <input type="text" name="nilai_setor" class="form-control text-right moneyFormat" id="nilaiSetor">
                                                </th>
                                            </tr>
                                            <tr>
                                                <th colspan="4" class="text-right">Sisa Piutang Sekarang</th>
                                                <th>
                                                    <input type="text" name="sisa_piutang_sesudah" class="form-control text-right moneyFormat" id="sisaPiutangSesudah" readonly>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
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
                                <td><input type="text" id="type1" name="type[]" value="BUM" class="form-control" readonly /></td>
                                <td><input type="text" id="no_coa1" name="no_coa[]" class="form-control" readonly /></td>
                                <td><input type="text" id="nama_coa1" name="nama_coa[]" class="form-control" readonly /></td>
                                <td><input type="hidden" id="debet1" name="debet[]" value="0" class="form-control" readonly />
                                    <input type="text" id="debet21" name="debet2[]" value="0" class="form-control" readonly />
                                </td>
                                <td><input type="hidden" id="kredit1" name="kredit[]" value="0" class="form-control" readonly />
                                    <input type="text" id="kredit21" name="kredit2[]" value="0" class="form-control" readonly />
                                </td>

                            </tr>
                            <tr bgcolor='#DCDCDC'>
                                <td><input type="date" id="tgl_jurnal2" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                <td><input type="text" id="type2" name="type[]" value="BUM" class="form-control" readonly /></td>
                                <td><input type="text" id="no_coa2" name="no_coa[]" value="1101-01-03" class="form-control" readonly /></td>
                                <td><input type="text" id="nama_coa2" name="nama_coa[]" value="Kas Penjualan Cirebon" class="form-control" readonly /></td>
                                <td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control" readonly />
                                    <input type="text" id="debet22" name="debet2[]" value="0" class="form-control" readonly />
                                </td>
                                <td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control" readonly />
                                    <input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control" readonly />
                                </td>

                            </tr>

                            <tr bgcolor='#DCDCDC'>
                                <td colspan="3" align="right"><b>TOTAL</b></td>
                                <td align="right"><input type="hidden" id="total" name="total" value="0" class="form-control" readonly />
                                    <input type="text" id="total31" name="total3" value="0" class="form-control" readonly />
                                </td>
                                <td align="right"><input type="hidden" id="total2" name="total2" value="0" class="form-control" readonly />
                                    <input type="text" id="total41" name="total4" value="0" class="form-control" readonly />
                                </td>

                            </tr>

                    </table>
                    <div class="form-group row">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-success" id="submitBtn"><i class="fa fa-save"></i> Save</button>
                            <a class="btn btn-default" href="<?= base_url('setor_kasir') ?>">
                                <i class="fa fa-reply"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>

<script>
    $(document).ready(function() {
        moneyFormat('.moneyFormat');

        $('.select2').select2({
            width: '100%',
        });

        $('#bank').on('change', function() {
            var norek = $(this).find(':selected').data('rekening');
            $('#norek').val(norek || '');

            const noPerkiraan = $(this).val(); // value option
            const namaBank = $(this).find(':selected').data('nama'); // data-nama

            //$('input[name="no_coa[]"]').val(noPerkiraan);

            $('#bank_name').val(namaBank);
            $('#no_coa1').val(noPerkiraan);
            $('#nama_coa1').val(namaBank);
        });

        $('#nilaiSetor').on('input', function() {
            const nilaiSetor = parseFloat($(this).val().replace(/[^0-9.-]+/g, "")) || 0;
            const totalPiutangSales = parseFloat($('#totalPiutangSales').val().replace(/[^0-9.-]+/g, "")) || 0;
            const sisa = totalPiutangSales - nilaiSetor;

            const bank = $('#bank').val();
            $('#no_coa1').val(bank)

            $('#debet1').val(nilaiSetor);
            $('#debet21').val(number_format(nilaiSetor, 2));

            $('#kredit2').val(nilaiSetor);
            $('#kredit22').val(number_format(nilaiSetor, 2));

            $('#total').val(nilaiSetor);
            $('#total31').val(number_format(nilaiSetor, 2));
            $('#total2').val(nilaiSetor);
            $('#total41').val(number_format(nilaiSetor, 2));

            $('#sisaPiutangSesudah').val(sisa.toLocaleString());
        });

        $(document).on('submit', '#data-form', function(e) {
            e.preventDefault();

            const tgl_setor = $('#tgl_setor').val();
            if (!tgl_setor) {
                swal("Error", "Silahkan pilih Tanggal Setoran terlebih dahulu", "warning");
                return;
            }

            const bank = $('#bank').val();
            if (!bank) {
                swal("Error", "Silahkan pilih Bank terlebih dahulu", "warning");
                return;
            }

            const nilaiSetor = $('#nilaiSetor').val();
            if (!nilaiSetor) {
                swal("Error", "Nilai setoran tidak boleh 0", "warning");
                return;
            }

            const form = document.getElementById('data-form');
            const formData = new FormData(form);

            swal({
                title: "Warning!",
                text: "Yakin lakukan setoran?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Setor",
                confirmButtonColor: "#00a65a",
                cancelButtonColor: "#c9302c"
            }, function(confirm) {
                if (confirm) {
                    $('#submitBtn').prop('disabled', true);

                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'save_bank',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(result) {
                            $('#submitBtn').prop('disabled', false);

                            if (result.status) {
                                swal({
                                    title: 'Berhasil!',
                                    text: result.message,
                                    type: 'success'
                                }, function() {
                                    window.location.href = siteurl + active_controller;
                                });
                            } else {
                                swal('Gagal!', result.message, 'warning');
                            }
                        },
                        error: function() {
                            $('#submitBtn').prop('disabled', false);
                            swal('Error!', 'Terjadi kesalahan, silakan coba lagi.', 'error');
                        }
                    });
                }
            });
        });
    });

    function loadSisaPiutangSebelumnya() {
        $.ajax({
            url: siteurl + active_controller + 'get_sisa_piutang_sebelumnya',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    const total = parseFloat(res.total || 0);
                    $('#sisaPiutangSebelum').val(total);
                    moneyFormat('#sisaPiutangSebelum');
                    updatePnTable(); // agar total_piutang_sales ikut ke-refresh
                }
            },
            error: function() {
                console.warn('Gagal memuat sisa piutang sebelumnya');
            }
        });
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

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
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
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
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

    function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }
</script>