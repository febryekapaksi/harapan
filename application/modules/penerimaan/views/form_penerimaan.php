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
                                <input type="date" class="form-control" name="tgl_pembayaran" id="tgl_pembayaran" min="2025-09-01">
                            </div>
                        </div>

                        <!-- Customer -->
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

                        <!-- Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Pilih Bank <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select name="bank" id="bank" class="form-control select2" <?= $disabled ?>>
                                    <option value="">-- Pilih ---</option>
                                    <?php foreach ($bank as $b): ?>
                                        <option value="<?= $b->no_perkiraan; ?>" data-nama="<?= $b->nama; ?>">
                                            <?= $b->nama . " (" . $b->no_perkiraan . ")" ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" id="bank_name" name="bank_name" value="">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Keterangan Pembayaran -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Keterangan Pembayaran</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="ket_bayar" class="form-control"></textarea>
                            </div>
                        </div>

                        <!-- Pembayaran Bank -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label>Penerimaan Bank <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" name="total_bank" class="form-control total-bank moneyFormat text-right" id="totalBank" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="selectInv"><i class="fa fa-plus"></i> Add Invoice</a>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="tableInv">
                                <thead class="bg-blue">
                                    <tr>
                                        <th style="min-width: 20px;" class="text-nowrap">No</th>
                                        <th style="min-width: 250px;" class="text-nowrap">No Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Nominal Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Sisa Invoice</th>
                                        <th style="min-width: 100px;" class="text-nowrap">Nominal Bayar</th>
                                        <th style="min-width: 20px;" class="text-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="bg-info">
                                        <th colspan="4" class="text-right">Total Bayar Invoice</th>
                                        <th>
                                            <input type="text" name="total_terima" class="form-control input-sm moneyFormat text-right" id="totalBayarInvoice" readonly>
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    <tr class="bg-info">
                                        <th colspan="4" class="text-right">Total Tagihan Invoice</th>
                                        <th>
                                            <input type="text" name="total_invoice" class="form-control input-sm moneyFormat text-right" id="totalInvoice" readonly>
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    <tr class="bg-info">
                                        <th colspan="4" class="text-right">Selisih</th>
                                        <th>
                                            <input type="text" name="selisih" class="form-control input-sm moneyFormat text-right" id="selisih" readonly>
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    <tr class="bg-info" hidden>
                                        <th colspan="4" class="text-right">Biaya Administrasi</th>
                                        <th>
                                            <input type="text" name="biaya_adm" class="form-control input-sm moneyFormat text-right" id="biayaAdm">
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    <tr class="bg-info" hidden>
                                        <th colspan="4" class="text-right">Lebih Bayar</th>
                                        <th>
                                            <input type="text" name="lebih_bayar" class="form-control input-sm moneyFormat text-right" id="lebihBayar">
                                        </th>
                                        <th colspan="4"></th>
                                    </tr>
                                    <tr class="bg-info">
                                        <th colspan="4" class="text-right">Kontrol</th>
                                        <th>
                                            <input type="text" name="kontrol" class="form-control input-sm moneyFormat text-right" id="kontrol" readonly>
                                        </th>
                                        <th colspan="4"></th>
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
                                        <td><input type="text" id="nama_coa1" name="nama_coa[]" value="" class="form-control" readonly /></td>
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
                                        <td><input type="text" id="no_coa2" name="no_coa[]" value="1102-01-01" class="form-control" readonly /></td>
                                        <td><input type="text" id="nama_coa2" name="nama_coa[]" value="Piutang Dagang" class="form-control" readonly /></td>
                                        <td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control" readonly />
                                            <input type="text" id="debet22" name="debet2[]" value="0" class="form-control" readonly />
                                        </td>
                                        <td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control" readonly />
                                            <input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control" readonly />
                                        </td>

                                    </tr>
                                    <tr bgcolor='#DCDCDC'>
                                        <td><input type="date" id="tgl_jurnal3" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
                                        <td><input type="text" id="type3" name="type[]" value="BUM" class="form-control" readonly /></td>
                                        <td><input type="text" id="no_coa3" name="no_coa[]" value="7201-01-02" class="form-control" readonly /></td>
                                        <td><input type="text" id="nama_coa3" name="nama_coa[]" value="Biaya Adm Bank & Buku Cek/Giro" class="form-control" readonly /></td>
                                        <td><input type="hidden" id="debet3" name="debet[]" value="0" class="form-control" readonly />
                                            <input type="text" id="debet23" name="debet2[]" value="0" class="form-control" readonly />
                                        </td>
                                        <td><input type="hidden" id="kredit3" name="kredit[]" value="0" class="form-control" readonly />
                                            <input type="text" id="kredit23" name="kredit2[]" value="0" class="form-control" readonly />
                                        </td>

                                    </tr>

                                    <tr bgcolor='#DCDCDC'>
                                        <td colspan="4" align="right"><b>TOTAL</b></td>
                                        <td align="right"><input type="hidden" id="total" name="total" value="0" class="form-control" readonly />
                                            <input type="text" id="total31" name="total3" value="0" class="form-control" readonly />
                                        </td>
                                        <td align="right"><input type="hidden" id="total2" name="total2" value="0" class="form-control" readonly />
                                            <input type="text" id="total41" name="total4" value="0" class="form-control" readonly />
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-success" id="btnSave"><i class="fa fa-save"></i> Save</button>
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
                            <th>Tanggal Invoice</th>
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


