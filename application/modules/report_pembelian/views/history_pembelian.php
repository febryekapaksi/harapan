<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-2">
                <label>Dari Tanggal PO</label>
                <input type="date" id="tgl_dari" class="form-control input-sm">
            </div>
            <div class="col-md-2">
                <label>Sampai Tanggal PO</label>
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
            <table class="table table-bordered table-striped" id="example1">
                <thead>
                    <tr>
                        <th width="5" class="text-center">No</th>
                        <th class="text-center">Permintaan Barang (PR)</th>
                        <th class="text-center">Pesanan Pembelian (PO)</th>
                        <th class="text-center">Penerimaan Barang</th>
                        <th class="text-center">Faktur Pembelian</th>
                        <th class="text-center">Pembayaran Pembelian</th>
                        <th class="text-center">Term (Hari)</th>
                        <th class="text-center">Jatuh Tempo</th>
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
            $('#example1').DataTable().ajax.reload();
        });

        $('#btnReset').on('click', function() {
            $('#tgl_dari').val('');
            $('#tgl_sampai').val('');
            $('#example1').DataTable().ajax.reload();
        });

        $('#btnExportExcel').on('click', function() {
            var tgl_dari = $('#tgl_dari').val();
            var tgl_sampai = $('#tgl_sampai').val();
            var searchVal = $('#example1_filter input').val();

            // Pastikan active_controller diakhiri dengan slash jika perlu
            var url = siteurl + active_controller + 'export_excel_report' +
                '?tgl_dari=' + encodeURIComponent(tgl_dari || '') +
                '&tgl_sampai=' + encodeURIComponent(tgl_sampai || '') +
                '&search=' + encodeURIComponent(searchVal || '');

            window.location = url;
        });
    });

    function DataTables() {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "searching": true,
            "responsive": true,
            "aaSorting": [
                [2, "desc"] // Default sort ke kolom PO
            ],
            "columnDefs": [{
                    "targets": [0], // Kolom Nomor
                    "orderable": false,
                    "className": "text-center"
                },
                {
                    "targets": [1, 2, 3, 4, 5, 6, 7],
                    "className": "text-left"
                }
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: siteurl + active_controller + 'data_side_history_pembelian',
                type: "post",
                data: function(d) {
                    d.tgl_dari = $('#tgl_dari').val();
                    d.tgl_sampai = $('#tgl_sampai').val();
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#example1").append('<tbody class="my-grid-error"><tr><th colspan="8">No data found in the server</th></tr></tbody>');
                    $("#example1_processing").css("display", "none");
                }
            }
        });
    }
</script>