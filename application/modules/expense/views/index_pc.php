<?php
$ENABLE_ADD     = has_permission('Expense_Petty_Cash.Add');
$ENABLE_MANAGE  = has_permission('Expense_Petty_Cash.Manage');
$ENABLE_VIEW    = has_permission('Expense_Petty_Cash.View');
$ENABLE_DELETE  = has_permission('Expense_Petty_Cash.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_pc thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_pc tbody td {
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
		<h3 class="box-title"><i class="fa fa-money"></i> Daftar Petty Cash</h3>
		<div>
			<?php if ($ENABLE_ADD) : ?>
				<button class="btn btn-success btn-sm" type="button" onclick="data_add()">
					<i class="fa fa-plus">&nbsp;</i> Tambah Petty Cash
				</button>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_pc" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="15%">No Dokumen</th>
						<th width="10%">Tanggal</th>
						<th width="15%">Nama</th>
						<th width="15%">Approval</th>
						<th width="18%">Keterangan</th>
						<th width="12%">Nominal</th>
						<th width="10%">Status</th>
						<th width="11%">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$numb++; 
							$tgl_doc = (!empty($record->tgl_doc) && $record->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($record->tgl_doc)) : '-';
							?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td class="text-center"><b><?= $record->no_doc ?></b></td>
								<td class="text-center"><?= $tgl_doc ?></td>
								<td><?= $record->nmuser ?: $record->nama ?></td>
								<td><?= $record->nmapproval ?: '-' ?></td>
								<td><?= $record->informasi ?></td>
								<td class="text-right" style="font-weight: 600;"><?= number_format($record->nominal, 0, ',', '.') ?></td>
								<td class="text-center">
									<?php
									$st_label = isset($status[$record->status]) ? $status[$record->status] : $record->status;
									if ($record->status == 0) {
										echo '<span class="badge bg-yellow">' . $st_label . '</span>';
									} elseif ($record->status == 1 || $record->status == 2) {
										echo '<span class="badge bg-green">' . $st_label . '</span>';
									} elseif ($record->status == 9) {
										echo '<span class="badge bg-red">' . $st_label . '</span>';
									} else {
										echo '<span class="badge bg-gray">' . $st_label . '</span>';
									}
									?>
								</td>
								<td class="text-center" style="white-space: nowrap;">
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-default btn-xs" href="<?= base_url('expense/expense_pettycash_print/' . $record->id) ?>" target="expense_print" title="Print"><i class="fa fa-print"></i></a>
										<a class="btn btn-info btn-xs" href="javascript:void(0)" title="Lihat Detail" onclick="data_view('<?= $record->id ?>')"><i class="fa fa-eye"></i></a>
									<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-warning btn-xs" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->id ?>')"><i class="fa fa-edit"></i></a>
										<?php }
									endif;
									if ($ENABLE_DELETE) :
										if ($record->status == 0 || $record->status == 9) { ?>
											<a class="btn btn-danger btn-xs" href="javascript:void(0)" title="Hapus" onclick="data_delete('<?= $record->id ?>')"><i class="fa fa-trash"></i></a>
									<?php }
									endif; ?>
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

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl + 'expense/create_pc/';
	var url_edit = siteurl + 'expense/edit_pc/';
	var url_delete = siteurl + 'expense/delete/';
	var url_view = siteurl + 'expense/view_pc/';

	$(document).ready(function() {
		$("#mytabledata_pc").DataTable({
			"responsive": true,
			"order": [[1, "desc"]]
		});
	});
</script>