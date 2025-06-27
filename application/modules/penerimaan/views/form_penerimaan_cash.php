<style>
    .otp-input-container .otp-input {
        width: 48px;
        height: 56px;
        text-align: center;
        font-size: 24px;
        border: 2px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: border-color 0.3s;
    }

    .otp-input:focus {
        border-color: #28a745;
        box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
    }
</style>
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="POST">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Tanggal Penerimaan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Tanggal Pembayaran <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control" name="tgl_pembayaran" id="tgl_pembayaran" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Customer <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select name="id_customer" id="id_customer" class="form-control select2">
                                    <option value="">-- Pilih ---</option>
                                    <?php foreach ($customers as $cs): ?>
                                        <option value="<?= $cs->id_customer ?>">
                                            <?= $cs->name_customer ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Keterangan </label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="ket_bayar" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="selectInv"><i class="fa fa-plus"></i> Add Invoice</a>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="tableInv">
                                <thead class="bg-blue">
                                    <tr>
                                        <th style="min-width: 20px;" class="text-nowrap">No</th>
                                        <th style="min-width: 100px;" class="text-nowrap">No Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Total Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Sisa Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Total Bayar</th>
                                        <th style="min-width: 20px;" class="text-nowrap">Tipe Bayar</th>
                                        <th style="min-width: 20px;" class="text-nowrap">Jenis PPh</th>
                                        <th style="min-width: 20px;" class="text-nowrap">PPh</th>
                                        <th style="min-width: 20px;" class="text-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="bg-info">
                                        <th colspan="2" class="text-right">TOTAL</th>
                                        <th class="text-right">
                                            <input type="text" name="total_invoice" class="form-control moneyFormat" id="totalInvoice" readonly>
                                        </th>
                                        <th></th>
                                        <th class="text-right">
                                            <input type="text" name="total_terima" class="form-control moneyFormat" id="totalTerima">
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="ModalInv" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Daftar Invoice</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="tableModalInv" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Inovice</th>
                            <th>No Invoice</th>
                            <th>Tanggal SO</th>
                            <th>No SO</th>
                            <th>Total Invoice</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btnPilihInv">Pilih</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal OTP -->
