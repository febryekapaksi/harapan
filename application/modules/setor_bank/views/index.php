<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
    <div class="box-header">
        <span class="pull-left">
            <a href="<?= base_url('setor_bank/create') ?>" class="btn btn-success"><i class="fa fa-plus"></i>&emsp;Buat Setoran</a>
        </span>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr class="bg-blue">
                        <th>No</th>
                        <th>Kode Setor</th>
                        <th>Tanggal Setor</th>
                        <th>No Penerimaan</th>
                        <th>Nilai Setor</th>
                        <th></th>
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

        // Cancel setoran
        $(document).on('click', '.btn-cancel-setor', function() {
            var id = $(this).data('id');

            swal({
                title: "Konfirmasi Pembatalan",
                text: "Apakah Anda yakin ingin membatalkan setoran " + id + "? Proses ini akan mengembalikan status penerimaan dan membatalkan jurnal.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d9534f",
                confirmButtonText: "Ya, Batalkan!",
                cancelButtonText: "Tidak",
                closeOnConfirm: false
            }, function(confirm) {
                if (confirm) {
                    $.ajax({
                        type: 'POST',
                        url: siteurl + 'setor_bank/cancel/' + id,
                        dataType: 'json',
                        success: function(result) {
                            if (result.status) {
                                swal({
                                    title: 'Berhasil!',
                                    text: result.message,
                                    type: 'success'
                                }, function() {
                                    $('#example1').DataTable().ajax.reload(null, false);
                                });
                            } else {
                                swal('Gagal!', result.message, 'warning');
                            }
                        },
                        error: function() {
                            swal('Error!', 'Terjadi kesalahan, silakan coba lagi.', 'error');
                        }
                    });
                }
            });
        });
    });

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
                url: siteurl + active_controller + 'data_side_setoran_bank',
                type: "post",
                // data: function(d) {
                //     d.sales_order = sales_order
                // },
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