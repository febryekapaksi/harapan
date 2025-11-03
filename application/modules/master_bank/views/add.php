<?php
$ENABLE_ADD     = has_permission('Master_Bank.Add');
$ENABLE_MANAGE  = has_permission('Master_Bank.Manage');
$ENABLE_VIEW    = has_permission('Master_Bank.View');
$ENABLE_DELETE  = has_permission('Master_Bank.Delete');

$id         	= (!empty($result[0]->id)) ? $result[0]->id : '';
$no_perkiraan   = (!empty($result[0]->no_perkiraan)) ? $result[0]->no_perkiraan : '';
$no_rekening    = (!empty($result[0]->no_rekening)) ? $result[0]->no_rekening : '';
$nama  			= (!empty($result[0]->nama)) ? $result[0]->nama : '';
?>

<div class="box-body">
	<form id="data_form" autocomplete="off">
		<input type="hidden" name="id" value="<?= $id; ?>">

		<div class="form-group row">
			<div class="col-md-3">
				<label>Nomor Perkiraan</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control" name="no_perkiraan" required value="<?= $no_perkiraan; ?>">
			</div>
		</div>

		<div class="form-group row">
			<div class="col-md-3">
				<label>Nomor Rekening</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control" name="no_rekening" required value="<?= $no_rekening; ?>">
			</div>
		</div>


		<div class="form-group row">
			<div class="col-md-3">
				<label>Nama</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control" name="nama" required value="<?= $nama; ?>">
			</div>
		</div>

		<div class="form-group row">
			<div class="col-md-3"></div>
			<div class="col-md-9">
				<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
			</div>
		</div>
	</form>
</div>