<?php
$ENABLE_ADD     = has_permission('Product_Master.Add');
$ENABLE_MANAGE  = has_permission('Product_Master.Manage');
$ENABLE_VIEW    = has_permission('Product_Master.View');
$ENABLE_DELETE  = has_permission('Product_Master.Delete');

$id = (!empty($listData[0]->id)) ? $listData[0]->id : '';
$code_lv1 = (!empty($listData[0]->code_lv1)) ? $listData[0]->code_lv1 : '';
$code_lv2 = (!empty($listData[0]->code_lv2)) ? $listData[0]->code_lv2 : '';
$code_lv3 = (!empty($listData[0]->code_lv3)) ? $listData[0]->code_lv3 : '';
$code_lv4 = (!empty($listData[0]->code_lv4)) ? $listData[0]->code_lv4 : '';
$nama = (!empty($listData[0]->nama)) ? $listData[0]->nama : '';
$retail = (!empty($listData[0]->retail)) ? $listData[0]->retail : '';

$code = (!empty($listData[0]->code)) ? $listData[0]->code : '';
$trade_name = (!empty($listData[0]->trade_name)) ? $listData[0]->trade_name : '';
$max_stok = (!empty($listData[0]->max_stok)) ? $listData[0]->max_stok : '';
$min_stok = (!empty($listData[0]->min_stok)) ? $listData[0]->min_stok : '';
$moq = (!empty($listData[0]->moq)) ? $listData[0]->moq : '';

$id_unit_packing = (!empty($listData[0]->id_unit_packing)) ? $listData[0]->id_unit_packing : '';
$konversi = (!empty($listData[0]->konversi)) ? $listData[0]->konversi : '';
$id_unit = (!empty($listData[0]->id_unit)) ? $listData[0]->id_unit : '';

$length = (!empty($listData[0]->length)) ? $listData[0]->length : '';
$wide 	= (!empty($listData[0]->wide)) ? $listData[0]->wide : '';
$high 	= (!empty($listData[0]->high)) ? $listData[0]->high : '';
$weight = (!empty($listData[0]->weight)) ? $listData[0]->weight : '';
$cub 	= (!empty($listData[0]->cub)) ? $listData[0]->cub : '';

$file_msds 	= (!empty($listData[0]->file_msds)) ? $listData[0]->file_msds : '';