<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        let selectedInvoiceIds = [];

        $('.select2').select2({
            width: '100%'
        });

        $('.moneyFormat').each(function() {
            let val = parseFloat($(this).val().replace(/,/g, '')) || 0;
            $(this).val(number_format(val, 2));
        });

        $('#bank').on('change', function() {
            const noPerkiraan = $(this).val(); // value option
            const namaBank = $(this).find(':selected').data('nama'); // data-nama

            //$('input[name="no_coa[]"]').val(noPerkiraan);

            $('#bank_name').val(namaBank);
            $('#nama_coa1').val(namaBank);
            $('#no_coa1').val(noPerkiraan);
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
                    showConfirmButton: true,
                    allowOutsideClick: trigger_error
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
                    showConfirmButton: true,
                    allowOutsideClick: true
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
                                <td class="text-right">${parseFloat(item.sisa_tagihan).toLocaleString()}</td>
                                <td class="text-center">
                                  <input type="checkbox" class="select-inv" data-inv='${JSON.stringify(item)}' 
                                    ${selectedInvoiceIds.includes(item.id_invoice) ? 'checked' : ''}>
                                </td>
                            </tr>
                    `;
                        });
                    }

                    $('#tableModalInv tbody').html(html);
                    // Cekbox chaining logic
                    const checkboxes = $('#tableModalInv .select-inv');
                    checkboxes.prop('disabled', false); //sementara buat aktifin semua checkbox

                    // checkboxes.prop('disabled', true); // awalnya semua disabled
                    // if (checkboxes.length > 0) checkboxes.eq(0).prop('disabled', false); // kecuali yang pertama

                    // checkboxes.on('change', function() {
                    //     const idx = checkboxes.index(this);
                    //     const checked = $(this).prop('checked');

                    //     if (checked) {
                    //         // Aktifkan checkbox berikutnya
                    //         if (idx + 1 < checkboxes.length) {
                    //             checkboxes.eq(idx + 1).prop('disabled', false);
                    //         }
                    //     } else {
                    //         // Uncheck & disable semua checkbox setelahnya
                    //         for (let i = idx + 1; i < checkboxes.length; i++) {
                    //             checkboxes.eq(i).prop('checked', false).prop('disabled', true);
                    //         }
                    //     }
                    // });

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
                const nominal = parseFloat(inv.sisa_tagihan || inv.grand_total || 0);

                $('#tableInv tbody').append(`
                    <tr>
                        <td class="text-center">${rowIndex}</td>
                        <td>${inv.id_invoice}</td>

                        <td>
                            <input type="text" name="detail[${rowIndex}][tagihan]" class="form-control input-sm text-right tagihan moneyFormat" value="${nominal}" readonly />
                        </td>
                        <td>
                            <input type="text" name="detail[${rowIndex}][sisa_invoice]" class="form-control input-sm text-right sisa_invoice moneyFormat" value="${nominal}" readonly/>
                        </td>
                        <td>
                            <input type="text" name="detail[${rowIndex}][total_bayar]" class="form-control input-sm text-right total_bayar moneyFormat" value="${nominal}" readonly/>
                        </td>
                       
                     
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button>
                        </td>

                        <input type="hidden" name="detail[${rowIndex}][id_invoice]" value="${inv.id_invoice}">
                        <input type="hidden" name="detail[${rowIndex}][id_so]" value="${inv.id_so}">
                       
                    </tr>
                `);
            });

            $('#ModalInv').modal('hide');
            moneyFormat('.moneyFormat');
            updateInvoiceTotals();
        });

        // Proses simpan
        $(document).on('submit', '#data-form', function(e) {
            e.preventDefault();

            const form = document.getElementById('data-form');
            const formData = new FormData(form);

            swal({
                title: "Warning!",
                text: "Yakin simpan data?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Bayar",
                confirmButtonColor: "#00a65a",
                cancelButtonColor: "#c9302c"
            }, function(confirm) {
                if (confirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'save',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal('Success', result.message, 'success')
                                window.location.href = siteurl + active_controller
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

        $('#totalBank').on('input', function() {
            updateInvoiceTotals(); // hitung ulang pembagian
        });

        $('#biayaAdm, #lebihBayar').on('input', function() {
            const totalBank = parseFloat($('#totalBank').val().replace(/,/g, '')) || 0;
            const totalInvoice = parseFloat($('#totalInvoice').val().replace(/,/g, '')) || 0;
            calculateSelisihDanKontrol(totalBank, totalInvoice);
        });
    })

    function updateInvoiceTotals() {
        let totalInvoice = 0;
        let totalBayarInvoice = 0;
        let totalBank = parseFloat($('#totalBank').val().replace(/,/g, '')) || 0;
        let sisaBank = totalBank;
        let bank = $('#bank').val();

        // Loop per baris invoice
        $('#tableInv tbody tr').each(function() {
            const $row = $(this);
            const tagihan = parseFloat($row.find('.tagihan').val().replace(/,/g, '')) || 0;

            totalInvoice += tagihan;

            let bayar = 0;
            if (sisaBank >= tagihan) {
                bayar = tagihan;
                sisaBank -= tagihan;
            } else {
                bayar = sisaBank;
                sisaBank = 0;
            }

            const sisa = tagihan - bayar;
            totalBayarInvoice += bayar

            // Set Total Bayar
            $row.find('.total_bayar').val(number_format(bayar, 2));
            // Set Sisa Invoice
            $row.find('.sisa_invoice').val(number_format(sisa, 2));
        });

        $('#totalInvoice').val(number_format(totalInvoice, 2));
        $('#totalBayarInvoice').val(number_format(totalBayarInvoice, 2));

        $('#no_coa1').val(bank)

        $('#debet1').val(totalBank);
        $('#debet21').val(number_format(totalBank, 2));

        $('#kredit2').val(totalBayarInvoice);
        $('#kredit22').val(number_format(totalBayarInvoice, 2));

        $('#total').val(totalBank);
        $('#total31').val(number_format(totalBank, 2));
        $('#total2').val(totalBayarInvoice);
        $('#total41').val(number_format(totalBayarInvoice, 2));



        calculateSelisihDanKontrol(totalBank, totalBayarInvoice);
    }


    function calculateSelisihDanKontrol(totalBank, totalBayarInvoice) {
        const biayaAdm = parseFloat($('#biayaAdm').val().replace(/,/g, '')) || 0;
        const lebihBayar = parseFloat($('#lebihBayar').val().replace(/,/g, '')) || 0;

        const selisih = totalBank - totalBayarInvoice;
        const kontrol = selisih + biayaAdm - lebihBayar;

        $('#selisih').val(number_format(selisih, 2));
        $('#kontrol').val(number_format(kontrol, 2));

        // Enable/Disable tombol Save berdasarkan nilai kontrol
        if (Math.abs(kontrol) < 0.01) {
            $('#btnSave').prop('disabled', false);
        } else {
            $('#btnSave').prop('disabled', true);
        }
    }


    function moneyFormat(e) {
        $(e).inputmask({
            alias: "decimal",
            digits: 2,
            radixPoint: ".",
            autoGroup: true,
            placeholder: "0",
            rightAlign: false,
            allowMinus: true,
            integerDigits: 13,
            groupSeparator: ",",
            digitsOptional: false,
            showMaskOnHover: true,
        })
    }

    function number_format(number, decimals = 2, dec_point = '.', thousands_sep = ',') {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number;
        var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
        var sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
        var dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
        var s = '';

        var toFixedFix = function(n, prec) {
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