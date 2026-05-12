<?php
$ENABLE_ADD    = has_permission('Amortisasi.Add');
$ENABLE_MANAGE = has_permission('Amortisasi.Manage');
$ENABLE_VIEW   = has_permission('Amortisasi.View');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<!-- ===== DASHBOARD SUMMARY CARDS ===== -->
<div class="row" hidden>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-file-text-o"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Kontrak Aktif</span>
                <span class="info-box-number"><?= number_format($summary['jml_active'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-blue"><i class="fa fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Selesai</span>
                <span class="info-box-number"><?= number_format($summary['jml_completed'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sudah Diamortisasi</span>
                <span class="info-box-number" style="font-size:16px;"><?= number_format($summary['total_sudah_amort'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Sisa Belum Diamortisasi</span>
                <span class="info-box-number" style="font-size:16px;"><?= number_format($summary['total_remaining'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABEL MASTER AMORTISASI ===== -->
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text-o"></i> Daftar Biaya Dibayar Dimuka</h3>
        <div class="box-tools pull-right">
            <?php if ($ENABLE_ADD) : ?>
                <button type="button" class="btn btn-success btn-sm" id="btn-tambah">
                    <i class="fa fa-plus"></i> Tambah Kontrak
                </button>
            <?php endif; ?>
            <?php if ($ENABLE_MANAGE) : ?>
                <button type="button" class="btn btn-primary btn-sm" id="btn-posting-bulanan">
                    <i class="fa fa-magic"></i> Posting Jurnal Bulanan
                </button>
            <?php endif; ?>
            <a href="<?= site_url('amortisasi/log_jurnal') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-history"></i> Log Jurnal
            </a>
        </div>
    </div>

    <div class="box-body">
        <!-- Filter Status -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-3">
                <label>Filter Status</label>
                <select id="filter_status" class="form-control input-sm">
                    <option value="">Semua Status</option>
                    <option value="active" selected>Active</option>
                    <option value="completed">Completed</option>
                    <option value="terminated">Terminated</option>
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
                    <th class="text-center" width="120">Kode</th>
                    <th class="text-center">Nama Item / Akun Biaya</th>
                    <th class="text-center">Total Biaya</th>
                    <th class="text-center">Tgl Mulai</th>
                    <th class="text-center">Tgl Selesai</th>
                    <th class="text-center">Sudah Amort</th>
                    <th class="text-center">Sisa</th>
                    <th class="text-center">Status</th>
                    <th class="text-center no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- ===== MODAL FORM TAMBAH/EDIT ===== -->
<div class="modal fade" id="ModalForm" tabindex="-1">
    <div class="modal-dialog" style="width:70%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title" id="modal_form_title">
                    <i class="fa fa-plus"></i> Tambah Kontrak Amortisasi
                </h4>
            </div>
            <div class="modal-body" id="form_content">
                <div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Memuat...</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL JADWAL BULANAN ===== -->
<div class="modal fade" id="ModalJadwal" tabindex="-1">
    <div class="modal-dialog" style="width:80%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-list"></i> Jadwal Amortisasi Bulanan</h4>
            </div>
            <div class="modal-body">
                <div id="info_item" class="alert alert-info" style="margin-bottom:10px;"></div>
                <table id="tbl_jadwal" class="table table-bordered table-striped table-condensed" width="100%">
                    <thead>
                        <tr class="bg-blue">
                            <th class="text-center" width="40">#</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Nilai Amortisasi</th>
                            <th class="text-center">Saldo Awal</th>
                            <th class="text-center">Saldo Akhir</th>
                            <th class="text-center">Status Jurnal</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL POSTING JURNAL BULANAN ===== -->
<div class="modal fade" id="ModalPosting" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-check-circle"></i> Posting Jurnal Amortisasi</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Bulan</label>
                            <select id="post_bulan" class="form-control">
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tahun</label>
                            <select id="post_tahun" class="form-control">
                                <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--) : ?>
                                    <option value="<?= $y ?>" <?= ($y == date('Y')) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i>
                    Sistem akan menjurnal <b>semua kontrak aktif</b> yang memiliki jadwal amortisasi pada periode yang dipilih.
                    <br>Jurnal: <b>(D) Akun Biaya &nbsp;|&nbsp; (K) Biaya Dibayar Dimuka</b>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-primary" id="btn-konfirmasi-posting">
                    <i class="fa fa-check"></i> Posting Sekarang
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script>
    var _dataTable = null;
    var _jadwalTable = null;
    var _currentAmortId = null;

    $(document).ready(function() {
        // Set default bulan posting ke bulan sekarang
        var bln = ('0' + new Date().getMonth()).slice(-2); // bulan lalu (closing)
        if (bln == '00') bln = '12';
        $('#post_bulan').val(bln == '00' ? '12' : ('0' + new Date().getMonth()).slice(-2));

        initDataTable();
    });

    // -----------------------------------------------------------------------
    // DataTable master
    // -----------------------------------------------------------------------
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
                [0, "desc"]
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
                    d.filter_status = $('#filter_status').val();
                },
                cache: false
            }
        });
    }

    $('#btn-filter').on('click', function() {
        initDataTable();
    });

    // -----------------------------------------------------------------------
    // Tambah kontrak baru
    // -----------------------------------------------------------------------
    $('#btn-tambah').on('click', function() {
        $('#modal_form_title').html('<i class="fa fa-plus"></i> Tambah Kontrak Amortisasi');
        $('#form_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Memuat...</div>');
        $('#ModalForm').modal('show');
        $.get(base_url + active_controller + '/form_tambah', function(html) {
            $('#form_content').html(html);
        });
    });

    // -----------------------------------------------------------------------
    // Edit kontrak
    // -----------------------------------------------------------------------
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        $('#modal_form_title').html('<i class="fa fa-pencil"></i> Edit Kontrak Amortisasi');
        $('#form_content').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i> Memuat...</div>');
        $('#ModalForm').modal('show');
        $.get(base_url + active_controller + '/form_edit/' + id, function(html) {
            $('#form_content').html(html);
        });
    });

    // -----------------------------------------------------------------------
    // Simpan form (tambah/edit) – dipanggil dari dalam form_item.php
    // -----------------------------------------------------------------------
    $(document).on('submit', '#form-amortisasi', function(e) {
        e.preventDefault();
        var $btn = $('#btn-simpan-form');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: base_url + active_controller + '/simpan',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    $('#ModalForm').modal('hide');
                    swal({
                        title: 'Berhasil!',
                        text: res.pesan,
                        type: 'success',
                        timer: 5000,
                        html: true
                    });
                    initDataTable();
                } else {
                    swal({
                        title: 'Gagal!',
                        text: res.pesan,
                        type: 'warning'
                    });
                }
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            },
            error: function() {
                swal({
                    title: 'Error!',
                    text: 'Terjadi kesalahan.',
                    type: 'error'
                });
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // -----------------------------------------------------------------------
    // Lihat jadwal bulanan
    // -----------------------------------------------------------------------
    $(document).on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        var nama = $(this).closest('tr').find('td:eq(2)').text().trim();
        _currentAmortId = id;

        $('#info_item').html('<i class="fa fa-file-text-o"></i> <b>' + nama + '</b>');

        if (_jadwalTable !== null) {
            _jadwalTable.destroy();
        }
        _jadwalTable = $('#tbl_jadwal').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            destroy: true,
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
                [0, "asc"]
            ],
            sPaginationType: "simple_numbers",
            iDisplayLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: base_url + active_controller + '/detail_schedule/' + id,
                type: "POST",
                cache: false
            }
        });

        $('#ModalJadwal').modal('show');
    });

    // -----------------------------------------------------------------------
    // Terminate kontrak
    // -----------------------------------------------------------------------
    $(document).on('click', '.btn-terminate', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        swal({
            title: 'Stop Amortisasi?',
            text: 'Kontrak <b>' + nama + '</b> akan dihentikan.\nSisa saldo yang belum diamortisasi akan langsung dijurnalkan sebagai beban.',
            type: 'warning',
            html: true,
            showCancelButton: true,
            confirmButtonClass: 'btn-danger',
            confirmButtonText: 'Ya, Hentikan!',
            cancelButtonText: 'Batal',
            closeOnConfirm: false
        }, function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: base_url + active_controller + '/terminate',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == 1) {
                            swal({
                                title: 'Berhasil!',
                                text: res.pesan,
                                type: 'success',
                                html: true,
                                timer: 6000
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

    // -----------------------------------------------------------------------
    // Posting jurnal bulanan
    // -----------------------------------------------------------------------
    $('#btn-posting-bulanan').on('click', function() {
        $('#ModalPosting').modal('show');
    });

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
                        timer: 6000
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
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Posting Sekarang');
            },
            error: function() {
                swal({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat posting.',
                    type: 'error'
                });
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Posting Sekarang');
            }
        });
    });
</script>