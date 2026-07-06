<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table-tanda-terima">
                <thead>
                    <tr class="bg-blue">
                        <th width="30">No</th>
                        <th>No. Retur</th>
                        <th>No. Invoice</th>
                        <th>Nama Supplier</th>
                        <th>Tgl Retur</th>
                        <th>Total Retur</th>
                        <th width="150">Action</th>
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
    $('#table-tanda-terima').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'retur_pembelian/data_retur_nota',
            type: 'POST'
        }
    });
});
</script>
