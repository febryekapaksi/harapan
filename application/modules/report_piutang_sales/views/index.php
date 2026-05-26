<div class="box box-primary">

    <div class="box-body">

        <!-- Filter Cut-off Tanggal -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-5">
                <label>Pilih Tanggal</label>
                <div class="input-group">
                    <input type="date" id="tanggal" class="form-control input-sm"
                        value="<?= htmlspecialchars($tanggal ?? '') ?>">
                    <span class="input-group-btn">
                        <button class="btn btn-primary btn-sm" id="btnFilter">
                            <i class="fa fa-search"></i> Tampilkan
                        </button>
                        <button class="btn btn-default btn-sm" id="btnReset" title="Tampilkan semua">
                            <i class="fa fa-times"></i> Reset
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <!-- Tabel Ringkasan Piutang per Sales -->
        <div class="table-responsive" style="max-height:450px; overflow-y:auto;">
            <table class="table table-bordered table-hover" id="tblPiutang"
                style="margin-bottom:0;">
                <thead>
                    <tr class="bg-blue">
                        <th style="width:40px; ">No</th>
                        <th style="">Nama Sales</th>
                        <th class="text-right" style="">Saldo Piutang</th>
                        <th class="text-center" style="width:100px; ">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($piutang_sales)): ?>
                        <?php $no = 1;
                        foreach ($piutang_sales as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= ucfirst(htmlspecialchars($row['nm_lengkap'])) ?></td>
                                <td class="text-right">
                                    <strong><?= number_format($row['saldo_piutang'], 0, ',', '.') ?></strong>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('report_piutang_sales/detail?id_user=' . $row['id_user'] . (!empty($tanggal) ? '&tanggal=' . $tanggal : '')) ?>"
                                        class="btn btn-warning btn-xs">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                <i class="fa fa-info-circle"></i> Tidak ada data piutang
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-light-blue-active">
                        <th colspan="2" class="text-right">
                            <strong>Total Piutang Sales</strong>
                        </th>
                        <th class="text-right">
                            <strong style="font-size:14px;">
                                <?= number_format($total_piutang ?? 0, 0, ',', '.') ?>
                            </strong>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('#btnFilter').on('click', function() {
            var tgl = $('#tanggal').val();
            var url = siteurl + 'report_piutang_sales';
            if (tgl) url += '?tanggal=' + tgl;
            window.location.href = url;
        });

        $('#btnReset').on('click', function() {
            window.location.href = siteurl + 'report_piutang_sales';
        });

        $('#tanggal').on('keypress', function(e) {
            if (e.which === 13) {
                $('#btnFilter').trigger('click');
            }
        });
    });
</script>