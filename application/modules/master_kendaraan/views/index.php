<?php
$ENABLE_ADD     = has_permission('Master_Kendaraan.Add');
$ENABLE_MANAGE  = has_permission('Master_Kendaraan.Manage');
$ENABLE_VIEW    = has_permission('Master_Kendaraan.View');
$ENABLE_DELETE  = has_permission('Master_Kendaraan.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<!-- /.box-header -->
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
		<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>

	<!-- /.box-body -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Nopol</th>
					<th>Jenis Kendaraan</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($data)) {
				} else {
					$numb = 0;
					foreach ($data as $record) {
						$numb++; ?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->nopol ?></td>
							<td><?= $record->jenis ?></td>
							<td>
								<?php if ($ENABLE_MANAGE) : ?>
									<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Edit" data-id="<?= $record->id ?>"><i class="fa fa-edit"></i>
									</a>
								<?php endif; ?>

								<?php if ($ENABLE_DELETE) : ?>
									<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?= $record->id ?>"><i class="fa fa-trash"></i>
									</a>
								<?php endif; ?>
							</td>
						</tr>
				<?php }
				}  ?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="head_title"></h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>


<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		var table = $('#example1').DataTable({
			orderCellsTop: true,
			fixedHeader: true
		});
		$("#form-area").hide();
	});

	$(document).on('click', '.add', function() {
		var id = ($(this).data('id') == undefined) ? '' : $(this).data('id')
		let title = (id == '') ? 'Add' : 'Edit'
		$("#head_title").html(`<b>${title} Kendaraan<b>`);
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'add/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				$('.select').select2({
					width: '100%'
				});
				moneyFormat('.moneyFormat')
			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.delete', function(e) {
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
				title: "Are you sure ?",
				text: "Delete this data",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Hapus!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'delete',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(data) {
						if (data.status == '1') {
							swal({
									title: "Success",
									text: data.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: data.pesan,
								type: "error"
							})
						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error Process !",
							type: "error"
						})
					}
				})
			});
	})

	$(document).on('submit', '#data_form', function(e) {
		e.preventDefault()
		var data = $('#data_form').serialize();

		swal({
				title: "Are you sure ?",
				text: "Process this data",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'add',
					dataType: "json",
					data: data,
					success: function(data) {
						if (data.status == '1') {
							swal({
									title: "Success",
									text: data.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: data.pesan,
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Error Process !",
							type: "error"
						})
					}
				})
			});

	})

	function moneyFormat(e) {
		$(e).inputmask({
			alias: "decimal",
			digits: 2,
			radixPoint: ".",
			autoGroup: true,
			placeholder: "0",
			rightAlign: false,
			allowMinus: false,
			integerDigits: 13,
			groupSeparator: ",",
			digitsOptional: false,
			showMaskOnHover: true,
		})
	}
</script>