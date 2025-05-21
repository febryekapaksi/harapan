<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<style>
    th {
        text-align: center;
        vertical-align: middle !important;
    }
</style>

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table id="table-kartu-stok" class="table table-bordered table-striped">
                <thead class="bg-blue">
                    <tr>
                        <th rowspan='2'>#</th>
                        <th rowspan='2'>Tgl Transaksi</th>
                        <th rowspan='2'>No.Transaksi</th>
                        <th rowspan='2'>Jenis Transaksi</th>
                        <th rowspan='2'>Id Produk</th>
                        <th rowspan='2'>Produk</th>
                        <th colspan='3'>AWAL</th>
                        <th colspan='2'>TRANSAKSI</th>
                        <th colspan='3'>AKHIR</th>

                    </tr>
                    <tr>
                        <th>Stock</th>
                        <th>Booking</th>
                        <th>Free Stock</th>
                        <th>In/Out</th>
                        <th>Booking</th>
                        <th>Stock</th>
                        <th>Booking</th>
                        <th>Free Stock</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        DataTables();
    });

    function DataTables(status = null) {
        var dataTable = $('#table-kartu-stok').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: base_url + active_controller + 'data_side_kartu_stok',
                type: "post",
                data: function(d) {
                    d.status = status
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });
    }
</script>