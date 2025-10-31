<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="example1">
                <thead>
                    <tr class='bg-blue'>
                        <th class="text-center">#</th>
                        <th class="text-center">No PR</th>
                        <th class="text-center">Departemen</th>
                        <th class="text-center no-sort">Nama Barang/Jasa</th>
                        <th class="text-center no-sort">Spec / Requirement</th>
                        <th class="text-center no-sort" width='7%'>Qty</th>
                        <th class="text-center no-sort">Dibutuhkan</th>
                        <th class="text-center no-sort">Keterangan</th>
                        <th class="text-center no-sort">PIC</th>
                        <th class="text-center no-sort">Status</th>
                        <th class="text-center no-sort" width='13%'>Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- modal -->
<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
    <div class="modal-dialog" style='width:80%; '>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="head_title2"></h4>
            </div>
            <div class="modal-body" id="view2">
            </div>
            <div class="modal-footer">
                <!--<button type="button" class="btn btn-primary">Save</button>-->
                <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
            </div>
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
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "fixedHeader": true,
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
                url: siteurl + active_controller + 'data_side_approval_pr_departemen',
                type: "post",
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