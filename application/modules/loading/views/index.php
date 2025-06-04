<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <span class="pull-left">
            <a href="<?= site_url('loading/add') ?>" class='btn btn-primary'>Atur Muatan</a>
        </span>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tableLoading">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Muat Kendaraan</th>
                        <th>Nopol Kendaraan</th>
                        <th>Pengiriman</th>
                        <th>Muatan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalLoading" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Detail Muatan Kendaraan | <button class="btn btn-sm btn-primary float-right ml-2" id="printDetailLoading"><i class="fa fa-print"></i> Cetak</button></h4>

            </div>
            <div id="print-area-loading">
                <div class="modal-body">
                    <table class="table table-bordered" id="tabelModal" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No SPK</th>
                                <th>No SO</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Berat (Kg)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        DataTables()

        $(document).on('click', '.view-loading', function() {
            const no_loading = $(this).data('id');

            $.ajax({
                url: siteurl + 'loading/get_detail_loading',
                type: 'GET',
                data: {
                    no_loading
                },
                success: function(res) {
                    const data = JSON.parse(res);
                    let html = '';

                    data.forEach((item) => {
                        html += `
                    <tr>
                        <td>${item.no_delivery}</td>
                        <td>${item.no_so}</td>
                        <td>${item.customer}</td>
                        <td>${item.product}</td>
                        <td class="text-center">${item.qty_spk}</td>
                        <td class="text-right">${parseFloat(item.jumlah_berat).toFixed(2)}</td>
                    </tr>
                `;
                    });

                    $('#modalLoading').modal('show');
                    $('#tabelModal tbody').html(html);
                }
            });
        });

        $(document).on('click', '#printDetailLoading', function() {
            const printContents = document.getElementById('print-area-loading').innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload(); // agar modal tertutup kembali
        });
    });

    function DataTables() {
        var dataTable = $('#tableLoading').DataTable({
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
                url: siteurl + active_controller + 'data_side_loading',
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