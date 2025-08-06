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
                    <tr class="bg-blue">
                        <th>#</th>
                        <th>No. Muat Kendaraan</th>
                        <th>No. SPK Delivery</th>
                        <th>Nopol Kendaraan</th>
                        <th>Pengiriman</th>
                        <th>Muatan</th>
                        <th>Tanggal Muat</th>
                        <th>Status</th>
                        <th>Option</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalViewLoading" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close text-white" data-dismiss="modal"><i class="fa fa-times"></i></button>
                <h4 class="modal-title"><b>Detail Loading</b></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-3">
                    <tr>
                        <th>Pengiriman</th>
                        <td id="view-pengiriman"></td>
                        <th>Kendaraan</th>
                        <td id="view-kendaraan"></td>
                    </tr>
                    <tr>
                        <th>Tanggal Muat</th>
                        <td id="view-tgl-muat"></td>
                        <th>Total Berat</th>
                        <td id="view-total-berat"></td>
                    </tr>
                </table>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr class="bg-light">
                            <th>No SPK</th>
                            <th>No SO</th>
                            <th>Customer</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Berat (Kg)</th>
                        </tr>
                    </thead>
                    <tbody id="view-detail-body">
                        <!-- diisi via JS -->
                    </tbody>
                </table>
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
            const id = $(this).data('id');

            $.ajax({
                url: siteurl + 'loading/get_view',
                type: 'GET',
                data: {
                    no_loading: id
                },
                success: function(res) {
                    const data = JSON.parse(res);

                    // Header Info
                    $('#view-pengiriman').text(data.header.pengiriman);
                    $('#view-kendaraan').text(data.header.nopol);
                    $('#view-tgl-muat').text(data.header.tanggal_muat);
                    $('#view-total-berat').text(parseFloat(data.header.total_berat).toFixed(2));

                    // Grouping Detail by No SPK
                    const grouped = {};
                    data.detail.forEach(row => {
                        if (!grouped[row.no_delivery]) {
                            grouped[row.no_delivery] = {
                                customer: row.customer,
                                items: []
                            };
                        }
                        grouped[row.no_delivery].items.push(row);
                    });

                    // Generate HTML
                    let html = '';
                    Object.keys(grouped).forEach(no_spk => {
                        const group = grouped[no_spk];

                        // Header SPK
                        html += `
                            <tr style='background-color:#f0f0f0; font-weight:bold;'>
                                <td colspan="6">No SPK : ${no_spk} - ${group.customer}</td>
                            </tr>
                            `;

                        // Produk per SPK
                        group.items.forEach(item => {
                            html += `
                                <tr>
                                <td>${item.no_delivery}</td>
                                <td>${item.no_so}</td>
                                <td>${item.customer}</td>
                                <td>${item.product}</td>
                                <td>${item.qty_spk}</td>
                                <td>${parseFloat(item.jumlah_berat).toFixed(2)}</td>
                                </tr>
                            `;
                        });
                    });

                    $('#view-detail-body').html(html);
                    $('#modalViewLoading').modal('show');
                },
                error: function() {
                    swal("Error", "Gagal mengambil data detail.", "error");
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