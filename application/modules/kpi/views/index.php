<?php
$ENABLE_ADD     = has_permission('KPI.Add');
$ENABLE_MANAGE  = has_permission('KPI.Manage');
$ENABLE_VIEW    = has_permission('KPI.View');
$ENABLE_DELETE  = has_permission('KPI.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>">
<!-- SweetAlert2 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>
<style>
	.ajax_loader {
		display: none !important;
	}

	.skeleton {
		background: #f2f2f2;
		border-radius: 4px;
		animation: shimmer 1.5s infinite linear;
		background: linear-gradient(90deg, #f2f2f2 25%, #e0e0e0 50%, #f2f2f2 75%);
		background-size: 200% 100%;
	}

	@keyframes shimmer {
		0% {
			background-position: 200% 0;
		}

		100% {
			background-position: -200% 0;
		}
	}

	.skeleton-line {
		height: 20px;
		margin: 8px 0;
	}

	.skeleton-line.short {
		width: 60%;
	}

	.skeleton-line.medium {
		width: 80%;
	}
</style>

<div class="box">
	<!-- /.box-header -->
	<div class="box-body">
		<div class="box-header text-right" style="padding-bottom:10px;">
			<button class="btn btn-sm btn-primary refresh-list-kpi">
				<i class="fa fa-refresh"></i> Refresh
			</button>
			<?php if (has_permission('KPI.Add')): ?>
				<a href="<?= site_url('kpi/add') ?>" class="btn btn-success btn-sm">
					<i class="fa fa-plus"></i> Add New KPI
				</a>
			<?php endif; ?>
		</div>
		<div id="skeleton-loading">
			<table class="table table-bordered">
				<tbody>
					<?php for ($i = 0; $i < 5; $i++): ?>
						<tr>
							<td width="5%">
								<div class="skeleton skeleton-line short"></div>
							</td>
							<td>
								<div class="skeleton skeleton-line medium"></div>
							</td>
							<td width="15%">
								<div class="skeleton skeleton-line short"></div>
							</td>
							<td width="15%">
								<div class="skeleton skeleton-line short"></div>
							</td>
							<td width="15%">
								<div class="skeleton skeleton-line medium"></div>
							</td>
							<td width="15%">
								<div class="skeleton skeleton-line medium"></div>
							</td>
							<td width="20%">
								<div class="skeleton skeleton-line short"></div>
							</td>
						</tr>
					<?php endfor; ?>
				</tbody>
			</table>
		</div>

		<div id="kpi-content" style="display:none;"></div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
	function loadKpiList() {
		$.ajax({
			url: siteurl + active_controller + 'get_list',
			type: 'GET',
			beforeSend: function() {
				$('#skeleton-loading').show();
				$('#kpi-content').hide();
			},
			success: function(response) {
				$('#skeleton-loading').hide();
				$('#kpi-content').html(response).fadeIn();

				if ($.fn.DataTable.isDataTable('#table_kpi')) {
					$('#table_kpi').DataTable().destroy();
				}

				$('#table_kpi').DataTable({
					paging: true,
					searching: true,
					ordering: true,
					info: true
				});
			},
			error: function() {
				$('#skeleton-loading').hide();
				$('#kpi-content')
					.html('<p class="text-danger">Gagal memuat data.</p>')
					.show();
			}
		});
	}

	$(document).ready(function() {
		loadKpiList();

		$(document).on('click', '.refresh-list-kpi', function(e) {
			e.preventDefault();
			loadKpiList();
		});

		$(document).on('click', '.btn-realisasi', function(e) {
			e.preventDefault();

			var headerId = $(this).data('id');
			var isRealisasi = $(this).data('is-realisasi');
			var divisi = $(this).data('divisi');

			if (isRealisasi == 1 || isRealisasi == 2) {
				window.location.href = '<?= site_url('kpi/realisasi/') ?>' + headerId;
			} else {
				Swal.fire({
					title: 'Perhatian!',
					html: `
                        <div style="text-align: center;">
                            <p class="text-warning" style="font-size: 14px;">
                                <i class="fa fa-exclamation-triangle"></i> 
                                <strong>Pastikan pengisian indikator sudah selesai dibuat!</strong>
                            </p>
                            <p style="font-size: 13px;" class="text-danger">
                                Setelah Anda mulai mengisi realisasi, <strong>indikator tidak dapat diubah lagi</strong>.
                            </p>
                            <br>
                            <p style="font-size: 13px;">Apakah Anda yakin ingin melanjutkan?</p>
                        </div>
                    `,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#3085d6',
					cancelButtonColor: '#d33',
					confirmButtonText: '<i class="fa fa-check"></i> Ya, Lanjutkan',
					cancelButtonText: '<i class="fa fa-times"></i> Batal',
					customClass: {
						popup: 'swal-wide'
					}
				}).then((result) => {
					if (result.isConfirmed) {
						window.location.href = '<?= site_url('kpi/realisasi/') ?>' + headerId;
					}
				});
			}
		});

		$(document).on('click', '.btn-close-period', function(e) {
			e.preventDefault();

			var headerId = $(this).data('id');
			var periode = $(this).data('periode');
			var divisi = $(this).data('divisi');
			var button = $(this);

			Swal.fire({
				title: 'Tutup Periode KPI?',
				html: `
                        <p class="text-danger">
                            <i class="fa fa-exclamation-triangle"></i>
                            Realisasi akan dikunci dan tidak dapat diubah.
                        </p>
                        `,

				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: '<i class="fa fa-lock"></i> Ya, Tutup Periode!',
				cancelButtonText: '<i class="fa fa-times"></i> Batal',
				width: '400px'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Memproses...',
						text: 'Sedang menutup periode KPI',
						allowOutsideClick: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});
					$.ajax({
						url: '<?= site_url('kpi/close_period') ?>',
						type: 'POST',
						data: {
							header_id: headerId,
							<?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
						},
						dataType: 'json',
						success: function(res) {
							if (res.status === 'success') {
								Swal.fire({
									title: 'Berhasil!',
									text: 'Periode KPI telah ditutup.',
									icon: 'success',
									timer: 2000,
									showConfirmButton: false
								}).then(() => {
									loadKpiList();
								});
							} else {
								Swal.fire({
									title: 'Gagal',
									text: res.message,
									icon: 'error'
								});
							}
						},
						error: function() {
							Swal.fire({
								title: 'Error!',
								text: 'Tidak dapat terhubung ke server.',
								icon: 'error'
							});
						}
					});
				}
			});
		});

		$(document).on('click', '.btn-delete', function(e) {
			e.preventDefault();

			var url = $(this).attr('href');
			var divisi = $(this).data('divisi');
			var button = $(this);
			var row = button.closest('tr');

			Swal.fire({
				title: 'Konfirmasi Hapus',
				html: `Yakin ingin menghapus KPI untuk divisi <strong>${divisi}</strong>?<br><small class="text-danger">Data yang sudah dihapus tidak dapat dikembalikan!</small>`,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
				cancelButtonText: '<i class="fa fa-times"></i> Batal'
			}).then((result) => {
				if (result.isConfirmed) {
					Swal.fire({
						title: 'Menghapus...',
						text: 'Sedang memproses',
						icon: 'info',
						allowOutsideClick: false,
						allowEscapeKey: false,
						showConfirmButton: false,
						didOpen: () => {
							Swal.showLoading();
						}
					});

					$.ajax({
						url: url,
						type: 'GET',
						dataType: 'json',
						success: function(res) {
							if (res.status === 'success') {
								if (row.length) {
									row.fadeOut(500, function() {
										$(this).remove();
									});
								}
								Swal.fire({
									title: 'Berhasil!',
									html: res.message,
									icon: 'success',
									showConfirmButton: false,
									timer: 1500
								});
							} else {
								Swal.fire({
									title: 'Gagal',
									text: res.message || 'Terjadi kesalahan saat menghapus.',
									icon: 'error',
									confirmButtonText: 'OK'
								});
							}
						},
						error: function() {
							Swal.fire({
								title: 'Error!',
								text: 'Tidak dapat terhubung ke server. Coba lagi nanti.',
								icon: 'error',
								confirmButtonText: 'OK'
							});
						}
					});
				}
			});
		});
	});
</script>