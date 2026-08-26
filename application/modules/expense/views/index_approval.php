<?php
$ENABLE_ADD     = has_permission('Expense_Approval.Add');
$ENABLE_MANAGE  = has_permission('Expense_Approval.Manage');
$ENABLE_VIEW    = has_permission('Expense_Approval.View');
$ENABLE_DELETE  = has_permission('Expense_Approval.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_approval thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_approval tbody td {
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
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Daftar Pengajuan Expense Menunggu Approval</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_approval" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="15%">No Dokumen</th>
						<th width="10%">Tanggal</th>
						<th width="15%">Nama</th>
						<th width="20%">Keterangan</th>
						<th width="14%">Nominal</th>
						<th width="10%">Status</th>
						<th width="12%">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($data)) {
						$numb = 0;
						foreach ($data as $record) {
							$numb++; 
							$tgl_doc = (!empty($record->tgl_doc) && $record->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($record->tgl_doc)) : '-';
							?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td class="text-center"><b><?= $record->no_doc ?></b></td>
								<td class="text-center"><?= $tgl_doc ?></td>
								<td><?= $record->nmuser ?: $record->nama ?></td>
								<td><?= $record->informasi ?></td>
								<td class="text-right" style="font-weight: 600;"><?= number_format($record->nominal, 0, ',', '.') ?></td>
								<td class="text-center">
									<?php
									$st_label = isset($status[$record->status]) ? $status[$record->status] : 'Menunggu Approval';
									echo '<span class="badge bg-yellow">' . $st_label . '</span>';
									?>
								</td>
								<td class="text-center" style="white-space: nowrap;">
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-info btn-xs" href="javascript:void(0)" title="Lihat Detail" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i> Detail</a>
									<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0) { ?>
											<a class="btn btn-success btn-xs" href="javascript:void(0)" title="Proses Approval" onclick="data_approve('<?= $record->id ?>')"><i class="fa fa-check-square-o"></i> Approve</a>
									<?php }
									endif;
									?>
								</td>
							</tr>
					<?php
						}
					}  ?>
				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>

<script type="text/javascript">
	var url_edit = siteurl + 'expense/edit/';
	var url_view = siteurl + 'expense/view/';
	var url_approval = siteurl + 'expense/approval/';

	function data_approve(id) {
		if (id != "") {
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(url_approval + id);
		}
	}

	$(document).ready(function() {
		$("#mytabledata_approval").DataTable({
			"responsive": true,
			"order": [[1, "desc"]]
		});
	});
</script>