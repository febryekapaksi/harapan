<?php
$ENABLE_ADD     = has_permission('List_Transportasi.Add');
$ENABLE_MANAGE  = has_permission('List_Transportasi.Manage');
$ENABLE_VIEW    = has_permission('List_Transportasi.View');
$ENABLE_DELETE  = has_permission('List_Transportasi.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_transport_req_all thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_transport_req_all tbody td {
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
		<h3 class="box-title"><i class="fa fa-list"></i> Semua Data Pengajuan Transportasi</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_transport_req_all" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="15%">No Dokumen</th>
						<th width="10%">Tanggal</th>
						<th width="15%">Nama</th>
						<th width="15%">Approval Date</th>
						<th width="14%">Total Transport</th>
						<th width="12%">Status</th>
						<th width="10%">Action</th>
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
		DataTables();
	});

	function DataTables() {
		var DataTables = $('#mytabledata_transport_req_all').dataTable({
			serverSide: true,
			processing: true,
			responsive: true,
			destroy: true,
			order: [[1, 'desc']],
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_req_all',
				cache: false,
				dataType: 'json'
			},
			columns: [
				{ data: 'no', className: 'text-center' },
				{ data: 'no_doc', className: 'text-center' },
				{ data: 'tanggal', className: 'text-center' },
				{ data: 'nama' },
				{ data: 'approval_date', className: 'text-center' },
				{ data: 'total_transport', className: 'text-right' },
				{ data: 'status', className: 'text-center' },
				{ data: 'action', className: 'text-center' }
			]
		});
	}
</script>