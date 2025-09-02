<?php
$ENABLE_ADD     = has_permission('Master_Prizes.Add');
$ENABLE_MANAGE  = has_permission('Master_Prizes.Manage');
$ENABLE_VIEW    = has_permission('Master_Prizes.View');
$ENABLE_DELETE  = has_permission('Master_Prizes.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <span class="pull-left">
            <?php if ($ENABLE_ADD) : ?>
                <a class="btn btn-success add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i> Tambah Hadiah</a>
            <?php endif; ?>
        </span>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr class="bg-blue">
                        <th>#</th>
                        <th style="min-width: 200px;">Nama</th>
                        <th>Stock Total</th>
                        <th>Stock Claim</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)) {
                    } else {
                        $numb = 0;
                        foreach ($results as $record) {
                            $numb++; ?>
                            <tr>
                                <td><?= $numb; ?></td>
                                <td><?= $record->name ?></td>
                                <td><?= $record->stock_total ?></td>
                                <td><?= $record->stock_claimed ?></td>
                                <td><?= $record->status ?></td>
                                <td>
                                    <?php if ($ENABLE_MANAGE) : ?>
                                        <a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Edit" data-id="<?= $record->id ?>"><i class="fa fa-edit"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($ENABLE_DELETE) : ?>
                                        <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= $record->id ?>"><i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php }
                    }  ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="head_title"></h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).on('click', '.add', function() {
        var id = ($(this).data('id') == undefined) ? '' : $(this).data('id')
        let title = (id == '') ? 'Tambah' : 'Edit'
        $("#head_title").html(`<b>${title} Prizes<b>`);
        $.ajax({
            type: 'GET',
            url: base_url + active_controller + 'add/' + id,
            success: function(data) {
                $("#dialog-popup").modal();
                $("#ModalView").html(data);
            }
        });
    });

    // DELETE DATA
    $(document).on('click', '.delete', function(e) {
        e.preventDefault()
        var id = $(this).data('id');
        // alert(id);
        swal({
                title: "Are you sure ?",
                text: "Delete this data",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: 'POST',
                    url: base_url + active_controller + 'hapus',
                    dataType: "json",
                    data: {
                        'id': id
                    },
                    success: function(data) {
                        if (data.status == '1') {
                            swal({
                                    title: "Success",
                                    text: data.pesan,
                                    type: "success"
                                },
                                function() {
                                    window.location.reload(true);
                                })
                        } else {
                            swal({
                                title: "Error",
                                text: data.pesan,
                                type: "error"
                            })
                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error Process !",
                            type: "error"
                        })
                    }
                })
            });
    })

    $(document).on('submit', '#data_form', function(e) {
        e.preventDefault()
        var data = $('#data_form').serialize();

        swal({
                title: "Are you sure ?",
                text: "Process this data",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-info",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            },
            function() {
                $.ajax({
                    type: 'POST',
                    url: base_url + active_controller + 'add',
                    dataType: "json",
                    data: data,
                    success: function(data) {
                        if (data.status == '1') {
                            swal({
                                    title: "Success",
                                    text: data.pesan,
                                    type: "success"
                                },
                                function() {
                                    window.location.reload(true);
                                })
                        } else {
                            swal({
                                title: "Error",
                                text: data.pesan,
                                type: "error"
                            })

                        }
                    },
                    error: function() {
                        swal({
                            title: "Error",
                            text: "Error Process !",
                            type: "error"
                        })
                    }
                })
            });

    })

    $(function() {
        var table = $('#example1').DataTable({
            orderCellsTop: true,
            fixedHeader: true
        });
        $("#form-area").hide();
    });
</script>