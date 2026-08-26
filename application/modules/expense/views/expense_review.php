<?php
$data_session = $this->session->userdata;
$dateTime     = date('Y-m-d H:i:s');
$UserName     = isset($data_session['app_session']['id_user']) ? $data_session['app_session']['id_user'] : '';
$dept         = isset($data_session['app_session']['department_id']) ? $data_session['app_session']['department_id'] : '';
$readonly     = 'readonly';

// Ambil riwayat pengembalian jika ada
$history_pengembalian = [];
if (isset($data->no_doc)) {
	$this->db->select('a.*, b.nama as nama_bank');
	$this->db->from('tr_pengembalian_expense a');
	$this->db->join(DBACC . '.coa_master b', 'b.no_perkiraan = a.transfer_coa_bank', 'left');
	$this->db->where('a.no_doc', $data->no_doc);
	$this->db->order_by('a.id', 'asc');
	$history_pengembalian = $this->db->get()->result();
}

$has_pending_return = false;
$total_kembali_terbayar = 0;
foreach ($history_pengembalian as $hp) {
	if ($hp->status == 1) {
		$total_kembali_terbayar += floatval($hp->transfer_jumlah);
	} elseif ($hp->status == 0 || $hp->status === null) {
		$has_pending_return = true;
	}
}
?>

