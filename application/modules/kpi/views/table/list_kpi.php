<?php
$ENABLE_MANAGE = has_permission('KPI.Manage');
$ENABLE_VIEW = has_permission('KPI.View');
$ENABLE_DELETE = has_permission('KPI.Delete');
?>

<table class="table table-bordered table-striped" id="table_kpi">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th>Divisi Name</th>
            <th width="15%">Periode</th>
            <th width="15%">Skor KPI</th>
            <th width="12%">Status Realisasi</th>
            <th width="15%">Create by</th>
            <th width="25%">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($kpi_headers)): ?>
            <?php $no = 1;
            foreach ($kpi_headers as $row): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>

                    <td><?= htmlspecialchars($row['divisi_name']) ?></td>

                    <td class="text-center">
                        <?php if (!empty($row['periode'])): ?>
                            <span class="label label-primary">
                                <i class="fa fa-calendar"></i>
                                Jan <?= $row['periode'] ?> - Dec <?= $row['periode'] ?>
                            </span>
                        <?php else: ?>
                            <span class="label label-default">-</span>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($row['skor_kpi']) ?></td>

                    <td class="text-center">
                        <?php if ($row['is_realisasi'] == 2): ?>
                            <span class="label label-success">
                                <i class="fa fa-check-circle"></i> Realisasi Lengkap
                            </span>

                            <?php if ($row['is_close'] == 1): ?>
                                <br>
                                <span class="label label-danger" style="margin-top:3px; display:inline-block;">
                                    <i class="fa fa-lock"></i> Close Period
                                </span>
                            <?php endif; ?>

                        <?php elseif ($row['is_realisasi'] == 1): ?>
                            <span class="label label-info">
                                <i class="fa fa-clock-o"></i> Proses Realisasi
                            </span>
                        <?php else: ?>
                            <span class="label label-warning">
                                <i class="fa fa-hourglass-start"></i> Belum Direalisasi
                            </span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?= $row['create_by'] ?><br>
                        <span class="text-muted small">
                            <i><?= date('d-m-Y H:i', strtotime($row['create_date'])) ?></i>
                        </span>
                    </td>

                    <td class="text-center">
                        <?php if ($ENABLE_MANAGE): ?>

                            <?php if ($row['is_close'] == 1): ?>
                                <a href="<?= site_url('kpi/realisasi/' . $row['id']) ?>"
                                    class="btn btn-info btn-xs"
                                    style="width:100px; margin:1px;"
                                    title="Lihat Realisasi">
                                    <i class="fa fa-eye"></i> View Realisasi
                                </a>
                            <?php else: ?>
                                <a href="javascript:void(0)"
                                    class="btn btn-primary btn-xs btn-realisasi"
                                    style="width:100px; margin:1px;"
                                    data-id="<?= $row['id'] ?>"
                                    data-is-realisasi="<?= $row['is_realisasi'] ?>"
                                    data-divisi="<?= htmlspecialchars($row['divisi_name']) ?>"
                                    title="Isi Realisasi">
                                    <i class="fa fa-pencil"></i> Isi Realisasi
                                </a>

                                <?php if ($row['is_realisasi'] == 2): ?>
                                    <button type="button"
                                        class="btn btn-success btn-xs btn-close-period"
                                        style="width:100px; margin:1px;"
                                        data-id="<?= $row['id'] ?>"
                                        data-periode="<?= $row['periode'] ?>"
                                        data-divisi="<?= htmlspecialchars($row['divisi_name']) ?>"
                                        title="Tutup Periode">
                                        <i class="fa fa-lock"></i> Tutup Periode
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                        <?php endif; ?>

                        <?php if ($ENABLE_VIEW): ?>
                            <a href="<?= site_url('kpi/view/' . $row['id']) ?>"
                                class="btn btn-info btn-xs"
                                style="width:100px; margin:1px;"
                                title="Lihat Indikator">
                                <i class="fa fa-eye"></i> View Indikator
                            </a>
                        <?php endif; ?>

                        <?php if ($ENABLE_MANAGE && $row['is_realisasi'] == 0 && $row['is_close'] == 0): ?>
                            <a href="<?= site_url('kpi/edit/' . $row['id']) ?>"
                                class="btn btn-warning btn-xs"
                                style="width:100px; margin:1px;"
                                title="Edit Indikator">
                                <i class="fa fa-edit"></i> Edit Indikator
                            </a>
                        <?php endif; ?>

                        <?php if ($ENABLE_DELETE && $row['is_realisasi'] == 0 && $row['is_close'] == 0): ?>
                            <a href="<?= site_url('kpi/delete/' . $row['id']) ?>"
                                class="btn btn-danger btn-xs btn-delete"
                                style="width:100px; margin:1px;"
                                data-id="<?= $row['id'] ?>"
                                data-divisi="<?= htmlspecialchars($row['divisi_name']) ?>"
                                title="Hapus KPI">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>