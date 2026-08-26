<link rel="stylesheet" href="<?= base_url() ?>assets/plugins/select2/select2.css">
<script src="<?= base_url() ?>assets/plugins/select2/select2.full.min.js"></script>
<?php
$gambar = '';
$dept = '';
$app = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
	$data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
	$data_employee = $this->db->get_where('employee', ['id' => $data_user->employee_id])->row();
	$dept = $data_user->department_id;
	if (!empty($data_employee)) {
		$bank_id = $data_employee->bank_id;
		$accnumber = $data_employee->accnumber;
		$accname = $data_employee->accname;
		$data_head = $this->db->get_where('divisions_head', ['id' => $data_employee->division_head])->row();
		$app = $data_head->employee_id;
	}
}
?>
<?= form_open($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>
<input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="departement" name="departement" value="<?php echo $dept; ?>">
<input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
<input type="hidden" id="approval" name="approval" value="<?php echo (isset($data->approval) ? $data->approval : $app); ?>">
<input type="hidden" name="" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">

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
	.table-detail tfoot td {
		font-weight: bold;
		vertical-align: middle;
	}
</style>

<div class="box box-primary">
	<div class="box-body" style="padding: 20px;">
		<!-- SECTION 1: Informasi Dokumen -->
		<div class="section-title">
			<i class="fa fa-file-text-o"></i> 1. Informasi Pengajuan Expense
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
					<label class="col-sm-4 control-label">Tanggal Dokumen <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
							<input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label class="col-sm-4 control-label">Keterangan <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<textarea class="form-control" id="informasi" name="informasi" rows="2" placeholder="Keterangan / Keperluan Expense" required><?php echo (isset($data->informasi) ? $data->informasi : ""); ?></textarea>
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
				<div class="form-group">
					<label class="col-sm-4 control-label">Bon Bukti <b class="text-red">*</b></label>
					<div class="col-sm-8">
						<input class="form-control" type="file" name="doc_file[]" id="id_doc_file" multiple <?= (isset($data->bon_bukti) ? "" : "required") ?> style="padding: 4px; height: auto;" />
						<?php if (isset($data->bon_bukti) && $data->bon_bukti != ''): ?>
							<div style="margin-top: 5px;">
								<a href="<?= base_url($data->bon_bukti) ?>" download target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Unduh Bon Bukti</a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<!-- SECTION 2: Rekening Bank -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-university"></i> 2. Informasi Rekening Bank
		</div>
		<div class="row" id="formRekening">
			<div class="col-md-4">
				<div class="form-group">
					<label class="col-sm-4 control-label">Bank</label>
					<div class="col-sm-8">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-bank"></i></span>
							<input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Nama Bank">
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
			<input type="hidden" id="no_doc_kasbon" name="no_doc_kasbon">
			<input type="hidden" id="idKasbon" name="idKasbon">
		</div>

		<!-- SECTION 3: Rincian Expense & Kasbon -->
		<div class="section-title" style="margin-top: 15px;">
			<i class="fa fa-table"></i> 3. Rincian Expense & Kasbon
		</div>

		<div class="row" style="margin-bottom: 10px;">
			<div class="col-md-12">
				<button class="btn btn-info btn-sm stsview" type="button" onclick="add_kasbon()" id="add-kasbon">
					<i class="fa fa-user"></i> Expense Kasbon
				</button>
				<button class="btn btn-success btn-sm stsview pull-right" type="button" onclick="add_detail()" id="add-material">
					<i class="fa fa-plus"></i> Tambah Item Expense
				</button>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover table-detail" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="18%">Jenis COA</th>
						<th width="10%">Tanggal</th>
						<th width="20%">Barang / Jasa</th>
						<th width="18%">Spesifikasi</th>
						<th width="7%">Jumlah</th>
						<th width="11%">Harga Satuan</th>
						<th width="12%">Expense (Rp)</th>
						<th width="6%" class="stsview">Aksi</th>
					</tr>
				</thead>
				<tbody id="detail_body">
					<?php $total = 0;
					$idd = 1;
					$grand_total = 0;
					$total_expense = 0;
					$total_kasbon = 0;
					if (!empty($data_detail)) {
						foreach ($data_detail as $record) {
							$tekskasbon = "";
							if ($record->id_kasbon != '') $tekskasbon = 'readonly'; ?>
							<tr id='tr1_<?= $idd ?>' class='delAll <?= ($record->id_kasbon != '' ? 'kasbonrow' : '') ?>'>
								<td class="text-center" data-header="#">
									<input type='hidden' name='id_kasbon[]' id='id_kasbon_<?= $idd ?>' value='<?= $record->id_kasbon; ?>'>
									<input type="hidden" name="filename[]" id="filename_<?= $idd ?>" value="<?= $record->doc_file; ?>">
									<input type="hidden" name="detail_id[]" id="raw_id_<?= $idd ?>" value="<?= $idd; ?>" class="dtlloop">
									<input type="hidden" name="id_detail[]" id="id_detail_<?= $idd ?>" value="<?= $record->id; ?>" class="dtlloop">
									<?= $idd ?>
								</td>
								<td data-header="Jenis">
									<?php
									if ($tekskasbon == '') {
										echo form_dropdown('coa[]', $option_coa, (isset($record->coa) ? $record->coa : ''), array('id' => 'coa' . $idd, 'required' => 'required', 'class' => 'form-control select2', 'style' => 'width: 100%;'));
									} else {
										echo '<input type="hidden" name="coa[]" id="coa' . $idd . '" value="' . $record->coa . '"><span class="badge bg-gray">' . (isset($record->coa) ? $record->coa : '') . '</span>';
									}
									?>
								</td>
								<td data-header="Tanggal">
									<input type="text" class="form-control tanggal input-sm text-center" name="tanggal[]" id="tanggal<?= $idd; ?>" value="<?= $record->tanggal; ?>" <?= $tekskasbon ?>>
								</td>
								<td data-header="Barang / Jasa">
									<textarea class="form-control" rows="2" name="deskripsi[]" id="deskripsi_<?= $idd; ?>" <?= $tekskasbon; ?>><?= $record->deskripsi; ?></textarea>
								</td>
								<td data-header="Spesifikasi">
									<textarea class="form-control" rows="2" name="keterangan[]" id="keterangan_<?= $idd; ?>" <?= $tekskasbon ?>><?= $record->keterangan; ?></textarea>
								</td>
								<td data-header="Qty">
									<input type="text" class="form-control divide input-sm text-center" name="qty[]" id="qty_<?= $idd; ?>" value="<?= $record->qty; ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?>>
								</td>
								<td data-header="Harga Satuan">
									<input type="text" class="form-control divide input-sm text-right" name="harga[]" id="harga_<?= $idd; ?>" value="<?= (($tekskasbon != "") ? $record->kasbon : $record->expense) ?>" onblur="cektotal(<?= $idd; ?>)" <?= $tekskasbon ?>>
								</td>
								<td data-header="Expense">
									<input type="text" class="form-control divide subtotal input-sm text-right" name="expense[]" id="expense_<?= $idd; ?>" value="<?= (($tekskasbon != "") ? $record->kasbon : $record->expense) ?>" tabindex="-1" readonly style="font-weight: 600;">
								</td>
								<td class="text-center stsview">
									<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(<?= $idd ?>)' title='Hapus data'><i class='fa fa-trash'></i></button>
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
									$gambar .= '<div class="col-md-4" style="margin-bottom: 15px;"><a href="' . base_url('uploads/expense/' . $record->doc_file) . '" target="_blank"><img src="' . base_url('uploads/expense/' . $record->doc_file) . '" class="img-responsive img-thumbnail"></a><br /><b>' . $record->no_doc . '</b></div>';
								}
							}
							$total_expense = ($total_expense + ($record->expense));
							$total_kasbon = ($total_kasbon + ($record->kasbon));
							$idd++;
						}
						if ($data->lebih_bayar != null) {
							$grand_total = 0;
						} else {
							$grand_total = ($grand_total + ($total_expense - $total_kasbon));
						}
					} ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="7" align="right">TOTAL EXPENSE:</td>
						<td colspan="2">
							<input type="text" class="form-control divide input-sm text-right" id="total_expense" name="total_expense" value="<?= (isset($data_detail)) ? $total_expense : "" ?>" placeholder="0" tabindex="-1" readonly style="font-weight: bold;">
						</td>
					</tr>
					<tr id="total_kasbon_row" hidden>
						<td colspan="7" align="right">KASBON:</td>
						<td colspan="2">
							<input type="text" class="form-control divide input-sm text-right" id="total_kasbon" name="total_kasbon" value="<?= (isset($data_detail)) ? $total_kasbon : "" ?>" placeholder="0" tabindex="-1" readonly disabled style="font-weight: bold; color: #3c8dbc;">
						</td>
					</tr>
					<tr id="kontrol_row" <?= (isset($data->lebih_bayar)) ? "" : "hidden" ?>>
						<td colspan="7" align="right">KONTROL:</td>
						<td colspan="2">
							<input type="text" class="form-control divide input-sm text-right" onblur="updateGrandTotal()" id="kontrol" placeholder="0" tabindex="-1" value="<?= (isset($data->lebih_bayar)) ? $data->lebih_bayar : "" ?>">
						</td>
					</tr>
					<tr id="selisih_row" hidden>
						<td colspan="7" align="right">SELISIH:</td>
						<td colspan="2">
							<input type="text" class="form-control divide input-sm text-right" id="grand_total" name="grand_total" value="<?= (isset($data_detail)) ? $grand_total : "" ?>" placeholder="0" tabindex="-1" readonly disabled style="font-weight: bold; color: #dd4b39;">
							<input type="hidden" id="initial_grand_total">
						</td>
					</tr>
				</tfoot>
			</table>
		</div>

		<div class="row" id="pengembalian" <?= (isset($data->lebih_bayar)) ? "" : "hidden" ?> style="margin-top: 15px;">
			<div class="col-md-6">
				<div class="panel panel-info">
					<div class="panel-heading" style="font-weight: 600;">
						<i class="fa fa-reply"></i> Pengembalian Kasbon
					</div>
					<div class="panel-body">
						<div class="radio">
							<label>
								<input type="radio" name="pengembalian" value="1" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 1) ? 'checked' : null ?>> Cash (Pettycash)
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="pengembalian" value="2" <?= (isset($data->tipe_pengembalian) && $data->tipe_pengembalian == 2) ? 'checked' : null ?>> Transfer Bank
							</label>
						</div>
						<div style="margin-top: 10px;">
							<label class="control-label">Upload Bukti Transfer</label>
							<input type="file" name="bukti_pengembalian[]" class="form-control" multiple style="padding: 4px; height: auto;">
							<?php
							$file = '';
							if (isset($data->bukti_pengembalian)) {
								if (strpos($data->bukti_pengembalian, 'pdf', 0) > 1) {
									$file .= '<div style="margin-top: 10px;">
						<iframe src="' . base_url($data->bukti_pengembalian) . '#toolbar=0&navpanes=0" title="PDF" style="width:100%; height:300px;" frameborder="0">
								 <a href="' . base_url($data->bukti_pengembalian) . '">Download PDF</a>
						</iframe>
						<br /><b>' . $data->no_doc . '</b></div>';
								} else {
									$file .= '<div style="margin-top: 10px;"><a href="' . base_url($data->bukti_pengembalian) . '" target="_blank"><img src="' . base_url($data->bukti_pengembalian) . '" class="img-responsive img-thumbnail"></a><br /><b>' . $data->no_doc . '</b></div>';
								}
							}
							?>
							<?= $file ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
		if (isset($data_exp_kasbon) && !empty($data_exp_kasbon)) {
			foreach ($data_exp_kasbon as $exp_kasbon) :
				$no_kasbon_detail = 1;
				$this->db->select('a.*, IF(b.code IS NULL, "Pcs", b.code) AS satuan');
				$this->db->from('tr_pr_detail_kasbon a');
				$this->db->join('ms_satuan b', 'b.id = a.unit', 'left');
				$this->db->where('a.id_kasbon', $exp_kasbon['id_kasbon']);
				$get_pr_kasbon_detail = $this->db->get()->result_array();

				if (!empty($get_pr_kasbon_detail)) {
					echo '<div class="section-title" style="margin-top: 20px;"><i class="fa fa-list"></i> Rincian PR Non PO (No PR: ' . $get_pr_kasbon_detail[0]['no_pr'] . ')</div>';
					echo '<div class="table-responsive">';
					echo '<table class="table table-bordered table-striped">';
					echo '<thead>';
					echo '<tr style="background-color: #3c8dbc; color: #fff;">';
					echo '<th class="text-center" width="5%">No.</th>';
					echo '<th class="text-center">Material Name</th>';
					echo '<th class="text-center" width="10%">Qty</th>';
					echo '<th class="text-center" width="10%">Unit</th>';
					echo '<th class="text-center" width="15%">Price</th>';
					echo '<th class="text-center" width="15%">Total Price</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';

					foreach ($get_pr_kasbon_detail as $kasbon_detail) :
						echo '<tr>';
						echo '<td class="text-center">' . $no_kasbon_detail . '</td>';
						echo '<td>' . $kasbon_detail['nm_material'] . '</td>';
						echo '<td class="text-center">' . number_format($kasbon_detail['qty']) . '</td>';
						echo '<td class="text-center">' . $kasbon_detail['satuan'] . '</td>';
						echo '<td class="text-right">' . number_format($kasbon_detail['harga'], 0, ',', '.') . '</td>';
						echo '<td class="text-right" style="font-weight: 600;">' . number_format($kasbon_detail['total_harga'], 0, ',', '.') . '</td>';
						echo '</tr>';

						$no_kasbon_detail++;
					endforeach;

					echo '</tbody>';
					echo '</table>';
					echo '</div>';
				}
			endforeach;
		}
		?>

		<?php if (!empty($gambar)): ?>
			<div class="section-title" style="margin-top: 20px;">
				<i class="fa fa-paperclip"></i> Preview Lampiran Dokumen
			</div>
			<div class="row">
				<?= $gambar ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="box-footer text-center" style="background-color: #f9f9f9; padding: 15px;">
		<?php
		$urlback = '';
		if (isset($data)) {
			if ($data->status == 0) {
				if ($stsview == 'approval') {
					$urlback = 'list_expense_approval';
					echo '<button type="button" class="btn btn-success btn-sm" onclick="data_approve()" style="margin-right: 5px;"><i class="fa fa-check-square-o">&nbsp;</i> Approve</button>';
					echo '<button type="button" class="btn btn-danger btn-sm" onclick="data_reject()" style="margin-right: 5px;"><i class="fa fa-ban">&nbsp;</i> Reject</button>';
				}
			}
		}
		?>
		<button type="submit" name="save" class="btn btn-success btn-sm stsview" id="submit" style="margin-right: 5px;"><i class="fa fa-save">&nbsp;</i> Simpan Expense</button>
		<a class="btn btn-default btn-sm" onclick="window.location.reload();return false;"><i class="fa fa-reply">&nbsp;</i> Batal</a>
	</div>
