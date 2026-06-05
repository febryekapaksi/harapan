<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">

    <div class="box-body">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-2">
                <label>Dari Tanggal Faktur</label>
                <input type="date" id="tgl_dari" class="form-control input-sm">
            </div>
            <div class="col-md-2">
                <label>Sampai Tanggal Faktur</label>
                <input type="date" id="tgl_sampai" class="form-control input-sm">
            </div>
            <div class="col-md-3" style="padding-top:25px;">
                <button class="btn btn-primary btn-sm" id="btnFilter">
                    <i class="fa fa-search"></i> Filter
                </button>
                <button class="btn btn-default btn-sm" id="btnReset">Reset</button>
                <button class="btn btn-success btn-sm" id="btnExportExcel">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="tableItem">
                <thead>
                    <tr class="bg-blue">
                        <th width="50" class="text-center">No</th>
                        <th class="text-center">Nama Barang</th>
                        <th width="150" class="text-center">Kts (Unit#1)</th>
                        <th width="200" class="text-center">Total Pembelian</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        DataTables();

        $('#btnFilter').on('click', function() {
            $('#tableItem').DataTable().ajax.reload();
        });

        $('#btnReset').on('click', function() {
            $('#tgl_dari').val('');
            $('#tgl_sampai').val('');
            $('#tableItem').DataTable().ajax.reload();
        });

        $('#btnExportExcel').on('click', function() {
            var tgl_dari = $('#tgl_dari').val();
            var tgl_sampai = $('#tgl_sampai').val();
            var searchVal = $('#tableItem_filter input').val();

            var url = siteurl + active_controller + 'export_pembelian_per_barang' +
                '?tgl_dari=' + encodeURIComponent(tgl_dari || '') +
                '&tgl_sampai=' + encodeURIComponent(tgl_sampai || '') +
                '&search=' + encodeURIComponent(searchVal || '');

            window.location = url;
        });
    });

    function DataTables() {
        var dataTable = $('#tableItem').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "searching": true,
            "responsive": true,
            "aaSorting": [
                [3, "desc"]
            ],
            "columnDefs": [{
                    "targets": [0],
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    "targets": [1],
                    "className": "text-left"
                },
                {
                    "targets": [2, 3],
                    "className": "text-right"
                }
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100],
                [10, 20, 50, 100]
            ],
            "ajax": {
                url: siteurl + active_controller + 'data_side_pembelian_per_barang',
                type: "post",
                data: function(d) {
                    d.tgl_dari = $('#tgl_dari').val();
                    d.tgl_sampai = $('#tgl_sampai').val();
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#tableItem").append('<tbody class="my-grid-error"><tr><th colspan="4">No data found in the server</th></tr></tbody>');
                    $("#tableItem_processing").css("display", "none");
                }
            }
        });
    }
</script>