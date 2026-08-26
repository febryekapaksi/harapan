<?php
$ENABLE_ADD     = has_permission('Kasbon_Approval.Add');
$ENABLE_MANAGE  = has_permission('Kasbon_Approval.Manage');
$ENABLE_VIEW    = has_permission('Kasbon_Approval.View');
$ENABLE_DELETE  = has_permission('Kasbon_Approval.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata_kasbon_fin thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata_kasbon_fin tbody td {
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
		<h3 class="box-title"><i class="fa fa-check-square-o"></i> Approval Kasbon (Finance)</h3>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata_kasbon_fin" class="table table-bordered table-striped table-hover" width="100%">
				<thead>
					<tr>
						<th width="4%">#</th>
						<th width="18%">No Kasbon</th>
						<th width="12%">Tanggal</th>
						<th width="22%">Nama</th>
						<th width="14%">Nominal Kasbon</th>
						<th width="12%">Status</th>
						<th width="14%">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$nmuser = $record->nmuser;
							$check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $record->no_doc])->result();
							if (count($check_detail)) {
								if ($record->tipe_pr == 'pr departemen') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('rutin_non_planning_header a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();
									if (!empty($get_single_detail)) $nmuser = $get_single_detail->nm_lengkap;
								}

								if ($record->tipe_pr == 'pr stok') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('material_planning_base_on_produksi a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();
									if (!empty($get_single_detail)) $nmuser = $get_single_detail->nm_lengkap;
								}

								if ($record->tipe_pr == 'pr asset') {
									$this->db->select('b.nm_lengkap');
									$this->db->from('tran_pr_header a');
									$this->db->join('users b', 'b.id_user = a.created_by');
									$this->db->where('a.no_pr', $record->id_pr);
									$get_single_detail = $this->db->get()->row();
									if (!empty($get_single_detail)) $nmuser = $get_single_detail->nm_lengkap;
								}
							}
							$numb++; 
							$tgl_doc = (!empty($record->tgl_doc) && $record->tgl_doc != '0000-00-00') ? date('d M Y', strtotime($record->tgl_doc)) : '-';
							?>
							<tr>
								<td class="text-center"><?= $numb; ?></td>
								<td class="text-center"><b><?= $record->no_doc ?></b></td>
								<td class="text-center"><?= $tgl_doc ?></td>
								<td><?= $nmuser ?></td>
								<td class="text-right" style="font-weight: 600;"><?= isset($record->jumlah_kasbon) ? number_format($record->jumlah_kasbon, 0, ',', '.') : '-' ?></td>
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
										<a class="btn btn-default btn-xs" href="<?= base_url('expense/kasbon_print/' . $record->id) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a>
										<a class="btn btn-info btn-xs" href="<?= base_url('expense/kasbon_view/' . $record->id . '/_fin') ?>" title="Lihat Detail"><i class="fa fa-eye"></i> Detail</a>
									<?php endif;
									if ($ENABLE_MANAGE) :
										if ($record->status == 0) { ?>
											<a class="btn btn-success btn-xs" href="<?= base_url('expense/kasbon_edit/' . $record->id . '/_fin') ?>" title="Approve"><i class="fa fa-check-square-o"></i> Approve</a>
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

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
		$("#mytabledata_kasbon_fin").DataTable({
			"responsive": true,
			"order": [[1, "desc"]]
		});
	});
</script>