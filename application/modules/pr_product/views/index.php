<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <div class="text-right">
            <span class="pull-right">
                <a href="<?= site_url('pr_product/add') ?>" class='btn btn-success'><i class="fa fa-plus"></i>&emsp; Add Request</a>
            </span>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tabelPr">
                <thead>
                    <tr class="bg-blue">
                        <th>#</th>
                        <th>No.PR</th>
                        <th>Nama Product</th>
                        <th>Qty</th>
                        <th>Dibutuhkan</th>
                        <th>Status</th>
                        <th>Request By</th>
                        <th>Request Date</th>
                        <th class="text-center">Option</th>
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
        var dataTable = $('#tabelPr').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": false,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "order": [
                [7, "desc"]
            ],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: base_url + active_controller + 'data_side_material_planning',
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