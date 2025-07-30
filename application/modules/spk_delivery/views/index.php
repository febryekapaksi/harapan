<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
	<!-- <div class="box-header"> -->
	<!-- <a class="btn btn-primary" style='float:left;' href="<?= base_url('spk_delivery/add') ?>">Create SPK</a>
		<br> -->
	<!-- <div class="form-group row">
			<div class="col-md-10"></div>
			<div class="col-md-2">
				<select name='sales_order' id='sales_order' class='form-control input-sm chosen-select'>
					<option value='0'>All Sales Order</option>
					<?php
					foreach ($listSO as $key => $value) {
						echo "<option value='" . $value['no_so'] . "'>" . $value['no_so'] . "</option>";
					}
					?>
				</select>
			</div>
		</div> -->
	<!-- </div> -->
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="example1" class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr class="bg-blue">
						<th class='text-center'>#</th>
						<th class='text-center'>No SPK Delivery</th>
						<th class='text-center'>No Sales Order</th>
						<th class='text-center'>Customer</th>
						<th class='text-center'>Pengiriman</th>
						<th class='text-center'>Tanggal SPK</th>
						<th class='text-center no-sort'>Status</th>
						<th class='text-center no-sort'>Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- Modal -->
<div class="modal fade" id="modalSpkDet" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-archive"></span>&nbsp;Detail SPK Delivery | <button class="btn btn-sm btn-primary float-right ml-2" id="printDetailSpk"><i class="fa fa-print"></i> Cetak</button></h4>

			</div>
			<div id="print-area-loading">
				<div class="modal-body">
					<table class="table table-bordered" id="tabelModal" style="width: 100%;">
						<thead>
							<tr>
								<th>No SPK</th>
								<th>No SO</th>
								<th>Customer</th>
								<th>Produk</th>
								<th>Qty</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		var sales_order = $("#sales_order").val();
		DataTables(sales_order);

		$(document).on('click', '.view-spk', function() {
			const no_delivery = $(this).data('id');

			$.ajax({
				url: siteurl + 'spk_delivery/get_spk_detail',
				type: 'GET',
				data: {
					no_delivery
				},
				success: function(res) {
					const data = JSON.parse(res);
					let html = '';

					data.forEach((item) => {
						html += `
                    <tr>
                        <td>${item.no_delivery}</td>
                        <td>${item.no_so}</td>
                        <td>${item.customer}</td>
                        <td>${item.product}</td>
                        <td class="text-center">${item.qty_spk}</td>
                    </tr>
                `;
					});

					$('#modalSpkDet').modal('show');
					$('#tabelModal tbody').html(html);
				}
			});
		});

		$(document).on('click', '#printDetailSpk', function() {
			const printContents = document.getElementById('print-area-loading').innerHTML;
			const originalContents = document.body.innerHTML;

			document.body.innerHTML = printContents;
			window.print();
			document.body.innerHTML = originalContents;
			location.reload(); // agar modal tertutup kembali
		});
	});


	function DataTables(sales_order = null) {
		var dataTable = $('#example1').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": false,
			"destroy": true,
			"searching": true,
			"responsive": true,
			"aaSorting": [
				[1, "desc"]
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
				url: siteurl + active_controller + 'data_side_spk_deliv',
				type: "post",
				data: function(d) {
					d.sales_order = sales_order
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