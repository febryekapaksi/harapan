<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-money"></i> Daftar Retur - Penerimaan Uang dari Supplier</h3>
    </div>
    <div class="box-body">
        <div class="callout callout-info">
            <p><i class="fa fa-info-circle"></i> Menampilkan data retur pembelian yang masih memiliki <strong>SISA RETUR</strong> dan belum selesai. Klik <strong>View</strong> untuk melakukan penerimaan uang.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table-terima-uang">
                <thead>
                    <tr class="bg-blue">
                        <th width="30">No</th>
                        <th>No. Retur</th>
                        <th>No. Invoice</th>
                        <th>Nama Supplier</th>
                        <th>Tgl Retur</th>
                        <th>Total Nilai</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function() {
    initDataTable();
});

function initDataTable() {
    $('#table-terima-uang').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'terima_uang_supplier/data',
            type: 'POST',
            error: function() {
                swal("Error", "Gagal memuat data", "error");
            }
        }
    });
}
</script>