<div id="modal-otp" class="modal fade">
    <div class="modal-dialog">
        <form id="otp-form">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Verifikasi OTP</h4>
                </div>
                <div class="modal-body text-center">
                    <p id="otp-message">Kode OTP telah dikirim ke WhatsApp Anda.</p>

                    <div class="d-flex justify-content-center gap-2 otp-input-container mb-3">
                        <input type="text" class="otp-input" maxlength="1" />
                        <input type="text" class="otp-input" maxlength="1" />
                        <input type="text" class="otp-input" maxlength="1" />
                        <input type="text" class="otp-input" maxlength="1" />
                        <input type="text" class="otp-input" maxlength="1" />
                        <input type="text" class="otp-input" maxlength="1" />
                    </div>

                    <input type="hidden" name="otp_code" id="otp_code_combined">
                    <input type="hidden" name="kd_pembayaran" id="otp-kd-pembayaran">

                    <div id="countdown-text">Kirim ulang dalam <span id="otp-timer">60</span> detik</div>
                    <button type="button" id="resend-otp-btn" class="btn btn-link" style="display:none;">Kirim ulang OTP</button>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">Verifikasi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        let selectedIds = [];
        let selectedInvoices = [];
        let selectedInvoiceIds = [];

        $('.select2').select2({
            width: '100%'
        });

        moneyFormat('.moneyFormat');

        startOtpTimer();

        $(document).on('input', 'input[name="total_bayar[]"]', function() {
            updateInvoiceTotals();
        });

        $(document).on('click', '.btn-remove', function() {
            const id_invoice = $(this).closest('tr').find('td:nth-child(2)').text();

            // Hapus dari array selected
            selectedInvoiceIds = selectedInvoiceIds.filter(id => id !== id_invoice);

            // Hapus baris
            $(this).closest('tr').remove();

            // Re-number baris
            $('#tableInv tbody tr').each(function(i, row) {
                $(row).find('td:first').text(i + 1);
            });
            updateInvoiceTotals();
        });

        // Tombol Pilih Inv
        $('#selectInv').on('click', function() {
            const tgl_pembayaran = $('#tgl_pembayaran').val();
            const id_customer = $('#id_customer').val();

            if (!tgl_pembayaran) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Pilih Tanggal Pembayaran terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }

            if (!id_customer) {
                swal({
                    title: "Error Message !",
                    text: 'Silahkan Pilih Customer terlebih dahulu..',
                    type: "warning",
                    timer: 7000,
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                return;
            }

            // Ambil data invoice dari server
            $.ajax({
                url: siteurl + 'penerimaan/get_inv',
                type: 'GET',
                data: {
                    id_customer
                },
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';
                    let no = 1;
                    let currentCustomer = '';

                    if (data.length === 0) {
                        html = `<tr><td colspan="5" class="text-center">Tidak ada data invoice</td></tr>`;
                    } else {
                        data.forEach((item) => {
                            if (item.name_customer !== currentCustomer) {
                                html += `
                                            <tr style="background-color:#f0f0f0; font-weight:bold;">
                                                <td colspan="7">Customer: ${item.name_customer}</td>
                                            </tr>
                                        `;
                                currentCustomer = item.name_customer;
                            }

                            html += `
                        	<tr>
                                <td class="text-center">${no++}</td>
                                <td>${item.tgl_inv}</td>
                                <td>${item.id_invoice}</td>
                                <td>${item.tgl_so ?? '-'}</td>
                                <td>${item.id_so ?? '-'}</td>
                                <td class="text-right">${parseFloat(item.grand_total).toLocaleString()}</td>
                                <td class="text-center">
                                  <input type="checkbox" class="select-inv" data-inv='${JSON.stringify(item)}' 
                                    ${selectedInvoiceIds.includes(item.id_invoice) ? 'checked' : ''}>
                                </td>
                            </tr>
                    `;
                        });
                    }

                    $('#tableModalInv tbody').html(html);
                    $('#ModalInv').modal('show');
                },
                error: function() {
                    swal("Error", "Gagal mengambil data invoice.", "error");
                }
            });
        });

        // Tombol Select Inv
        $('#btnPilihInv').on('click', function() {
            const selectedInvoices = [];

            $('.select-inv:checked').each(function() {
                const data = $(this).data('inv');
                if (data) selectedInvoices.push(data);
            });

            if (selectedInvoices.length === 0) {
                swal("Warning", "Silakan pilih minimal satu invoice.", "warning");
                return;
            }

            let rowIndex = $('#tableInv tbody tr').length;

            selectedInvoices.forEach((inv) => {
                if (selectedInvoiceIds.includes(inv.id_invoice)) return;

                selectedInvoiceIds.push(inv.id_invoice);
                rowIndex++; // <- ini penting, jangan hilang!
                const nominal = parseFloat(inv.grand_total || 0);

                $('#tableInv tbody').append(`
                    <tr>
                        <td class="text-center">${rowIndex}</td>
                        <td>${inv.id_invoice}</td>
                        <td class="text-right">${nominal.toLocaleString()}</td>
                        <td class="text-right">${nominal.toLocaleString()}</td>
                        <td>
                            <input type="number" name="detail[${rowIndex}][total_bayar]" class="form-control text-right total_bayar" value="${nominal}" readonly/>
                        </td>
                        <td>
                            <select name="detail[${rowIndex}][tipe_bayar]" class="form-control">
                                <option value="tunai">Tunai</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </td>
                        <td>
                            <select name="detail[${rowIndex}][jenis_pph]" class="form-control">
                                <option value="">-</option>
                                <option value="pph21">PPH 21</option>
                                <option value="pph23">PPH 23</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="detail[${rowIndex}][pph]" class="form-control text-right" value="0" />
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button>
                        </td>

                        <td hidden>
                        <input type="hidden" name="detail[${rowIndex}][id_invoice]" value="${inv.id_invoice}">
                        <input type="hidden" name="detail[${rowIndex}][id_so]" value="${inv.id_so}">
                        </td>
                    </tr>
                `);
            });

            $('#ModalInv').modal('hide');
            updateInvoiceTotals();
        });

        // Proses simpan
        $(document).on('submit', '#data-form', function(e) {
            e.preventDefault();

            const form = document.getElementById('data-form');
            const formData = new FormData(form);

            swal({
                title: "Warning!",
                text: "Yakin lakukan pembayaran?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Bayar",
                confirmButtonColor: "#00a65a",
                cancelButtonColor: "#c9302c"
            }, function(confirm) {
                if (confirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'save_cash',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                // Setelah OTP dikirim, tampilkan modal input
                                $('#modal-otp').modal('show');
                                $('#otp-kd-pembayaran').val(result.kd_pembayaran);
                            } else {
                                swal('Failed!', result.message, 'warning');
                            }
                        },
                        error: function() {
                            swal('Error!', 'Please try again later!', 'error');
                        }
                    });
                }
            });
        });

        //Proses submit otp
        $(document).on('submit', '#otp-form', function(e) {
            e.preventDefault();
            $.ajax({
                url: siteurl + active_controller + 'verify_otp',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(result) {
                    if (result.status == 1) {
                        swal('Sukses', result.message, 'success');
                        $('#modal-otp').modal('hide');
                        window.location.href = base_url + active_controller + 'index_cash';
                    } else {
                        swal('Gagal', result.message, 'error');
                    }
                }
            });
        });

        // Otomatis isi dan pindah
        $(document).on('input', '.otp-input', function() {
            const $inputs = $('.otp-input');
            const current = $inputs.index(this);
            if (this.value.length === 1 && current < $inputs.length - 1) {
                $inputs.eq(current + 1).focus();
            }

            let combined = '';
            $inputs.each(function() {
                combined += this.value;
            });
            $('#otp_code_combined').val(combined);
        });
        $(document).on('keydown', '.otp-input', function(e) {
            const $inputs = $('.otp-input');
            const current = $inputs.index(this);

            if (e.key === 'Backspace' && this.value === '' && current > 0) {
                $inputs.eq(current - 1).focus();
            }
        });

        // Resend OTP klik
        $('#resend-otp-btn').on('click', function() {
            const kd = $('#otp-kd-pembayaran').val();
            $.ajax({
                url: siteurl + active_controller + 'resend_otp',
                type: 'POST',
                data: {
                    kd_pembayaran: kd
                },
                success: function(res) {
                    otpSeconds = 60;
                    startOtpTimer();
                    swal('Berhasil', 'OTP baru telah dikirim ke WhatsApp', 'success');
                },
                error: function() {
                    swal('Gagal', 'Gagal mengirim ulang OTP', 'error');
                }
            });
        });
    })


    let otpCountdown;
    let otpSeconds = 90;

    function startOtpTimer() {
        $('#otp-timer').text(otpSeconds);
        $('#resend-otp-btn').hide();
        $('#countdown-text').show();

        otpCountdown = setInterval(() => {
            otpSeconds--;
            $('#otp-timer').text(otpSeconds);
            if (otpSeconds <= 0) {
                clearInterval(otpCountdown);
                $('#countdown-text').hide();
                $('#resend-otp-btn').show();
            }
        }, 1000);
    }

    function updateInvoiceTotals() {
        let totalInvoice = 0;
        let totalBayar = 0;

        $('#tableInv tbody tr').each(function() {
            const nominal = parseFloat($(this).find('td:eq(2)').text().replace(/,/g, '')) || 0;
            const bayar = parseFloat($(this).find('.total_bayar').val()) || 0;

            totalInvoice += nominal;
            totalBayar += bayar;
        });

        $('#totalInvoice').val(totalInvoice);
        $('#totalTerima').val(totalBayar);
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