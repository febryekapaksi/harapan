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
                                <a href="javascript:void(0);" class="btn btn-warning" id="selectPenerimaan"><i class="fa fa-plus"></i>&emsp;Pilih Penerimaan</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label>Pilih Bank</label>
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
                    <!-- <div class="form-group row">
                        <div class="col-md-6">
                            <div class="col-md-4">
                                <label>No Rekening</label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="norek" id="norek">
                            </div>
                        </div>
                    </div> -->
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tablePn">
                                <thead class="bg-blue">
                                    <tr>
                                        <th style="min-width: 20px;" class="text-nowrap">No</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Kode Penerimaan</th>
                                        <th style="min-width: 200px;" class="text-nowrap">Nama Customer</th>
                                        <th style="min-width: 50px;" class="text-nowrap">No Invoice</th>
                                        <th style="min-width: 50px;" class="text-nowrap">Total Invoice</th>
                                        <th style="min-width: 50px;" class="text-nowrap">Total Penerimaan</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr hidden>
                                        <th colspan="5" class="text-right">Sisa Piutang Sebelumnya</th>
                                        <th>
                                            <input type="text" name="sisa_piutang_sebelum" class="form-control moneyFormat text-right" id="sisaPiutangSebelum" readonly>
                                        </th>
                                    </tr>
                                    <tr hidden>
                                        <th colspan="5" class="text-right">Penerimaan Uang Cash</th>
                                        <th>
                                            <input type="text" name="total_penerimaan" class="form-control moneyFormat text-right" id="totalPenerimaan" readonly>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Total Piutang Sales</th>
                                        <th>
                                            <input type="text" name="total_piutang_sales" class="form-control moneyFormat text-right" id="totalPiutangSales" readonly>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-right">Nilai Setor</th>
                                        <th>
                                            <input type="text" name="nilai_setor" class="form-control moneyFormat text-right" id="nilaiSetor">
                                        </th>
                                    </tr>
                                    <tr hidden>
                                        <th colspan="5" class="text-right">Sisa Piutang Sekarang</th>
                                        <th>
                                            <input type="text" name="sisa_piutang_sesudah" class="form-control moneyFormat text-right" id="sisaPiutangSesudah" readonly>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <hr>
                        <label>Informasi Jurnal</label>
                        <div class="table-responsive">
                            <table id="tableJurnal" class="table table-bordered table-hover">
                                <thead>
                                    <tr bgcolor='#9acfea'>
                                        <th class="text-center">Tanggal</th>
                                        <th class="text-center">Tipe</th>
                                        <th class="text-center">No. COA</th>
                                        <th class="text-center">Nama COA</th>
                                        <th class="text-center">Keterangan</th>
                                        <th class="text-center">Debit</th>
                                        <th class="text-center">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- KOSONG -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" align="right"><b>TOTAL</b></td>
                                        <td><input type="text" id="totalDebit" name="total_debit" class="form-control text-right" readonly></td>
                                        <td><input type="text" id="totalKredit" name="total_kredit" class="form-control text-right" readonly></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success" id="submitBtn"><i class="fa fa-save"></i> Save</button>
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
<div class="modal fade" id="modalPn" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Data Penerimaan</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tableModalPn" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Penerimaan</th>
                                <th>Nama Customer</th>
                                <th>No Invoice</th>
                                <th>Total Invoice</th>
                                <th>Total Penerimaan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnPilihPn">Pilih</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>

