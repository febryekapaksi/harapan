<?php
$ENABLE_ADD     = has_permission('SO_Complete.Add');
$ENABLE_MANAGE  = has_permission('SO_Complete.Manage');
$ENABLE_VIEW    = has_permission('SO_Complete.View');
$ENABLE_DELETE  = has_permission('SO_Complete.Delete');
?>
<style type="text/css">
  thead input {
    width: 100%;
  }

  .col-md-8 table td {
    padding-right: 8px;
    padding-bottom: 4px;
    vertical-align: middle;
  }
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
  <div class="box-header">
    <div class="row">
      <div class="col-md-10">
        <table>
          <tr>
            <td><label class="form-label">Pilih Tanggal SO</label></td>
            <td><input type="date" id="start_date" class="form-control input-sm"></td>
            <td style="padding: 0 8px;"><i class="fa fa-arrow-right"></i></td>
            <td><input type="date" id="end_date" class="form-control input-sm"></td>
            <td style="padding-left: 8px;">
              <button id="btnFilter" class="btn bg-purple btn-sm">
                <i class="fa fa-filter"></i> Filter
              </button>
              <button id="btnReset" class="btn btn-default btn-sm">
                Reset
              </button>
              <a id="btnExport" href="javascript:void(0)" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o"></i> Export Excel
              </a>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <!-- /.box-header -->

  <div class="box-body">
    <div class="callout callout-info">
      <h4><i class="fa fa-info-circle"></i> Informasi</h4>
      <p>Halaman ini menampilkan semua Sales Order yang sudah <strong>SPK Lengkap</strong>. 
        Gunakan tombol <strong>Cancel</strong> untuk membatalkan sisa qty SO yang belum terkirim.</p>
    </div>

    <div class="table-responsive">
      <table id="example1" class="table table-bordered table-striped" width="100%">
        <thead class="bg-blue">
          <tr>
            <th class="text-center" width="3%">#</th>
            <th class="text-center">No. SO</th>
            <th class="text-center">No. Penawaran</th>
            <th class="text-center">Tanggal SO</th>
            <th class="text-center" width="20%">Customer</th>
            <th class="text-center">Sales</th>
            <th class="text-center">Nilai SO</th>
            <th class="text-center">Tipe Quotation</th>
            <th class="text-center" style="min-width: 120px;">Status</th>
            <th class="text-center no-sort" style="min-width: 160px;">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
  <!-- /.box-body -->
</div>

<!-- Modal Cancel SO -->
<div class="modal fade" id="modalCancelSO" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-red">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Cancel Sisa SO</h4>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cancel_no_so">
        <div class="alert alert-warning">
          <i class="fa fa-warning"></i> <strong>Perhatian!</strong><br>
          Proses ini akan membatalkan sisa qty SO yang belum di-SPK dan mengembalikan stock booking ke warehouse.
        </div>
        <div class="form-group">
          <label>No. SO yang akan di-cancel:</label>
          <p class="form-control-static" id="label_cancel_no_so" style="font-weight: bold;"></p>
        </div>
        <div class="form-group">
          <label for="cancel_reason">Alasan Pembatalan <span class="text-red">*</span></label>
          <textarea id="cancel_reason" class="form-control" rows="3" placeholder="Masukkan alasan pembatalan..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-danger" id="btnConfirmCancel">
          <i class="fa fa-times"></i> Ya, Cancel SO
        </button>
      </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script type="text/javascript">
  $(document).ready(function() {
    DataTables();

    // Filter
    $('#btnFilter').on('click', function(e) {
      e.preventDefault();
      if ($.fn.dataTable.isDataTable('#example1')) {
        $('#example1').DataTable().ajax.reload(null, true);
      }
    });

    // Reset
    $('#btnReset').on('click', function(e) {
      e.preventDefault();
      $('#start_date, #end_date').val('');
      if ($.fn.dataTable.isDataTable('#example1')) {
        $('#example1').DataTable().ajax.reload(null, true);
      }
    });

    // Export Excel
    $('#btnExport').on('click', function(e) {
      e.preventDefault();
      var start = $('#start_date').val();
      var end = $('#end_date').val();
      window.location.href = base_url + 'so_complete/export_excel?start_date=' + start + '&end_date=' + end;
    });

    // Cancel SO - open modal
    $(document).on('click', '.cancel-so', function(e) {
      e.preventDefault();
      var no_so = $(this).data('no');
      $('#cancel_no_so').val(no_so);
      $('#label_cancel_no_so').text(no_so);
      $('#cancel_reason').val('');
      $('#modalCancelSO').modal('show');
    });

    // Confirm Cancel SO
    $('#btnConfirmCancel').on('click', function() {
      var no_so = $('#cancel_no_so').val();
      var reason = $('#cancel_reason').val();

      if (!reason || reason.trim() === '') {
        swal("Peringatan", "Alasan pembatalan harus diisi!", "warning");
        return;
      }

      $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

      $.ajax({
        url: base_url + 'so_complete/cancel_so',
        type: 'POST',
        dataType: 'json',
        data: {
          no_so: no_so,
          reason: reason
        },
        success: function(data) {
          $('#btnConfirmCancel').prop('disabled', false).html('<i class="fa fa-times"></i> Ya, Cancel SO');
          $('#modalCancelSO').modal('hide');

          if (data.status == 1) {
            swal({
              title: "Berhasil!",
              text: data.pesan,
              type: "success"
            }, function() {
              window.location.reload();
            });
          } else {
            swal("Gagal!", data.pesan, "error");
          }
        },
        error: function() {
          $('#btnConfirmCancel').prop('disabled', false).html('<i class="fa fa-times"></i> Ya, Cancel SO');
          swal("Error!", "Terjadi kesalahan. Silakan coba lagi.", "error");
        }
      });
    });
  });

  function DataTables() {
    var dataTable = $('#example1').DataTable({
      processing: true,
      serverSide: true,
      stateSave: true,
      autoWidth: false,
      destroy: true,
      responsive: true,
      aaSorting: [[3, "desc"]],
      columnDefs: [{
        targets: 'no-sort',
        orderable: false
      }],
      sPaginationType: "simple_numbers",
      iDisplayLength: 10,
      aLengthMenu: [
        [10, 20, 50, 100, 150],
        [10, 20, 50, 100, 150]
      ],
      ajax: {
        url: base_url + active_controller + 'data_side_so_complete',
        type: "post",
        data: function(d) {
          d.start_date = $('#start_date').val();
          d.end_date = $('#end_date').val();
        },
        cache: false
      }
    });
  }
</script>
