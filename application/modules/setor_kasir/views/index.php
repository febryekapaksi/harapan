<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
    <div class="box-header">
        <span class="pull-left">
            <a href="<?= base_url('setor_kasir/create') ?>" class="btn btn-success"><i class="fa fa-plus"></i>&emsp;Buat Setoran</a>
        </span>
        <span class="pull-right">
            <a href="javascript:void(0)" class="btn btn-warning" id="btnSetorBank"><i class="fa fa-bank"></i>&emsp;Setor ke Bank</a>
        </span>
    </div>
    <div class="box-body">
        <div class="row" style="margin-bottom:15px;">
            <div class="col-md-3">
                <label>Tanggal Dari</label>
                <input type="date" class="form-control" id="filter_tgl_dari">
            </div>
            <div class="col-md-3">
                <label>Tanggal Sampai</label>
                <input type="date" class="form-control" id="filter_tgl_sampai">
            </div>
            <div class="col-md-3" style="padding-top:25px;">
                <button type="button" class="btn btn-primary" id="btnFilter"><i class="fa fa-search"></i>&emsp;Filter</button>
                <button type="button" class="btn btn-default" id="btnReset"><i class="fa fa-refresh"></i>&emsp;Reset</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr class="bg-blue">
                        <th>No</th>
                        <th>Kode Setor</th>
                        <th>Tanggal Setor</th>
                        <th>Sales</th>
                        <th>No Penerimaan</th>
                        <th>Nilai Setor</th>
                        <th>Status</th>
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

        // Cancel setoran kasir
        $(document).on('click', '.btn-cancel-setor-kasir', function() {
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
                        url: siteurl + 'setor_kasir/cancel/' + id,
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

        $(document).on('click', '#btnSetorBank', function() {
            let selectedIDs = [];
            $('.check-setor-kasir:checked').each(function() {
                selectedIDs.push($(this).data('id'));
            });

            if (selectedIDs.length === 0) {
                swal("Warning", "Silakan pilih minimal satu data setor kasir.", "warning");
                return;
            }

            // SweetAlert Konfirmasi
            swal({
                title: "Konfirmasi",
                text: "Yakin ingin memproses " + selectedIDs.length + " data ke setor bank?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#00a65a",
                cancelButtonColor: "#c9302c",
                confirmButtonText: "Ya, Proses",
                cancelButtonText: "Batal"
            }, function(confirm) {
                if (confirm) {
                    const url = siteurl + 'setor_kasir/add_from_kasir?ids=' + selectedIDs.join(',');
                    window.location.href = url;
                }
            });
        });

        $('#btnFilter').click(function() {
            $('#example1').DataTable().ajax.reload(null, false);
        });

        $('#btnReset').click(function() {
            $('#filter_tgl_dari').val('');
            $('#filter_tgl_sampai').val('');
            $('#example1').DataTable().ajax.reload(null, false);
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
                url: siteurl + active_controller + 'data_side_setoran_kasir',
                type: "post",
                data: function(d) {
                    d.tgl_dari = $('#filter_tgl_dari').val();
                    d.tgl_sampai = $('#filter_tgl_sampai').val();
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