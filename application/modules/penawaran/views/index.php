<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-sm-12">
                <a href="<?= site_url('penawaran/add') ?>" class='btn btn-primary'><i class="fa fa-plus"></i>&emsp;Penawaran</a>
            </div>
        </div>
        <hr>
        <div class="row" style="align-items:center;">
            <div class="col-sm-2" style="display:flex;align-items:center;">
                <label class="form-label" style="margin:0;">Pilih Tanggal Penawaran</label>
            </div>
            <div class="col-sm-2">
                <input type="date" id="start_date" class="form-control input-sm">
            </div>
            <div class="col-sm-1 text-center" style="display:flex;align-items:center;justify-content:center;">
                <i class="fa fa-arrow-right"></i>
            </div>
            <div class="col-sm-2">
                <input type="date" id="end_date" class="form-control input-sm">
            </div>
            <div class="col-sm-3">
                <button id="btnFilter" class="btn bg-purple btn-sm">
                    <i class="fa fa-filter"></i> Filter
                </button>
                <button id="btnReset" class="btn btn-default btn-sm">
                    Reset
                </button>
                <a id="btnExport" href="javascript:void(0)" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- <div class="form-group row">
            <div class="col-md-10">

            </div>
            <div class="col-md-2">
                <select name="status" id="status" class='form-control select2'>
                    <option value="0">ALL STATUS</option>
                    <option value="N">Waiting Submission</option>
                    <option value="WA">Waiting Approval</option>
                    <option value="A">Approved</option>
                    <option value="R">Rejected</option>
                </select>
            </div>
        </div> -->
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped">
                <thead class="bg-blue">
                    <tr>
                        <th class="text-center"> #</th>
                        <th class="text-center">Quotation No.</th>
                        <th class="text-center" width="30%">Customer</th>
                        <th class="text-center">Tanggal Penawaran</th>
                        <th class="text-center">Nilai Penawaran</th>
                        <th class="text-center">Rev</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(function() {
        $('.select2').select2({
            width: '100%'
        });

        $(document).on('change', '#status', function() {
            var status = $('#status').val()
            DataTables(status);
        })

        var status = $('#status').val()
        DataTables(status);

        $('#btnFilter').on('click', function(e) {
            e.preventDefault();
            if ($.fn.dataTable.isDataTable('#example1')) {
                $('#example1').DataTable().ajax.reload(null, true);
            }
        });

        $('#btnReset').on('click', function(e) {
            e.preventDefault();
            $('#start_date, #end_date').val('');
            if ($.fn.dataTable.isDataTable('#example1')) {
                $('#example1').DataTable().ajax.reload(null, true);
            }
        });

        $('#btnExport').on('click', function(e) {
            e.preventDefault();
            var start = $('#start_date').val();
            var end = $('#end_date').val();
            window.location.href = base_url + 'penawaran/export_excel?start_date=' + start + '&end_date=' + end;
        });

        $(document).on('click', '.btn-loss', function(e) {
            e.preventDefault();

            var id_penawaran = $(this).data('id');
            console.log(id_penawaran)

            swal({
                title: "Are you sure?",
                text: "Penawaran ini ditandai sebagai Loss!",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Yes, mark as Loss!",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + 'penawaran/loss',
                        type: 'POST',
                        data: {
                            id_penawaran: id_penawaran,
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 1) {
                                swal("Success!", response.pesan, "success");
                                window.location.href = base_url + active_controller;
                            } else {
                                swal("Failed", response.pesan, "warning");
                            }
                        },
                        error: function() {
                            swal("Error", "Something went wrong.", "error");
                        }
                    });
                }
            });
        });
    });

    function DataTables(status = null) {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            // "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [3, "asc"]
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
                url: base_url + active_controller + 'data_side_penawaran',
                type: "post",
                data: function(d) {
                    d.status = status;
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
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