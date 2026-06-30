<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Pilih Retur untuk Buat Tanda Terima</h3>
        <div class="box-tools pull-right">
            <a href="<?= site_url('retur_pembelian/tanda_terima') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-reply"></i> Kembali
            </a>
        </div>
    </div>
    <div class="box-body">
        <?php if (empty($retur_list)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Tidak ada data retur yang memerlukan Tanda Terima Nota Retur.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table-pilih-retur">
                <thead>
                    <tr class="bg-blue">
                        <th width="30">No</th>
                        <th>No. Retur</th>
                        <th>No. Invoice</th>
                        <th>Nama Supplier</th>
                        <th>Tgl Retur</th>
                        <th>Total Retur</th>
                        <th width="100">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 0; foreach ($retur_list as $r): $no++; ?>
                    <tr>
                        <td class="text-center"><?= $no ?></td>
                        <td><?= $r['no_retur'] ?></td>
                        <td><?= $r['no_invoice'] ?></td>
                        <td><?= $r['nama_supplier'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tgl_retur'])) ?></td>
                        <td class="text-right"><?= number_format($r['total_retur'], 2, ',', '.') ?></td>
                        <td class="text-center">
                            <a href="<?= site_url('retur_pembelian/create_tanda_terima/' . $r['id']) ?>" class="btn btn-xs btn-success" title="Buat Tanda Terima">
                                <i class="fa fa-plus"></i> Create
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
$(document).ready(function() {
    $('#table-pilih-retur').DataTable({
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10
    });
});
</script>
