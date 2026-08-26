<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<style>
	.section-title {
		font-size: 15px;
		font-weight: 600;
		color: #337ab7;
		padding-bottom: 8px;
		margin-bottom: 15px;
		border-bottom: 2px solid #e7eaec;
	}
</style>

<div class="box box-primary">
	<div class="box-body" style="padding: 20px;">
		<!-- SECTION 1: Informasi Dokumen -->
		<div class="section-title">
			<i class="fa fa-file-text-o"></i> 1. Informasi Pengajuan Transportasi
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">No Dokumen</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">Tanggal <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group hidden">
			<?php
			$dept = '';
			$datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
			?>
			<input type="hidden" id="departement" name="departement" value="<?php echo set_value('departement', isset($data->departement) ? $data->departement : $datauser->department_id); ?>">
			<input type="hidden" id="nama" name="nama" value="<?php echo set_value('nama', isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
		</div>

		<!-- SECTION 2: Data Berangkat -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-car"></i> 2. Data Keberangkatan & Kendaraan
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">No. Polisi</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="nopol" name="nopol" value="<?php echo (isset($data->nopol) ? $data->nopol : ''); ?>" placeholder="Contoh: B 1234 ABC" maxlength="15">
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">KM. Awal</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="km_awal" name="km_awal" value="<?php echo (isset($data->km_awal) ? $data->km_awal : '0'); ?>" placeholder="KM Awal">
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 3: Data Kepulangan & Biaya -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-money"></i> 3. Data Kepulangan & Rincian Biaya
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">Keperluan</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="keperluan" name="keperluan" value="<?php echo (isset($data->keperluan) ? $data->keperluan : ''); ?>" placeholder="Keperluan perjalanan dinas">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Bensin (Rp)</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="bensin" name="bensin" value="<?php echo (isset($data->bensin) ? $data->bensin : '0'); ?>" placeholder="Biaya Bensin">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Parkir (Rp)</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="parkir" name="parkir" value="<?php echo (isset($data->parkir) ? $data->parkir : '0'); ?>" placeholder="Biaya Parkir">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">KM. Akhir</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="km_akhir" name="km_akhir" value="<?php echo (isset($data->km_akhir) ? $data->km_akhir : '0'); ?>" placeholder="KM Akhir">
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">Rute Perjalanan</label>
					<div class="col-sm-8">
						<input type="text" class="form-control" id="rute" name="rute" value="<?php echo (isset($data->rute) ? $data->rute : ''); ?>" placeholder="Rute perjalanan">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Tol (Rp)</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="tol" name="tol" value="<?php echo (isset($data->tol) ? $data->tol : '0'); ?>" placeholder="Biaya Tol">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Lain-Lain (Rp)</label>
					<div class="col-sm-8">
						<input type="text" class="form-control divide text-right" id="lainnya" name="lainnya" value="<?php echo (isset($data->lainnya) ? $data->lainnya : '0'); ?>" placeholder="Biaya Lain-lain">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label">Upload Dokumen/Bukti</label>
					<div class="col-sm-8">
						<input type="hidden" name="filename" id="filename" value="<?= (isset($data->doc_file) ? $data->doc_file : ''); ?>">
						<input type="file" name="doc_file" id="doc_file" class="form-control" style="height: auto; padding: 4px;" accept="image/*">
						<?php
						$gambar = '';
						if (isset($data->doc_file) && $data->doc_file != '') {
							echo '<div style="margin-top: 5px;"><a href="' . base_url('uploads/expense/' . $data->doc_file) . '" download target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Unduh Bukti</a></div>';
							if (strpos($data->doc_file, 'pdf', 0) > 1) {
								$gambar .= '<div class="col-md-12" style="margin-top: 15px;">
								<iframe src="' . base_url('uploads/expense/' . $data->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:100%; height:400px;" frameborder="0">
										 <a href="' . base_url('uploads/expense/' . $data->doc_file) . '">Download PDF</a>
								</iframe>
								<br /><b>' . $data->no_doc . '</b></div>';
							} else {
								$gambar .= '<div class="col-md-6" style="margin-top: 15px;"><a href="' . base_url('uploads/expense/' . $data->doc_file) . '" target="_blank"><img src="' . base_url('uploads/expense/' . $data->doc_file) . '" class="img-responsive img-thumbnail"></a><br /><b>' . $data->no_doc . '</b></div>';
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="form-group">
					<label class="col-sm-2 control-label">Catatan Tambahan</label>
					<div class="col-sm-10">
						<textarea class="form-control" rows="2" id="keterangan" name="keterangan" placeholder="Catatan / Keterangan tambahan"><?php echo (isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
					</div>
				</div>
			</div>
		</div>

		<?php if (!empty($gambar)): ?>
			<div class="section-title" style="margin-top: 15px;">
				<i class="fa fa-paperclip"></i> Preview Lampiran Bukti
			</div>
			<div class="row">
				<?= $gambar ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="box-footer text-center" style="background-color: #f9f9f9; padding: 15px;">
		<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit" style="margin-right: 5px;"><i class="fa fa-save">&nbsp;</i> Simpan Transport</button>
		<a class="btn btn-default btn-sm" onclick="window.location=siteurl+'expense/transport';return false;"><i class="fa fa-reply">&nbsp;</i> Batal</a>
	</div>
</div>
<?= form_close() ?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
	<script type="text/javascript">
		var url_save = siteurl + 'expense/transport_save/';
		var url_approve = siteurl + 'expense/transport_approve/';
		$('.divide').divide();
		$('#frm_data').on('submit', async function(e) {
			e.preventDefault();
			let errors = "";

			if ($("#filename").val() == "") {
				if ($('#doc_file').get(0).files.length === 0) {
					errors = "Dokumen harus diupload";
				}
			}
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
				}, async function(isConfirm) {
					if (isConfirm) {
						$('.ajax_loader').show();

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
										text: msg['msg'],
										type: "success",
										timer: 1500
									}, function() {
										window.location = siteurl + 'expense/transport';
									});
								} else {
									swal({
										title: "Gagal!",
										text: msg['msg'],
										type: "error",
										timer: 1500
									});
								}
							},
							error: function(msg) {
								swal({
									title: "Gagal!",
									text: "Ajax Data Gagal Di Proses",
									type: "error",
									timer: 1500
								});
								console.log(msg);
							}
						});
						// try {
						// 	const imageInput = document.getElementById('doc_file');
						// 	const imageFile = imageInput.files[0];

						// 	if (!imageFile) {
						// 		alert('Please select an image!');
						// 		return false;
						// 	}

						// 	// Detect mobile devices
						// 	const isMobile = /Mobi|Android/i.test(navigator.userAgent);

						// 	// Compression settings
						// 	const options = {
						// 		maxSizeMB: 1,
						// 		maxWidthOrHeight: isMobile ? 1024 : 2000,
						// 		useWebWorker: !isMobile // Disable worker on mobile if needed
						// 	};

						// 	// 🧠 Compress the image
						// 	console.log("Original size:", imageFile.size / 1024, "KB");

						// 	const compressedFile = await imageCompression(imageFile, options);

						// 	console.log("Compressed size:", compressedFile.size / 1024, "KB");

						// 	// Build new FormData and append compressed image
						// 	const formdata = new FormData();
						// 	// formdata.append('doc_file', compressedFile, compressedFile.name);

						// 	// Append all other form fields except file
						// 	$('#frm_data').find('input, select, textarea').each(function() {
						// 		const $el = $(this);
						// 		const type = $el.attr('type');
						// 		const name = $el.attr('name');
						// 		if (!name || type === 'file') return;

						// 		if ((type === 'checkbox' || type === 'radio') && !$el.is(':checked')) return;

						// 		formdata.append(name, $el.val());
						// 	});

						// 	// 🚀 Send the AJAX request
						// 	$.ajax({
						// 		url: url_save,
						// 		dataType: "json",
						// 		type: 'POST',
						// 		data: formdata,
						// 		processData: false,
						// 		contentType: false,
						// 		success: function(msg) {
						// 			if (msg['save'] == '1') {
						// 				swal({
						// 					title: "Sukses!",
						// 					text: msg['msg'],
						// 					type: "success",
						// 					timer: 1500
						// 				}, function() {
						// 					window.location = siteurl + 'expense/transport';
						// 				});
						// 			} else {
						// 				swal({
						// 					title: "Gagal!",
						// 					text: msg['msg'],
						// 					type: "error",
						// 					timer: 1500
						// 				});
						// 			}
						// 		},
						// 		error: function(msg) {
						// 			swal({
						// 				title: "Gagal!",
						// 				text: "Ajax Data Gagal Di Proses",
						// 				type: "error",
						// 				timer: 1500
						// 			});
						// 			console.log(msg);
						// 		}
						// 	});
						// } catch (error) {
						// 	console.error("Compression failed:", error);
						// 	alert("Image compression failed. Coba gunakan gambar dengan ukuran lebih kecil.");
						// }
					}
				});
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

		function data_approve() {
			swal({
					title: "Anda Yakin?",
					text: "Data Akan Diupdate!",
					type: "info",
					showCancelButton: true,
					confirmButtonText: "Ya, setuju!",
					cancelButtonText: "Tidak!",
					closeOnConfirm: false,
					closeOnCancel: true
				},
				function(isConfirm) {
					if (isConfirm) {
						id = $("#id").val();
						$.ajax({
							url: url_approve + id,
							dataType: "json",
							type: 'POST',
							success: function(msg) {
								if (msg['save'] == '1') {
									swal({
										title: "Sukses!",
										text: "Data Berhasil Di Update",
										type: "success",
										timer: 1500
									});
									window.location.reload();
								} else {
									swal({
										title: "Gagal!",
										text: "Data Gagal Di Update",
										type: "error",
										timer: 1500
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
	</script>