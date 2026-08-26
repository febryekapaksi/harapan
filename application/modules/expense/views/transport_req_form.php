<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<?php
$dept = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$datauser = $this->db->get_where('users', ['username' => $this->auth->user_name()])->row();
	$datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
	if (!empty($datadept)) {
		$dept = $datadept->department_id;
		$bank_id = $datadept->bank_id;
		$accnumber = $datadept->accnumber;
		$accname = $datadept->accname;
	}
}

$datauser = $this->db->get_where('users', ['username' => $this->auth->user_name()])->row();
$dept = $datauser->department_id;
?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo (isset($data->departement) ? $data->departement : $dept); ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">

<style>
	.section-title {
		font-size: 15px;
		font-weight: 600;
		color: #337ab7;
		padding-bottom: 8px;
		margin-bottom: 15px;
		border-bottom: 2px solid #e7eaec;
	}
	.table-detail thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	.table-detail tbody td {
		vertical-align: middle;
	}
</style>

<div class="box box-primary">
	<div class="box-body" style="padding: 20px;">
		<!-- SECTION 1: Informasi Dokumen -->
		<div class="section-title">
			<i class="fa fa-file-text-o"></i> 1. Informasi Request Transportasi
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">No Dokumen</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Periode Mulai <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" class="form-control" id="date1" name="date1" value="<?php echo (isset($data->date1) ? $data->date1 : date("Y-m-d")); ?>" required>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">Tanggal Dokumen <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" class="form-control" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" required>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Periode Selesai <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="date" class="form-control" id="date2" name="date2" value="<?php echo (isset($data->date2) ? $data->date2 : date("Y-m-d")); ?>" required>
						</div>
						<?php
						if (isset($data->st_reject) && $data->st_reject != '') {
							echo '
							<div class="alert alert-danger alert-dismissible" style="margin-top: 10px; margin-bottom: 0;">
								<h4><i class="icon fa fa-ban"></i> Alasan Penolakan:</h4>
								' . $data->st_reject . '
							</div>';
						}
						?>
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 2: Rekening Bank -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-university"></i> 2. Informasi Rekening Bank
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-sm-4 control-label">Bank</label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-bank"></i></span>
							<input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Bank">
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-sm-5 control-label">Nomor Rekening</label>
					<div class="col-sm-7">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
							<input type="text" class="form-control" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-sm-4 control-label">Nama Rekening</label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" class="form-control" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik">
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 3: Rincian Transportasi -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-table"></i> 3. Rincian Transportasi
		</div>

		<div class="row" style="margin-bottom: 10px;">
			<div class="col-md-12 text-right">
				<button class="btn btn-info btn-sm stsview" type="button" onclick="add_detail()" id="add-kasbon">
					<i class="fa fa-refresh"></i> Generate / Tarik Data Transport
				</button>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover table-detail" width="100%">
				<thead>
					<tr>
						<th width="10%">No Doc</th>
						<th width="10%">Tanggal</th>
						<th width="14%">Keperluan</th>
						<th width="14%">Rute</th>
						<th width="9%">Bensin</th>
						<th width="9%">Tol</th>
						<th width="9%">Parkir</th>
						<th width="9%">Lain-Lain</th>
						<th width="6%">KM Awal</th>
						<th width="6%">KM Akhir</th>
						<th width="6%">Total KM</th>
						<th width="4%">Bukti</th>
					</tr>
				</thead>
				<tbody id="detail_body">
					<?php $total_bensin = 0;
					$total_tol = 0;
					$total_parkir = 0;
					$total_kasbon = 0;
					$idd = 1;
					$total_km = 0;
					$grand_total = 0;
					$total_lainnya = 0;
					$gambar = '';
					if (!empty($data_detail)) {
						foreach ($data_detail as $record) {
					?>
							<tr id='tr1_<?= $idd ?>' class='delAll'>
								<td class="text-center">
									<input type="hidden" name="id_transport[]" id="id_transport_<?= $idd ?>" value="<?= $record->id; ?>">
									<b><?= $record->no_doc; ?></b>
									<input type='hidden' class='fben' name='bensin[]' value='<?= $record->bensin; ?>' id='bensin_<?= $idd ?>' />
									<input type='hidden' class='ftol' name='tol[]' value='<?= $record->tol; ?>' id='tol_<?= $idd ?>' />
									<input type='hidden' class='fpark' name='parkir[]' value='<?= $record->parkir; ?>' id='parkir_<?= $idd ?>' />
									<input type='hidden' class='flainnya' name='lainnya[]' value='<?= $record->lainnya; ?>' id='lainnya_<?= $idd ?>' />
								</td>
								<td class="text-center"><?= (!empty($record->tgl_doc) && $record->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($record->tgl_doc)) : '-'; ?></td>
								<td><?= $record->keperluan; ?></td>
								<td><?= $record->rute; ?></td>
								<td class="divide text-right"><?= number_format($record->bensin, 0, ',', '.'); ?></td>
								<td class="divide text-right"><?= number_format($record->tol, 0, ',', '.'); ?></td>
								<td class="divide text-right"><?= number_format($record->parkir, 0, ',', '.'); ?></td>
								<td class="divide text-right"><?= number_format($record->lainnya, 0, ',', '.'); ?></td>
								<td class="divide text-right"><?= number_format($record->km_awal, 0, ',', '.'); ?></td>
								<td class="divide text-right"><?= number_format($record->km_akhir, 0, ',', '.'); ?></td>
								<td class="divide text-right" style="font-weight: 600;"><?= number_format($record->km_akhir - $record->km_awal, 0, ',', '.'); ?></td>
								<td class="text-center">
									<?= ($record->doc_file != '' ? '<a href="' . base_url('uploads/expense/' . $record->doc_file) . '" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i></a>' : '-') ?>
								</td>
							</tr>
					<?php
							if ($record->doc_file != '') {
								if (strpos($record->doc_file, 'pdf', 0) > 1) {
									$gambar .= '<div class="col-md-12" style="margin-bottom: 15px;">
						<iframe src="' . base_url('uploads/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:100%; height:400px;" frameborder="0">
								 <a href="' . base_url('uploads/expense/' . $record->doc_file) . '">Download PDF</a>
						</iframe>
						<br /><b>' . $record->no_doc . '</b></div>';
								} else {
									$gambar .= '<div class="col-md-3" style="margin-bottom: 15px;"><a href="' . base_url('uploads/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('uploads/expense/' . $record->doc_file) . '" class="img-responsive img-thumbnail"></a><br /><b>' . $record->no_doc . '</b></div>';
								}
							}

							$total_bensin = ($total_bensin + ($record->bensin));
							$total_tol = ($total_tol + ($record->tol));
							$total_parkir = ($total_parkir + ($record->parkir));
							$total_km = ($total_km + ($record->km_akhir - $record->km_awal));
							$total_lainnya = ($total_lainnya + $record->lainnya);
							$idd++;
						}
					}
					$grand_total = ($total_bensin + $total_tol + $total_parkir + $total_lainnya);
					?>
				</tbody>
				<tfoot>
					<tr style="background-color: #f5f5f5; font-weight: bold;">
						<td colspan="4" class="text-right">SUB TOTAL:</td>
						<td><input type="text" class="form-control divide input-sm text-right" id="total_bensin" name="total_bensin" value="<?= $total_bensin ?>" placeholder="Total Bensin" tabindex="-1" readonly style="font-weight: bold;"></td>
						<td><input type="text" class="form-control divide input-sm text-right" id="total_tol" name="total_tol" value="<?= $total_tol ?>" placeholder="Total Tol" tabindex="-1" readonly style="font-weight: bold;"></td>
						<td><input type="text" class="form-control divide input-sm text-right" id="total_parkir" name="total_parkir" value="<?= $total_parkir ?>" placeholder="Total Parkir" tabindex="-1" readonly style="font-weight: bold;"></td>
						<td><input type="text" class="form-control divide input-sm text-right" id="total_lainnya" name="total_lainnya" value="<?= $total_lainnya ?>" placeholder="Total Lainnya" tabindex="-1" readonly style="font-weight: bold;"></td>
						<td colspan="2"></td>
						<td><input type="text" class="form-control divide input-sm text-right" id="total_km" name="total_km" value="<?= $total_km ?>" placeholder="Total KM" tabindex="-1" readonly style="font-weight: bold;"></td>
						<td></td>
					</tr>
					<tr style="background-color: #e8f4f8; font-weight: bold;">
						<td colspan="4" class="text-right" style="font-size: 15px; color: #3c8dbc;">TOTAL EXPENSE:</td>
						<td colspan="4"><input type="text" class="form-control divide input-sm text-right" id="jumlah_expense" name="jumlah_expense" value="<?= $grand_total ?>" placeholder="Total" tabindex="-1" readonly style="font-size: 15px; font-weight: bold; color: #3c8dbc;"></td>
						<td colspan="4"></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<?php if (!empty($gambar)): ?>
			<div class="section-title" style="margin-top: 20px;">
				<i class="fa fa-paperclip"></i> Preview Lampiran Bukti
			</div>
			<div class="row">
				<?= $gambar ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="box-footer text-center" style="background-color: #f9f9f9; padding: 15px;">
		<?php
		if (isset($data)) {
			if (($data->status == 0 || $data->status == 1) && $stsview == '') {
				if (($mod == '_fin' || $mod == '_mgt')) {
					echo '<button type="button" class="btn btn-primary btn-sm" id="approve" onclick="data_approve(' . $data->id . ',' . ($data->status + 1) . ')" style="margin-right: 5px;"><i class="fa fa-check-square-o"></i> Approve</button>';
					echo '<button type="button" class="btn btn-danger btn-sm" onclick="data_reject()" style="margin-right: 5px;"><i class="fa fa-ban">&nbsp;</i> Reject</button>';
					$stsview = 'view';
				}
			}
		}
		?>
		<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit" style="margin-right: 5px;"><i class="fa fa-save">&nbsp;</i> Simpan Pengajuan</button>
		<a class="btn btn-default btn-sm" onclick="window.location=siteurl+'expense/transport_req<?= $mod ?>';return false;"><i class="fa fa-reply"></i> Batal</a>
	</div>
</div>
<?= form_close() ?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript">
		var url_save = siteurl + 'expense/transport_req_save/';
		var url_approve = siteurl + 'expense/transport_req_approve/';
		var nomor = <?= $idd ?>;
		$('.divide').divide();
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";
			if ($("#jumlah_expense").val() == "0") errors = "Total tidak boleh kosong";
			if ($("#coa").val() == "0") errors = "COA tidak boleh kosong";
			if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
			if (errors == "") {

				swal({
						title: "Anda Yakin?",
						text: "Data Akan Disimpan!",
						type: "info",
						showCancelButton: true,
						confirmButtonText: "Ya, simpan!",
						cancelButtonText: "Tidak!",
						closeOnConfirm: false,
						closeOnCancel: true
					},
					function(isConfirm) {
						if (isConfirm) {
							var formdata = new FormData($('#frm_data')[0]);
							$.ajax({
								url: url_save,
								dataType: "json",
								type: 'POST',
								data: formdata,
								processData: false,
								contentType: false,
								success: function(msg) {
									if (msg['save'] == '1') {
										swal({
											title: "Sukses!",
											text: "Data Berhasil Di Simpan",
											type: "success",
											timer: 1500,
											showConfirmButton: false
										});
										window.location = siteurl + 'expense/transport_req';
									} else {
										swal({
											title: "Gagal!",
											text: "Data Gagal Di Simpan",
											type: "error",
											timer: 1500,
											showConfirmButton: false
										});
									};
									console.log(msg);
								},
								error: function(msg) {
									swal({
										title: "Gagal!",
										text: "Ajax Data Gagal Di Proses",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
									console.log(msg);
								}
							});
						}
					});

				//			data_save();
			} else {
				swal(errors);
				return false;
			}
		});
		<?php if (isset($stsview)) {
			if ($stsview == 'view') {
		?>
				$(".stsview").addClass("hidden");
				$("#frm_data :input").prop("disabled", true);
		<?php
			}
		} ?>
		$(function() {
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				endDate: "0",
				autoclose: true
			});
		});

		function cektotal(id) {
			var sum = 0;
			$('.fben').each(function() {
				sum += Number($(this).val());
			});
			$("#total_bensin").val(sum);
			var sum1 = 0;
			$('.ftol').each(function() {
				sum1 += Number($(this).val());
			});
			$("#total_tol").val(sum1);
			var sum2 = 0;
			$('.fpark').each(function() {
				sum2 += Number($(this).val());
			});
			$("#total_parkir").val(sum2);
			var sum3 = 0;
			$('.fkm').each(function() {
				sum3 += Number($(this).val());
			});
			$("#total_km").val(sum3);
			var sum4 = 0;
			$('.flainnya').each(function() {
				sum4 += Number($(this).val());
			});
			$("#total_lainnya").val(sum4);
			$("#jumlah_expense").val(sum + sum1 + sum2 + sum4);
		}

		function add_detail() {
			$('.kasbonrow').remove();
			var nama = $("#nama").val();
			var departement = $("#departement").val();
			var date1 = $("#date1").val();
			var date2 = $("#date2").val();
			$.ajax({
				url: siteurl + 'expense/get_list_req_transport/' + nama + '/' + departement + '/' + date1 + '/' + date2,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data) {
					var i;
					for (i = 0; i < data.length; i++) {
						var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
						Rows += "<td><input type='hidden' name='id_transport[]' id='id_transport_" + nomor + "' value='" + data[i].id + "'>";
						Rows += data[i].no_doc + "</td>";
						Rows += "<td>" + data[i].tgl_doc + "</td>";
						Rows += "<td>" + data[i].keperluan + "</td>";
						Rows += "<td>" + data[i].rute + "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fben input-sm' name='bensin[]' value='" + data[i].bensin + "' id='bensin_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide ftol input-sm' name='tol[]' value='" + data[i].tol + "' id='tol_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fpark input-sm' name='parkir[]' value='" + data[i].parkir + "' id='parkir_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide flainnya input-sm' name='lainnya[]' value='" + data[i].lainnya + "' id='lainnya_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm' name='km_awal[]' value='" + data[i].km_awal + "' id='km_awal_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide input-sm' name='km_akhir[]' value='" + data[i].km_akhir + "' id='km_akhir_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<input type='text' class='form-control divide fkm input-sm' name='total_km[]' value='" + (data[i].km_akhir - data[i].km_awal) + "' id='total_km_" + nomor + "' tabindex='-1' readonly />";
						Rows += "</td>";
						Rows += "<td>";
						Rows += "<span class='pull-right'>";
						if (data[i].doc_file != '') {
							Rows += "<a href='<?= base_url('uploads/expense/') ?>" + data[i].doc_file + "' target='_blank'><i class='fa fa-download'></i></a></span>";
						}
						Rows += "</td>";
						Rows += "</tr>";
						nomor++;
						$('#detail_body').append(Rows);
					}
					$(".divide").divide();
					cektotal();
				},
				error: function() {
					swal({
						title: "Error Message !",
						text: 'Connection Time Out. Please try again..',
						type: "warning",
						timer: 3000,
						showCancelButton: false,
						showConfirmButton: false,
						allowOutsideClick: false
					});
				}
			});
		}

		function data_approve(id, status) {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Disetujui!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, setuju!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						$.ajax({
							url: url_approve + id + '/' + status,
							dataType: "json",
							type: 'POST',
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Setujui",
										type: "success",
										timer: 1500,
										showConfirmButton: false
									});
									window.location = siteurl + 'expense/transport_req<?= $mod ?>';
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Setujui",
										type: "error",
										timer: 1500,
										showConfirmButton: false
									});
								};
								console.log(msg);
							},
							error: function(msg) {
								swal({
									title: "Gagal!",
									text: "Ajax Data Gagal Di Proses",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
								console.log(msg);
							}
						});
					}
				});
		}

		function data_reject() {
			swal({
					title: "Perhatian",
					text: "Berikan alasan penolakan",
					type: "input",
					showCancelButton: true,
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(inputValue) {
					if (inputValue === false) return false;
					if (inputValue === "") {
						swal.showInputError("Tuliskan alasan anda");
						return false
					}

					swal({
							title: "Anda Yakin?",
							text: "Data Akan Tolak!",
							type: "warning",
							showCancelButton: true,
							confirmButtonText: "Ya, tolak!",
							cancelButtonText: "Tidak!",
							closeOnConfirm: false,
							closeOnCancel: true
						},
						function(isConfirm) {
							if (isConfirm) {
								id = $("#id").val();
								$.ajax({
									url: base_url + 'expense/reject/',
									data: {
										'id': id,
										'reason': inputValue,
										'table': 'tr_transport_req'
									},
									dataType: "json",
									type: 'POST',
									success: function(msg) {
										if (msg['save'] == '1') {
											swal({
												title: "Sukses!",
												text: "Data Berhasil Di Tolak",
												type: "success",
												timer: 1500,
												showConfirmButton: false
											});
											window.location = siteurl + 'expense/transport_req<?= $mod ?>';
										} else {
											swal({
												title: "Gagal!",
												text: "Data Gagal Di Tolak",
												type: "error",
												timer: 1500,
												showConfirmButton: false
											});
										};
										console.log(msg);
									},
									error: function(msg) {
										swal({
											title: "Gagal!",
											text: "Ajax Data Gagal Di Proses",
											type: "error",
											timer: 1500,
											showConfirmButton: false
										});
										console.log(msg);
									}
								});
							}
						});

				});
		}
	</script>