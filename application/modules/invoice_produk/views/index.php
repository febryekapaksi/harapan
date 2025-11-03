<?php
$ENABLE_ADD     = has_permission('Invoice_Produk.Add');
$ENABLE_MANAGE  = has_permission('Invoice_Produk.Manage');
$ENABLE_VIEW    = has_permission('Invoice_Produk.View');
$ENABLE_DELETE  = has_permission('Invoice_Produk.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.dataTables.min.css">
<style>
	.font-11 {
		fonts-size: 11px;
	}
</style>
<div class="box box-primary">
	<div class="box-body">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="dp_tab tab_pin active"><a onclick="change_tab('dp')">Invoice DP</a></li>
			<li role="presentation" class="delivery_tab tab_pin"><a onclick="change_tab('delivery')">Invoice Delivery</a></li>
			<!-- <li role="presentation" class="retensi_tab tab_pin"><a onclick="change_tab('retensi')">Invoice Retensi</a></li> -->
			<!-- <li role="presentation" class="jaminan_tab tab_pin"><a onclick="change_tab('jaminan')">Invoice Jaminan</a></li> -->
		</ul>

		<div class="tab_invoice table-responsive" style="margin-top: 1rem;">
			<table class="table table-bordered datatable">
				<thead class="bg-blue">
					<tr>
						<th class="text-center">No. SO</th>
						<th class="text-center">No. Invoice</th>
						<th class="text-center">Customer Name</th>
						<th class="text-center">SO</th>
						<th class="text-center">Invoiced</th>
						<th class="text-center">Outstanding Invoice</th>
						<th class="text-center">Billing Plan Date</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($list_invoice_dp as $item) {

						$total_so = 0;
						$get_total_so = $this->db
							->select('a.harga_satuan, a.diskon_nilai, a.qty, b.ppn')
							->from('tr_sales_order_detail a')
							->join('tr_penawaran b', 'b.no_penawaran = a.no_penawaran', 'left')
							->where('a.no_so', $item->no_so)
							->group_by('a.id_so_detail')
							->get()
							->result();
						foreach ($get_total_so as $item_total_so) {
							// $total_ppn = (float) ((($item_total_so->harga_satuan - $item_total_so->diskon_nilai) * $item_total_so->ppn / 100) * $item_total_so->qty);
							$total_so += ((($item_total_so->harga_satuan - $item_total_so->diskon_nilai) * $item_total_so->qty));
						}

						$get_so = $this->db->get_where('tr_sales_order', ['no_so' => $item->no_so])->row();

						$get_other_cost = $this->db->select('SUM(total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost', ['id_penawaran' => $get_so->no_penawaran])->row();
						if (!empty($get_other_cost)) {
							$nilai_so += $get_other_cost->ttl_other_cost;
						}
						$get_other_item = $this->db->select('SUM(total) as ttl_other_item')->get_where('tr_penawaran_other_item', ['id_penawaran' => $get_so->no_penawaran])->row();
						if (!empty($get_other_item)) {
							$nilai_so += $get_other_item->ttl_other_item;
						}

						$get_penawaran = $this->db->get_where('tr_penawaran', array('no_penawaran' => $get_so->no_penawaran))->row();

						$nilai_ppn = 0;
						if (!empty($get_penawaran)) {
							$nilai_ppn = $get_penawaran->nilai_ppn;
						}

						$total_so = ($total_so + $nilai_ppn);

						$invoiced_value = 0;
						$get_invoiced_value = $this->db
							->select('nilai_invoice, tipe_billing')
							->from('tr_invoice_sales')
							->where('id_so', $item->no_so)
							->get()
							->result();
						// print_r($get_invoiced_value);
						// exit;
						foreach ($get_invoiced_value as $item_invoice) {
							if ($item_invoice->tipe_billing == 'jaminan') {
								// $invoiced_value -= $item_invoice->nilai_invoice;
							} else {
								$invoiced_value += $item_invoice->nilai_invoice;
							}
						}

						$id_invoice = '';
						$get_id_invoice = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id])->row();
						if (!empty($get_id_invoice)) {
							$id_invoice = $get_id_invoice->id_invoice;
						}

						echo '<tr>';
						echo '<td class="text-center">' . $item->no_so . '</td>';
						echo '<td class="text-center">' . $item->id . '</td>';
						echo '<td class="text-center">' . $item->nm_customer . '</td>';
						echo '<td class="text-right">' . number_format($total_so, 2) . '</td>';
						echo '<td class="text-right">' . number_format($invoiced_value, 2) . '</td>';
						echo '<td class="text-right">' . number_format($total_so - $invoiced_value, 2) . '</td>';
						echo '<td class="text-center">' . date('d F Y', strtotime($item->billing_plan_date)) . '</td>';

						$edit = '<button type="button" class="btn btn-sm btn-success create_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="dp" title="Create"><i class="fa fa-check"></i></button>';

						$view = '<button type="button" class="btn btn-sm btn-info view_invoice_modal" data-no_so="' . $item->no_so . '" data-id="' . $item->id . '" data-tipe_billing="dp"><i class="fa fa-eye"></i></button>';

						$print = '<a href="invoice_produk/print_invoice_dp/' . $id_invoice . '" class="btn btn-sm btn-primary print_invoice_dp" target="_blank" data-id_invoice="' . $id_invoice . '" title="Print Invoice"><i class="fa fa-print"></i></a>';

						$check_invoice_dp = $this->db->get_where('tr_invoice_sales', ['id_billing' => $item->id, 'tipe_billing' => 'dp'])->num_rows();
						if ($check_invoice_dp > 0) {
							$button = $view . ' ' . $print;
						} else {
							$button = $edit;
						}

						echo '<td class="text-center">
								' . $button . '
							</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Create Invoice</h4>
			</div>
			<form action="" id="frm-data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Close
					</button>
					<button type="submit" id="saveInvoice" class="btn btn-success"><i class="fa fa-save"></i> Create Invoice</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal modal-default fade" id="dialog-view" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">View Invoice</h4>
			</div>
			<div class="modal-body" id="ModalView2">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close
				</button>
			</div>
		</div>
	</div>
