<?php
$ENABLE_VIEW = has_permission('SO_Complete.View');
?>
<style type="text/css">
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
                  <?php if (isset($so['status_so']) && $so['status_so'] == 'CLOSED'): ?>
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
                <th class="text-center">Qty SO</th>
                <th class="text-center">Qty SPK</th>
                <th class="text-center">Sisa</th>
                <th class="text-center">Qty Cancel</th>
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
