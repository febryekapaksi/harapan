<div class="box box-primary">
    <div class="box-body box-primary">
        <form id="data_form" autocomplete="off">

            <!-- Hidden ID jika mode edit -->
            <?php if (!empty($target->id)): ?>
                <input type="hidden" name="id" value="<?= $target->id ?>">
                <input type="hidden" name="id_karyawan" value="<?= $target->id_karyawan ?>">
            <?php endif; ?>

            <!-- Pilih Sales -->
            <div class="form-group row">
                <label class="col-md-3 control-label">Sales</label>
                <div class="col-md-9">
                    <select name="id_karyawan" class="form-control select2" id="sales-select" required <?= isset($target->id_karyawan) ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Sales --</option>
                        <?php foreach ($sales as $s): ?>
                            <option
                                value="<?= $s['id'] ?>"
                                data-nama="<?= $s['nm_karyawan'] ?>"
                                <?= (isset($target->id_karyawan) && $target->id_karyawan == $s['id']) ? 'selected' : '' ?>>
                                <?= ucfirst($s['nm_karyawan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Hidden untuk nm_karyawan -->
                    <input type="hidden" name="nm_karyawan" id="nm_karyawan"
                        value="<?= isset($target->nm_karyawan) ? $target->nm_karyawan : '' ?>">
                </div>
            </div>

            <!-- Input Bulan -->
            <div class="form-group row">
                <label class="col-md-3 control-label">Target Bulanan</label>
                <div class="col-md-9">
                    <div class="row">
                        <?php foreach ($bulan as $b): ?>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?= $b['bulan'] ?></label>
                                    <input type="text"
                                        name="<?= $b['bulan_id'] ?>"
                                        class="form-control moneyFormat"
                                        value="<?= isset($target->{$b['bulan_id']}) ? $target->{$b['bulan_id']} : 0 ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class=" form-group row">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/plugins/select2/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
        });
        moneyFormat('.moneyFormat')

        $(document).on('change', '#sales-select', function() {
            const selectedNama = $(this).find('option:selected').data('nama') || '';
            $('#nm_karyawan').val(selectedNama);
        });
    });

    function moneyFormat(e) {
        $(e).inputmask({
            alias: "decimal",
            digits: 2,
            radixPoint: ".",
            autoGroup: true,
            placeholder: "0",
            rightAlign: false,
            allowMinus: false,
            integerDigits: 13,
            groupSeparator: ",",
            digitsOptional: false,
            showMaskOnHover: true,
        })
    }
</script>