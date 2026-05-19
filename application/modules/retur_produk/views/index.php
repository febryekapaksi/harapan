<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr class="bg-blue">
                        <th>#</th>
                        <th>No. Retur</th>
                        <th>Tgl. Retur</th>
                        <th>No. Surat Jalan</th>
                        <th>No. SO</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Option</th>
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
        DataTables()
    });

    function closeRetur(id_sj) {
        swal({
            title: "Close Retur?",
            text: "Barang retur tidak akan dikirim ulang. Piutang akan mengikuti nilai confirm awal. Lanjutkan?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya, Close",
            cancelButtonText: "Batal",
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm) {
            if (!isConfirm) return;

            $.ajax({
                url: siteurl + 'retur_produk/close_retur',
                type: 'POST',
                data: {
                    id_sj: id_sj
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 1) {
                        swal({
                            title: "Berhasil",
                            text: res.pesan,
                            type: "success",
                            timer: 3000
                        });
                        $('#example1').DataTable().ajax.reload();
                    } else {
                        swal({
                            title: "Gagal",
                            text: res.pesan,
                            type: "warning",
                            timer: 4000
                        });
                    }
                },
                error: function() {
                    swal({
                        title: "Error",
                        text: "Terjadi kesalahan, coba lagi.",
                        type: "error",
                        timer: 3000
                    });
                }
            });
        });
    }

    function DataTables() {
        var dataTable = $('#example1').DataTable({
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
                url: siteurl + active_controller + 'data_side_retur',
                type: "post",
                // data: function(d) {},
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