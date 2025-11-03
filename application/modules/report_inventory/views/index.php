<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header clearfix">
        <span class="pull-left">
            <a target="_blank" id="btnExportExcel" href="<?= base_url('report_inventory/export_excel') ?>" class="btn btn-success">
                <i class="fa fa-file-excel-o"></i>&emsp;Export Excel
            </a>
        </span>
        <span class="pull-right" style="max-width:250px">
            <div class="input-group">
                <span class="input-group-addon">Pilih Tanggal</span>
                <input type="text" name="tanggal" id="filterTanggal" class="form-control datepicker tanggal">
            </div>
        </span>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="tableReport">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Id Product</th>
                        <th>Code Product</th>
                        <th>Product</th>
                        <th>Qty Stock</th>
                        <th>Qty Booking</th>
                        <th>Qty Free</th>
                        <th>Gudang</th>
                        <th>Tanggal Stock</th>
                        <th>Costbook</th>
                        <th>Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
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
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });

        $('#filterTanggal').on('change', function() {
            DataTables();
        });

        $('#btnExportExcel').on('click', function(e) {
            const tanggal = $('#filterTanggal').val();

            if (!tanggal) {
                e.preventDefault()
                swal("Error", "Silahkan pilih Tanggal terlebih dahulu", "warning");
                return;
            }

            this.href = siteurl + active_controller + 'export_excel?tanggal=' + encodeURIComponent(tanggal);
        });
    });

    function DataTables() {
        var table = $('#tableReport').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            autoWidth: false,
            destroy: true,
            searching: true,
            responsive: true,
            aaSorting: [
                [1, "desc"]
            ],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }],
            sPaginationType: "simple_numbers",
            iDisplayLength: 10,
            aLengthMenu: [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            ajax: {
                url: siteurl + active_controller + 'data_side_stock',
                type: "post",
                data: function(d) {
                    d.tanggal = $('#filterTanggal').val();
                },
                cache: false
            }
        });
    }
</script>