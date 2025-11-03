<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <span class="pull-right">
            <a href="<?= site_url('penerimaan_cash/add') ?>" class='btn btn-success'><i class="fa fa-plus"></i>&emsp; Buat Penerimaan</a>
        </span>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="example1" width='100%'>
            <thead>
                <tr class='bg-blue'>
                    <th class="text-center" width='4%'>No</th>
                    <th width='7%'>Tgl Penerimaan</th>
                    <th width='7%'>Kode Penerimaan</th>
                    <th width='7%'>Nama Customer</th>
                    <th width='18%'>Keterangan</th>
                    <th style="min-width: 100%;">No Invoice</th>
                    <th class="text-right" width='7%'>Total Invoice</th>
                    <!-- <th class="text-right">PPH</th>
                    <th class="text-right">Biaya Admin</th> -->
                    <th class="text-right" width='7%'>Total Penerimaan <br> (IDR)</th>
                    <th class="text-center" width='7%'>Option</th>
                </tr>
            </thead>
        </table>
        <tbody></tbody>
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
        var dataTable = $('#example1').DataTable({
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
                url: base_url + active_controller + 'data_side_penerimaan_cash',
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