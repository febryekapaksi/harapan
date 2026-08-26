<?php
$ENABLE_ADD     = has_permission('Pengajuan_Transportasi_Approval.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Transportasi_Approval.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Transportasi_Approval.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Transportasi_Approval.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_transport_req_fin thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_transport_req_fin tbody td {
		vertical-align: middle;
	}
	.badge {
		font-size: 11px;
		padding: 4px 8px;
		font-weight: 600;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="box box-primary">
	<div class="box-header with-border">
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Approval Request Transportasi (Finance)</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_transport_req_fin" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="20%">No Dokumen</th>
						<th width="15%">Tanggal</th>
						<th width="25%">Nama</th>
						<th width="15%">Status</th>
						<th width="15%">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/transport_req_view/';
	var url_approve = siteurl + 'expense/transport_req_approve/';

	$(document).ready(function() {
		datatables();
	});

	function data_approve(id) {
		swal({
			title: "Anda Yakin?",
			text: "Pengajuan transportasi ini akan disetujui!",
			type: "info",
			showCancelButton: true,
			confirmButtonText: "Ya, Setujui!",
			cancelButtonText: "Batal",
			closeOnConfirm: false,
			closeOnCancel: true
		},
		function(isConfirm) {
			if (isConfirm) {
				$.ajax({
					url: url_approve + id + '/1',
					dataType: "json",
					type: 'POST',
					success: function(msg) {
						if (msg['save'] == '1') {
							swal({
								title: "Sukses!",
								text: "Data berhasil disetujui",
								type: "success",
								timer: 1500,
								showConfirmButton: false
							});
							window.location.reload();
						} else {
							swal({
								title: "Gagal!",
								text: "Data gagal disetujui",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
						}
					},
					error: function(msg) {
						swal({
							title: "Gagal!",
							text: "Terjadi kesalahan AJAX, silakan coba lagi.",
							type: "error",
							timer: 1500,
							showConfirmButton: false
						});
					}
				});
			}
		});
	}

	function datatables() {
		var datatables = $('#mytabledata_transport_req_fin').dataTable({
			serverSide: true,
			processing: true,
			responsive: true,
			destroy: true,
			order: [[1, 'desc']],
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_req_fin_list',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_transport', className: 'text-center' },
				{ data: 'tanggal', className: 'text-center' },
				{ data: 'nama' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center' }
			]
		});
	}
</script>