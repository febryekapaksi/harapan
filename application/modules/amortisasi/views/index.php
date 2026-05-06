<?php
$ENABLE_ADD    = has_permission('Amortisasi.Add');
$ENABLE_MANAGE = has_permission('Amortisasi.Manage');
$ENABLE_VIEW   = has_permission('Amortisasi.View');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-calculator"></i> Jadwal Amortisasi Asset</h3>
        <div class="box-tools pull-right">
            <?php if ($ENABLE_ADD) : ?>
                <button type="button" class="btn btn-success btn-sm" id="btn-proses-otomatis" title="Proses Jurnal Bulan Ini">
                    <i class="fa fa-magic"></i> Proses Otomatis Bulan Ini
                </button>
            <?php endif; ?>
            <a href="<?= site_url('amortisasi/log_jurnal') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-history"></i> Log Jurnal
            </a>
        </div>
    </div>

    <div class="box-body">
        <!-- Filter -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-2">
                <label>Bulan</label>
                <select id="filter_bulan" class="form-control input-sm">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($bulan_list as $k => $v) : ?>
                        <option value="<?= $k ?>" <?= ($k == $bulan_now) ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>Tahun</label>
                <select id="filter_tahun" class="form-control input-sm">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--) : ?>
                        <option value="<?= $y ?>" <?= ($y == $tahun_now) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Kategori</label>
                <select id="filter_kategori" class="form-control input-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategori as $k) : ?>
                        <option value="<?= $k['id'] ?>"><?= strtoupper($k['nm_category']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label><br>
                <button type="button" class="btn btn-primary btn-sm" id="btn-filter">
                    <i class="fa fa-search"></i> Tampilkan
                </button>
            </div>
        </div>

        <table id="tbl_amortisasi" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center" width="40">#</th>
                    <th class="text-center">Periode</th>
                    <th class="text-center">Kategori Asset</th>
                    <th class="text-center">Cabang</th>
                    <th class="text-center">Jumlah Asset</th>
                    <th class="text-center">Total Amortisasi</th>
                    <th class="text-center">Status Jurnal</th>
                    <th class="text-center no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Detail Asset -->
<div class="modal fade" id="ModalDetail" tabindex="-1">
    <div class="modal-dialog" style="width:85%;">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-list"></i> Detail Asset Amortisasi</h4>
            </div>
            <div class="modal-body" id="detail_content">
                <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Memuat data...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Posting -->
<div class="modal fade" id="ModalPosting" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Konfirmasi Posting Jurnal</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Anda akan memposting jurnal amortisasi untuk periode
                    <strong id="lbl_periode_posting"></strong>.
                    <br>Proses ini akan membuat entri jurnal di sistem akuntansi.
                </div>
                <div id="preview_posting_content">
                    <div class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat preview...</div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="post_bulan" value="">
                <input type="hidden" id="post_tahun" value="">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-konfirmasi-posting">
                    <i class="fa fa-check"></i> Ya, Posting Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script>
    var _dataTable = null;

    $(document).ready(function() {
        initDataTable();
    });

    function initDataTable() {
        if (_dataTable !== null) {
            _dataTable.destroy();
        }
        _dataTable = $('#tbl_amortisasi').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            destroy: true,
            responsive: true,
            oLanguage: {
                sSearch: "<b>Cari : </b>",
                sLengthMenu: "_MENU_ &nbsp;<b>Data Per Halaman</b>",
                sInfo: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                sZeroRecords: "Tidak ada data",
                sEmptyTable: "Tidak ada data tersedia",
                sLoadingRecords: "Memuat...",
                oPaginate: {
                    sPrevious: "Prev",
                    sNext: "Next"
                }
            },
            aaSorting: [
                [1, "desc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            sPaginationType: "simple_numbers",
            iDisplayLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: base_url + active_controller + '/data_side',
                type: "POST",
                data: function(d) {
                    d.bulan = $('#filter_bulan').val();
                    d.tahun = $('#filter_tahun').val();
                    d.kategori = $('#filter_kategori').val();
                },
                cache: false
            }
        });
    }

    // Filter
    $('#btn-filter').on('click', function() {
        initDataTable();
    });

    // Detail asset
    $(document).on('click', '.btn-detail', function() {
        var bulan = $(this).data('bulan');
        var tahun = $(this).data('tahun');
        var kdcab = $(this).data('kdcab');
        var category = $(this).data('category');

        $('#detail_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Memuat data...</div>');
        $('#ModalDetail').modal('show');

        $.ajax({
            url: base_url + active_controller + '/detail',
            type: 'GET',
            data: {
                bulan: bulan,
                tahun: tahun,
                kdcab: kdcab
            },
            dataType: 'json',
            success: function(data) {
                renderDetail(data, bulan, tahun);
            },
            error: function() {
                $('#detail_content').html('<div class="alert alert-danger">Gagal memuat data.</div>');
            }
        });
    });

    function renderDetail(data, bulan, tahun) {
        var bulan_nama = {
            '01': 'Januari',
            '02': 'Februari',
            '03': 'Maret',
            '04': 'April',
            '05': 'Mei',
            '06': 'Juni',
            '07': 'Juli',
            '08': 'Agustus',
            '09': 'September',
            '10': 'Oktober',
            '11': 'November',
            '12': 'Desember'
        };
        var html = '<h5><b>Periode: ' + (bulan_nama[bulan] || bulan) + ' ' + tahun + '</b></h5>';
        html += '<div style="max-height:400px;overflow-y:auto;">';
        html += '<table class="table table-bordered table-striped table-condensed">';
        html += '<thead><tr class="bg-blue">' +
            '<th>#</th><th>Kode Asset</th><th>Nama Asset</th>' +
            '<th>Kategori</th><th>Cabang</th>' +
            '<th>Nilai Perolehan</th><th>Amortisasi/Bln</th>' +
            '<th>COA Debit</th><th>COA Kredit</th><th>Status</th>' +
            '</tr></thead><tbody>';

        var total = 0;
        $.each(data, function(i, row) {
            var flag = (row.flag == 'Y') ?
                "<span class='badge bg-green'>Dijurnal</span>" :
                "<span class='badge bg-red'>Belum</span>";
            total += parseFloat(row.nilai_susut);
            html += '<tr>' +
                '<td align="center">' + (i + 1) + '</td>' +
                '<td>' + row.kd_asset + '</td>' +
                '<td>' + row.nm_asset + '</td>' +
                '<td>' + row.nm_category + '</td>' +
                '<td>' + (row.namacabang || row.kdcab) + '</td>' +
                '<td align="right">' + numberFormat(row.nilai_asset) + '</td>' +
                '<td align="right"><b>' + numberFormat(row.nilai_susut) + '</b></td>' +
                '<td><small>' + (row.coa_debit || '-') + '<br>' + (row.nm_coa_debit || '') + '</small></td>' +
                '<td><small>' + (row.coa_kredit || '-') + '<br>' + (row.nm_coa_kredit || '') + '</small></td>' +
                '<td align="center">' + flag + '</td>' +
                '</tr>';
        });

        html += '<tr class="bg-yellow"><td colspan="6"><b>TOTAL</b></td>' +
            '<td align="right"><b>' + numberFormat(total) + '</b></td>' +
            '<td colspan="3"></td></tr>';
        html += '</tbody></table></div>';
        $('#detail_content').html(html);
    }

    // Posting jurnal
    $(document).on('click', '.btn-posting', function() {
        var bulan = $(this).data('bulan');
        var tahun = $(this).data('tahun');
        var bulan_nama = {
            '01': 'Januari',
            '02': 'Februari',
            '03': 'Maret',
            '04': 'April',
            '05': 'Mei',
            '06': 'Juni',
            '07': 'Juli',
            '08': 'Agustus',
            '09': 'September',
            '10': 'Oktober',
            '11': 'November',
            '12': 'Desember'
        };

        $('#post_bulan').val(bulan);
        $('#post_tahun').val(tahun);
        $('#lbl_periode_posting').text((bulan_nama[bulan] || bulan) + ' ' + tahun);

        // Load preview
        $('#preview_posting_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat preview...</div>');
        $.ajax({
            url: base_url + active_controller + '/detail',
            type: 'GET',
            data: {
                bulan: bulan,
                tahun: tahun
            },
            dataType: 'json',
            success: function(data) {
                var html = '<table class="table table-bordered table-condensed">';
                html += '<thead><tr class="bg-blue"><th>Kategori</th><th>Cabang</th><th>Jml Asset</th><th>Total Amortisasi</th><th>COA Debit</th><th>COA Kredit</th></tr></thead><tbody>';
                var grouped = {};
                $.each(data, function(i, row) {
                    var key = row.category + '_' + row.kdcab;
                    if (!grouped[key]) {
                        grouped[key] = {
                            nm_category: row.nm_category,
                            kdcab: row.kdcab,
                            namacabang: row.namacabang,
                            total: 0,
                            jml: 0,
                            coa_debit: row.coa_debit,
                            nm_coa_debit: row.nm_coa_debit,
                            coa_kredit: row.coa_kredit,
                            nm_coa_kredit: row.nm_coa_kredit
                        };
                    }
                    grouped[key].total += parseFloat(row.nilai_susut);
                    grouped[key].jml++;
                });
                var grandTotal = 0;
                $.each(grouped, function(k, g) {
                    grandTotal += g.total;
                    html += '<tr>' +
                        '<td>' + g.nm_category + '</td>' +
                        '<td>' + (g.namacabang || g.kdcab) + '</td>' +
                        '<td align="center">' + g.jml + '</td>' +
                        '<td align="right"><b>' + numberFormat(g.total) + '</b></td>' +
                        '<td><small>' + (g.coa_debit || '-') + '</small></td>' +
                        '<td><small>' + (g.coa_kredit || '-') + '</small></td>' +
                        '</tr>';
                });
                html += '<tr class="bg-yellow"><td colspan="3"><b>GRAND TOTAL</b></td><td align="right"><b>' + numberFormat(grandTotal) + '</b></td><td colspan="2"></td></tr>';
                html += '</tbody></table>';
                $('#preview_posting_content').html(html);
            }
        });

        $('#ModalPosting').modal('show');
    });

    // Konfirmasi posting
    $('#btn-konfirmasi-posting').on('click', function() {
        var bulan = $('#post_bulan').val();
        var tahun = $('#post_tahun').val();
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

        $.ajax({
            url: base_url + active_controller + '/posting_jurnal',
            type: 'POST',
            data: {
                bulan: bulan,
                tahun: tahun
            },
            dataType: 'json',
            success: function(res) {
                $('#ModalPosting').modal('hide');
                if (res.status == 1) {
                    swal({
                        title: 'Berhasil!',
                        text: res.pesan,
                        type: 'success',
                        timer: 5000
                    });
                    initDataTable();
                } else {
                    swal({
                        title: 'Gagal!',
                        text: res.pesan,
                        type: 'warning',
                        timer: 7000
                    });
                }
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Ya, Posting Sekarang');
            },
            error: function() {
                swal({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat proses posting.',
                    type: 'error'
                });
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Ya, Posting Sekarang');
            }
        });
    });

    // Batal jurnal
    $(document).on('click', '.btn-batal', function() {
        var bulan = $(this).data('bulan');
        var tahun = $(this).data('tahun');
        var bulan_nama = {
            '01': 'Januari',
            '02': 'Februari',
            '03': 'Maret',
            '04': 'April',
            '05': 'Mei',
            '06': 'Juni',
            '07': 'Juli',
            '08': 'Agustus',
            '09': 'September',
            '10': 'Oktober',
            '11': 'November',
            '12': 'Desember'
        };

        swal({
            title: 'Batal Jurnal?',
            text: 'Jurnal amortisasi periode ' + (bulan_nama[bulan] || bulan) + ' ' + tahun + ' akan dihapus dari sistem akuntansi.',
            type: 'warning',
            showCancelButton: true,
            confirmButtonClass: 'btn-danger',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tidak',
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + 'index.php/' + active_controller + '/batal_jurnal',
                    type: 'POST',
                    data: {
                        bulan: bulan,
                        tahun: tahun
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            swal({
                                title: 'Berhasil!',
                                text: res.pesan,
                                type: 'success',
                                timer: 5000
                            });
                            initDataTable();
                        } else {
                            swal({
                                title: 'Gagal!',
                                text: res.pesan,
                                type: 'warning'
                            });
                        }
                    }
                });
            }
        });
    });

    // Proses otomatis bulan ini
    $('#btn-proses-otomatis').on('click', function() {
        var bulan = ('0' + new Date().getMonth() + 1).slice(-2);
        var tahun = new Date().getFullYear();
        swal({
            title: 'Proses Otomatis?',
            text: 'Sistem akan memproses jurnal amortisasi untuk bulan ini secara otomatis.',
            type: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses!',
            cancelButtonText: 'Batal',
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + active_controller + '/proses_otomatis',
                    type: 'GET',
                    success: function() {
                        swal({
                            title: 'Selesai!',
                            text: 'Proses otomatis selesai.',
                            type: 'success',
                            timer: 4000
                        });
                        initDataTable();
                    }
                });
            }
        });
    });

    function numberFormat(n) {
        return parseFloat(n).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
</script>