$status1 = (!empty($listData[0]->status) and $listData[0]->status == '1') ? 'checked' : '';
$status2 = (!empty($listData[0]->status) and $listData[0]->status == '2') ? 'checked' : '';
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data_form_master_product" method="post" autocomplete="off" enctype='multiple/form-data'>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Product Type <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<select name="code_lv1" id="code_lv1" class='chosen-select'>
						<option value="0">Select Product Type</option>
						<?php
						foreach ($listLevel1 as $key => $value) {
							$selected = ($code_lv1 == $value['code_lv1']) ? 'selected' : '';
							echo "<option value='" . $value['code_lv1'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Product Category <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<select name="code_lv2" id="code_lv2" class='chosen-select'>
						<?php
						if (!empty($id) and !empty($listLevel2)) {
							echo "<option value='0'>Select Product Category</option>";
							foreach ($listLevel2 as $key => $value) {
								$selected = ($code_lv2 == $value['code_lv2']) ? 'selected' : '';
								echo "<option value='" . $value['code_lv2'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
							}
						} else {
							echo "<option value='0'>List Empty</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Product Jenis <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<select name="code_lv3" id="code_lv3" class='chosen-select'>
						<?php
						if (!empty($id) and !empty($listLevel3)) {
							echo "<option value='0'>Select Product Jenis</option>";
							foreach ($listLevel3 as $key => $value) {
								$selected = ($code_lv3 == $value['code_lv3']) ? 'selected' : '';
								echo "<option value='" . $value['code_lv3'] . "' " . $selected . ">" . strtoupper($value['nama']) . "</option>";
							}
						} else {
							echo "<option value='0'>List Empty</option>";
						}
						?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Product Master <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<input type="hidden" class="form-control" id="id" name="id" value='<?= $id; ?>'>
					<input type="hidden" class="form-control" id="code_lv4" name="code_lv4" value='<?= $code_lv4; ?>'>
					<input type="text" class="form-control" id="nama" required name="nama" placeholder="Product Type" value='<?= $nama; ?>'>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="">Product Retail</label>
				</div>
				<div class="col-md-10">
					<input type="text" class="form-control" id="retail" name="retail" placeholder="Product Retail" value="<?= $retail ?>">
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Product Code</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="code" name="code" value='<?= $code; ?>' placeholder="Product Code">
					<span style='cursor:pointer;' class='text-primary' id='updateManualCode'>Update Code Program</span>
				</div>
				<div class="col-md-2">
					<label>Trade Name</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control" id="trade_name" name="trade_name" value='<?= $trade_name; ?>' placeholder="Trade Name">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Packing Unit / Conversion</label>
				</div>
				<div class="col-md-2">
					<select id="id_unit_packing" name="id_unit_packing" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
						<?php foreach ($satuan_packing as $value) {
							$sel = ($value->id == $id_unit_packing) ? 'selected' : '';
						?>
							<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper(strtolower($value->code)) ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<input type="text" id="konversi" name="konversi" class="form-control input-md maskM" placeholder="Conversion" value='<?= $konversi; ?>'>
				</div>
				<div class="col-md-2">
					<label>Unit Measurement</label>
				</div>
				<div class="col-md-2">
					<select id="id_unit" name="id_unit" class="form-control input-md chosen-select">
						<option value="0">Select An Option</option>
						<?php foreach ($satuan as $value) {
							$sel = ($value->id == $id_unit) ? 'selected' : '';
						?>
							<option value="<?= $value->id; ?>" <?= $sel; ?>><?= strtoupper($value->code) ?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Maximum Stock</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="max_stok" name="max_stok" value='<?= $max_stok; ?>' placeholder="Maksimum Stok">
				</div>
				<div class="col-md-2">
					<label>Minimum Stok</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="min_stok" name="min_stok" value='<?= $min_stok; ?>' placeholder="Minimum Stok">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Upload MSDS</label>
				</div>
				<div class="col-md-4">
					<div class="form-group">
						<input type="file" name='photo' id="photo">
					</div>
					<?php if (!empty($file_msds)) { ?>
						<a href='<?= base_url() . $file_msds; ?>' target='_blank' class="help-block" title='Download'>Download File</a>
					<?php } ?>
				</div>
				<div class="col-md-2">
					<label>MOQ</label>
				</div>
				<div class="col-md-4">
					<input type="text" class="form-control maskM" id="moq" name="moq" value='<?= $moq; ?>' placeholder="MOQ">
				</div>
			</div>
			<hr>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Dimensi (P,L,T)</label>
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control maskM getCub" id="length" name="length" value='<?= $length; ?>' placeholder="Panjang">
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control maskM getCub" id="wide" name="wide" value='<?= $wide; ?>' placeholder="Lebar">
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control maskM getCub" id="high" name="high" value='<?= $high; ?>' placeholder="Tinggi">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>Berat/unit</label>
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="weight" name="weight" value="<?= $weight ?>" placeholder="Berat per unit">
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label>CBM</label>
				</div>
				<div class="col-md-3">
					<input type="text" class="form-control" id="cub" name="cub" placeholder="CBM" readonly value='<?= $cub; ?>'>
				</div>
			</div>
			<?php if (!empty($id)) { ?>
				<div class="form-group row">
					<div class="col-md-2">
						<label for="">Status</label>
					</div>
					<div class="col-md-4">
						<label>
							<input type="radio" class="radio-control" name="status" value="1" <?= $status1; ?>> Aktif
						</label>
						&nbsp &nbsp &nbsp
						<label>
							<input type="radio" class="radio-control" name="status" value="0" <?= $status2; ?>> Non-Aktif
						</label>
					</div>
				</div>
			<?php } ?>
			<div class="form-group row">
				<div class="col-md-2"></div>
				<div class="col-md-10">
					<button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('.chosen-select').select2({
			width: '100%'
		});
		$('.maskM').autoNumeric();
	});
</script>