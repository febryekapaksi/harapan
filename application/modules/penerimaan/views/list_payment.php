<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <div class="box-header">
        <div class="row">
            <div class="col-sm-12">
                <a href="<?= site_url('penerimaan/add') ?>" class='btn btn-success'><i class="fa fa-plus"></i>&emsp; Buat Penerimaan</a>
            </div>
        </div>
        <hr>
        <div class="row" style="align-items:center;">
            <div class="col-sm-2" style="display:flex;align-items:center;">
                <label class="form-label" style="margin:0;">Pilih Tanggal Penerimaan</label>
            </div>
            <div class="col-sm-2">
                <input type="date" id="start_date" class="form-control input-sm">
            </div>
            <div class="col-sm-1 text-center" style="display:flex;align-items:center;justify-content:center;">
                <i class="fa fa-arrow-right"></i>
            </div>
            <div class="col-sm-2">
                <input type="date" id="end_date" class="form-control input-sm">
            </div>
            <div class="col-sm-3">
                <button id="btnFilter" class="btn bg-purple btn-sm">
                    <i class="fa fa-filter"></i> Filter
                </button>
                <button id="btnReset" class="btn btn-default btn-sm">
                    Reset
                </button>
                <a id="btnExport" href="javascript:void(0)" class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
    <div class="box-body table-responsive">
        <table class="table table-bordered table-striped" id="example1" width='100%'>
            <thead>
                <tr class='bg-blue'>
                    <th class="text-center" width='4%'>No</th>
                    <th width='7%'>Tgl Penerimaan</th>
                    <th width='7%'>Kode Penerimaan</th>
                    <th width='7%'>Nama Customer</th>
                    <th width='18%'>Keterangan</th>
                    <th style="min-width: 100%;">No Invoice</th>
                    <th class="text-right" width='7%'>Total Invoice</th>
                    <!-- <th class="text-right">PPH</th> -->
                    <th class="text-right">Biaya Admin</th>
                    <th class="text-right" width='7%'>Total Penerimaan <br> (IDR)</th>
                    <th class="text-center" width='7%'>Option</th>
                </tr>
            </thead>
        </table>
        <tbody></tbody>
    </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        DataTables();

        $('#btnFilter').on('click', function(e) {
            e.preventDefault();
            if ($.fn.dataTable.isDataTable('#example1')) {
                $('#example1').DataTable().ajax.reload(null, true);
            }
        });

        $('#btnReset').on('click', function(e) {
            e.preventDefault();
            $('#start_date, #end_date').val('');
            if ($.fn.dataTable.isDataTable('#example1')) {
                $('#example1').DataTable().ajax.reload(null, true);
            }
        });

        $('#btnExport').on('click', function(e) {
            e.preventDefault();
            var start = $('#start_date').val();
            var end = $('#end_date').val();
            window.location.href = base_url + 'penerimaan/export_excel?start_date=' + start + '&end_date=' + end;
        });
    });

    function DataTables(status = null) {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "autoWidth": false,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [1, "asc"]
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
                url: base_url + active_controller + 'data_side_penerimaan',
                type: "post",
                data: function(d) {
                    d.status = status;
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            }
        });

        // Event handler untuk tombol Batalkan
        $('#example1').on('click', '.btn-batalkan', function() {
            var kd = $(this).data('kd');
            if (confirm('Apakah Anda yakin ingin MEMBATALKAN penerimaan ' + kd + '?\n\nData akan dipindahkan ke tabel delete dan jurnal akan dibalik.')) {
                $.ajax({
                    url: base_url + 'penerimaan/batalkan_penerimaan',
                    type: 'POST',
                    data: {
                        kd_pembayaran: kd
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        $('.btn-batalkan[data-kd="' + kd + '"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    success: function(res) {
                        if (res.status == 1) {
                            alert(res.message);
                            $('#example1').DataTable().ajax.reload(null, false);
                        } else {
                            alert('Gagal: ' + res.message);
                            $('.btn-batalkan[data-kd="' + kd + '"]').prop('disabled', false).html('<i class="fa fa-times"></i>');
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan server.');
                        $('.btn-batalkan[data-kd="' + kd + '"]').prop('disabled', false).html('<i class="fa fa-times"></i>');
                    }
                });
            }
        });
    }
</script>