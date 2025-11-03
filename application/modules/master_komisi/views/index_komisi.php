<?php
$ENABLE_ADD     = has_permission('Master_Komisi.Add');
$ENABLE_MANAGE  = has_permission('Master_Komisi.Manage');
$ENABLE_VIEW    = has_permission('Master_Komisi.View');
$ENABLE_DELETE  = has_permission('Master_Komisi.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-md-6">
                <?php if ($ENABLE_ADD) : ?>
                    <a class="btn btn-success add" href="javascript:void(0)" title="Add">
                        <i class="fa fa-plus"></i> Perhitungan Komisi
                    </a>
                <?php endif; ?>
            </div>

            <div class="col-md-6 text-right">
                <form method="get" id="filter-form" class="form-inline">
                    <div class="form-group">
                        <select name="bulan" id="bulan" class="form-control select2" style="width: 200px;">
                            <option value="">- Pilih Bulan -</option>
                            <?php foreach ($bulan as $b): ?>
                                <option value="<?= $b['bulan_id'] ?>" <?= isset($_GET['bulan']) && $_GET['bulan'] === $b['bulan_id'] ? 'selected' : '' ?>>
                                    <?= $b['bulan'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">
                        <i class="fa fa-filter"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="example1" class="table table-bordered table-striped">
                <thead class="bg-blue">
                    <tr>
                        <th>#</th>
                        <th>Nama Sales</th>
                        <th>Bulan</th>
                        <th>Kinerja Pembayaran Ontime</th>
                        <th>Kinerja Pembayaran Tunggakan</th>
                        <th>Kinerja Penjualan</th>
                        <th>Nilai Komisi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
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

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({});
        DataTables();

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            $('#example1').DataTable().ajax.reload();
        });

        $(document).on('click', '.edit', function(e) {
            var id = $(this).data('id');
            $("#head_title").html("<b>Form Perhitungan Komisi</b>");
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/add_komisi/' + id,
                success: function(data) {
                    $("#dialog-popup").modal();
                    $("#ModalView").html(data);

                }
            })
        });

        $(document).on('click', '.add', function() {
            $("#head_title").html("<b>Form Perhitungan Komisi</b>");
            $.ajax({
                type: 'POST',
                url: siteurl + active_controller + '/add_komisi/',
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
                        url: siteurl + active_controller + 'save_komisi',
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
                        url: siteurl + active_controller + '/delete_komisi',
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

    function DataTables() {
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
                url: base_url + active_controller + 'data_side_komisi',
                type: "post",
                data: function(d) {
                    d.bulan = $('#bulan').val();
                },
                error: function(xhr, error, thrown) {
                    console.log("AJAX Error:", xhr.responseText);
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