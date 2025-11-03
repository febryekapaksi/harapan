<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table id="tableHilang" class="table table-bordered table-striped" width='100%'>
                <thead>
                    <tr>
                        <th class='text-center'>#</th>
                        <th class='text-center'>No Surat Jalan</th>
                        <th class='text-center'>Customer</th>
                        <th class='text-center'>Tanggal Kirim</th>
                        <th class='text-center no-sort'>Jumlah Qty Hilang</th>
                        <!-- <th class='text-center no-sort'>File Bukti</th> -->
                        <th class='text-center no-sort'>Option</th>
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

    function DataTables() {
        var dataTable = $('#tableHilang').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "searching": true,
            "responsive": true,
            "aaSorting": [
                [1, "desc"]
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
                url: siteurl + active_controller + 'data_side_hilang',
                type: "post",
                // data: function(d) {
                //     d.sales_order = sales_order
                // },
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