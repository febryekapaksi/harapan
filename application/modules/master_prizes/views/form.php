<?php
$ENABLE_ADD     = has_permission('Master_Kelas.Add');
$ENABLE_MANAGE  = has_permission('Master_Kelas.Manage');
$ENABLE_VIEW    = has_permission('Master_Kelas.View');
$ENABLE_DELETE  = has_permission('Master_Kelas.Delete');
?>

<div class="box-body">
    <form id="data_form" autocomplete="off">
        <div class="form-group row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="checkbox">

                        <label class="form-check-label" for="is_zonk">
                            <input class="form-check-input" type="checkbox" name="is_zonk" id="is_zonk" value="1" <?= !empty($is_zonk) ? 'checked' : '' ?>>
                            <b>Flag Zonk</b>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <div class="col-md-3">
                    <label>Nama</label>
                </div>
                <div class="col-md-9">
                    <input type="hidden" class="form-control" id="id" name="id" value="<?= (!empty($id)) ? $id : '' ?>">
                    <input type="hidden" class="form-control" id="code" name="code" value="<?= (!empty($code)) ? $code : '' ?>">
                    <input type="text" class="form-control" id="name" required name="name" value="<?= (!empty($name)) ? $name  : '' ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-3">
                    <label>Status</label>
                </div>
                <div class="col-md-9">
                    <select name="status" id="status" class="form-control">
                        <option value="" disabled selected>-- Pilih Status --</option>
                        <option value="1" <?= (!empty($status) && $status == 1) ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (!empty($status) && $status == 0) ? 'selected' : '' ?>>Non Aktif</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <div class="col-md-3">
                    <label>Stock</label>
                </div>
                <div class="col-md-9">
                    <input type="number" class="form-control" id="stock_total" required name="stock_total" value="<?= (!empty($stock_total)) ? $stock_total : '' ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="col-md-3">
                    <label>Keterangan</label>
                </div>
                <div class="col-md-9">
                    <textarea name="note" class="form-control"><?= (!empty($note)) ? $note : '' ?></textarea>
                </div>
            </div>
        </div>


        <div class="text-center">
            <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#is_zonk').on('change', function() {
            const $n = $('#name');
            if (this.checked) {
                if (!$n.val()) $n.val('ANDA KURANG BERUNTUNG'); // isi hanya jika kosong
                $n.prop('readonly', true).addClass('bg-light');
            } else {
                // JANGAN $n.val('') di sini
                $n.prop('readonly', false).removeClass('bg-light');
            }
        }).trigger('change');
    });
</script>