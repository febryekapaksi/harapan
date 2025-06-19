<?php
$ENABLE_ADD     = has_permission('Sales_order.Add');
$ENABLE_MANAGE  = has_permission('Sales_order.Manage');
$ENABLE_VIEW    = has_permission('Sales_order.View');
$ENABLE_DELETE  = has_permission('Sales_order.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
	<div class="box-header">

	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped">
				<thead class="bg-blue">
					<tr>
						<th class="text-center"> #</th>
						<th class="text-center">SO No.</th>
						<th class="text-center">Quotation No.</th>
						<th class="text-center" width="30%">Customer</th>
						<th class="text-center">Marketing</th>
						<th class="text-center">Nilai Penawaran</th>
						<th class="text-center">Nilai SO</th>
						<th class="text-center">Rev</th>
						<th class="text-center">Tipe Quot</th>
						<th class="text-center" style="min-width: 150px;">Status</th>
						<th class="text-center" style="min-width: 100px;">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:80%; '>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('.select2').select2({
			width: '100%'
		});

		$(document).on('change', '#status', function() {
			var status = $('#status').val()
			DataTables(status);
		})

		var status = $('#status').val()
		DataTables(status);

		$(document).on('click', '.deal-so', function(e) {
			e.preventDefault();

			var no_so = $(this).data('no');
			var actionUrl = base_url + active_controller + '/deal_so';

			swal({
				title: "Are you sure?",
				text: "You will not be able to process this data again!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			}, function(isConfirm) {
				if (isConfirm) {
					var payload = {
						no_so: no_so
					};


					$.ajax({
						url: actionUrl,
						type: "POST",
						data: payload,
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 3000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								setTimeout(() => {
									window.location.reload();
								}, 3000);
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 3000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						},
						error: function() {
							swal({
								title: "Error Message!",
								text: 'An Error Occurred During Process. Please try again.',
								type: "warning",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be processed again :)", "error");
				}
			});
		});

	});

	function DataTables(status = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": false,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + 'data_side_sales_order',
				type: "post",
				data: function(d) {
					d.status = status
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>