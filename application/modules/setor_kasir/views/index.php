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