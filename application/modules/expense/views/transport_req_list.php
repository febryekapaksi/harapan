<?php
$ENABLE_ADD     = has_permission('Pengajuan_Transportasi.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Transportasi.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Transportasi.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Transportasi.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_transport_req thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_transport_req tbody td {
		vertical-align: middle;
	}
	.badge {
		font-size: 11px;
		padding: 4px 8px;
		font-weight: 600;
	}
	.box-header-flex {
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
</style>

<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="box box-primary">
	<div class="box-header with-border box-header-flex">
		<h3 class="box-title"><i class="fa fa-paper-plane"></i> Daftar Request Transportasi</h3>
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Buat Pengajuan
				</button>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_transport_req" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="18%">No Dokumen</th>
						<th width="12%">Tanggal</th>
						<th width="22%">Nama</th>
						<th width="18%">Total Transport</th>
						<th width="12%">Status</th>
						<th width="14%">Action</th>
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
	var url_add = siteurl + 'expense/transport_req_create/';
	var url_edit = siteurl + 'expense/transport_req_edit/';
	var url_delete = siteurl + 'expense/transport_req_delete/';
	var url_view = siteurl + 'expense/transport_req_view/';

	$(document).ready(function() {
		datatables();
	});

	function datatables() {
		var datatables = $('#mytabledata_transport_req').dataTable({
			serverSide: true,
			processing: true,
			responsive: true,
			destroy: true,
			order: [[1, 'desc']],
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_req',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_transport', className: 'text-center' },
				{ data: 'tanggal', className: 'text-center' },
				{ data: 'nama' },
				{ data: 'total', className: 'text-right' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center' }
			]
		});
	}
</script>