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
                <button class="btn btn-primary btn-sm" id="btnFilter">Filter</button>
                <button class="btn btn-default btn-sm" id="btnReset">Reset</button>
                <button class="btn btn-success btn-sm" id="btnExportExcel">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="tablePemasok">
                <thead>
                    <tr class="bg-blue">
                        <th width="50" class="text-center">No</th>
                        <th class="text-center">Pemasok</th>
                        <th width="250" class="text-center">Total Pembelian</th>
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
        DataTablesPemasok();

        $('#btnFilter').on('click', function() {
            $('#tablePemasok').DataTable().ajax.reload();
        });

        $('#btnReset').on('click', function() {
            $('#tgl_dari').val('');
            $('#tgl_sampai').val('');
            $('#tablePemasok').DataTable().ajax.reload();
        });

        $('#btnExportExcel').on('click', function() {
            var tgl_dari = $('#tgl_dari').val();
            var tgl_sampai = $('#tgl_sampai').val();
            var searchVal = $('#tablePemasok_filter input').val();

            var url = siteurl + active_controller + 'export_pembelian_per_vendor' +
                '?tgl_dari=' + encodeURIComponent(tgl_dari || '') +
                '&tgl_sampai=' + encodeURIComponent(tgl_sampai || '') +
                '&search=' + encodeURIComponent(searchVal || '');

            window.location = url;
        });
    });

    function DataTablesPemasok() {
        $('#tablePemasok').DataTable({
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "destroy": true,
            "aaSorting": [
                [2, "desc"]
            ],
            "ajax": {
                url: siteurl + active_controller + 'data_side_pembelian_per_vendor',
                type: "post",
                data: function(d) {
                    d.tgl_dari = $('#tgl_dari').val();
                    d.tgl_sampai = $('#tgl_sampai').val();
                }
            },
            "columnDefs": [{
                    "targets": [0],
                    "className": "text-center",
                    "orderable": false
                },
                {
                    "targets": [2],
                    "className": "text-right"
                }
            ]
        });
    }
</script>