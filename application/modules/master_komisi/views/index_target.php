<?php
$ENABLE_ADD     = has_permission('Master_Komisi.Add');
$ENABLE_MANAGE  = has_permission('Master_Komisi.Manage');
$ENABLE_VIEW    = has_permission('Master_Komisi.View');
$ENABLE_DELETE  = has_permission('Master_Komisi.Delete');
?>
<div class="box box-primary">
    <div class="box-header">
        <?php if ($ENABLE_ADD) : ?>
            <a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i> Set Target</a>
        <?php endif; ?>

        <span class="pull-right">
        </span>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table-target">
            <thead class="bg-blue">
                <tr>
                    <th>Sales</th>
                    <?php foreach ($bulan as $b): ?>
                        <th class="text-center"><?= ucfirst($b['bulan_id']) ?></th>
                    <?php endforeach; ?>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($target as $row): ?>
                    <?php
                    $total = 0;
                    foreach ($bulan as $b) {
                        $key = $b['bulan_id'];
                        $total += isset($row[$key]) ? $row[$key] : 0;
                    }
                    ?>
                    <tr>
                        <td><?= ucfirst($row['nm_karyawan']) ?></td>
                        <?php foreach ($bulan as $b): ?>
                            <td class="text-right">
                                <?= number_format(isset($row[$b['bulan_id']]) ? $row[$b['bulan_id']] : 0, 2) ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center">
                            <?php if ($ENABLE_MANAGE) : ?>
                                <a class="btn btn-primary btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?= $row['id'] ?>"><i class="fa fa-edit"></i>
                                </a>
                            <?php endif; ?>

                            <?php if ($ENABLE_DELETE) : ?>
                                <a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= $row['id'] ?>"><i class="fa fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal untuk add data -->
<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="head_title">Default</h4>
            </div>
            <div class="modal-body" id="ModalView">
                ...
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
        });

        $(document).on('click', '.edit', function(e) {
            var id = $(this).data('id');
            $("#head_title").html("<b>Form Target Penjualan Sales</b>");
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/add_target/' + id,
                success: function(data) {
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '.add', function() {
            $("#head_title").html("<b>Form Target Penjualan Sales</b>");
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/add_target/',
                success: function(data) {
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('submit', '#data_form', function(e) {
            e.preventDefault()
            var data = $('#data_form').serialize();
            // alert(data);
            swal({
                    title: "Anda Yakin?",
                    text: "Data akan diproses!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-info",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    closeOnConfirm: false
                },
                function() {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + 'save_target',
                        dataType: "json",
                        data: data,
                        success: function(data) {
                            if (data.status == '1') {
                                swal({
                                        title: "Sukses",
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
                                text: "Error proccess !",
                                type: "error"
                            })
                        }
                    })
                });

        })


        // DELETE DATA
        $(document).on('click', '.delete', function(e) {
            e.preventDefault()
            var id = $(this).data('id');
            // alert(id);
            swal({
                    title: "Anda Yakin?",
                    text: "Data akan di hapus!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-info",
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    closeOnConfirm: false
                },
                function() {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + active_controller + '/delete_target',
                        dataType: "json",
                        data: {
                            'id': id
                        },
                        success: function(data) {
                            if (data.status == '1') {
                                swal({
                                        title: "Sukses",
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
                                text: "Error proccess !",
                                type: "error"
                            })
                        }
                    })
                });

        })
    });
</script>