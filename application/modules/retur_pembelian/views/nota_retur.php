<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="table-nota">
                <thead>
                    <tr class="bg-blue">
                        <th width="30">#</th>
                        <th>No. Retur</th>
                        <th>Supplier</th>
                        <th>Tgl Retur</th>
                        <th>Total Retur</th>
                        <th>Status Nota</th>
                        <th width="120">Action</th>
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
    $('#table-nota').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        responsive: true,
        aaSorting: [[1, "desc"]],
        iDisplayLength: 10,
        ajax: {
            url: siteurl + 'retur_pembelian/data_nota_retur',
            type: 'POST'
        }
    });
});

function terimaNota(id) {
    swal({
        title: "Konfirmasi Terima Nota Retur?",
        text: "Nota retur dari supplier sudah diterima?",
        type: "info",
        showCancelButton: true,
        confirmButtonText: "Ya, Sudah Diterima",
        cancelButtonText: "Batal",
        closeOnConfirm: false
    }, function(isConfirm) {
        if (!isConfirm) return;
        $.ajax({
            url: siteurl + 'retur_pembelian/terima_nota/' + id,
            type: 'POST',
            data: { tgl_terima: new Date().toISOString().split('T')[0] },
            dataType: 'json',
            success: function(res) {
                if (res.status == 1) {
                    swal("Berhasil", res.pesan, "success");
                    $('#table-nota').DataTable().ajax.reload();
                } else {
                    swal("Gagal", res.pesan, "warning");
                }
            },
            error: function() { swal("Error", "Terjadi kesalahan", "error"); }
        });
    });
}
</script>
