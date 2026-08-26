<?php
$ENABLE_ADD     = has_permission('Kasbon_List.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_List.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_List.View');
$ENABLE_DELETE  = has_permission('Kasbon_List.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	#mytabledata_kasbon_all thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_kasbon_all tbody td {
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

<div class="box box-primary">
	<div class="box-header with-border box-header-flex">
		<h3 class="box-title"><i class="fa fa-list-alt"></i> Kasbon List (Semua Data)</h3>
		<div>
			<button class="btn btn-default btn-sm" type="button" onclick="reload_table_all()" title="Reload Data">
				<i class="fa fa-refresh"></i> Refresh
			</button>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_kasbon_all" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="14%">No Kasbon</th>
						<th width="9%">Tanggal</th>
						<th width="13%">Nama</th>
						<th width="11%">Nominal Kasbon</th>
						<th width="12%">Created By</th>
						<th width="11%">Created Date</th>
						<th width="11%">Approval Date</th>
						<th width="7%">Status</th>
						<th width="8%">Action</th>
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

<!-- DataTables & Select -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/kasbon_view/';
	var table_kasbon_all;

	$(document).ready(function() {
		load_data_kasbon_all();
	});

	function load_data_kasbon_all() {
		table_kasbon_all = $('#mytabledata_kasbon_all').DataTable({
			"processing": true,
			"serverSide": true,
			"destroy": true,
			"responsive": true,
			"order": [[1, "desc"]],
			"columnDefs": [
				{
					"targets": [0, 9],
					"orderable": false,
					"searchable": false
				}
			],
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 25, 50, 100],
				[10, 25, 50, 100]
			],
			"ajax": {
				url: siteurl + 'expense/get_data_kasbon_all',
				type: "POST",
				dataType: "json",
				error: function(xhr, error, thrown) {
					console.log("Error loading kasbon all datatable: ", thrown);
				}
			},
			"columns": [
				{ "data": "no" },
				{ "data": "no_doc" },
				{ "data": "tgl_doc" },
				{ "data": "nama" },
				{ "data": "nominal_kasbon" },
				{ "data": "created_by" },
				{ "data": "created_date" },
				{ "data": "approval_date" },
				{ "data": "status" },
				{ "data": "action" }
			]
		});
	}

	function reload_table_all() {
		if (table_kasbon_all) {
			table_kasbon_all.ajax.reload(null, false);
		}
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>