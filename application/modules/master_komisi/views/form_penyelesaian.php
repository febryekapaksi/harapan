<div class="box box-primary">
    <div class="box-body box-primary">
        <form id="data_form" autocomplete="off">
            <div class="form-group row">
                <div class="col-md-3">
                    <label>Koefisien Komisi Penyelesaian Tunggakan <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <input type="hidden" class="form-control" id="id" name="id" value="<?= (!empty($komisi->id) ? $komisi->id : '') ?>">
                    <input type="number" step="0.01" class="form-control" id="komisi_penyelesaian" required name="komisi_penyelesaian" value="<?= (!empty($komisi->komisi_penyelesaian) ? $komisi->komisi_penyelesaian : '') ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-3">
                    <label>Keterangan <span class='text-danger'>*</span></label>
                </div>
                <div class="col-md-9">
                    <textarea name="keterangan" class="form-control"><?= (!empty($komisi->keterangan) ? $komisi->keterangan : '') ?></textarea>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-3"></div>
                <div class="col-md-9">
                    <button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
</div>