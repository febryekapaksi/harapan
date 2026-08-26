<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
	#mytabledata thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	#mytabledata tbody td {
		vertical-align: middle;
	}
	.badge {
		font-size: 11px;
		padding: 4px 8px;
		font-weight: 600;
	}
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-check-square-o"></i> Approval Pengembalian Expense</h3>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table id="mytabledata" class="table table-bordered table-striped table-hover" width="100%">
                <thead>
                    <tr>
                        <th width="4%">No.</th>
                        <th width="15%">No Dokumen</th>
                        <th width="20%">Bank Tujuan</th>
                        <th width="12%">Tanggal Transfer</th>
                        <th width="15%">Nilai Transfer</th>
                        <th width="12%">Bukti Transfer</th>
                        <th width="10%">Status</th>
                        <th width="12%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($data_pengembalian as $item) {
                        $status = '<span class="badge bg-yellow">Waiting Approval</span>';
                        if ($item->status == 1) {
                            $status = '<span class="badge bg-green">Approved</span>';
                        } elseif ($item->status == 2) {
                            $status = '<span class="badge bg-red">Rejected</span>';
                        }

                        $bank_info = (!empty($item->nama_bank)) ? $item->transfer_coa_bank . ' - ' . $item->nama_bank : (!empty($item->transfer_coa_bank) ? $item->transfer_coa_bank : '-');
                        $tgl_transfer = (!empty($item->transfer_tanggal) && $item->transfer_tanggal != '0000-00-00') ? date('d M Y', strtotime($item->transfer_tanggal)) : '-';

                        $bukti_btn = '-';
                        if (!empty($item->transfer_file)) {
                            $bukti_btn = '<a href="' . base_url('uploads/expense/' . $item->transfer_file) . '" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-download"></i> Bukti</a>';
                        }

                        echo '<tr>';
                        echo '<td class="text-center">' . $no . '</td>';
                        echo '<td class="text-center"><b>' . $item->no_doc . '</b></td>';
                        echo '<td>' . $bank_info . '</td>';
                        echo '<td class="text-center">' . $tgl_transfer . '</td>';
                        echo '<td class="text-right" style="font-weight: 600;">' . number_format($item->transfer_jumlah, 0, ',', '.') . '</td>';
                        echo '<td class="text-center">' . $bukti_btn . '</td>';
                        echo '<td class="text-center">' . $status . '</td>';
                        echo '<td class="text-center" style="white-space: nowrap;">';
                        if ($item->status != 1) {
                            echo '<button type="button" class="btn btn-sm btn-success approval" data-id="' . $item->id . '" title="Setujui (Approve)"><i class="fa fa-check"></i> Approve</button> ';
                            echo '<button type="button" class="btn btn-sm btn-danger reject" data-id="' . $item->id . '" title="Tolak (Reject)"><i class="fa fa-close"></i> Reject</button>';
                        } else {
                            echo '<span class="text-muted"><i class="fa fa-check-circle text-green"></i> Selesai</span>';
                        }
                        echo '</td>';
                        echo '</tr>';

                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#mytabledata").DataTable({
            "responsive": true,
            "order": [[1, "desc"]]
        });
    });

    $(document).on('click', '.approval', function() {
        var id = $(this).data('id');

        swal({
            title: "Anda Yakin?",
            text: "Pengembalian expense ini akan disetujui!",
            type: "info",
            showCancelButton: true,
            confirmButtonText: "Ya, Setujui!",
            cancelButtonText: "Batal",
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: siteurl + active_controller + 'approve_pengembalian_expense',
                    dataType: "json",
                    type: 'POST',
                    data: { 'id': id },
                    cache: false,
                    success: function(msg) {
                        if (msg.status == '1') {
                            swal({
                                title: "Sukses!",
                                text: "Data pengembalian berhasil disetujui",
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                            window.location.reload();
                        } else {
                            swal({
                                title: "Gagal!",
                                text: "Data pengembalian gagal disetujui!",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(msg) {
                        swal({
                            title: "Gagal!",
                            text: "Terjadi kesalahan AJAX, silakan coba lagi.",
                            type: "error",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '.reject', function() {
        var id = $(this).data('id');

        swal({
            title: "Anda Yakin?",
            text: "Pengembalian expense ini akan ditolak!",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Tolak!",
            cancelButtonText: "Batal",
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function(isConfirm) {
            if (isConfirm) {
                $.ajax({
                    url: siteurl + active_controller + 'reject_pengembalian_expense',
                    dataType: "json",
                    type: 'POST',
                    data: { 'id': id },
                    cache: false,
                    success: function(msg) {
                        if (msg.status == '1') {
                            swal({
                                title: "Sukses!",
                                text: "Data pengembalian berhasil ditolak",
                                type: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                            window.location.reload();
                        } else {
                            swal({
                                title: "Gagal!",
                                text: "Data pengembalian gagal ditolak!",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function(msg) {
                        swal({
                            title: "Gagal!",
                            text: "Terjadi kesalahan AJAX, silakan coba lagi.",
                            type: "error",
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    });
</script>