<?php
$ENABLE_ADD     = has_permission('Master_customer.Add');
$ENABLE_MANAGE  = has_permission('Master_customer.Manage');
$ENABLE_VIEW    = has_permission('Master_customer.View');
$ENABLE_DELETE  = has_permission('Master_customer.Delete');
?>
<div class="box box-primary">
	<div class="box-body">
		<form id="data-form" method="post">
			<div class="input_fields_wrap2">
				<div class="row">
					<div class="col-sm-12">
						<center>
							<h3>DETAIL IDENTITAS CUSTOMER</h3>
						</center>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_supplier">Id Customer</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="id_customer" required name="id_customer" readonly placeholder="Id Customer">
								</div>
							</div>

							<div class="form-group row" hidden>
								<div class="col-md-6">
									<label for="id_category_customer">Category Customer</label>
								</div>
								<div class="col-md-6">
									<select id="id_category_customer" name="id_category_customer" class="form-control select">
										<option value="">--pilih--</option>
										<?php foreach ($results['category'] as $category) { ?>
											<option value="<?= $category->id_category_customer ?>"><?= ucfirst(strtolower($category->name_category_customer)) ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Nama Customer <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="name_customer" required name="name_customer" placeholder="Nama Customer">
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Telephone <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="telephone" required name="telephone" placeholder="Nomor Telephone">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer"></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="telephone_2" name="telephone_2" placeholder="Nomor Telephone">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Fax</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="fax" required name="fax" placeholder="Nomor Fax">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Email <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="email" required name="email" placeholder="email@domain.adress">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Tanggal Mulai <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="date" class="form-control" id="start_date" required name="start_date" placeholder="Tanggal Mulai">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_category_customer">Sales</label>
								</div>
								<div class="col-md-6">
									<select id="id_karyawan" name="id_karyawan" class="form-control select" required>
										<option value="">--pilih--</option>
										<?php foreach ($results['karyawan'] as $karyawan) { ?>
											<option value="<?= $karyawan->id ?>"><?= ucfirst(strtolower($karyawan->nm_karyawan)) ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Channel Pemasaran <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<div class="row">
										<div class="col-md-12">
											<label>
												<input type="checkbox" class="checkbox-control" id="chanel_toko" name="chanel_toko" value="Toko dan User" onclick="togglePersentaseInput()"> Toko dan User
											</label>
										</div>
									</div>
									<div class="row mt-1">
										<div class="col-md-6">
											<label>
												<input type="checkbox" class="checkbox-control" id="chanel_project" name="chanel_project" value="Project" onclick="togglePersentaseInput()"> Project
											</label>
										</div>
										<div class="col-6">
											<div class="input-group">
												<input type="text" class="form-control input-sm divide" name="persentase" id="persentase" disabled>
												<span class="input-group-addon"><i class="fa fa-percent"></i></span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_category_supplier">Provinsi <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<select id="id_prov" name="id_prov" class="form-control select" onchange="get_kota()" required>
										<option value="">--Pilih--</option>
										<?php foreach ($results['prof'] as $prof) { ?>
											<option value="<?= $prof->id_prov ?>"><?= ucfirst(strtolower($prof->nama)) ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_category_supplier">Kota <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<select id="id_kota" name="id_kota" class="form-control select" required>
										<option value="">--Pilih--</option>
									</select>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Alamat <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<textarea type="text" name="address_office" id="address_office" class="form-control input-sm required w70" placeholder="Alamat"></textarea>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Kode Pos</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Kode Pos">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Longitude <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="longitude" required name="longitude" placeholder="Longtitude">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Latitude <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="latitude" required name="latitude" placeholder="Latitude">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Status <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="radio" class="radio-control" id="activation" name="activation" value="aktif" required> Aktif
									</label>
									<label>
										<input type="radio" class="radio-control" id="activation" name="activation" value="inaktif" required> Non aktif
									</label>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Mulai Usaha Sejak</label>
								</div>
								<div class="col-md-6">
									<select name="tahun_mulai" class="form-control select">
										<option value="">-- Pilih Tahun --</option>
										<?php
										$currentYear = date("Y");
										for ($year = $currentYear; $year >= $currentYear - 50; $year--) {
											echo "<option value='$year'>$year</option>";
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Kategori Customer <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<select name="kategori_cust" id="kategori_cust" class="form-control select" required>
										<option value="">-- Pilih --</option>
										<option value="Distributor">Distributor</option>
										<option value="Retail">Retail</option>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Kategori Toko</label>
								</div>
								<div class="col-md-6">
									<select name="kategori_toko" id="kategori_toko" class="form-control select">
										<option value="">-- Pilih --</option>
										<option value="Toko 1">Toko 1</option>
										<option value="Toko 2">Toko 2</option>
										<option value="Toko 3">Toko 3</option>
										<option value="Dropship">Dropship</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>PENILAIAN CUSTOMER</h3>
						</center>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Bayar 3 Bulan On Time</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="radio" class="radio-control" id="ontime" name="data4[ontime]" value="Yes"> Yes
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="ontime" name="data4[ontime]" value="No"> No
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="ontime" name="data4[ontime]" value="New"> New
									</label>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Toko Milik Sendiri</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="radio" class="radio-control" id="toko_sendiri" name="data4[toko_sendiri]" value="Yes"> Yes
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="toko_sendiri" name="data4[toko_sendiri]" value="No"> No
									</label>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Armada Pickup</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" name="data4[armada_pickup]">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Armada Truck</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" name="data4[armada_truck]">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Attitude</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="radio" class="radio-control" id="attitude" name="data4[attitude]" value="Yes"> Yes
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="attitude" name="data4[attitude]" value="No"> No
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="attitude" name="data4[attitude]" value="New"> New
									</label>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Luas Tanah</label>
								</div>
								<div class="col-md-6">
									<textarea class="form-control" name="data4[luas_tanah]" id="luas_tanah"></textarea>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">PBB</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="radio" class="radio-control" id="pbb" name="data4[pbb]" value="Yes"> Yes
									</label>
									&nbsp;
									<label>
										<input type="radio" class="radio-control" id="pbb" name="data4[pbb]" value="No"> No
									</label>
									&nbsp;
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>SUPPLIER EXISTING CUSTOMER</h3>
						</center>
						<div class="form-group row">
							<div class="col-md-12">
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<td align='center'><b>Nama PT</b></td>
											<td align='center'><b>PIC</b></td>
											<td align='center'><b>No Telepon</b></td>
											<td style="width: 50px;" align='center'>
												<?php
												echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-existing'));
												?>
											</td>
										</tr>
									</thead>
									<tbody id='list_existing'>

									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>CATEGORY CUSTOMER</h3>
						</center>
						<div class="form-group row">
							<div class="col-md-12">

								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<td align='center'><b>Category Customer</b></td>
											<td style="width: 50px;" align='center'>
												<?php
												echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-category'));
												?>
											</td>
										</tr>

									</thead>
									<tbody id='list_category'>

									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>PIC</h3>
						</center>
						<div class="form-group row">
							<div class="col-md-12">
								<table class='table table-bordered table-striped'>
									<thead>
										<tr class='bg-blue'>
											<td align='center'><b>Nama PIC</b></td>
											<td align='center'><b>Nomor Telp</b></td>
											<td align='center'><b>Email</b></td>
											<td align='center'><b>Jabatan</b></td>
											<td style="width: 50px;" align='center'>
												<?php
												echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'value' => 'back', 'content' => 'Add', 'id' => 'add-payment'));
												?>
											</td>
										</tr>

									</thead>
									<tbody id='list_payment'>
										<?php
										$defaultRows = [
											['name' => 'PIC'],
											['name' => 'Owner'],
											['name' => 'KA Toko']
										];

										foreach ($defaultRows as $index => $row) {
											$loop = $index + 1;
											echo "<tr id='tr_$loop'>";
											foreach (['name_pic', 'phone_pic', 'email_pic', 'position_pic'] as $field) {
												$value = ($field == 'position_pic') ? $row['name'] : '';
												$readonly = ($field == 'position_pic') ? 'readonly' : '';
												$required = ($row['name'] === 'PIC' && $field !== 'position_pic') ? 'required' : '';
												echo "<td align='left'>";
												echo "<input type='text' class='form-control input-sm' name='data1[$loop][$field]' id='data1_{$loop}_{$field}' value='$value' $readonly $required>";
												echo "</td>";
											}
											echo "<td></td>";
											echo "</tr>";
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>INFORMASI PEMBAYARAN</h3>
						</center>
						<div class="col-sm-6">
							<div class="col-md-12">
								<label for="id_supplier">
									<h4>Informasi Bank</h4>
								</label>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_supplier">Nama Bank</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="name_bank" name="name_bank" placeholder="Nama Bank">
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_category_supplier">Nomor Akun</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="no_rekening" name="no_rekening" placeholder="No Rekening">
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Nama Akun</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="nama_rekening" name="nama_rekening" placeholder="Nama Rekening">
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Alamat Bank</label>
								</div>
								<div class="col-md-6">
									<textarea type="text" name="alamat_bank" id="alamat_bank" class="form-control input-sm w70" placeholder="Alamat_Bank"></textarea>
								</div>
							</div>

							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Swift Code</label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="swift_code" name="swift_code" placeholder="Swift Code">
								</div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="col-md-12">
								<label for="id_supplier">
									<h4>Informasi Pajak</h4>
								</label>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Nomor NPWP/KTP <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="npwp" required name="npwp" placeholder="Nomor NPWP">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Nama NPWP/KTP <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="npwp_name" required name="npwp_name" placeholder="Nama NPWP">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Alamat NPWP/KTP <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="npwp_address" required name="npwp_address" placeholder="Alamat NPWP">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Term Of Payment <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<select id="payment_term" name="payment_term" class="form-control select" required>
										<option value="">-- Pilih --</option>
										<?php foreach ($results['payment_terms'] as $terms): ?>
											<option value="<?= htmlspecialchars($terms->id) ?>">
												<?= htmlspecialchars($terms->name) ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="customer">Nominal DP <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<input type="text" class="form-control" id="nominal_dp" value='' name="nominal_dp">
								</div>
							</div>
							<div class="form-group row">
								<div class="col-md-6">
									<label for="id_category_customer">Sisa Pembayaran <span class="text-red">*</span></label>
								</div>
								<div class="col-md-6">
									<select id="sisa_pembayaran" name="sisa_pembayaran" class="form-control select" required>
										<option value="">-- Pilih --</option>
										<option value="15 After Delifery">15 After Delifery</option>
										<option value="30 After Delifery">30 After Delifery</option>
									</select>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>INFORMASI INVOICE</h3>
						</center>
						<div class="col-sm-12">
							<div class="form-group-row">
								<div class="col-md-3">
									<label for="customer">Hari Terima <span class="text-red">*</span></label>
								</div>
								<div class="col-md-9">
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="senin" name="senin" value="Y"> Senin
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="selasa" name="selasa" value="Y"> Selasa
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="rabu" name="rabu" value="Y"> Rabu
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="kamis" name="kamis" value="Y"> Kamis
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="jumat" name="jumat" value="Y"> Jumat
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="sabtu" name="sabtu" value="Y"> Sabtu
									</label>
									&nbsp
									<label>
										<input type="checkbox" class="radio-control hari-checkbox" id="minggu" name="minggu" value="Y"> Minggu
									</label>
								</div>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-12">
										<label for="customer">Waktu Penerimaan Invoice</label>
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-2">
										<label for="customer">Start</label>
									</div>
									<div class="col-md-4">
										<input type="time" class="form-control" id="start_recive" name="start_recive" placeholder="Latitude">
									</div>
									<div class="col-md-2">
										<label for="customer">END</label>
									</div>
									<div class="col-md-4">
										<input type="time" class="form-control" id="end_recive" name="end_recive" placeholder="Latitude">
									</div>
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group row">
									<div class="col-md-6">
										<label for="customer">Alamat Invoice</label>
									</div>
								</div>
								<div class="form-group row">
									<div class="col-md-12">
										<textarea type="text" name="address_invoice" id="address_invoice" class="form-control input-sm w70" placeholder="Alamat"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<h3>PERSYARATAN PEMBAYARAN</h3>
						</center>
						<div class="col-sm-12">
							<div class="col-sm-4">
								<div class="form-group row">
									<div class="col-md-12">
										<label>
											<input type="checkbox" class="radio-control payterm-checkbox" id="invoice" name="invoice" value="Y"> Invoice
										</label>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group row">
									<div class="col-md-12">
										<label>
											<input type="checkbox" class="radio-control" id="sj" name="sj" value="Y"> SJ
										</label>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="form-group row">
									<div class="col-md-12">
										<label>
											<input type="checkbox" class="radio-control" id="faktur" name="faktur" value="Y"> Faktur Pajak
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-sm-12">
						<center>
							<button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i> Simpan</button>
						</center>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.divide').divide();
		var base_url = '<?php echo base_url(); ?>';
		var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

		var data_pay = <?php echo json_encode($results['supplier']); ?>;
		$('.select').select2({
			width: '100%',
			dropdownParent: $('#dialog-popup')
		});


		var max_fields2 = 10; //maximum input boxes allowed
		var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
		var add_button2 = $(".add_field_button2"); //Add button ID

		//console.log(persen);

		var x2 = 1; //initlal text box count
		$(add_button2).click(function(e) { //on add input button click
			e.preventDefault();
			if (x2 < max_fields2) { //max input box allowed
				x2++; //text box increment

				$(wrapper2).append('<div class="row">' +
					'<div class="col-xs-1">' + x2 + '</div>' +
					'<div class="col-xs-3">' +
					'<div class="input-group">' +
					'<input type="text" name="hd' + x2 + '[produk]"  class="form-control input-sm" value="">' +
					'</div>' +
					'<div class="input-group">' +
					'<input type="text" name="hd' + x2 + '[costcenter]"  class="form-control input-sm" value="">' +
					'</div>' +
					'<div class="input-group">' +
					'<input type="text" name="hd' + x2 + '[mesin]"  class="form-control input-sm" value="">' +
					'</div>' +
					'<div class="input-group">' +
					'<input type="text" name="hd' + x2 + '[mold_tools]"  class="form-control input-sm" value="">' +
					'</div>' +
					'</div>' +
					'<a href="#" class="remove_field2">Remove</a>' +
					'</div>'); //add input box
				$('#datepickerxxr' + x2).datepicker({
					format: 'dd-mm-yyyy',
					autoclose: true
				});
			}
		});

		$(wrapper2).on("click", ".remove_field2", function(e) { //user click on remove text
			e.preventDefault();
			$(this).parent('div').remove();
			x2--;
		})

		$('#add-payment').click(function() {
			var jumlah = $('#list_payment').find('tr').length;
			if (jumlah == 0 || jumlah == null) {
				var ada = 0;
				var loop = 1;
			} else {
				var nilai = $('#list_payment tr:last').attr('id');
				var jum1 = nilai.split('_');
				var loop = parseInt(jum1[1]) + 1;
			}
			Template = '<tr id="tr_' + loop + '">';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][name_pic]" id="data1_' + loop + '_name_pic" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][phone_pic]" id="data1_' + loop + '_phone_pic" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][email_pic]" id="data1_' + loop + '_email_pic" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][position_pic]" id="data1_' + loop + '_position_pic" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
			Template += '</tr>';
			$('#list_payment').append(Template);
		});
		$('#add-category').click(function() {
			var jumlah = $('#list_category').find('tr').length;
			if (jumlah == 0 || jumlah == null) {
				var ada = 0;
				var loop = 1;
			} else {
				var nilai = $('#list_category tr:last').attr('id');
				var jum1 = nilai.split('_');
				var loop = parseInt(jum1[1]) + 1;
			}
			Template = '<tr id="tr_' + loop + '">';
			Template += '<td align="left">';
			Template += '<select id="data2_' + loop + '_id_category_customer" name="data2[' + loop + '][id_category_customer]" class="form-control select" required>';
			Template += '<option value="">--pilih--</option>';
			Template += '<?php foreach ($results['category'] as $category) { ?>';
			Template += '<option value="<?= $category->name_category_customer ?>"><?= ucfirst(strtolower($category->name_category_customer)) ?></option>';
			Template += '<?php } ?>';
			Template += '</select>';
			Template += '</td>';
			Template += '</td>';
			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem2(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
			Template += '</tr>';
			$('#list_category').append(Template);
		});
		$('#add-existing').click(function() {
			var jumlah = $('#list_existing').find('tr').length;
			if (jumlah == 0 || jumlah == null) {
				var ada = 0;
				var loop = 1;
			} else {
				var nilai = $('#list_existing tr:last').attr('id');
				var jum1 = nilai.split('_');
				var loop = parseInt(jum1[1]) + 1;
			}
			Template = '<tr id="tr_' + loop + '">';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pt]" id="data3_' + loop + '_existing_pt" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pic]" id="data3_' + loop + '_existing_pic" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="left">';
			Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_telp]" id="data3_' + loop + '_existing_telp" label="FALSE" div="FALSE">';
			Template += '</td>';
			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem3(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
			Template += '</tr>';
			$('#list_existing').append(Template);
		});


		$('#data-form').submit(function(e) {
			e.preventDefault();

			const checkboxes = document.querySelectorAll(".hari-checkbox");
			const paytermcbb = document.querySelectorAll(".payterm-checkbox");
			const oneChecked = Array.from(checkboxes).some(cb => cb.checked);
			const onePaytermcbb = Array.from(paytermcbb).some(cb => cb.checked);

			// Jika salah satu checkbox group belum dipilih, tampilkan alert dan hentikan proses
			if (!oneChecked) {
				alert("Pilih minimal satu hari terima!");
				return false; // ini penting
			}

			if (!onePaytermcbb) {
				alert("Pilih minimal satu syarat pembayaran!");
				return false; // ini penting
			}

			// Jika validasi lolos, baru tampilkan swal konfirmasi
			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Process it!",
					cancelButtonText: "No, cancel process!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						var formData = new FormData($('#data-form')[0]);
						var baseurl = siteurl + 'master_customers/saveNewcustomer';

						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
									window.location.href = base_url + active_controller;
								} else {
									swal({
										title: "Save Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								}
							},
							error: function() {
								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
					}
				});
		});

	});

	function togglePersentaseInput() {
		const projectCheckbox = document.querySelector("input[name='chanel_project']");
		const persentaseInput = document.getElementById("persentase");

		if (projectCheckbox && projectCheckbox.checked) {
			persentaseInput.disabled = false;
			persentaseInput.required = true;
			persentaseInput.focus();
		} else {
			persentaseInput.disabled = true;
			persentaseInput.required = false;
			persentaseInput.value = ""; // Kosongkan kalau tidak aktif
		}
	}

	function get_kota() {
		var id_prov = $("#id_prov").val();
		$.ajax({
			type: "GET",
			url: siteurl + 'master_customers/getkota',
			data: "id_prov=" + id_prov,
			success: function(html) {
				$("#id_kota").html(html);
			}
		});
	}

	function DelItem(id) {
		$('#list_payment #tr_' + id).remove();

	}

	function DelItem2(id) {
		$('#list_category #tr_' + id).remove();

	}

	function DelItem3(id) {
		$('#list_existing #tr_' + id).remove();
	}
</script>