<?php
$mode = isset($mode) ? $mode : 'add';
$is_readonly = ($mode === 'view');
$header_id = isset($header) ? $header->id : 0;
$form_action = ($mode === 'edit') ? 'kpi/update' : 'kpi/save';

$ENABLE_ADD = has_permission('KPI.Add');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/nouislider/nouislider.min.css') ?>">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css"> -->
 <!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- noUiSlider CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script>


<script src="<?= base_url('assets/plugins/nouislider/nouislider.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.0/nouislider.min.js"></script> -->

<style>
    input[type=number]::-webkit-outer-spin-button,
    input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type=number] {
        -moz-appearance: textfield;
    }

    .table-responsive {
        overflow-x: auto;
    }

    #table_items {
        width: auto;
        min-width: 1200px;
        table-layout: auto;
    }

    .table-kpi th:nth-child(1),
    .table-kpi td:nth-child(1) {
        min-width: 140px;
    }

    .table-kpi th:nth-child(2),
    .table-kpi td:nth-child(2) {
        min-width: 220px;
    }

    .table-kpi th:nth-child(3),
    .table-kpi td:nth-child(3) {
        min-width: 140px;
    }

    .table-kpi th:nth-child(4),
    .table-kpi td:nth-child(4) {
        min-width: 100px;
    }

    .table-kpi th:nth-child(5),
    .table-kpi td:nth-child(5) {
        min-width: 120px;
    }

    .table-kpi th:nth-child(6),
    .table-kpi td:nth-child(6) {
        min-width: 280px;
    }

    .table-kpi th:nth-child(7),
    .table-kpi td:nth-child(7) {
        min-width: 120px;
    }

    .table-kpi th:nth-child(8),
    .table-kpi td:nth-child(8) {
        min-width: 80px;
    }

    <?php if (!$is_readonly): ?>.table-kpi th:nth-child(10),
    .table-kpi td:nth-child(10) {
        min-width: 30px;
    }

    <?php endif; ?>.threshold-cell {
        min-width: 280px;
        padding: 8px !important;
        background: #f9f9f9;
    }

    .threshold-type-select {
        width: 100%;
        margin-bottom: 8px;
        font-size: 11px;
        padding: 2px 4px;
    }

    .slider-wrapper {
        padding: 10px 8px;
        position: relative;
    }

    .slider-element {
        margin: 0;
    }

    .threshold-display-mini {
        display: flex;
        gap: 3px;
        margin-top: 8px;
        font-size: 10px;
    }

    .threshold-box-mini {
        flex: 1;
        text-align: center;
        padding: 3px 2px;
        border-radius: 3px;
        font-weight: bold;
        line-height: 1.2;
    }

    .threshold-box-mini.merah {
        background: #dc3545;
        color: white;
    }

    .threshold-box-mini.kuning {
        background: #ffc107;
        color: #333;
    }

    .threshold-box-mini.hijau {
        background: #28a745;
        color: white;
    }

    .threshold-box-mini .label {
        font-size: 9px;
    }

    .threshold-box-mini .value {
        font-size: 10px;
        font-weight: bold;
    }

    .threshold-cell .noUi-horizontal {
        height: 8px;
    }

    .threshold-cell .noUi-handle {
        height: 16px;
        width: 16px;
        top: -4px;
        border-radius: 50%;
        cursor: grab;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        border: 2px solid #fff;
    }

    .threshold-cell .noUi-handle:active {
        cursor: grabbing;
    }

    .threshold-cell .noUi-handle:before,
    .threshold-cell .noUi-handle:after {
        display: none;
    }

    .threshold-cell .noUi-connect {
        background: #28a745;
    }

    .threshold-cell .noUi-tooltip {
        font-size: 10px;
        padding: 2px 4px;
        border: none;
        background: rgba(0, 0, 0, 0.75);
        color: white;
    }

    .target-info {
        text-align: center;
        font-size: 10px;
        color: #666;
        margin-top: 5px;
        font-weight: bold;
    }

    .merah-manual,
    .kuning-manual {
        font-size: 10px !important;
        padding: 3px 4px !important;
        height: 24px !important;
        text-align: center;
    }

    .merah-manual {
        border-color: #dc3545;
    }

    .kuning-manual {
        border-color: #ffc107;
    }

    .merah-manual:focus {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .kuning-manual:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
</style>

<div class="box">
    <div class="box-body">
        <form id="form_kpi">
            <?php if ($mode === 'edit'): ?>
                <input type="hidden" name="header_id" value="<?= $header_id ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Divisi <span class="text-danger">*</span></label>
                        <select name="divisi" id="divisi_select" class="form-control select2" <?= $is_readonly ? 'disabled' : 'required' ?> style="width:100%">
                            <option value="">- Pilih Divisi -</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?= $div->id_divisi ?>" data-name="<?= htmlspecialchars($div->nm_divisi) ?>"
                                    <?php
                                    if (isset($header)) {
                                        echo ($header->divisi_name === $div->nm_divisi) ? 'selected' : '';
                                    }
                                    ?>>
                                    <?= htmlspecialchars($div->nm_divisi) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="divisi_name" id="divisi_name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Menggunakan bobot? <span class="text-danger">*</span></label>
                        <select name="bobot_enabled" id="bobot_enabled" class="form-control" <?= $is_readonly ? 'disabled' : 'required' ?>>
                            <option value="1" <?= (isset($header) && $header->is_bobot == 1) ? 'selected' : '' ?>>Ya</option>
                            <option value="0" <?= (!isset($header) || $header->is_bobot == 0) ? 'selected' : '' ?>>Tidak</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <?php if (!$is_readonly): ?>
                <button type="button" class="btn btn-primary btn-sm" style="margin-bottom: 10px;" id="add_row">
                    <i class="fa fa-plus"></i> Tambah Item
                </button>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-bordered table-kpi" id="table_items">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Indikator</th>
                            <th>PIC</th>
                            <th>Target</th>
                            <th>Satuan</th>
                            <th>Deskripsi Formula</th>
                            <th>Sistem Penilaian</th>
                            <th class="bobot_col" style="display:none;">Bobot</th>
                            <th>Threshold</th>
                            <?php if (!$is_readonly): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="item_rows">
                        <?php if (isset($items) && !empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <input type="text" name="item[]" class="form-control" autocomplete="off" value="<?= htmlspecialchars($item->item) ?>" <?= $is_readonly ? 'readonly' : 'required' ?>>
                                    </td>
                                    <td>
                                        <input type="text" name="indikator[]" class="form-control" autocomplete="off" value="<?= htmlspecialchars($item->indikator) ?>" <?= $is_readonly ? 'readonly' : 'required' ?>>
                                    </td>
                                    <td>
                                        <select name="pic_id[]" class="form-control pic-select" <?= $is_readonly ? 'disabled' : 'required' ?> style="width:100%">
                                            <option value="">- Pilih -</option>
                                            <?php foreach ($employees as $emp): ?>
                                                <option value="<?= $emp->id ?>" <?= ($item->pic_id == $emp->id) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($emp->nm_karyawan) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="target[]" class="form-control target-input" step="1" value="<?= $item->target ?>" <?= $is_readonly ? 'readonly' : 'required' ?>>
                                    </td>
                                    <td>
                                        <select name="satuan[]" class="form-control satuan-select" <?= $is_readonly ? 'disabled' : 'required' ?>>
                                            <option value="angka" <?= ($item->satuan === 'angka') ? 'selected' : '' ?>>Angka</option>
                                            <option value="persen" <?= ($item->satuan === 'persen') ? 'selected' : '' ?>>Persen</option>
                                            <option value="nominal" <?= ($item->satuan === 'nominal') ? 'selected' : '' ?>>Nominal</option>
                                        </select>
                                    </td>
                                    <td>
                                        <textarea name="formula[]" class="form-control" rows="3" <?= $is_readonly ? 'readonly' : 'required' ?>><?= htmlspecialchars($item->formula_description) ?></textarea>
                                    </td>
                                    <td>
                                        <select name="sistem_penilaian[]" class="form-control" <?= $is_readonly ? 'disabled' : 'required' ?>>
                                            <option value="0" <?= ($item->sistem_penilaian == 0) ? 'selected' : '' ?>>Rata-rata</option>
                                            <option value="1" <?= ($item->sistem_penilaian == 1) ? 'selected' : '' ?>>Akumulatif</option>
                                            <option value="2" <?= ($item->sistem_penilaian == 2) ? 'selected' : '' ?>>Angka Terakhir</option>
                                        </select>
                                    </td>
                                    <td class="bobot_col">
                                        <input type="number" name="bobot[]" class="form-control" step="0.01" value="<?= $item->bobot ?>" min="0" max="100" <?= $is_readonly ? 'readonly' : '' ?>>
                                    </td>
                                    <td class="threshold-cell" data-type="<?= $item->threshold_type ?>" data-merah="<?= $item->merah_value ?>" data-kuning="<?= $item->kuning_value ?>">
                                        <!-- Threshold content -->
                                    </td>
                                    <?php if (!$is_readonly): ?>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-xs remove_row" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <?php if (!$is_readonly): ?>
                <button type="submit" class="btn btn-success">
                    <i class="fa fa-save"></i> <?= ($mode === 'edit') ? 'Update KPI' : 'Simpan KPI' ?>
                </button>
            <?php endif; ?>

            <a href="<?= site_url('kpi') ?>" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </form>
    </div>
</div>

<!-- Row Template (hanya untuk mode add/edit) -->
<?php if (!$is_readonly): ?>
    <table style="display:none;">
        <tbody id="row_template">
            <tr>
                <td>
                    <input type="text" name="item[]" class="form-control" placeholder="e.g. Instagram" autocomplete="off" required>
                </td>
                <td>
                    <input type="text" name="indikator[]" class="form-control" placeholder="e.g Deskripsi indikator" autocomplete="off" required>
                </td>
                <td>
                    <select name="pic_id[]" class="form-control pic-select" required style="width:100%">
                        <option value="">- Pilih -</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp->id ?>"><?= htmlspecialchars($emp->nm_karyawan) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" name="target[]" class="form-control target-input" step="1" placeholder="e.g. 4000" required>
                </td>
                <td>
                    <select name="satuan[]" class="form-control satuan-select" required>
                        <option value="angka">Angka</option>
                        <option value="persen">Persen</option>
                        <option value="nominal">Nominal</option>
                    </select>
                </td>
                <td>
                    <textarea name="formula[]" class="form-control" rows="3" placeholder="e.g. Jumlah jangkauan / rumus perhitungan" required></textarea>
                </td>
                <td>
                    <select name="sistem_penilaian[]" class="form-control" required>
                        <option value="0">Rata-rata</option>
                        <option value="1">Akumulatif</option>
                        <option value="2">Angka Terakhir</option>
                    </select>
                </td>
                <td class="bobot_col">
                    <input type="number" name="bobot[]" class="form-control" step="0.01" value="0" min="0" max="100">
                </td>
                <td class="threshold-cell">
                    <!-- createThresholdHTML() -->
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-xs remove_row" title="Hapus">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<script>
    const isReadonly = <?= $is_readonly ? 'true' : 'false' ?>;
    const formMode = '<?= $mode ?>';

    $(document).ready(function() {
        $('select[name="divisi"]').select2();

        $('#divisi_select').change(function() {
            var selectedOption = $(this).find('option:selected');
            var divisiName = selectedOption.data('name');
            $('#divisi_name').val(divisiName);
        }).trigger('change');

        <?php if (isset($items) && !empty($items)): ?>
            $('#item_rows tr').each(function() {
                var $row = $(this);
                var $thresholdCell = $row.find('.threshold-cell');
                var thresholdHTML = createThresholdHTML(isReadonly);
                $thresholdCell.html(thresholdHTML);

                var thresholdType = $thresholdCell.attr('data-type') || '1';
                var merahValue = parseFloat($thresholdCell.attr('data-merah')) || 0;
                var kuningValue = parseFloat($thresholdCell.attr('data-kuning')) || 0;
                var target = parseFloat($row.find('.target-input').val()) || 0;

                $thresholdCell.find('.threshold-type-select').val(thresholdType);

                if (!isReadonly) {
                    initializeSlider($row);

                    if (target > 0 && (merahValue > 0 || kuningValue > 0)) {
                        var sliderElement = $row.find('.slider-element')[0];

                        if (sliderElement && sliderElement.noUiSlider) {
                            toggleThresholdControls($row, true);
                            sliderElement.removeAttribute('disabled');

                            var percent1 = (merahValue / target) * 100;
                            var percent2 = (kuningValue / target) * 100;

                            sliderElement.noUiSlider.set([percent1, percent2]);

                            updateThresholdDisplay($row, [percent1, percent2]);

                            $row.find('.merah-manual').val(merahValue);
                            $row.find('.kuning-manual').val(kuningValue);
                        }
                    }
                } else {
                    displayThresholdValues($row, thresholdType, merahValue, kuningValue, target);
                }

                $row.find('.pic-select').select2({
                    width: '100%',
                    disabled: isReadonly
                });
            });
        <?php endif; ?>

        $('#bobot_enabled').change(function() {
            if ($(this).val() == '1') {
                $('.bobot_col').show();
            } else {
                $('.bobot_col').hide();
            }
        }).trigger('change');

        <?php if (!$is_readonly): ?>
            $('#add_row').click(function() {
                var $row = $('#row_template tr').clone();
                var $thresholdCell = $row.find('.threshold-cell');

                $thresholdCell.html(createThresholdHTML(false));

                $('#item_rows').append($row);

                $row.find('.pic-select').select2({
                    width: '100%',
                    dropdownParent: $row.closest('table')
                });

                initializeSlider($row);
            });

            $(document).on('click', '.remove_row', function() {
                var $row = $(this).closest('tr');

                var $select = $row.find('.pic-select');
                if ($select.length) {
                    $select.select2('destroy');
                }

                var sliderElement = $row.find('.slider-element')[0];
                if (sliderElement && sliderElement.noUiSlider) {
                    sliderElement.noUiSlider.destroy();
                }

                $row.remove();
            });


            // Form submit
            $('#form_kpi').submit(function(e) {
                e.preventDefault();

                if ($('#item_rows tr').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Tambahkan minimal 1 item KPI'
                    });
                    return;
                }

                if ($('#bobot_enabled').val() == '1') {
                    let totalBobot = 0;
                    let hasEmptyBobot = false;

                    $('input[name="bobot[]"]').each(function() {
                        const bobotValue = parseFloat($(this).val()) || 0;

                        if (!$(this).val() || bobotValue <= 0) {
                            hasEmptyBobot = true;
                        }

                        totalBobot += bobotValue;
                    });

                    // if (hasEmptyBobot) {
                    //     Swal.fire({
                    //         icon: 'warning',
                    //         title: 'Bobot Belum Lengkap',
                    //         text: 'Pastikan semua bobot sudah diisi dengan nilai lebih dari 0'
                    //     });
                    //     return;
                    // }

                    if (totalBobot > 100) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Total Bobot Melebihi 100%',
                            html: `Total bobot saat ini: <b>${totalBobot.toFixed(2)}%</b><br>Maksimal bobot adalah <b>100%</b>`,
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }

                    if (totalBobot < 100) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Total Bobot Kurang dari 100%',
                            html: `Total bobot saat ini: <b>${totalBobot.toFixed(2)}%</b><br>Total bobot harus tepat <b>100%</b>`,
                            confirmButtonText: 'Mengerti'
                        });
                        return;
                    }
                }

                let hasEmptyTarget = false;
                $('.target-input:not([readonly])').each(function() {
                    if (!$(this).val() || parseFloat($(this).val()) <= 0) {
                        hasEmptyTarget = true;
                        return false;
                    }
                });

                // if (hasEmptyTarget) {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Perhatian',
                //         text: 'Pastikan semua Target sudah diisi dan lebih dari 0'
                //     });
                //     return;
                // }

                let hasEmptyThreshold = false;
                let emptyThresholdMsg = '';
                $('.merah-input').each(function(index) {
                    var merah = $(this).val();
                    var kuning = $('.kuning-input').eq(index).val();
                    var target = $('.target-input').eq(index).val();

                    if (target && parseFloat(target) > 0) {
                        if (!merah || !kuning) {
                            hasEmptyThreshold = true;
                            emptyThresholdMsg = 'Pastikan semua Threshold (Merah & Kuning) sudah diisi pada item dengan Target yang terisi';
                            return false;
                        }
                    }
                });

                if (hasEmptyThreshold) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: emptyThresholdMsg
                    });
                    return;
                }

                Swal.fire({
                    title: 'Loading...',
                    text: formMode === 'edit' ? 'Mengupdate data KPI' : 'Menyimpan data KPI',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '<?= site_url($form_action) ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 1500,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                window.location = '<?= site_url('kpi') ?>';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Terjadi kesalahan'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan server'
                        });
                    }
                });
            });
        <?php endif; ?>

        $(document).on('click', '.slider-element[disabled], .threshold-type-select:disabled, .merah-manual:disabled, .kuning-manual:disabled', function(e) {
            e.preventDefault();

            Swal.fire({
                icon: 'info',
                title: 'Threshold Belum Aktif',
                html: 'Silakan isi <b>Target</b> terlebih dahulu untuk mengaktifkan pengaturan threshold',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#3085d6'
            });
        });
    });

    function createThresholdHTML(readonly) {
        var html = `
            <select class="form-control threshold-type-select" ${readonly ? 'disabled' : ''}>
                <option value="1">↑ Higher Better</option>
                <option value="2">↓ Lower Better</option>
            </select>

            <div class="slider-wrapper">
                <div class="slider-element"></div>
            </div>

            <div class="threshold-display-mini">
                <div class="threshold-box-mini merah">
                    <div class="label"></div>
                    <div class="value merah-text">≤0</div>
                </div>
                <div class="threshold-box-mini kuning">
                    <div class="label"></div>
                    <div class="value kuning-text">0-0</div>
                </div>
                <div class="threshold-box-mini hijau">
                    <div class="label"></div>
                    <div class="value hijau-text">≥0</div>
                </div>
            </div>

            <div class="target-info">
                Target: <span class="target-display">-</span>
            </div>`;

        if (!readonly) {
            html += `
            <div style="margin-top: 8px; display: flex; gap: 4px; font-size: 10px;">
                <div style="flex: 1;">
                    <input type="number" class="form-control input-sm merah-manual" placeholder="Merah" step="1" title="Batas maksimal zona merah" disabled>
                </div>
                <div style="flex: 1;">
                    <input type="number" class="form-control input-sm kuning-manual" placeholder="Kuning" step="1" title="Batas maksimal zona kuning" disabled>
                </div>
            </div>`;
        }

        html += `
            <input type="hidden" name="merah[]" class="merah-input">
            <input type="hidden" name="kuning[]" class="kuning-input">
            <input type="hidden" name="hijau[]" class="hijau-input">
            <input type="hidden" name="threshold_type[]" class="threshold-type-input">`;

        return html;
    }

    function displayThresholdValues($row, thresholdType, merahValue, kuningValue, target) {
        const satuan = $row.find('.satuan-select').val();

        function formatValue(val) {
            if (satuan === 'nominal') {
                return 'Rp' + Math.round(val).toLocaleString('id-ID');
            } else if (satuan === 'persen') {
                return val.toFixed(1) + '%';
            } else {
                return Math.round(val).toLocaleString('id-ID');
            }
        }

        let targetDisplay = formatValue(target);
        $row.find('.target-display').text(targetDisplay);

        if (thresholdType === '1') {
            $row.find('.merah-text').text('≤' + formatValue(merahValue));
            $row.find('.kuning-text').text(formatValue(merahValue) + '-' + formatValue(kuningValue));
            $row.find('.hijau-text').text('≥' + formatValue(kuningValue));
        } else {
            $row.find('.hijau-text').text('≤' + formatValue(merahValue));
            $row.find('.kuning-text').text(formatValue(merahValue) + '-' + formatValue(kuningValue));
            $row.find('.merah-text').text('≥' + formatValue(kuningValue));
        }

        $row.find('.threshold-type-input').val(thresholdType);
        $row.find('.merah-input').val(merahValue);
        $row.find('.kuning-input').val(kuningValue);
        $row.find('.hijau-input').val(kuningValue);
    }

    function toggleThresholdControls($row, enable) {
        const $thresholdSelect = $row.find('.threshold-type-select');
        const $merahManual = $row.find('.merah-manual');
        const $kuningManual = $row.find('.kuning-manual');
        const sliderElement = $row.find('.slider-element')[0];

        if (enable) {
            $thresholdSelect.prop('disabled', false);
            $merahManual.prop('disabled', false);
            $kuningManual.prop('disabled', false);
            if (sliderElement && sliderElement.noUiSlider) {
                sliderElement.removeAttribute('disabled');
            }
        } else {
            $thresholdSelect.prop('disabled', true);
            $merahManual.prop('disabled', true).val('');
            $kuningManual.prop('disabled', true).val('');
            if (sliderElement && sliderElement.noUiSlider) {
                sliderElement.setAttribute('disabled', true);
            }
        }
    }

    function initializeSlider($row) {
        const sliderElement = $row.find('.slider-element')[0];
        const $target = $row.find('.target-input');
        const $thresholdType = $row.find('.threshold-type-select');
        const $satuan = $row.find('.satuan-select');
        const $merahManual = $row.find('.merah-manual');
        const $kuningManual = $row.find('.kuning-manual');

        let isUpdatingFromManual = false;

        toggleThresholdControls($row, false);

        noUiSlider.create(sliderElement, {
            start: [25, 75],
            connect: [true, true, true],
            range: {
                'min': 0,
                'max': 100
            },
            step: 0.1,
            tooltips: [{
                to: function(value) {
                    return Math.round(value * 10) / 10 + '%';
                }
            }, {
                to: function(value) {
                    return Math.round(value * 10) / 10 + '%';
                }
            }]
        });

        sliderElement.setAttribute('disabled', true);

        $target.on('input', function() {
            const targetValue = parseFloat($(this).val());

            if (targetValue && targetValue > 0) {
                toggleThresholdControls($row, true);
                sliderElement.removeAttribute('disabled');
                updateThresholdDisplay($row, sliderElement.noUiSlider.get());
            } else {
                toggleThresholdControls($row, false);
                sliderElement.setAttribute('disabled', true);
                $row.find('.target-display').text('-');
                $row.find('.merah-text').text('0');
                $row.find('.kuning-text').text('0-0');
                $row.find('.hijau-text').text('0');
            }
        });

        sliderElement.noUiSlider.on('update', function(values) {
            if (!isUpdatingFromManual) {
                const target = parseFloat($target.val()) || 100;
                const val1 = Math.round(target * parseFloat(values[0]) / 100);
                const val2 = Math.round(target * parseFloat(values[1]) / 100);

                if (val2 <= val1) {
                    const newPercent2 = ((val1 + 1) / target) * 100;
                    if (newPercent2 <= 100) {
                        isUpdatingFromManual = true;
                        sliderElement.noUiSlider.set([null, newPercent2]);
                        isUpdatingFromManual = false;
                        return;
                    }
                }

                updateThresholdDisplay($row, values);
                $merahManual.val(val1);
                $kuningManual.val(val2);
            }
        });

        $merahManual.on('blur', function() {
            const target = parseFloat($target.val());
            if (!target || target === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Target Belum Diisi',
                    text: 'Silakan isi Target terlebih dahulu'
                });
                $(this).val('');
                return;
            }

            const manualValue = parseFloat($(this).val());
            if (isNaN(manualValue) || manualValue === '') {
                return;
            }

            if (manualValue < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai Tidak Valid',
                    text: 'Batas Merah tidak boleh negatif'
                });
                $(this).val($row.find('.merah-input').val());
                return;
            }

            if (manualValue > target) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai Tidak Valid',
                    text: 'Batas Merah tidak boleh melebihi Target'
                });
                $(this).val($row.find('.merah-input').val());
                return;
            }

            const currentKuningValue = parseFloat($kuningManual.val());

            if (!isNaN(currentKuningValue) && manualValue >= currentKuningValue) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Batas Merah harus lebih kecil dari Batas Kuning'
                });
                $(this).val($row.find('.merah-input').val());
                return;
            }

            const percent = (manualValue / target) * 100;

            isUpdatingFromManual = true;
            sliderElement.noUiSlider.set([percent, null]);
            isUpdatingFromManual = false;

            updateThresholdDisplay($row, sliderElement.noUiSlider.get());
        });

        $kuningManual.on('blur', function() {
            const target = parseFloat($target.val());
            if (!target || target === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Target Belum Diisi',
                    text: 'Silakan isi Target terlebih dahulu'
                });
                $(this).val('');
                return;
            }

            const manualValue = parseFloat($(this).val());
            if (isNaN(manualValue) || manualValue === '') {
                return;
            }

            if (manualValue < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai Tidak Valid',
                    text: 'Batas Kuning tidak boleh negatif'
                });
                $(this).val($row.find('.kuning-input').val());
                return;
            }

            if (manualValue > target) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nilai Tidak Valid',
                    text: 'Batas Kuning tidak boleh melebihi Target'
                });
                $(this).val($row.find('.kuning-input').val());
                return;
            }

            const currentMerahValue = parseFloat($merahManual.val());

            if (!isNaN(currentMerahValue) && manualValue <= currentMerahValue) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Batas Kuning harus lebih besar dari Batas Merah'
                });
                $(this).val($row.find('.kuning-input').val());
                return;
            }

            const percent = (manualValue / target) * 100;

            isUpdatingFromManual = true;
            sliderElement.noUiSlider.set([null, percent]);
            isUpdatingFromManual = false;

            updateThresholdDisplay($row, sliderElement.noUiSlider.get());
        });

        $thresholdType.change(function() {
            const target = parseFloat($target.val());
            if (target && target > 0) {
                updateThresholdDisplay($row, sliderElement.noUiSlider.get());
            }
        });

        $satuan.change(function() {
            const target = parseFloat($target.val());
            if (target && target > 0) {
                updateThresholdDisplay($row, sliderElement.noUiSlider.get());
            }
        });
    }

    function updateThresholdDisplay($row, values) {
        const target = parseFloat($row.find('.target-input').val()) || 100;
        const type = $row.find('.threshold-type-select').val();
        const satuan = $row.find('.satuan-select').val();

        const percent1 = parseFloat(values[0]);
        const percent2 = parseFloat(values[1]);

        const value1 = Math.round(target * percent1 / 100);
        const value2 = Math.round(target * percent2 / 100);

        function formatValue(val) {
            if (satuan === 'nominal') {
                return 'Rp' + Math.round(val).toLocaleString('id-ID');
            } else if (satuan === 'persen') {
                return val.toFixed(1) + '%';
            } else {
                return Math.round(val).toLocaleString('id-ID');
            }
        }

        let targetDisplay = formatValue(target);
        $row.find('.target-display').text(targetDisplay);

        if (type === '1') {
            $row.find('.merah-text').text('≤' + formatValue(value1));
            $row.find('.kuning-text').text(formatValue(value1) + '-' + formatValue(value2));
            $row.find('.hijau-text').text('≥' + formatValue(value2));

            $row.find('.merah-input').val(value1);
            $row.find('.kuning-input').val(value2);
            $row.find('.hijau-input').val(value2);

        } else {
            $row.find('.hijau-text').text('≤' + formatValue(value1));
            $row.find('.kuning-text').text(formatValue(value1) + '-' + formatValue(value2));
            $row.find('.merah-text').text('≥' + formatValue(value2));

            $row.find('.hijau-input').val(value1);
            $row.find('.kuning-input').val(value2);
            $row.find('.merah-input').val(value2);
        }

        $row.find('.threshold-type-input').val(type);
    }
</script>