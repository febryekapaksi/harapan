<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#tab-tanda-terima" role="tab" data-toggle="tab">
                    <i class="fa fa-file-text-o"></i> Tanda Terima Nota Retur
                </a>
            </li>
            <li role="presentation">
                <a href="#tab-penerimaan-uang" role="tab" data-toggle="tab">
                    <i class="fa fa-money"></i> Penerimaan Uang dari Supplier
                </a>
            </li>
        </ul>

        <div class="tab-content" style="padding-top: 15px;">
            <!-- Tab 1: Tanda Terima Nota Retur -->
            <div role="tabpanel" class="tab-pane active" id="tab-tanda-terima">
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

            <!-- Tab 2: Penerimaan Uang dari Supplier -->
            <div role="tabpanel" class="tab-pane" id="tab-penerimaan-uang">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="table-penerimaan">
                        <thead>
                            <tr class="bg-green">
                                <th width="30">No</th>
                                <th>No. Retur</th>
                                <th>Supplier</th>
                                <th>Total Retur</th>
                                <th>Settlement</th>
                                <th>Sisa</th>
                                <th>Status</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function() {
    initTableTandaTerima();
    initTablePenerimaan();
});

function initTableTandaTerima() {
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
}

function initTablePenerimaan() {
    $('#table-penerimaan').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10,
        ajax: {
            url: siteurl + 'retur_pembelian/data_penerimaan_uang',
            type: 'POST'
        }
    });
}
</script>