<style>
	.review-card {
		background: #fff;
		border-radius: 4px;
		border: 1px solid #e3e6f0;
		box-shadow: 0 1px 3px rgba(0,0,0,0.08);
		margin-bottom: 20px;
		padding: 15px 20px;
	}
	.review-card-title {
		font-size: 14px;
		font-weight: 700;
		color: #337ab7;
		border-bottom: 2px solid #e3e6f0;
		padding-bottom: 8px;
		margin-bottom: 15px;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.review-table thead th {
		background-color: #3c8dbc !important;
		color: #ffffff !important;
		text-align: center;
		vertical-align: middle !important;
		border: 1px solid #367fa9 !important;
		font-size: 12px;
		padding: 8px 5px !important;
	}
	.review-table tbody td {
		vertical-align: middle !important;
		font-size: 12px;
		padding: 6px 8px !important;
	}
	.badge-status {
		font-size: 11px;
		padding: 4px 8px;
		font-weight: 600;
		border-radius: 3px;
	}
	.summary-box {
		background-color: #f8f9fc;
		border-radius: 4px;
		padding: 10px 15px;
		border-left: 4px solid #3c8dbc;
	}
</style>

<?= form_open_multipart($this->uri->uri_string(), array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')); ?>
<input type="hidden" id="id" name="id" value="<?= set_value('id', isset($data->id) ? $data->id : ''); ?>">
<input type="hidden" id="nama" name="nama" value="<?= (isset($data->nama) ? $data->nama : $UserName); ?>">

<!-- 1. INFORMASI DOKUMEN -->
<div class="review-card">
	<div class="review-card-title">
		<i class="fa fa-file-text-o"></i> Informasi Expense Dokumen
	</div>
	<div class="row">
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; color: #555;">No Dokumen</label>
				<input type="text" class="form-control input-sm" id="no_doc" name="no_doc" value="<?= (isset($data->no_doc) ? $data->no_doc : ""); ?>" readonly style="font-weight: bold; background-color: #f4f6f9;">
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; color: #555;">Tanggal Dokumen</label>
				<input type="text" class="form-control input-sm" id="tgl_doc" name="tgl_doc" value="<?= (isset($data->tgl_doc) ? date('d M Y', strtotime($data->tgl_doc)) : date("d M Y")); ?>" readonly style="background-color: #f4f6f9;">
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; color: #555;">Request By</label>
				<input type="text" class="form-control input-sm" value="<?= (isset($data->nama) ? $data->nama : $UserName); ?>" readonly style="background-color: #f4f6f9;">
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 10px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; color: #555;">Department</label>
				<?php
				$dept_name = '-';
				$deptid = (isset($data->departement) ? $data->departement : $dept);
				foreach ($data_departement as $item) {
					if ($item->id == $deptid) {
						$dept_name = strtoupper($item->nama);
						break;
					}
				}
				?>
				<input type="text" class="form-control input-sm" value="<?= $dept_name ?>" readonly style="background-color: #f4f6f9;">
			</div>
		</div>
		<div class="col-md-12">
			<div class="form-group" style="margin-bottom: 0; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; color: #555;">Keterangan / Keperluan</label>
				<textarea class="form-control input-sm" rows="2" readonly style="background-color: #f4f6f9; resize: none;"><?= (isset($data->informasi) ? $data->informasi : ""); ?></textarea>
			</div>
		</div>
	</div>
</div>

<!-- 2. RINCIAN EXPENSE & KASBON -->
<div class="review-card">
	<div class="review-card-title">
		<i class="fa fa-list-alt"></i> Rincian Transaksi Expense & Kasbon
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-striped table-hover review-table" width="100%">
			<thead>
				<tr>
					<th width="4%">#</th>
					<th width="20%">Jenis & Tanggal</th>
					<th width="24%">Barang / Jasa & Keterangan</th>
					<th width="8%">Qty</th>
					<th width="14%">Harga Satuan</th>
					<th width="14%">Expense</th>
					<th width="14%">Kasbon</th>
					<th width="6%">Bukti</th>
				</tr>
			</thead>
			<tbody id="detail_body">
				<?php
				$idd           = 1;
				$total_expense = 0;
				$total_kasbon  = 0;
				if (!empty($data_detail)) {
					foreach ($data_detail as $record) {
						$is_kasbon = ($record->id_kasbon != '');
						$coa_label = '-';
						if (!empty($record->coa) && $record->coa != '0') {
							$coa_label = isset($data_budget[$record->coa]) ? $data_budget[$record->coa] : $record->coa;
						} elseif ($is_kasbon) {
							$coa_label = '<span class="badge bg-yellow" style="font-size: 11px;">Kasbon</span>';
						}
						$tgl_item  = (!empty($record->tanggal) && $record->tanggal != '0000-00-00') ? date('d/m/Y', strtotime($record->tanggal)) : '-';
						?>
						<tr class="<?= ($is_kasbon ? 'info' : '') ?>">
							<td class="text-center"><?= $idd; ?></td>
							<td>
								<b><?= $coa_label ?></b>
								<?php if ($tgl_item != '-') : ?>
									<div class="text-muted" style="font-size: 11px;"><i class="fa fa-calendar"></i> <?= $tgl_item ?></div>
								<?php endif; ?>
							</td>
							<td>
								<b><?= $record->deskripsi ? $record->deskripsi : '-' ?></b>
								<?php if (!empty($record->keterangan)) : ?>
									<div class="text-muted" style="font-size: 11px;"><?= $record->keterangan ?></div>
								<?php endif; ?>
							</td>
							<td class="text-center"><?= number_format($record->qty, 0, ',', '.') ?></td>
							<td class="text-right">Rp <?= number_format($record->harga, 0, ',', '.') ?></td>
							<td class="text-right" style="font-weight: 600; color: #337ab7;">
								<?= ($record->expense > 0) ? 'Rp ' . number_format($record->expense, 0, ',', '.') : '-' ?>
							</td>
							<td class="text-right" style="font-weight: 600; color: #f39c12;">
								<?= ($record->kasbon > 0) ? 'Rp ' . number_format($record->kasbon, 0, ',', '.') : '-' ?>
							</td>
							<td class="text-center">
								<?php if (!empty($record->doc_file)) : ?>
									<a href="<?= base_url('uploads/expense/' . $record->doc_file) ?>" download target="_blank" class="btn btn-xs btn-default" title="Unduh Bukti"><i class="fa fa-download text-primary"></i></a>
								<?php else : ?>
									<span class="text-muted">-</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php
						$total_expense += floatval($record->expense);
						$total_kasbon  += floatval($record->kasbon);
						$idd++;
					}
				} else {
					echo '<tr><td colspan="8" class="text-center text-muted">Tidak ada rincian data</td></tr>';
				}

				// Kalkulasi Saldo Kelebihan Kasbon
				$grand_total = $total_expense - $total_kasbon;
				$kelebihan_kasbon = 0;
				if ($grand_total < 0) {
					$kelebihan_kasbon = abs($grand_total);
				} elseif (isset($data->lebih_bayar) && $data->lebih_bayar > 0) {
					$kelebihan_kasbon = floatval($data->lebih_bayar);
				}
				?>
			</tbody>
			<tfoot>
				<tr style="background-color: #f8fafc; font-weight: bold;">
					<td colspan="5" class="text-right">TOTAL:</td>
					<td class="text-right" style="color: #337ab7; font-size: 13px;">Rp <?= number_format($total_expense, 0, ',', '.') ?></td>
					<td class="text-right" style="color: #f39c12; font-size: 13px;">Rp <?= number_format($total_kasbon, 0, ',', '.') ?></td>
					<td></td>
				</tr>
				<tr style="background-color: #eff6ff; font-weight: bold;">
					<td colspan="5" class="text-right" style="color: #1e3a8a; vertical-align: middle;">
						<i class="fa fa-arrow-circle-down"></i> Sisa Kelebihan Kasbon Yang Harus Dikembalikan:
					</td>
					<td colspan="3" class="text-right" style="color: #dc2626; font-size: 15px; vertical-align: middle;">
						Rp <?= number_format($kelebihan_kasbon, 0, ',', '.') ?>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

<!-- 3. RIWAYAT PENGEMBALIAN JIKA ADA -->
<?php if (!empty($history_pengembalian)) : ?>
<div class="review-card">
	<div class="review-card-title">
		<i class="fa fa-history"></i> Riwayat Pengembalian Dana Kasbon
	</div>
	<div class="table-responsive">
		<table class="table table-bordered table-striped review-table" width="100%">
			<thead>
				<tr>
					<th width="4%">#</th>
					<th width="15%">Tanggal Transfer</th>
					<th width="30%">Rekening Bank Tujuan</th>
					<th width="20%">Nilai Transfer</th>
					<th width="15%">Bukti Transfer</th>
					<th width="16%">Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no_h = 1;
				foreach ($history_pengembalian as $hp) :
					$st_badge = '<span class="badge badge-status bg-yellow">Menunggu Approval</span>';
					if ($hp->status == 1) {
						$st_badge = '<span class="badge badge-status bg-green">Disetujui (Approved)</span>';
					} elseif ($hp->status == 9 || $hp->status == 2) {
						$st_badge = '<span class="badge badge-status bg-red">Ditolak</span>';
					}
					?>
					<tr>
						<td class="text-center"><?= $no_h++ ?></td>
						<td class="text-center"><?= date('d M Y', strtotime($hp->transfer_tanggal)) ?></td>
						<td><?= $hp->transfer_coa_bank ?> - <?= $hp->nama_bank ?></td>
						<td class="text-right" style="font-weight: 600; color: #00a65a;">Rp <?= number_format($hp->transfer_jumlah, 0, ',', '.') ?></td>
						<td class="text-center">
							<?php if (!empty($hp->transfer_file)) : ?>
								<a href="<?= base_url('uploads/expense/' . $hp->transfer_file) ?>" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Bukti</a>
							<?php else : ?>
								<span class="text-muted">-</span>
							<?php endif; ?>
						</td>
						<td class="text-center"><?= $st_badge ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>

<!-- 4. FORM INPUT PENGEMBALIAN DANA (HANYA MUNCUL JIKA MASIH ADA SISA & TIDAK ADA PENDING APPROVAL) -->
<?php
$sisa_kembali = $kelebihan_kasbon - $total_kembali_terbayar;
if ($sisa_kembali < 0) $sisa_kembali = 0;
?>

<?php if ($has_pending_return) : ?>
<div class="alert alert-warning" style="margin-top: 15px; margin-bottom: 15px; font-size: 13px; border-radius: 4px; border-left: 4px solid #f39c12; background-color: #fffaf0; color: #8a6d3b;">
	<i class="fa fa-clock-o fa-lg text-yellow"></i> <b>Pengembalian Sedang Menunggu Approval:</b><br>
	Pengembalian dana untuk dokumen ini telah diinput dan saat ini sedang menunggu persetujuan (*approval*) oleh Finance / Management pada menu <b>Approval Pengembalian Expense</b>. Form input baru dinonaktifkan sementara sampai pengembalian tersebut selesai diproses.
</div>

<!-- TOMBOL AKSI VIEW -->
<div class="text-center" style="margin-top: 10px;">
	<button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="min-width: 100px;">
		<i class="fa fa-close"></i> Tutup
	</button>
</div>

<?php elseif ($sisa_kembali > 0) : ?>
<div class="review-card" style="border: 2px solid #3c8dbc; background-color: #fcfdfe;">
	<div class="review-card-title" style="color: #2c5282; border-bottom-color: #3c8dbc;">
		<i class="fa fa-university"></i> Form Input Pengembalian Dana ke Rekening Perusahaan
	</div>
	<div class="row">
		<div class="col-md-4 col-sm-6">
			<div class="form-group" style="margin-bottom: 12px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; font-weight: 600;">Rekening Bank Tujuan <span class="text-red">*</span></label>
				<select name="transfer_coa_bank" id="transfer_coa_bank" class="form-control select2" required style="width: 100%;">
					<option value="">-- Pilih Bank Tujuan --</option>
					<?php
					foreach ($data_coa as $item) {
						echo '<option value="' . $item->no_perkiraan . '">' . $item->no_perkiraan . ' - ' . $item->nama . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 12px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; font-weight: 600;">Tanggal Transfer <span class="text-red">*</span></label>
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
					<input type="text" class="form-control tanggal input-sm" name="transfer_tanggal" id="transfer_tanggal" value="<?= date("Y-m-d"); ?>" required autocomplete="off">
				</div>
			</div>
		</div>
		<div class="col-md-3 col-sm-6">
			<div class="form-group" style="margin-bottom: 12px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; font-weight: 600;">Nilai Transfer (Rp) <span class="text-red">*</span></label>
				<input type="text" class="form-control divide text-right input-sm" name="transfer_jumlah" id="transfer_jumlah" value="<?= $sisa_kembali ?>" required style="font-weight: bold; color: #008d4c;">
			</div>
		</div>
		<div class="col-md-2 col-sm-6">
			<div class="form-group" style="margin-bottom: 12px; padding: 0 10px;">
				<label class="control-label" style="font-size: 12px; font-weight: 600;">Bukti Transfer</label>
				<input type="file" name="transfer_file" id="transfer_file" class="form-control" style="height: auto; padding: 3px; font-size: 11px;">
			</div>
		</div>
	</div>
</div>

<!-- 5. TOMBOL AKSI INPUT -->
<div class="text-center" style="margin-top: 15px; padding-top: 10px;">
	<button type="submit" name="save" class="btn btn-success btn-flat" id="submit" style="min-width: 140px; margin-right: 8px;">
		<i class="fa fa-save"></i> Simpan Pengembalian
	</button>
	<button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="min-width: 90px;">
		<i class="fa fa-close"></i> Batal / Tutup
	</button>
</div>

<?php else : ?>
<div class="alert alert-success text-center" style="margin-top: 10px; margin-bottom: 15px; font-size: 13px; font-weight: 600; border-radius: 4px;">
	<i class="fa fa-check-circle fa-lg"></i> Seluruh dana kelebihan kasbon telah dikembalikan ke rekening perusahaan (Lunas).
</div>

<!-- 5. TOMBOL AKSI VIEW -->
<div class="text-center" style="margin-top: 10px;">
	<button type="button" class="btn btn-default btn-flat" data-dismiss="modal" style="min-width: 100px;">
		<i class="fa fa-close"></i> Tutup
	</button>
</div>
<?php endif; ?>

<?= form_close() ?>

<script type="text/javascript">
	var url_approve = siteurl + 'expense/return_confirm/';
	
	$('.divide').divide();
	$('.select2').select2({
		dropdownParent: $('#Mymodal')
	});

	$(".tanggal").datepicker({
		todayHighlight: true,
		format: "yyyy-mm-dd",
		autoclose: true
	});

	$('#frm_data').on('submit', function(e) {
		e.preventDefault();
		
		var bank = $('#transfer_coa_bank').val();
		var jumlah = parseFloat($('#transfer_jumlah').val().replace(/,/g, '')) || 0;

		if (!bank) {
			swal({
				title: "Peringatan!",
				text: "Silakan pilih rekening bank tujuan terlebih dahulu!",
				type: "warning"
			});
			return false;
		}

		if (jumlah <= 0) {
			swal({
				title: "Peringatan!",
				text: "Nilai transfer harus lebih dari 0!",
				type: "warning"
			});
			return false;
		}

		swal({
			title: "Konfirmasi Pengembalian",
			text: "Apakah data pengembalian dana sudah sesuai?",
			type: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, Simpan!",
			cancelButtonText: "Batal",
			closeOnConfirm: false,
			closeOnCancel: true
		},
		function(isConfirm) {
			if (isConfirm) {
				var formdata = new FormData($('#frm_data')[0]);
				var id = $("#id").val();
				
				$.ajax({
					url: url_approve + id,
					dataType: "json",
					type: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function(msg) {
						if (msg['save'] == '1' || msg['save'] == true) {
							swal({
								title: "Sukses!",
								text: "Data Pengembalian Berhasil Disimpan",
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							$('#Mymodal').modal('hide');
							if (typeof reload_table_return === 'function') {
								reload_table_return();
							} else {
								window.location.reload();
							}
						} else {
							if (msg['valid'] == 2) {
								swal({
									title: "Gagal!",
									text: "Total pengembalian melebihi sisa nilai kelebihan kasbon!",
									type: "error"
								});
							} else if (msg['valid'] == 3) {
								swal({
									title: "Perhatian!",
									text: "Pengembalian untuk dokumen ini sedang menunggu persetujuan (approval) oleh Finance!",
									type: "warning"
								});
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Disimpan, silakan periksa inputan Anda",
									type: "error"
								});
							}
						}
					},
					error: function(xhr, status, error) {
						swal({
							title: "Gagal!",
							text: "Terjadi kesalahan saat memproses data (" + error + ")",
							type: "error"
						});
					}
				});
			}
		});
	});
</script>