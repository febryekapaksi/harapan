<?php
$ENABLE_MANAGE = has_permission('SO_Complete.Manage');
?>
<style type="text/css">
  .info-box-custom {
    padding: 10px 15px;
    background: #f9f9f9;
    border-left: 4px solid #3c8dbc;
    margin-bottom: 10px;
  }

  .table-detail th {
    background: #f5f5f5;
    width: 180px;
  }

  .badge-status {
    font-size: 12px;
    padding: 4px 10px;
  }
</style>

<div class="row">
  <!-- Header Info -->
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-file-text"></i> Informasi Sales Order</h3>
        <div class="box-tools">
          <a href="<?= base_url('so_complete') ?>" class="btn btn-sm btn-default">
            <i class="fa fa-arrow-left"></i> Kembali
          </a>
          <a target="_blank" href="<?= base_url("sales_order/print_so/{$so['no_so']}") ?>" class="btn btn-sm btn-warning">
            <i class="fa fa-print"></i> Print SO
          </a>
          <?php if ($ENABLE_MANAGE && $so['status_so'] != 'CLOSED'): ?>
            <button class="btn btn-sm btn-danger" id="btnCancelSO" data-no="<?= $so['no_so'] ?>">
              <i class="fa fa-times"></i> Cancel Sisa SO
            </button>
          <?php endif; ?>
        </div>
      </div>
      <div class="box-body">
        <div class="row">
          <div class="col-md-6">
            <table class="table table-bordered table-detail">
              <tr>
                <th>No. SO</th>
                <td><strong><?= $so['no_so'] ?></strong></td>
              </tr>
              <tr>
                <th>No. Penawaran</th>
                <td><?= $so['id_penawaran'] ?></td>
              </tr>
              <tr>
                <th>Tanggal SO</th>
                <td><?= date('d/M/Y', strtotime($so['tgl_so'])) ?></td>
              </tr>
              <tr>
                <th>Customer</th>
                <td><?= strtoupper($so['name_customer']) ?></td>
              </tr>
              <tr>
                <th>Sales</th>
                <td><?= ucfirst($so['sales']) ?></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-bordered table-detail">
              <tr>
                <th>Nilai SO</th>
                <td><strong>Rp <?= number_format($so['nilai_so'], 2) ?></strong></td>
              </tr>
              <tr>
                <th>Tipe Quotation</th>
                <td>
                  <?php if ($so['tipe_penawaran'] == 'Dropship'): ?>
                    <span class="badge bg-blue">Dropship</span>
                  <?php else: ?>
                    <span class="badge bg-aqua">Standard</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>Status SPK</th>
                <td><span class="badge bg-green badge-status">SPK Lengkap</span></td>
              </tr>
              <tr>
                <th>Status SO</th>
                <td>
                  <?php if ($so['status_so'] == 'CLOSED'): ?>
                    <span class="badge bg-red badge-status">Closed</span>
                  <?php else: ?>
                    <span class="badge bg-green badge-status">Active</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <th>Alasan Cancel</th>
                <td><?= !empty($so['cancel_reason']) ? $so['cancel_reason'] : '-' ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Item SO -->
  <div class="col-md-12">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-list"></i> Detail Item SO</h3>
      </div>
      <div class="box-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="bg-light-blue">
              <tr>
                <th class="text-center" width="3%">#</th>
                <th class="text-center">Produk</th>
                <th class="text-center">Qty Order</th>
                <th class="text-center">Qty SPK</th>
                <th class="text-center">Sisa Belum SPK</th>
                <th class="text-center">Qty Cancelled</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              $total_order = 0;
              $total_spk = 0;
              $total_sisa = 0;
              $total_cancel = 0;
              foreach ($so_detail as $item):
                $qty_order     = floatval($item['qty_order']);
                $qty_spk       = floatval($item['qty_spk']);
                $sisa          = floatval($item['sisa_belum_spk']);
                $qty_cancelled = isset($item['qty_cancelled']) ? floatval($item['qty_cancelled']) : 0;
                $total_order   += $qty_order;
                $total_spk     += $qty_spk;
                $total_sisa    += $sisa;
                $total_cancel  += $qty_cancelled;
              ?>
                <tr>
                  <td class="text-center"><?= $no++ ?></td>
                  <td><?= $item['product'] ?></td>
                  <td class="text-center"><?= number_format($qty_order, 0) ?></td>
                  <td class="text-center"><?= number_format($qty_spk, 0) ?></td>
                  <td class="text-center">
                    <?php if ($sisa > 0): ?>
                      <span class="text-orange"><strong><?= number_format($sisa, 0) ?></strong></span>
                    <?php else: ?>
                      <span class="text-green">0</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($qty_cancelled > 0): ?>
                      <span class="text-red"><strong><?= number_format($qty_cancelled, 0) ?></strong></span>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <?php if ($qty_cancelled > 0): ?>
                      <span class="badge bg-red">Cancelled</span>
                    <?php elseif ($sisa == 0): ?>
                      <span class="badge bg-green">Complete</span>
                    <?php else: ?>
                      <span class="badge bg-yellow">Partial</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr class="bg-gray">
                <th colspan="2" class="text-center">TOTAL</th>
                <th class="text-center"><?= number_format($total_order, 0) ?></th>
                <th class="text-center"><?= number_format($total_spk, 0) ?></th>
                <th class="text-center"><?= number_format($total_sisa, 0) ?></th>
                <th class="text-center"><?= number_format($total_cancel, 0) ?></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Daftar SPK Terkait -->
  <div class="col-md-12">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-truck"></i> Daftar SPK Delivery</h3>
      </div>
      <div class="box-body">
        <?php if (!empty($spk_list)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th class="text-center" width="3%">#</th>
                  <th class="text-center">No. SPK Delivery</th>
                  <th class="text-center">Tanggal SPK</th>
                  <th class="text-center">Pengiriman</th>
                  <th class="text-center">Dibuat</th>
                  <?php if ($ENABLE_MANAGE): ?>
                    <th class="text-center">Action</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1;
                foreach ($spk_list as $spk): ?>
                  <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><strong><?= $spk['no_delivery'] ?></strong></td>
                    <td class="text-center"><?= !empty($spk['tanggal_spk']) ? date('d/M/Y', strtotime($spk['tanggal_spk'])) : '-' ?></td>
                    <td class="text-center"><?= $spk['pengiriman'] ?></td>
                    <td class="text-center"><?= date('d/M/Y H:i', strtotime($spk['created_date'])) ?></td>
                    <?php if ($ENABLE_MANAGE): ?>
                      <td class="text-center">
                        <button class="btn btn-xs btn-danger cancel-spk-btn" data-id="<?= $spk['no_delivery'] ?>" title="Cancel SPK">
                          <i class="fa fa-times"></i> Cancel SPK
                        </button>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted">Tidak ada data SPK Delivery.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
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
          <label>No. SO:</label>
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

<script type="text/javascript">
  $(document).ready(function() {
    // Cancel SO dari halaman detail
    $('#btnCancelSO').on('click', function(e) {
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

    // Cancel SPK individual
    $(document).on('click', '.cancel-spk-btn', function(e) {
      e.preventDefault();
      var no_delivery = $(this).data('id');

      swal({
        title: "Cancel SPK?",
        text: "SPK " + no_delivery + " akan dibatalkan. SO bisa di-SPK ulang setelah ini.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Ya, Cancel SPK",
        cancelButtonText: "Batal",
        closeOnConfirm: false
      }, function() {
        $.ajax({
          url: base_url + 'so_complete/cancel_spk',
          type: 'POST',
          dataType: 'json',
          data: {
            no_delivery: no_delivery
          },
          success: function(data) {
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
            swal("Error!", "Terjadi kesalahan. Silakan coba lagi.", "error");
          }
        });
      });
    });
  });
</script>
