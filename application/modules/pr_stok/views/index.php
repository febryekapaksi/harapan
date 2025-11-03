<?php
$ENABLE_ADD     = has_permission('PR_Stok.Add');
$ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
$ENABLE_VIEW    = has_permission('PR_Stok.View');
$ENABLE_DELETE  = has_permission('PR_Stok.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-md" style='float:right;' href="<?= base_url('pr_stok/add') ?>" title="Add"><i class="fa fa-plus"></i>&emsp; Add Request</a>
        <?php endif; ?>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr class="bg-blue">
                        <th class="text-center">#</th>
                        <th class="text-center">No. PR</th>
                        <th class="text-center">Kategori PR</th>
                        <th class="text-center">Nama Barang</th>
                        <th class="text-center" style="min-width: 8% !important;">Qty (Pack)</th>
                        <th class="text-center">Dibutuhkan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Request By</th>
                        <th class="text-center">Request Date</th>
                        <th class="text-center">Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Closing PR</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="$('#dialog-popup').modal('hide')">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Close PR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();

        $(document).on('click', '.close_pr_modal', function() {
            var so_number = $(this).data('so_number');

            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + 'close_pr_modal',
                data: {
                    'so_number': so_number
                },
                cache: false,
                success: function(result) {
                    $('#ModalView').html(result);
                    $('#dialog-popup').modal('show');
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    })
                }
            });
        });

        $(document).on('click', '.close_pr', function() {
            var so_number = $(this).data('so_number');

            swal({
                title: 'Are you sure to close this PR ?',
                showCancelButton: true,
                confirmButtonText: 'Close',
                confirmButtonColor: 'red',
                type: 'warning'
            }, function(onConfirm) {
                if (onConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'close_pr',
                        data: {
                            'so_number': so_number
                        },
                        cache: false,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                    title: 'Success !',
                                    text: 'PR has been closed',
                                    type: 'success'
                                }, function(onConfirm) {
                                    location.reload(true);
                                });
                            } else {
                                swal({
                                    title: 'Failed !',
                                    text: 'PR has not been closed',
                                    type: 'warning'
                                });
                            }
                        },
                        error: function(result) {
                            swal({
                                title: 'Error !',
                                text: 'Please try again later !',
                                type: 'error'
                            });
                        }
                    });
                }
            });
        });

        $(document).on('submit', '#frm-data', function(e) {
            e.preventDefault();

            var data = new FormData($('#frm-data')[0]);
            $.ajax({
                type: 'post',
                url: siteurl + active_controller + 'close_pr',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(result) {
                    if (result.status == '1') {
                        swal({
                            title: 'Success !',
                            text: 'PR has been closed',
                            type: 'success'
                        }, function(onConfirm) {
                            location.reload(true);
                        });
                    } else {
                        swal({
                            title: 'Failed !',
                            text: 'PR has not been closed',
                            type: 'warning'
                        });
                    }
                },
                error: function(result) {
                    swal({
                        title: 'Error !',
                        text: 'Please try again later !',
                        type: 'error'
                    });
                }
            });
        });

        $(document).on('click', '.detail', function() {
            var so_number = $(this).data('so_number');
            // alert(id);
            $("#head_title").html("<b>Detail>");
            $.ajax({
                type: 'POST',
                url: base_url + active_controller + 'detail',
                data: {
                    'so_number': so_number,
                },
                success: function(data) {
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        // DELETE DATA
        $(document).on('click', '.booking', function(e) {
            e.preventDefault()
            var so_number = $(this).data('so_number');
            // alert(id);
            swal({
                    title: "Anda Yakin?",
                    text: "Process Booking Material & PR !",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-info",
                    confirmButtonText: "Ya!",
                    cancelButtonText: "Batal",
                    closeOnConfirm: false
                },
                function() {
                    $.ajax({
                        type: 'POST',
                        url: base_url + active_controller + 'process_booking',
                        dataType: "json",
                        data: {
                            'so_number': so_number
                        },
                        success: function(result) {
                            if (result.status == '1') {
                                swal({
                                        title: "Sukses",
                                        text: result.pesan,
                                        type: "success"
                                    },
                                    function() {
                                        window.location.reload(true);
                                    })
                            } else {
                                swal({
                                    title: "Error",
                                    text: result.pesan,
                                    type: "error"
                                })

                            }
                        },
                        error: function() {
                            swal({
                                title: "Error",
                                text: "Data error. Gagal request Ajax",
                                type: "error"
                            })
                        }
                    })
                });

        })
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
                url: base_url + active_controller + 'data_side_pr_stok',
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

<!-- Trash -->
<!-- var product = $("#product").val();
    var costcenter = $("#costcenter").val();
    DataTables(costcenter, product);

    $(document).on('change', '#costcenter', function() {
        var costcenter = $("#costcenter").val();
        var product = $("#product").val();
        DataTables(costcenter, product);
    });

    $(document).on('change', '#product', function() {
        var costcenter = $("#costcenter").val();
        var product = $("#product").val();
        DataTables(costcenter, product);
    }); -->

<!-- <div class="form-group row" hidden>
            <div class="col-md-1">
                <b>Product Type</b>
            </div>
            <div class="col-md-3">
                <select name='product' id='product' class='form-control input-sm chosen-select'>
                    <option value='0'>All Product Type</option>
                    <?php
                    foreach (get_list_inventory_lv1('product') as $val => $valx) {
                        echo "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group row" hidden>
            <div class="col-md-1">
                <b>Costcenter</b>
            </div>
            <div class="col-md-3">
                <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
                    <option value='0'>All Costcenter</option>
                    <?php
                    foreach (get_costcenter() as $val => $valx) {
                        echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div> -->