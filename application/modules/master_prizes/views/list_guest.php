<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="example1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode Voucher</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Tanggal Klaim</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $i => $c): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($c['code']) ?></td>
                            <td><?= htmlspecialchars($c['guest_name']) ?></td>
                            <td><?= htmlspecialchars($c['guest_phone']) ?></td>
                            <td><?= htmlspecialchars($c['guest_email']) ?></td>
                            <td><?= htmlspecialchars($c['created_at']) ?></td>
                            <td class="text-center">
                                <?php if ($c['status'] == "PENDING") { ?>
                                    <span class="badge badge-pill bg-yellow"><?= $c['status'] ?></span>
                                <?php } else if ($c['status'] == "APPROVED") { ?>
                                    <span class="badge badge-pill bg-green"><?= $c['status'] ?></span>
                                <?php } else { ?>
                                    <span class="badge badge-pill bg-red"><?= $c['status'] ?></span>
                                <?php } ?>
                            </td>
                            <td></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script>
    $(function() {
        var table = $('#example1').DataTable({
            orderCellsTop: true,
            fixedHeader: true
        });
        $("#form-area").hide();
    });
</script>