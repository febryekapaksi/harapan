<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_return thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_return tbody td {
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
		<h3 class="box-title"><i class="fa fa-reply"></i> Daftar Pengembalian Expense (Kelebihan Kasbon)</h3>
		<div>
			<button class="btn btn-default btn-sm" type="button" onclick="reload_table_return()" title="Reload Data">
				<i class="fa fa-refresh"></i> Refresh
			</button>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_return" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="13%">No Dokumen</th>
						<th width="12%">Request By</th>
						<th width="9%">Tanggal</th>
						<th width="14%">Keperluan</th>
						<th width="7%">Tipe</th>
						<th width="11%">Nilai Pengembalian</th>
						<th width="10%">Terbayar</th>
						<th width="10%">Sisa</th>
						<th width="8%">Status</th>
						<th width="9%">Action</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- Modal Review & Input Pengembalian -->
<div class="modal fade" id="Mymodal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg" style="width: 85%; max-width: 1100px;">
		<div class="modal-content" style="border-radius: 6px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
			<div class="modal-header" style="background-color: #3c8dbc; color: #fff; padding: 12px 20px;">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #fff; opacity: 0.9;">&times;</button>
				<h4 class="modal-title" id="modalTitle" style="font-weight: 600;"><i class="fa fa-money"></i> Form Konfirmasi Pengembalian Expense</h4>
			</div>
			<div class="modal-body" id="listexpense" style="padding: 20px; background-color: #f4f6f9;">
				<div class="text-center" style="padding: 30px;">
					<i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
					<p style="margin-top: 10px;">Memuat data expense...</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- DataTables & Scripts -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>

<script type="text/javascript">
	var table_return;

	$(document).ready(function() {
		load_data_return();
	});

	function load_data_return() {
		table_return = $('#mytabledata_return').DataTable({
			"processing": true,
			"serverSide": true,
			"destroy": true,
			"responsive": true,
			"order": [[1, "desc"]],
			"columnDefs": [
				{
					"targets": [0, 10],
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
				url: siteurl + 'request_payment/get_data_list_return',
				type: "POST",
				dataType: "json",
				error: function(xhr, error, thrown) {
					console.log("Error loading list return datatable: ", thrown);
				}
			},
			"columns": [
				{ "data": "no" },
				{ "data": "no_doc" },
				{ "data": "request_by" },
				{ "data": "tgl_doc" },
				{ "data": "keperluan" },
				{ "data": "tipe" },
				{ "data": "nilai_pengembalian" },
				{ "data": "terbayar" },
				{ "data": "sisa" },
				{ "data": "status" },
				{ "data": "action" }
			]
		});
	}

	function reload_table_return() {
		if (table_return) {
			table_return.ajax.reload(null, false);
		}
	}

	function edit(id) {
		$("#listexpense").html('<div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i><p style="margin-top: 10px;">Memuat data expense...</p></div>');
		$("#listexpense").load(siteurl + 'expense/review/' + id, function() {
			$(".divide").divide();
		});
		$("#Mymodal").modal('show');
	}
</script>