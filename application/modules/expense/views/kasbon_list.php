<?php
$ENABLE_ADD     = has_permission('Kasbon.Add');
$ENABLE_MANAGE  = has_permission('Kasbon.Manage');
$ENABLE_VIEW    = has_permission('Kasbon.View');
$ENABLE_DELETE  = has_permission('Kasbon.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
	#mytabledata_kasbon thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_kasbon tbody td {
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
		<h3 class="box-title"><i class="fa fa-money"></i> Data Pengajuan Kasbon</h3>
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Tambah Kasbon
				</button>
			<?php endif; ?>
			<button class="btn btn-default btn-sm" type="button" onclick="reload_table()" title="Reload Data">
				<i class="fa fa-refresh"></i> Refresh
			</button>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_kasbon" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="14%">No Kasbon</th>
						<th width="10%">Tanggal</th>
						<th width="14%">Nama</th>
						<th width="12%">Nominal Kasbon</th>
						<th width="13%">Created By</th>
						<th width="12%">Created Date</th>
						<th width="8%">Status</th>
						<th width="13%">Action</th>
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
	var url_add    = siteurl + 'expense/kasbon_create/';
	var url_edit   = siteurl + 'expense/kasbon_edit/';
	var url_delete = siteurl + 'expense/kasbon_delete/';
	var url_view   = siteurl + 'expense/kasbon_view/';

	var table_kasbon;

	$(document).ready(function() {
		$('.chosen_select').chosen({
			width: '100%'
		});

		load_data_kasbon();
	});

	function load_data_kasbon() {
		table_kasbon = $('#mytabledata_kasbon').DataTable({
			"processing": true,
			"serverSide": true,
			"destroy": true,
			"responsive": true,
			"order": [[1, "desc"]],
			"columnDefs": [
				{
					"targets": [0, 8],
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
				url: siteurl + 'expense/get_data_kasbon',
				type: "POST",
				dataType: "json",
				error: function(xhr, error, thrown) {
					console.log("Error loading kasbon datatable: ", thrown);
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
				{ "data": "status" },
				{ "data": "action" }
			]
		});
	}

	function reload_table() {
		if (table_kasbon) {
			table_kasbon.ajax.reload(null, false);
		}
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>