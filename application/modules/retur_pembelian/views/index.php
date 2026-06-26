<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <a href="<?= site_url('retur_pembelian/add') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Add New
        </a>
    </div>
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table-retur">
                <thead>
                    <tr class="bg-blue">
                        <th width="30">#</th>
                        <th>No. Retur</th>
                        <th>No. Invoice</th>
                        <th>Supplier</th>
                        <th>Tgl Retur</th>
                        <th>Total Retur</th>
                        <th>Settlement</th>
                        <th>Sisa Retur</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
$(document).ready(function() {
    initDataTable();
});

function initDataTable() {
    $('#table-retur').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10,
        aLengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: siteurl + 'retur_pembelian/data',
            type: 'POST',
            error: function() {
                swal("Error", "Gagal memuat data", "error");
            }
        }
    });
}

function ajukanRetur(id) {
    swal({
        title: "Ajukan Retur?",
        text: "Setelah diajukan, data tidak bisa diedit dan jurnal akan terbentuk otomatis.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-success",
        confirmButtonText: "Ya, Ajukan!",
        cancelButtonText: "Batal",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: siteurl + 'retur_pembelian/ajukan/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal("Berhasil", res.pesan, "success");
                    $('#table-retur').DataTable().ajax.reload();
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() {
                swal("Error", "Terjadi kesalahan", "error");
            }
        });
    });
}

function cancelRetur(id) {
    swal({
        title: "Cancel Retur?",
        text: "Apakah Anda yakin ingin membatalkan retur ini?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Ya, Cancel!",
        cancelButtonText: "Tidak",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: siteurl + 'retur_pembelian/cancel/' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal("Berhasil", res.pesan, "success");
                    $('#table-retur').DataTable().ajax.reload();
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() {
                swal("Error", "Terjadi kesalahan", "error");
            }
        });
    });
}
</script>