</div>
<!-- /.modal -->

<!-- DataTables -->

<script src="https://cdn.datatables.net/2.1.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>


<!-- page script -->
<script type="text/javascript">
	function loadmod() {
		$('.datatable').dataTable();
		$('.auto_num').autoNumeric('init');
	}
	$(document).ready(function() {
		loadmod();


	});

	function change_tab(tipe, startDate = null, endDate = null) {
		$('.' + tipe + '_tab').addClass('active');
		if (tipe === 'dp') {
			$('.delivery_tab,.retensi_tab,.jaminan_tab').removeClass('active');
		}
		if (tipe === 'delivery') {
			$('.dp_tab,.retensi_tab,.jaminan_tab').removeClass('active');
		}
		if (tipe === 'retensi') {
			$('.dp_tab,.delivery_tab,.jaminan_tab').removeClass('active');
		}
		if (tipe === 'jaminan') {
			$('.delivery_tab,.retensi_tab,.dp_tab').removeClass('active');
		}

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'change_tab',
			data: {
				tipe: tipe,
				start_date: startDate,
				end_date: endDate
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.tab_invoice').html(result.hasil);
				loadmod();
			},
			error: function() {
				swal({
					title: 'Warning !',
					text: 'Please try again later !',
					type: 'error'
				});
			}
		});
	}

	function check_other_cost() {

		var id_billing = $('.id_billing').val();
		var no_so = $('.no_so').val();
		var tipe_billing = $('.tipe_billing').val();

		var nilai_other_cost = 0;
		$('.check_other_cost').each(function() {
			if ($(this).is(':checked')) {
				nilai_other_cost += parseFloat($(this).val());
			}
		});

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'hitung_delivery_w_other_cost',
			data: {
				'id_billing': id_billing,
				'no_so': no_so,
				'tipe_billing': tipe_billing,
				'nilai_other_cost': nilai_other_cost
			},
			cache: false,
			dataType: 'json',
			success: function(result) {
				$('.grand_total_info').html(result.hasil);

				$('.nilai_asli, .nilai_dpp, .nilai_invoice, .grand_total').val(result.total_tagihan);
				$('.nilai_ppn').val(result.nilai_ppn);
				$('.nilai_invoice').val(result.nilai_invoice);
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please, try again later !',
					type: 'error'
				});
			}
		});
	}

	$(document).on('click', '.create_invoice_modal', function() {
		var no_so = $(this).data('no_so');
		var id = $(this).data('id');
		var tipe_billing = $(this).data('tipe_billing');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'create_invoice_modal',
			data: {
				'no_so': no_so,
				'id': id,
				'tipe_billing': tipe_billing
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
				$('.datepicker').datepicker({
					dateFormat: 'yy-mm-dd',
					changeMonth: true,
					changeYear: true,
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		const form = document.getElementById('frm-data');
		const formData = new FormData(form);

		// (optional) tangkap ID biaya lain kalau ada
		const idOtherCost = [];
		document.querySelectorAll('.check_other_cost:checked').forEach(el => {
			idOtherCost.push(el.dataset.id);
		});
		formData.append('id_other_cost[]', idOtherCost);

		swal({
			title: "Warning!",
			text: "Are you sure to create this invoice?",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Yes, Create Invoice",
			confirmButtonColor: "#00a65a",
			cancelButtonColor: "#c9302c"
		}, function(confirm) {
			if (confirm) {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'create_invoice',
					data: formData,
					contentType: false,
					processData: false,
					dataType: 'json',
					success: function(result) {
						if (result.status) {
							swal({
								title: 'Success!',
								text: result.message,
								type: 'success'
							}, function() {
								window.location.href = siteurl + active_controller;
							});
						} else {
							swal('Failed!', 'Invoice has not been created!', 'warning');
						}
					},
					error: function() {
						swal('Error!', 'Please try again later!', 'error');
					}
				});
			}
		});
	});

	$(document).on('click', '.view_invoice_modal', function() {
		var no_so = $(this).data('no_so');
		var id = $(this).data('id');
		var tipe_billing = $(this).data('tipe_billing');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'create_invoice_modal',
			data: {
				'no_so': no_so,
				'id': id,
				'tipe_billing': tipe_billing
			},
			cache: false,
			success: function(result) {
				$('#ModalView2').html(result);
				$('#dialog-view').modal('show');
			}
		});
	});

	$(document).on('click', '.view_invoice_modal_delivery', function() {
		var no_so = $(this).data('no_so');
		var id = $(this).data('id');
		var tipe_billing = $(this).data('tipe_billing');
		var id_invoice = '';
		if (!$.isEmptyObject($(this).data('id_invoice'))) {
			id_invoice = $(this).data('id_invoice');
		}

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'view_invoice_modal',
			data: {
				'no_so': no_so,
				'id': id,
				'tipe_billing': tipe_billing,
				'id_invoice': id_invoice
			},
			cache: false,
			success: function(result) {
				$('#ModalView2').html(result);
				$('#dialog-view').modal('show');
			}
		});
	});

	// Filter khusus tab delivery
	$(document).on('click', '#btnFilterDelivery', function(e) {
		e.preventDefault();
		const s = $('#start_date_delivery').val() || null;
		const e2 = $('#end_date_delivery').val() || null;
		change_tab('delivery', s, e2);
	});

	$(document).on('click', '#btnResetDelivery', function(e) {
		e.preventDefault();
		$('#start_date_delivery').val('');
		$('#end_date_delivery').val('');
		change_tab('delivery', null, null);
	});
</script>