<?php
$ENABLE_SAVE = has_permission('KPI.Manage');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>

<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin-bottom: 15px;
    }

    #table_realisasi {
        white-space: nowrap;
        min-width: 100%;
    }

    .realisasi-cell.merah {
        background: #dc3545 !important;
    }

    .realisasi-cell.merah input {
        background: transparent !important;
        color: white !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    .realisasi-cell.merah input::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    .realisasi-cell.kuning {
        background: #ffc107 !important;
    }

    .realisasi-cell.kuning input {
        background: transparent !important;
        color: #333 !important;
        border-color: rgba(0, 0, 0, 0.2) !important;
    }

    .realisasi-cell.hijau {
        background: #28a745 !important;
    }

    .realisasi-cell.hijau input {
        background: transparent !important;
        color: white !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
    }

    .realisasi-cell.hijau input::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }

    .realisasi-input {
        text-align: center;
        width: 100%;
    }

    .skor-kpi-final {
        font-size: 18px;
        font-weight: bold;
    }

    .realisasi-input {
        position: relative;
    }

    .indicator-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 12px;
        line-height: 1.6;
        white-space: nowrap;
        z-index: 10000 !important;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        min-width: 280px;
        text-align: left;
    }

    .indicator-tooltip::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 8px solid transparent;
        border-top-color: #764ba2;
    }

    .indicator-tooltip.show {
        opacity: 1;
        transform: translateX(-50%) translateY(-12px);
    }

    .indicator-tooltip.tooltip-bottom {
        bottom: auto;
        top: 100%;
        transform: translateX(-50%) translateY(8px);
    }

    .indicator-tooltip.tooltip-bottom::before {
        top: auto;
        bottom: 100%;
        border-top-color: transparent;
        border-bottom-color: #667eea;
    }

    .indicator-tooltip.tooltip-bottom.show {
        transform: translateX(-50%) translateY(12px);
    }

    .indicator-tooltip .tooltip-row {
        display: flex;
        margin-bottom: 6px;
    }

    .indicator-tooltip .tooltip-row:last-child {
        margin-bottom: 0;
    }

    .indicator-tooltip .tooltip-label {
        font-weight: 600;
        min-width: 80px;
        color: rgba(255, 255, 255, 0.9);
    }

    .indicator-tooltip .tooltip-value {
        font-weight: 400;
        color: #fff;
        flex: 1;
    }

    .indicator-tooltip .tooltip-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.2);
        margin: 8px 0;
    }
</style>

