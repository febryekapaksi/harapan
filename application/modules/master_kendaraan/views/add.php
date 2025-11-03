<?php
$ENABLE_ADD     = has_permission('Master_Kendaraan.Add');
$ENABLE_MANAGE  = has_permission('Master_Kendaraan.Manage');
$ENABLE_VIEW    = has_permission('Master_Kendaraan.View');
$ENABLE_DELETE  = has_permission('Master_Kendaraan.Delete');

$id         = (!empty($result[0]->id)) ? $result[0]->id : '';
$nopol      = (!empty($result[0]->nopol)) ? $result[0]->nopol : '';
$jenis      = (!empty($result[0]->jenis)) ? $result[0]->jenis : '';
$kapasitas  = (!empty($result[0]->kapasitas)) ? $result[0]->kapasitas : '';
?>

<div class="box-body">
	<form id="data_form" autocomplete="off">
		<input type="hidden" name="id" value="<?= $id; ?>">

		<div class="form-group row">
			<div class="col-md-3">
				<label>Nomor Polisi</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control" name="nopol" required value="<?= $nopol; ?>">
			</div>
		</div>

		<div class="form-group row">
			<div class="col-md-3">
				<label>Jenis Kendaraan</label>
			</div>
			<div class="col-md-9">
				<select class="form-control select" name="jenis" required>
					<option value="" disabled <?= empty($jenis) ? 'selected' : '' ?>>-- Pilih --</option>
					<option value="TRUCK" <?= $jenis == 'TRUCK' ? 'selected' : '' ?>>TRUCK</option>
					<option value="PICKUP" <?= $jenis == 'PICKUP' ? 'selected' : '' ?>>PICKUP</option>
				</select>
			</div>
		</div>

		<div class="form-group row">
			<div class="col-md-3">
				<label>Kapasitas</label>
			</div>
			<div class="col-md-9">
				<input type="text" class="form-control moneyFormat" name="kapasitas" required value="<?= $kapasitas; ?>">
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