<script>
    let selectedPenerimaan = [];
    let penerimaanDataMap = {};

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
        });

        // Tombol Pilih Penerimaan
        $('#selectPenerimaan').on('click', function() {
            const tgl_setor = $('#tgl_setor').val();
            const bank = $('#bank').val();

            if (!tgl_setor) {
                swal("Error", "Silahkan pilih Tanggal Setoran terlebih dahulu", "warning");
                return;
            }
            if (!bank) {
                swal("Error", "Silahkan pilih Bank terlebih dahulu", "warning");
                return;
            }

            // Ambil data penerimaan
            $.ajax({
                url: siteurl + 'setor_bank/get_penerimaan',
                type: 'GET',
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';
                    let no = 1;

                    if (data.length === 0) {
                        html = `<tr><td colspan="7" class="text-center">Tidak ada data penerimaan</td></tr>`;
                    } else {
                        data.forEach(item => {
                            const isChecked = selectedPenerimaan.includes(item.kd_pembayaran) ? 'checked' : '';
                            const invoiceList = item.detail ?
                                item.detail.map(d => d.invoice).join(', ') :
                                item.invoiced;

                            html += `
                                <tr>
                                    <td class="text-center">${no++}</td>
                                    <td>${item.kd_pembayaran}</td>
                                    <td>${item.name_customer ?? '-'}</td>
                                    <td>${invoiceList}</td>
                                    <td class="text-right">${parseFloat(item.total_invoice || 0).toLocaleString()}</td>
                                    <td class="text-right">${parseFloat(item.totalinvoiced || 0).toLocaleString()}</td>
                                    <td class="text-center">
                                        <input type="checkbox" class="select-pn" data-pn='${JSON.stringify(item)}' ${isChecked}>
                                    </td>
                                    <td hidden>
                                        ${item.id_customer ?? '-'}
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    $('#tableModalPn tbody').html(html);
                    $('#modalPn').modal('show');
                },
                error: function() {
                    swal("Error", "Gagal mengambil data penerimaan.", "error");
                }
            });
        });

        $('#btnPilihPn').on('click', function() {
            const selected = $('.select-pn:checked');

            if (selected.length === 0) {
                swal("Info", "Silakan pilih minimal satu penerimaan.", "info");
                return;
            }

            selected.each(function() {
                const data = JSON.parse($(this).attr('data-pn'));
                const kd = data.kd_pembayaran;

                if (!selectedPenerimaan.includes(kd)) {

                    selectedPenerimaan.push(kd);

                    // 🔥 SIMPAN FULL DATA (TERMASUK DETAIL)
                    penerimaanDataMap[kd] = data;

                    // 🔥 AMBIL LIST INVOICE DARI DETAIL
                    const invoiceList = data.detail ?
                        data.detail.map(d => d.invoice).join(', ') :
                        data.invoiced;

                    const totalInvoice = parseFloat(data.total_invoice || 0);
                    const totalBayar = parseFloat(data.totalinvoiced || 0);

                    $('#tablePn tbody').append(`
                <tr data-kd="${kd}">
                    <td class="text-center"></td>

                    <td>
                        <input type="text" name="detail[${kd}][kd_pembayaran]" class="form-control input-sm" value="${kd}" readonly />
                    </td>

                    <td>
                        <input type="text" name="detail[${kd}][name_customer]" class="form-control input-sm" value="${data.name_customer ?? '-'}" readonly />
                    </td>

                    <td>
                        <textarea class="form-control input-sm" readonly>${invoiceList}</textarea>

                       
                        <input type="hidden" name="detail[${kd}][detail_json]" value='${JSON.stringify(data.detail || [])}' />
                    </td>

                    <td>
                        <input type="text" class="form-control input-sm total-invoice moneyFormat text-right"
                            value="${number_format(totalInvoice,2)}" readonly />
                    </td>

                    <td>
                        <input type="text" class="form-control input-sm total-invoiced moneyFormat text-right"
                            value="${number_format(totalBayar,2)}" readonly />
                    </td>

                    <td class="text-center">
                        <button class="btn btn-danger btn-sm remove-pn" data-kd="${kd}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>

                    <td hidden>
                        <input type="text" name="detail[${kd}][id_customer]" value="${data.id_customer ?? '-'}" readonly />
                    </td>
                </tr>
            `);
                }
            });

            moneyFormat('.moneyFormat');
            loadSisaPiutangSebelumnya();
            updatePnTable();
            generateJurnal(); // 🔥 penting

            $('#modalPn').modal('hide');
        });

        $('#nilaiSetor').on('input', function() {
            const nilaiSetor = parseFloat($(this).val().replace(/[^0-9.-]+/g, "")) || 0;
            const totalPiutangSales = parseFloat($('#totalPiutangSales').val().replace(/[^0-9.-]+/g, "")) || 0;
            const sisa = totalPiutangSales - nilaiSetor;
            const bank = $('#bank').val();

            $('#sisaPiutangSesudah').val(sisa.toLocaleString());
            generateJurnal();
        });

        $(document).on('click', '.remove-pn', function() {
            const kd = $(this).data('kd');
            $(this).closest('tr').remove();

            // Hapus dari tracking array dan data map
            selectedPenerimaan = selectedPenerimaan.filter(id => id !== kd);
            delete penerimaanDataMap[kd];
            // loadSisaPiutangSebelumnya();
            // updatePnTable();
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
                        url: siteurl + active_controller + 'save',
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

    function generateJurnal() {

        let jurnalHTML = '';
        let totalDebit = 0;
        let totalKredit = 0;

        const today = $('#tgl_setor').val() || '';

        const noPerkiraan = $('#bank').val() || '';
        const namaBank = $('#bank option:selected').data('nama') || '';

        let totalSetor = parseFloat($('#nilaiSetor').val().replace(/,/g, '')) || 0;

        // =========================
        // 1. BANK (DEBIT)
        // =========================
        if (totalSetor > 0) {
            jurnalHTML += `
        <tr>
            <td><input type="date" name="tgl_jurnal[]" value="${today}" class="form-control" readonly></td>
            <td><input type="text" name="type[]" value="BUM" class="form-control" readonly></td>
            <td><input type="text" name="no_coa[]" value="${noPerkiraan}" class="form-control" readonly></td>
            <td><input type="text" name="nama_coa[]" value="${namaBank}" class="form-control" readonly></td>
            <td><textarea name="keterangan[]" class="form-control" readonly>Setoran Sales ke ${namaBank}</textarea></td>

            <td>
                <input type="hidden" name="debet[]" value="${totalSetor}">
                <input type="text" value="${number_format(totalSetor,2)}" class="form-control text-right" readonly>
            </td>

            <td>
                <input type="hidden" name="kredit[]" value="0">
                <input type="text" value="0" class="form-control text-right" readonly>
            </td>
        </tr>
        `;
            totalDebit += totalSetor;
        }

        // =========================
        // 2. LOOP PER PENERIMAAN → PER INVOICE 🔥
        // =========================
        Object.values(penerimaanDataMap).forEach(pn => {

            if (!pn.detail) return; // safety

            pn.detail.forEach(inv => {

                const invoice = inv.invoice;
                const nominal = parseFloat(inv.nominal) || 0;

                if (nominal > 0) {

                    jurnalHTML += `
                <tr>
                    <td><input type="date" name="tgl_jurnal[]" value="${today}" class="form-control" readonly></td>
                    <td><input type="text" name="type[]" value="BUM" class="form-control" readonly></td>
                    <td><input type="text" name="no_coa[]" value="1102-01-04" class="form-control" readonly></td>
                    <td><input type="text" name="nama_coa[]" value="Piutang Sales" class="form-control" readonly></td>
                    <td><textarea name="keterangan[]" class="form-control" readonly>Setoran Invoice ${invoice} (${pn.kd_pembayaran})</textarea></td>

                    <td>
                        <input type="hidden" name="debet[]" value="0">
                        <input type="text" value="0" class="form-control text-right" readonly>
                    </td>

                    <td>
                        <input type="hidden" name="kredit[]" value="${nominal}">
                        <input type="text" value="${number_format(nominal,2)}" class="form-control text-right" readonly>
                    </td>
                </tr>
                `;

                    totalKredit += nominal;
                }
            });

        });

        // =========================
        // 3. BIAYA ADMIN
        // =========================
        let biayaAdmin = parseFloat($('#biaya_admin').val()?.replace(/,/g, '')) || 0;

        if (biayaAdmin > 0) {
            jurnalHTML += `
        <tr>
            <td><input type="date" name="tgl_jurnal[]" value="${today}" class="form-control" readonly></td>
            <td><input type="text" name="type[]" value="BUM" class="form-control" readonly></td>
            <td><input type="text" name="no_coa[]" value="7201-01-02" class="form-control" readonly></td>
            <td><input type="text" name="nama_coa[]" value="Biaya Admin Bank" class="form-control" readonly></td>
            <td><textarea name="keterangan[]" class="form-control" readonly>Biaya Admin Setoran</textarea></td>

            <td>
                <input type="hidden" name="debet[]" value="${biayaAdmin}">
                <input type="text" value="${number_format(biayaAdmin,2)}" class="form-control text-right" readonly>
            </td>

            <td>
                <input type="hidden" name="kredit[]" value="0">
                <input type="text" value="0" class="form-control text-right" readonly>
            </td>
        </tr>
        `;
            totalDebit += biayaAdmin;
        }

        // =========================
        // RENDER
        // =========================
        $('#tableJurnal tbody').html(jurnalHTML);

        $('#totalDebit').val(number_format(totalDebit, 2));
        $('#totalKredit').val(number_format(totalKredit, 2));

        // =========================
        // VALIDASI
        // =========================
        if (totalDebit !== totalKredit) {
            $('#totalDebit, #totalKredit').addClass('bg-red');
            $('#submitBtn').prop('disabled', true);
        } else {
            $('#totalDebit, #totalKredit').removeClass('bg-red');
            $('#submitBtn').prop('disabled', false);
        }
    }

    function updatePnTable() {
        let total = 0;
        $('#tablePn tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1); // nomor urut
            // const val = $(this).find('td').eq(5).text().replace(/[^0-9.-]+/g, "");
            const val = $(this).find('.total-invoiced').val().replace(/[^0-9.-]+/g, "");
            total += parseFloat(val) || 0;
        });

        $('#totalPenerimaan').val(total.toLocaleString());

        const sisaSebelumnya = parseFloat($('#sisaPiutangSebelum').val().replace(/[^0-9.-]+/g, "")) || 0;
        const totalSales = total + sisaSebelumnya;

        $('#totalPiutangSales').val(totalSales.toLocaleString());
        $('#nilaiSetor').val('');
        $('#sisaPiutangSesudah').val('');
    }

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