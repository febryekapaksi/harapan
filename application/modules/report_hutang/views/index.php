<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Report Hutang Per Invoice</h3>
    </div>
    <div class="box-body">

        <!-- Filter -->
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="text" id="tanggal" name="tanggal"
                           class="form-control datepicker"
                           placeholder="Pilih tanggal..."
                           autocomplete="off" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Total Hutang</label>
                    <input type="text" id="total_hutang_display"
                           class="form-control text-right"
                           readonly
                           style="background:#d9edf7; font-weight:bold; color:#31708f;">
                </div>
            </div>
            <div class="col-md-6" style="padding-top:25px;">
                <button type="button" id="btn-cari" class="btn btn-primary btn-sm">
                    <i class="fa fa-search"></i> Tampilkan
                </button>
                <button type="button" id="btn-print" class="btn btn-warning btn-sm" style="display:none;">
                    <i class="fa fa-print"></i> Print
                </button>
                <button type="button" id="btn-excel" class="btn btn-success btn-sm" style="display:none;">
                    <i class="fa fa-file-excel-o"></i> Download Excel
                </button>
            </div>
        </div>

        <!-- Tabel Hasil -->
        <div id="result-area" style="display:none; margin-top:10px;">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed" id="tbl-hutang">
                    <thead>
                        <tr class="bg-blue" style="color:#fff;">
                            <th class="text-center" style="vertical-align:middle;">Supplier</th>
                            <th class="text-center" style="vertical-align:middle;">Tanggal Invoice</th>
                            <th class="text-center" style="vertical-align:middle;">No PO</th>
                            <th class="text-center" style="vertical-align:middle;">No Invoice</th>
                            <th class="text-center" style="vertical-align:middle;">Nilai Invoice</th>
                            <th class="text-center" style="vertical-align:middle;">Kode Pembayaran</th>
                            <th class="text-center" style="vertical-align:middle;">Tanggal Bayar</th>
                            <th class="text-center" style="vertical-align:middle;">Nilai Bayar</th>
                            <th class="text-center" style="vertical-align:middle;">Total Bayar</th>
                            <th class="text-center" style="vertical-align:middle;">Sisa Hutang</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-hutang">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" class="text-right"><strong>Total Hutang</strong></td>
                            <td class="text-right" id="tfoot-total"><strong></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="no-data-area" style="display:none;">
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Tidak ada data hutang per tanggal yang dipilih.
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datepicker/datepicker3.css') ?>">
<script src="<?= base_url('assets/plugins/datepicker/bootstrap-datepicker.js') ?>"></script>

<script>
var base_url = '<?= base_url() ?>';
var active_controller = '<?= $this->uri->segment(1) ?>';

$(document).ready(function () {

    $('#tanggal').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    $('#btn-cari').on('click', function () {
        var tgl_display = $('#tanggal').val();
        if (!tgl_display) {
            swal({ title: 'Perhatian', text: 'Pilih tanggal terlebih dahulu.', type: 'warning' });
            return;
        }

        var parts = tgl_display.split('/');
        var tgl_server = parts[2] + '-' + parts[1] + '-' + parts[0];

        $.ajax({
            url: base_url + active_controller + '/get_data',
            type: 'POST',
            dataType: 'json',
            data: { tanggal: tgl_server },
            beforeSend: function () {
                $('#btn-cari').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
            },
            success: function (res) {
                $('#btn-cari').prop('disabled', false).html('<i class="fa fa-search"></i> Tampilkan');

                if (!res.status) {
                    swal({ title: 'Error', text: res.message, type: 'error' });
                    return;
                }

                if (res.data.length === 0) {
                    $('#result-area').hide();
                    $('#no-data-area').show();
                    $('#btn-print, #btn-excel').hide();
                    $('#total_hutang_display').val('');
                    return;
                }

                $('#no-data-area').hide();
                renderTable(res.data, res.total_hutang);
                $('#total_hutang_display').val(formatNumber(res.total_hutang));
                $('#result-area').show();
                $('#btn-print, #btn-excel').show().data('tanggal', tgl_server);
            },
            error: function () {
                $('#btn-cari').prop('disabled', false).html('<i class="fa fa-search"></i> Tampilkan');
                swal({ title: 'Error', text: 'Koneksi gagal, coba lagi.', type: 'error' });
            }
        });
    });

    $('#btn-print').on('click', function () {
        var tgl = $(this).data('tanggal');
        window.open(base_url + active_controller + '/print_report/' + tgl, '_blank');
    });

    $('#btn-excel').on('click', function () {
        var tgl = $(this).data('tanggal');
        window.location.href = base_url + active_controller + '/export_excel/' + tgl;
    });

    function renderTable(data, total) {
        var tbody = '';

        $.each(data, function (i, row) {
            tbody += '<tr>';

            if (row.is_first_row) {
                tbody += '<td rowspan="' + row.rowspan + '">' + escHtml(row.nm_supplier) + '</td>';
                tbody += '<td rowspan="' + row.rowspan + '" class="text-center">' + formatDate(row.tgl_invoice) + '</td>';
                tbody += '<td rowspan="' + row.rowspan + '" class="text-center">' + escHtml(row.no_po) + '</td>';
                tbody += '<td rowspan="' + row.rowspan + '" class="text-center">' + escHtml(row.id_invoice) + '</td>';
                tbody += '<td rowspan="' + row.rowspan + '" class="text-right">' + formatNumber(row.nilai_invoice) + '</td>';
            }

            tbody += '<td class="text-center">' + (row.kd_pembayaran ? escHtml(row.kd_pembayaran) : '') + '</td>';
            tbody += '<td class="text-center">' + (row.tgl_bayar ? formatDate(row.tgl_bayar) : '') + '</td>';
            tbody += '<td class="text-right">' + (row.nilai_bayar !== '' ? formatNumber(row.nilai_bayar) : '') + '</td>';
            tbody += '<td class="text-right">' + (row.total_bayar !== '' ? formatNumber(row.total_bayar) : '') + '</td>';
            tbody += '<td class="text-right">' + formatNumber(row.sisa_hutang) + '</td>';
            tbody += '</tr>';
        });

        $('#tbody-hutang').html(tbody);
        $('#tfoot-total strong').text(formatNumber(total));
    }

    function formatNumber(n) {
        if (n === '' || n === null || n === undefined) return '';
        return parseFloat(n).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function formatDate(d) {
        if (!d) return '';
        var dt = new Date(d);
        if (isNaN(dt)) return d;
        var months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return dt.getDate() + ' ' + months[dt.getMonth()] + ' ' + dt.getFullYear();
    }

    function escHtml(str) {
        if (!str) return '';
        return $('<div>').text(str).html();
    }
});
</script>