<div class="box">
    <div class="box-header">
        <?php if ($is_closed): ?>
            View Realisasi KPI <?= $periode_year ?> - Divisi <?= htmlspecialchars($header->divisi_name) ?>
        <?php else: ?>
            Isi Realisasi KPI <?= $periode_year ?> - Divisi <?= htmlspecialchars($header->divisi_name) ?>
        <?php endif; ?>
    </div>
    <div class="box-body">
        <?php if ($is_closed): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-lock"></i> Periode KPI Sudah Close</h4>
                Data realisasi untuk tahun <?= $periode_year ?> telah final dan <strong>tidak dapat diubah lagi</strong>.<br>
            </div>
        <?php endif; ?>
        <form id="form_realisasi">
            <input type="hidden" name="header_id" value="<?= $header->id ?>">
            <input type="hidden" id="is_bobot" value="<?= $header->is_bobot ?>">

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-striped table-kpi" id="table_realisasi">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 200px;">Item</th>
                            <th rowspan="2" style="min-width: 200px;">Indikator</th>
                            <th rowspan="2" style="min-width: 80px;">Bobot</th>
                            <th rowspan="2" style="min-width: 80px;">Target</th>
                            <th rowspan="2" style="min-width: 80px;">Satuan</th>
                            <?php foreach ($months as $month): ?>
                                <th style="min-width: 100px;"><?= $month ?></th>
                            <?php endforeach; ?>
                            <th rowspan="2" style="min-width: 80px;">Skor Realisasi</th>
                            <th rowspan="2" style="min-width: 80px;">Skor Pencapaian (%)</th>
                            <th rowspan="2" style="min-width: 80px;">Tipe Penjumlahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr data-item-id="<?= $item->id ?>"
                                data-target="<?= $item->target ?>"
                                data-satuan="<?= $item->satuan ?>"
                                data-sistem="<?= $item->sistem_penilaian ?>"
                                data-bobot="<?= $item->bobot ?>"
                                data-item-name="<?= htmlspecialchars($item->item) ?>"
                                data-indikator-name="<?= htmlspecialchars($item->indikator) ?>"
                                data-target-display="<?= $item->target_display ?>"
                                data-satuan-name="<?= htmlspecialchars(ucfirst($item->satuan)) ?>"
                                data-thresholds='<?= $item->threshold_json ?>'>

                                <td><?= htmlspecialchars($item->item) ?></td>
                                <td><?= htmlspecialchars($item->indikator) ?></td>
                                <td><?= ($item->bobot > 0) ? htmlspecialchars($item->bobot) : '-' ?></td>
                                <td><?= $item->target_display ?></td>
                                <td><?= htmlspecialchars(ucfirst($item->satuan)) ?></td>

                                <?php foreach ($months as $idx => $month): ?>
                                    <td class="realisasi-cell">
                                        <?php
                                        $existing_value = '';
                                        $month_number = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                                        $periode_key = $periode_year . '-' . $month_number;

                                        if (isset($realisations[$item->id])) {
                                            foreach ($realisations[$item->id] as $real) {
                                                if (substr($real->periode, 0, 7) === $periode_key) {
                                                    $existing_value = $real->value;
                                                    break;
                                                }
                                            }
                                        }

                                        $input_disabled = $is_closed ? 'disabled' : '';
                                        $input_readonly = $is_closed ? 'readonly' : '';
                                        $bg_color = $is_closed ? 'style="background-color: #f9f9f9;"' : '';
                                        ?>

                                        <input type="text"
                                            name="realisasi[<?= $item->id ?>][<?= $month ?>]"
                                            class="form-control realisasi-input"
                                            value="<?= htmlspecialchars($existing_value) ?>"
                                            data-month="<?= $month ?>"
                                            data-tooltip-month="<?= $month ?>"
                                            data-tooltip-item="<?= htmlspecialchars($item->item) ?>"
                                            data-tooltip-indikator="<?= htmlspecialchars($item->indikator) ?>"
                                            data-tooltip-bobot="<?= ($item->bobot > 0) ? $item->bobot : '-' ?>"
                                            data-tooltip-target="<?= $item->target_display ?>"
                                            data-tooltip-satuan="<?= htmlspecialchars(ucfirst($item->satuan)) ?>"
                                            placeholder="0"
                                            <?= $input_disabled ?>
                                            <?= $input_readonly ?>
                                            <?= $bg_color ?>>
                                    </td>
                                <?php endforeach; ?>
                                <td class="skor-realisasi text-center">-</td>
                                <td class="skor-pencapaian text-center">-</td>
                                <td>
                                    <?php
                                    if ($item->sistem_penilaian == 0) {
                                        echo 'Rata-rata';
                                    } elseif ($item->sistem_penilaian == 1) {
                                        echo 'Akumulatif';
                                    } elseif ($item->sistem_penilaian == 2) {
                                        echo 'Angka Terakhir';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?= 5 + count($months) + 1 ?>" class="text-right" style="font-weight: bold; font-size: 16px;">
                                Skor KPI:
                            </td>
                            <td colspan="2" class="text-center skor-kpi-final" id="skor-kpi-final">0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="text-right" style="margin-top: 20px;">
                <?php if (!$is_closed && $ENABLE_SAVE): ?>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa fa-save"></i> Simpan Realisasi
                    </button>
                <?php endif; ?>

                <a href="<?= site_url('kpi') ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali ke List KPI
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        function createModernTooltip($input) {
            var month = $input.data('tooltip-month');
            var item = $input.data('tooltip-item');
            var indikator = $input.data('tooltip-indikator');
            var bobot = $input.data('tooltip-bobot');
            var target = $input.data('tooltip-target');
            var satuan = $input.data('tooltip-satuan');

            var tooltipHtml = `
                <div class="indicator-tooltip">
                    <div class="tooltip-row">
                        <span class="tooltip-label">📅 Bulan:</span>
                        <span class="tooltip-value">${month}</span>
                    </div>
                    <div class="tooltip-divider"></div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">📋 Item:</span>
                        <span class="tooltip-value">${item}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">📊 Indikator:</span>
                        <span class="tooltip-value">${indikator}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">⚖️ Bobot:</span>
                        <span class="tooltip-value">${bobot}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">🎯 Target:</span>
                        <span class="tooltip-value">${target}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">📏 Satuan:</span>
                        <span class="tooltip-value">${satuan}</span>
                    </div>
                </div>
            `;

            return tooltipHtml;
        }

        $('.realisasi-input').on('mouseenter focus', function() {
            var $input = $(this);
            var $cell = $input.closest('td');
            var $row = $input.closest('tr');

            $('.indicator-tooltip').remove();

            var rowIndex = $row.index();
            var isTopRow = rowIndex < 3; 

            var $tooltip = $(createModernTooltip($input));

            if (isTopRow) {
                $tooltip.addClass('tooltip-bottom');
            }

            $cell.css('position', 'relative').append($tooltip);

            setTimeout(function() {
                $tooltip.addClass('show');
            }, 10);
        });

        $('.realisasi-input').on('mouseleave blur', function() {
            var $tooltip = $(this).closest('td').find('.indicator-tooltip');
            $tooltip.removeClass('show');

            setTimeout(function() {
                $tooltip.remove();
            }, 300);
        });

        function formatValue(val, satuan) {
            if (!val || isNaN(val)) return '-';

            if (satuan === 'nominal') {
                return 'Rp ' + parseFloat(val).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
            if (satuan === 'persen') {
                return parseFloat(val).toFixed(2) + '%';
            }
            return parseFloat(val).toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }

        function getStatus(val, thresholds) {
            if (val === null || val === undefined || val === '' || isNaN(val)) return null;

            val = parseFloat(val);

            if (thresholds.merah) {
                var merahMin = parseFloat(thresholds.merah.min) || 0;
                var merahMax = thresholds.merah.max !== null ? parseFloat(thresholds.merah.max) : Infinity;
                if (val >= merahMin && val <= merahMax) {
                    return 'merah';
                }
            }

            if (thresholds.kuning) {
                var kuningMin = parseFloat(thresholds.kuning.min) || 0;
                var kuningMax = thresholds.kuning.max !== null ? parseFloat(thresholds.kuning.max) : Infinity;
                if (val >= kuningMin && val <= kuningMax) {
                    return 'kuning';
                }
            }

            if (thresholds.hijau) {
                var hijauMin = parseFloat(thresholds.hijau.min) || 0;
                var hijauMax = thresholds.hijau.max !== null ? parseFloat(thresholds.hijau.max) : Infinity;
                if (val >= hijauMin && val <= hijauMax) {
                    return 'hijau';
                }
            }

            return null;
        }

        function updateRowCalculation($row) {
            var thresholdsData = $row.data('thresholds');
            var target = parseFloat($row.data('target')) || 0;
            var sistem = parseInt($row.data('sistem'));
            var satuan = $row.data('satuan');
            var bobot = parseFloat($row.data('bobot')) || 0;

            var thresholds = {};
            if (typeof thresholdsData === 'string') {
                thresholds = JSON.parse(thresholdsData);
            } else {
                thresholds = thresholdsData;
            }

            if (thresholds.merah && thresholds.merah.max === null) {
                thresholds.merah.max = Infinity;
            }
            if (thresholds.kuning && thresholds.kuning.max === null) {
                thresholds.kuning.max = Infinity;
            }
            if (thresholds.hijau && thresholds.hijau.max === null) {
                thresholds.hijau.max = Infinity;
            }

            var values = [];
            var filledMonthsCount = 0;

            $row.find('.realisasi-input').each(function() {
                var inputVal = $(this).val();

                if (inputVal !== '' && inputVal !== null && inputVal !== undefined) {
                    var val = parseFloat(inputVal);
                    if (!isNaN(val)) {
                        values.push(val);
                        filledMonthsCount++;
                    }
                }
            });

            var skor = 0;
            var validValues = values;

            if (validValues.length > 0) {
                if (sistem === 0) {
                    skor = validValues.reduce((a, b) => a + b, 0) / validValues.length;
                } else if (sistem === 1) {
                    skor = values.reduce((a, b) => a + b, 0);
                } else if (sistem === 2) {
                    skor = validValues[validValues.length - 1];
                }
            }

            $row.find('.skor-realisasi').text(formatValue(skor, satuan));

            var skorPencapaian = 0;
            if (target > 0 && filledMonthsCount > 0) {
                var adjustedTarget = target;

                if (sistem === 1 && filledMonthsCount > 0) {
                    adjustedTarget = target * filledMonthsCount;
                }

                skorPencapaian = (skor / adjustedTarget) * 100;
            }

            $row.find('.skor-pencapaian').text(skorPencapaian >= 0 ? skorPencapaian.toFixed(2) : '-');
            $row.data('skor-pencapaian', skorPencapaian);

            $row.find('.realisasi-input').each(function() {
                var $input = $(this);
                var $cell = $input.closest('td');
                var inputVal = $input.val();

                $cell.removeClass('merah kuning hijau');

                if (inputVal !== '' && inputVal !== null && inputVal !== undefined) {
                    var val = parseFloat(inputVal);
                    if (!isNaN(val)) {
                        var status = getStatus(val, thresholds);
                        if (status) {
                            $cell.addClass(status);
                        }
                    }
                }
            });
        }

        function updateSkorKPIFinal() {
            var isBobot = parseInt($('#is_bobot').val());
            var totalSkor = 0;
            var totalIndikator = 0;

            $('tbody tr').each(function() {
                var skorPencapaian = parseFloat($(this).data('skor-pencapaian'));
                var bobot = parseFloat($(this).data('bobot')) || 0;

                if (!isNaN(skorPencapaian)) {
                    totalIndikator++;

                    if (isBobot === 1) {
                        totalSkor += (skorPencapaian * bobot / 100);
                    } else {
                        totalSkor += skorPencapaian;
                    }
                }
            });

            var skorKPIFinal = 0;
            if (totalIndikator > 0) {
                if (isBobot === 1) {
                    skorKPIFinal = totalSkor;
                } else {
                    skorKPIFinal = (totalSkor / totalIndikator);
                }
            }

            $('#skor-kpi-final').text(skorKPIFinal.toFixed(2));
        }

        $('tbody tr').each(function() {
            updateRowCalculation($(this));
        });
        updateSkorKPIFinal();

        <?php if ($is_closed): ?>
            $('.realisasi-input').off('blur keypress');
            $('#form_realisasi').off('submit');
            $('.realisasi-input').css('cursor', 'not-allowed');
        <?php else: ?>
            $('.realisasi-input').on('blur', function() {
                var $row = $(this).closest('tr');
                updateRowCalculation($row);
                updateSkorKPIFinal();
            });

            $('.realisasi-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).blur();
                }
            });

            $('.realisasi-input').on('keypress', function(e) {
                var charCode = e.which;
                if (charCode === 46 || charCode === 8 || charCode === 9 || charCode === 27 || charCode === 13 ||
                    (charCode === 65 && e.ctrlKey === true) ||
                    (charCode === 67 && e.ctrlKey === true) ||
                    (charCode === 86 && e.ctrlKey === true) ||
                    (charCode === 88 && e.ctrlKey === true)) {
                    return;
                }
                if ((charCode < 48 || charCode > 57) && charCode !== 46) {
                    e.preventDefault();
                }
            });

            $('#form_realisasi').submit(function(e) {
                e.preventDefault();

                var $form = $(this);
                var $submitBtn = $form.find('button[type="submit"]');

                $submitBtn.prop('disabled', true);

                $.ajax({
                    url: '<?= site_url('kpi/save_realisasi') ?>',
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.status_info,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location = '<?= site_url('kpi') ?>';
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: res.message || 'Terjadi kesalahan',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                            $submitBtn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan data',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        $submitBtn.prop('disabled', false);
                    }
                });
            });
        <?php endif; ?>
    });
</script>