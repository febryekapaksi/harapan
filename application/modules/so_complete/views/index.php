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
      <p>Halaman ini menampilkan semua Sales Order yang sudah <strong>SPK Lengkap</strong>.</p>
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
            <th class="text-center no-sort" style="min-width: 100px;">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
  <!-- /.box-body -->
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
