<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <?php if ($ENABLE_ADD) { ?>
            <a href="<?php echo site_url('pr_departemen/add') ?>" class="btn btn-success" style="float: right;" id='btn-add'>
                <i class="fa fa-plus"></i> &emsp; Add Request
            </a>
        <?php } ?>
        <div style="float: left;">
            <select name="departemen" id="departemen" class="form-control form-control-sm chosen-select">
                <option value="" selected>Semua Departemen</option>
                <?php
                foreach ($list_department as $item) {
                    echo '<option value="' . $item->id . '">' . strtoupper($item->nama) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1" width='100%'>
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
                        <th class="text-center no-sort">Status</th>
                        <th class="text-center no-sort" width='13%'>Option</th>
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
        var departemen = $("#departemen").val();
        DataTables(departemen);

        $(document).on('change', '#departemen', function() {
            var departemen = $("#departemen").val();
            DataTables(departemen);
        });
    });

    function DataTables(departemen = null) {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": true,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [2, "asc"]
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
                url: base_url + active_controller + '/data_side_pr_departemen',
                type: "post",
                data: function(d) {
                    d.departemen = departemen
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            },
        });
    }
</script>