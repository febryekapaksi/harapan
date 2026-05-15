<?php
$ENABLE_VIEW   = has_permission('Gl_interface.View');
$ENABLE_MANAGE = has_permission('Gl_interface.Manage');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-sm-12">
                <div class="row" style="align-items:center;">
                    <div class="col-sm-3">
                        <input type="text" id="filterSearch" class="form-control input-sm" placeholder="Cari nomor / keterangan...">
                    </div>
                    <div class="col-sm-2">
                        <select id="filterJenis" class="form-control input-sm">
                            <option value="">-- Semua Tipe Transaksi --</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <select id="filterStatus" class="form-control input-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="pending">Pending</option>
                            <option value="posted">Posted</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button class="btn btn-default btn-sm" id="btnResetFilter">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body table-responsive">
        <table id="tblGlInterface" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center">Nomor</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Jenis</th>
                    <th class="text-center">Tipe Transaksi</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Total Debet</th>
                    <th class="text-center">Total Kredit</th>
                    <th class="text-center">Status</th>
                    <th class="text-center no-sort" width="60">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        function fmt(n) {
            n = parseFloat(n) || 0;
            return n.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        // Load jenis_transaksi options
        $.get('<?= base_url("gl_interface/get_jenis_list") ?>', function(res) {
            if (res && res.length) {
                $.each(res, function(i, v) {
                    $('#filterJenis').append('<option value="' + v + '">' + v.charAt(0).toUpperCase() + v.slice(1) + '</option>');
                });
            }
        }, 'json');

        // DataTable
        var table = $('#tblGlInterface').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            autoWidth: false,
            responsive: true,
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            ajax: {
                url: '<?= base_url("gl_interface/data") ?>',
                type: 'POST',
                data: function(d) {
                    d.jenis_transaksi = $('#filterJenis').val();
                    d.filter_status = $('#filterStatus').val();
                    d.search = {
                        value: $('#filterSearch').val()
                    };
                }
            },
            columns: [{
                    data: 'nomor',
                    className: 'text-center',
                    render: function(d) {
                        return d ? d : '<span class="text-muted">-</span>';
                    }
                },
                {
                    data: 'tgl',
                    className: 'text-center'
                },
                {
                    data: 'jenis',
                    className: 'text-center'
                },
                {
                    data: 'jenis_transaksi',
                    className: 'text-center',
                    render: function(d) {
                        return d ? d.charAt(0).toUpperCase() + d.slice(1) : '-';
                    }
                },
                {
                    data: 'keterangan',
                    render: function(d) {
                        if (!d) return '-';
                        return d.length > 60 ? d.substring(0, 60) + '...' : d;
                    }
                },
                {
                    data: 'total_debet',
                    className: 'text-right',
                    render: function(d) {
                        return fmt(d);
                    }
                },
                {
                    data: 'total_kredit',
                    className: 'text-right',
                    render: function(d) {
                        return fmt(d);
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(d) {
                        if (d === 'posted') {
                            return '<span class="label label-success">' + d.toUpperCase() + '</span>';
                        } else if (d === 'error') {
                            return '<span class="label label-danger">' + d.toUpperCase() + '</span>';
                        } else {
                            return '<span class="label label-warning">' + d.toUpperCase() + '</span>';
                        }
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    render: function(data) {
                        return '<a href="<?= base_url("gl_interface/view/") ?>' + data + '" class="btn btn-info btn-xs" title="Lihat Detail"><i class="fa fa-eye"></i></a>';
                    }
                }
            ]
        });

        // Filters
        var debounce;
        $('#filterSearch').on('keyup', function() {
            clearTimeout(debounce);
            debounce = setTimeout(function() {
                table.draw();
            }, 400);
        });
        $('#filterJenis, #filterStatus').on('change', function() {
            table.draw();
        });
        $('#btnResetFilter').on('click', function() {
            $('#filterSearch').val('');
            $('#filterJenis').val('');
            $('#filterStatus').val('');
            table.draw();
        });
    });
</script>