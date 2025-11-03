<?php
$ENABLE_ADD     = has_permission('Master_Kelas.Add');
$ENABLE_MANAGE  = has_permission('Master_Kelas.Manage');
$ENABLE_VIEW    = has_permission('Master_Kelas.View');
$ENABLE_DELETE  = has_permission('Master_Kelas.Delete');

$id = isset($id) ? $id : '';
$kelas = isset($kelas) ? $kelas : '';
$kredit_limit = isset($kredit_limit) ? $kredit_limit : '';
?>

<div class="box-body">
	<form id="data_form" autocomplete="off">
		<div class="form-group row">
			<div class="col-md-3">
				<label>Kelas</label>
			</div>
			<div class="col-md-9">
				<input type="hidden" class="form-control" id="id" required name="id" value='<?= $id; ?>'>
				<input type="text" class="form-control" id="kelas" required name="kelas" value='<?= $kelas; ?>'>
			</div>
		</div>
		<div class="form-group row">
			<div class="col-md-3">
				<label>Kredit Limit</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control moneyFormat" id="kredit_limit" required name="kredit_limit" value='<?= $kredit_limit; ?>'>
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