</div>
<?= form_close() ?>
	<?php
	$datacombocoa = "";
	foreach ($data_budget as $keys => $val) {
		$datacombocoa .= "<option value='" . $keys . "'>" . $val . "</option>";
	}

	$datacoa = "";
	foreach ($option_coa as $keys => $val) {
		$datacoa .= "<option value='" . $keys . "'>" . $val . "</option>";
	}
	?>
	<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
	<script type="text/javascript">
		var url_save = siteurl + 'expense/save/';
		var url_approve = siteurl + 'expense/approve/';
		var nomor = parseInt("<?= $idd ?>");
		$('.divide').divide();
		$('.select2').select2();

		var stsview = '<?= isset($stsview) ? $stsview : '' ?>';
		if (stsview == 'view' || stsview == 'approval') {
			$(".stsview").addClass("hidden");
			$("#frm_data input:not([type=hidden]), #frm_data select, #frm_data textarea").prop("disabled", true);
		}

		$(function() {
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				autoclose: true
			});
		});

		// Cek apakah ada kasbon yang perlu ditampilkan
		var totalKasbon = parseFloat($("#total_kasbon").val()) || 0;
		var grandTotal = parseFloat($("#grand_total").val()) || 0;

		// Jika ada kasbon, tampilkan baris yang tersembunyi
		if (totalKasbon > 0 || grandTotal !== 0) {
			$("#total_kasbon_row").show();
			$("#selisih_row").show();
		}

		// Save Expense
		$('#frm_data').on('submit', function(e) {
			e.preventDefault();
			var errors = "";

			// Ambil nilai dari input yang akan divalidasi
			var grandTotal = parseFloat($("#grand_total").val()) || 0;
			var totalExpense = parseFloat($("#total_expense").val()) || 0;
			var totalKasbon = parseFloat($("#total_kasbon").val()) || 0;

			// Validasi form input yang sudah ada
			if ($("#informasi").val() == "") errors = "Keterangan tidak boleh kosong";
			if ($("#coa").val() == "0") errors = "Jenis Expense tidak boleh kosong";
			if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";

			// Validasi selisih
			// if (grandTotal > 0) {
			// 	errors = "Selisih harus 0!";
			// } else if (grandTotal < 0 && totalExpense <= totalKasbon) {
			// 	errors = "Selisih minus hanya diperbolehkan jika Total Expense lebih besar dari Total Kasbon!";
			// }

			// Jika ada error, tampilkan SweetAlert dan hentikan submit
			if (errors !== "") {
				swal(errors);
				return false;
			}

			// Konfirmasi sebelum menyimpan data
			swal({
				title: "Anda Yakin?",
				text: "Data Akan Disimpan!",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, simpan!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			}, function(isConfirm) {
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
									text: "Data Berhasil Disimpan",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Disimpan",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							}
							console.log(msg);
						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Diproses",
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


		function cektotal(id) {
			var sqty = $("#qty_" + id).val();
			var pref = $("#harga_" + id).val();
			var subtotal = (parseFloat(sqty) * parseFloat(pref));
			$("#expense_" + id).val(subtotal);
			var sum = 0;
			$('.subtotal').each(function() {
				sum += Number($(this).val());
			});
			$("#total_expense").val(sum);
			var sumkasbon = 0;
			$('.subkasbon').each(function() {
				sumkasbon += Number($(this).val());
			});
			$("#total_kasbon").val(sumkasbon);
			$("#grand_total").val(Number(sumkasbon) - Number(sum));
			var grandTotal = $("#grand_total").val(grandTotal);

			var totalExpense = $("#total_expense").val()
			if (totalExpense > 0) {
				if (grandTotal > 0) {
					$("#initial_grand_total").val(grandTotal);

					$("#pengembalian").show();
					$("#kontrol_row").show();
					$("input[name='pengembalian']").prop("required", true).prop("disabled", false);
					$("input[name='kontrol']").prop("required", true).prop("disabled", false);

					$("input[name='pengembalian']").off("change").on("change", function() {
						if ($(this).val() == "2") {
							$("input[name='bukti_pengembalian']").prop("required", true).prop("disabled", false);
						} else {
							$("input[name='bukti_pengembalian']").prop("required", false).prop("disabled", true).val('');
						}
					});
				} else {
					$("#pengembalian").hide();
					$("#kontrol_row").hide();
					$("input[name='pengembalian']").prop("required", false).prop("disabled", true);
					$("input[name='bukti_pengembalian']").prop("required", false).prop("disabled", true).val('');
					$("input[name='kontrol']").prop("required", false).prop("disabled", true);
				}
			} else {
				$("#pengembalian").hide();
				$("#kontrol_row").hide();
				$("input[name='pengembalian']").prop("required", false).prop("disabled", true);
				$("input[name='bukti_pengembalian']").prop("required", false).prop("disabled", true).val('');
				$("input[name='kontrol']").prop("required", false).prop("disabled", true);
			}
		}

		function updateGrandTotal() {
			var initialGrandTotal = parseFloat($("#initial_grand_total").val()) || 0;
			var kontrolVal = parseFloat($("#kontrol").val()) || 0;
			var newGrandTotal = initialGrandTotal - kontrolVal;
			$("#grand_total").val(newGrandTotal);
		}

		function add_kasbon() {
			var nama = $("#nama").val();
			var departement = $("#departement").val();

			$.ajax({
				url: siteurl + 'expense/get_kasbon/' + nama + '/' + departement + '/<?= (isset($data->no_doc) ? $data->no_doc : ""); ?>',
				type: "POST",
				dataType: "json",
				success: function(data) {
					var tbody = '';
					for (var i = 0; i < data.length; i++) {
						tbody += '<tr>';
						tbody += '<td>' + (i + 1) + '</td>';
						tbody += '<td>' + data[i].no_doc + '</td>';
						tbody += '<td>' + data[i].tgl_doc + '</td>';
						tbody += '<td>' + data[i].keperluan + '</td>';
						tbody += '<td>' + data[i].keterangan + '</td>';
						tbody += '<td>' + data[i].jumlah_kasbon + '</td>';
						tbody += '<td style="display:none">' + data[i].bank_id + '</td>';
						tbody += '<td style="display:none">' + data[i].accnumber + '</td>';
						tbody += '<td style="display:none">' + data[i].accname + '</td>';
						tbody += '<td style="display:none">' + data[i].id + '</td>';
						tbody += '<td><button class="btn btn-primary btn-sm" onclick="selectKasbon(' + i + ')">Pilih</button></td>';
						tbody += '</tr>';
					}
					$('#tableKasbon tbody').html(tbody);
					$('#modalKasbon').modal('show');
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

		function selectKasbon(index) {
			var row = $('#tableKasbon tbody tr').eq(index);
			var no_doc = row.find('td').eq(1).text();
			var tgl_doc = row.find('td').eq(2).text();
			var keperluan = row.find('td').eq(3).text();
			var keterangan = row.find('td').eq(4).text();
			var jumlah = row.find('td').eq(5).text();
			var bank_id = row.find('td').eq(6).text();
			var accnumber = row.find('td').eq(7).text();
			var accname = row.find('td').eq(8).text();
			var id = row.find('td').eq(9).text();

			var nomor = $('.kasbonrow').length + 1;
			var datacoa = "<?= $datacoa ?>";

			var Rows = "<tr id='tr1_" + nomor + "' class='delAll kasbonrow'>";
			Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value='" + no_doc + "'>";
			Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "'>";
			Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "'>";
			Rows += nomor + " </td>";
			Rows += "<td data-header='COA'>";
			Rows += "<select name='coa[]' id='coa_" + nomor + "' class='form-control select' readonly><?= $datacoa ?></select>";
			Rows += "</td>";
			Rows += "<td data-header='Tanggal'>";
			Rows += "<input type='text' class='form-control tanggal input-sm' name='tanggal[]' id='tanggal_" + nomor + "' tabindex='-1' readonly value='" + tgl_doc + "' />";
			Rows += "</td>";
			Rows += "<td data-header='Barang / Jasa'>";
			Rows += "<textarea class='form-control' name='deskripsi[]' id='deskripsi_" + nomor + "' readonly>" + keperluan + "</textarea>";
			Rows += "<input type='hidden' class='form-control input-sm' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='' />";
			Rows += "</td>";
			Rows += "<td data-header='Spesifikasi'>";
			Rows += "<textarea class='form-control' name='keterangan[]' id='keterangan_" + nomor + "' readonly>" + keterangan + "</textarea>";
			Rows += "</td>";
			Rows += "<td data-header='Qty'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' value='1' id='qty_" + nomor + "' tabindex='-1' readonly />";
			Rows += "</td>";
			Rows += "<td data-header='Harga Satuan'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' value='" + jumlah + "' id='harga_" + nomor + "' tabindex='-1' readonly />";
			Rows += "</td>";
			Rows += "<td data-header='Expense'>";
			Rows += "<input type='hidden' class='form-control divide input-sm subtotal' name='expense[]' id='expense_" + nomor + "' tabindex='-1' readonly />";
			Rows += "<input type='text' class='form-control divide input-sm subkasbon' name='kasbon[]' value='" + jumlah + "' id='kasbon_" + nomor + "' readonly />";
			Rows += "</td>";
			Rows += "<td align='center'>";
			Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
			Rows += "</td>";
			Rows += "</tr>";

			//isi data rekening
			$('#bank_id').val(bank_id);
			$('#accnumber').val(accnumber);
			$('#accname').val(accname);
			$('#no_doc_kasbon').val(no_doc);
			$('#idKasbon').val(id);

			//showhide & disabled total kasbon dan selisih
			if (id != null) {
				$("#total_kasbon_row").show()
				$("#selisih_row").show()
				$("#total_kasbon").prop("disabled", false);
				$("#grand_total").prop("disabled", false);
			}

			$('#detail_body').append(Rows);
			$('#modalKasbon').modal('hide');
			$(".divide").divide();
			cektotal(); // Perbarui total setelah menambahkan kasbon
		}

		function add_detail() {
			var nomor = $("#detail_body tr").length + 1; // Hitung jumlah baris
			var datacombocoa = "<?= $datacombocoa ?>";
			var datacoa = "<?= $datacoa ?>";
			var Rows = "<tr id='tr1_" + nomor + "' class='delAll'>";
			Rows += "<td data-header='#'><input type='hidden' name='id_kasbon[]' id='id_kasbon_" + nomor + "' value=''>";
			Rows += "<input type='hidden' name='detail_id[]' id='raw_id_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
			Rows += "<input type='hidden' name='id_detail[]' id='id_detail_" + nomor + "' value='" + nomor + "' class='dtlloop'>";
			Rows += nomor + "</td>";
			Rows += "<td data-header='Jenis'>";
			Rows += "<select name='coa[]' id='coa_" + nomor + "' required='required' class='form-control select2'><?= $datacoa ?></select>";
			Rows += "</td>";
			Rows += "<td data-header='Tanggal'>";
			Rows += "<input type='text' class='form-control tanggal input-sm' placeholder='Tanggal' name='tanggal[]' id='tanggal_" + nomor + "' />";
			Rows += "</td>";
			Rows += "<td data-header='Barang / Jasa'>";
			Rows += "<textarea class='form-control' placeholder='Barang/Jasa' name='deskripsi[]' id='deskripsi_" + nomor + "'></textarea>";
			Rows += "<input type='hidden' class='form-control input-sm' name='id_expense_detail[]' id='id_expense_detail_" + nomor + "' value='' />";
			Rows += "</td>";
			Rows += "<td data-header='Spesifikasi'>";
			Rows += "<textarea class='form-control' placeholder='Spesifikasi' name='keterangan[]' id='keterangan_" + nomor + "'></textarea>";
			Rows += "</td>";
			Rows += "<td data-header='Qty'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='qty[]' id='qty_" + nomor + "' onblur='cektotal(" + nomor + ")'/>";
			Rows += "</td>";
			Rows += "<td data-header='Harga Satuan'>";
			Rows += "<input type='text' class='form-control divide input-sm' name='harga[]' id='harga_" + nomor + "' onblur='cektotal(" + nomor + ")' />";
			Rows += "</td>";
			Rows += "<td data-header='Expense'>";
			Rows += "<input type='text' class='form-control divide input-sm subtotal' name='expense[]' id='expense_" + nomor + "' tabindex='-1' readonly />";
			Rows += "<input type='hidden' class='form-control divide input-sm subkasbon' name='kasbon[]' id='kasbon_" + nomor + "' readonly />";
			Rows += "</td>";
			Rows += "<th align='center' th scope='row'>";
			Rows += "<button type='button' class='btn btn-danger btn-xs' data-toggle='tooltip' onClick='delDetail(" + nomor + ")' title='Hapus data'><i class='fa fa-close'></i> Hapus</button>";
			Rows += "</th>";
			Rows += "</tr>";
			$('#detail_body').append(Rows);
			$("#tanggal_" + nomor).focus();
			$(".tanggal").datepicker({
				todayHighlight: true,
				format: "yyyy-mm-dd",
				showInputs: true,
				autoclendif: true,
			});
			$('.select2').select2();
			$(".divide").divide();
			cektotal();
		}

		function delDetail(row) {
			var idKasbon = $('#idKasbon').val()

			$('#tr1_' + row).remove();

			$('#detail_body tr').each(function(index) {
				var newRowNum = index + 1;
				$(this).attr('id', 'tr1_' + newRowNum);

				$(this).find('[id]').each(function() {
					var id = $(this).attr('id');
					if (id) {
						var newId = id.replace(/\d+$/, newRowNum);
						$(this).attr('id', newId);
					}
				});

				$(this).find('[name]').each(function() {
					var name = $(this).attr('name');
					if (name) {
						var newName = name.replace(/\[\d+\]$/, '[' + newRowNum + ']');
						$(this).attr('name', newName);
					}
				});
			});

			var rowKasbon = $(".kasbonrow").length;

			if (rowKasbon < 1) {
				$("#total_kasbon_row").hide()
				$("#selisih_row").hide()
				$("#bank_id").val("")
				$("#accnumber").val("")
				$("#accname").val("")
				$("#total_kasbon").prop("disabled", true);
				$("#grand_total").prop("disabled", true);
			}

			cektotal();
		}

		function data_approve() {
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
						id = $("#id").val();
						$.ajax({
							url: url_approve + id,
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
									window.location = siteurl + 'expense/list_expense_approval';
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
							text: "Data Akan Ditolak!",
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
									url: siteurl + 'expense/reject/',
									data: {
										'id': id,
										'reason': inputValue,
										'table': 'tr_expense'
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
											window.location = siteurl + 'expense/list_expense_approval';
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
</div>