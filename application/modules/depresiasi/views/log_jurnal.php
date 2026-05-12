<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<div class="box box-primary">
    <div class="box-header with-border">
        <div class="pull-left">
            <a href="<?= site_url('depresiasi') ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    <div class="box-body">
        <table id="tbl_log" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr class="bg-blue">
                    <th class="text-center" width="40">#</th>
                    <th class="text-center">Tanggal Proses</th>
                    <th class="text-center">Bulan</th>
                    <th class="text-center">Tahun</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Diproses Oleh</th>
                    <th class="text-center">Cabang</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js'); ?>"></script>
<script>
    $(document).ready(function() {
        $('#tbl_log').DataTable({
            processing: true,
            serverSide: true,
            stateSave: false,
            destroy: true,
            responsive: true,
            oLanguage: {
                sSearch: "<b>Cari : </b>",
                sLengthMenu: "_MENU_ &nbsp;<b>Data Per Halaman</b>",
                sInfo: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                sZeroRecords: "Tidak ada data",
                sEmptyTable: "Tidak ada data tersedia",
                sLoadingRecords: "Memuat...",
                oPaginate: {
                    sPrevious: "Prev",
                    sNext: "Next"
                }
            },
            aaSorting: [
                [1, "desc"]
            ],
            sPaginationType: "simple_numbers",
            iDisplayLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            ajax: {
                url: base_url + 'index.php/' + active_controller + '/data_log',
                type: "POST",
                cache: false
            }
        });
    });
</script>