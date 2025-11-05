<?php
$id_so = $results['no_so'];
$id_billing = $results['id_billing'];
$tipe_billing = $results['tipe_billing'];
?>
<!-- <div class="col-md-6">
    <div class="form-group">
        <label for="">Tax Invoice No.</label>
        <input type="text" name="tax_invoice_no" id="" class="form-control form-control-sm tax_invoice_no" required>
    </div>
</div> -->

<!-- <h5>Other Cost</h5> -->
<!-- <table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center bg-primary">No</th>
            <th class="text-center bg-primary">Item</th>
            <th class="text-center bg-primary">Total</th>
            <th class="text-center bg-primary">Checklist</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $ttl_other_cost = 0;
        foreach ($results['list_other_cost'] as $item_other_cost) {

            $check_used_other_cost = $this->db->get_where('tr_used_invoice_sales_other_cost', ['id_other_cost' => $item_other_cost->id])->num_rows();

            if (!isset($results['view'])) {
                if ($check_used_other_cost < 1) {
                    echo '<tr>';
                    echo '<td class="text-center">' . $no . '</td>';
                    echo '<td class="text-center">' . $item_other_cost->keterangan . '</td>';
                    echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($item_other_cost->total_nilai, 2) . '</td>';
                    echo '<td class="text-center">
                        <input type="checkbox" name="" class="check_other_cost other_cost_' . $item_other_cost->id . '" value="' . $item_other_cost->total_nilai . '" onclick="check_other_cost()" data-id="' . $item_other_cost->id . '">
                    </td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr>';
                echo '<td class="text-center">' . $no . '</td>';
                echo '<td class="text-center">' . $item_other_cost->keterangan . '</td>';
                echo '<td class="text-right">(' . $results['currency'] . ') ' . number_format($item_other_cost->total_nilai, 2) . '</td>';
                echo '<td class="text-center">
                </td>';
                echo '</tr>';

                $ttl_other_cost += $item_other_cost->total_nilai;
            }
            $no++;
        }
        ?>
    </tbody>
</table> -->

<h5>Detail Invoice</h5>
<!-- <span class="pull-left" style="max-width:250px">
    <div class="input-group">
        <span class="input-group-addon">Pilih Tanggal</span>
        <input type="date" name="tgl_invoice" class="form-control">
    </div>
    <br>
</span> -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center bg-primary">No.</th>
            <th class="text-center bg-primary">Nama Produk</th>
            <th class="text-center bg-primary">Qty Order</th>
            <th class="text-center bg-primary">Qty Delivery</th>
            <th class="text-center bg-primary">Price/Unit</th>
            <th class="text-center bg-primary">Disc</th>
            <th class="text-center bg-primary">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $subtotal = 0;
        $grand_total_beli = 0;
        foreach ($results['list_so_detail'] as $item_detail) {

            $nilai_disc = (float) $item_detail->diskon_persen;
            // $total_harga = round((($item_detail->price_list * $item_detail->qty_delivery) * (1 + ($nilai_disc / 100))), -2);
            $total_harga = round(($item_detail->harga_penawaran * $item_detail->qty_delivery), -2);

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-left">' . $item_detail->product . '</td>';
            echo '<td class="text-center">' . number_format($item_detail->qty_order) . '</td>';
            echo '<td class="text-center">' . number_format($item_detail->qty_delivery) . '</td>';
            echo '<td class="text-right">' . number_format($item_detail->harga_penawaran, 2) . '</td>';
            echo '<td class="text-right">' . 0 . '</td>';
            echo '<td class="text-right">' . number_format($total_harga, 2) . '</td>';
            echo '</tr>';
            $total_harga_beli = $item_detail->harga_beli * $item_detail->qty_delivery;
            $subtotal += $total_harga;
            $grand_total_beli += $total_harga_beli;
            $no++;
        }
        ?>
    </tbody>
    <tbody class="text-bold grand_total_info">
        <?php

        $freight = $results['data_penawaran']->freight;
        $diskon_khusus = $results['data_penawaran']->diskon_khusus;
        $is_sj_pertama = $results['is_sj_pertama'];

        $freight_dipakai = $is_sj_pertama ? $freight : 0;
        $diskon_khusus_dipakai = $is_sj_pertama ? $diskon_khusus : 0;

        $includeppn = $subtotal -  $diskon_khusus_dipakai;
        $excludeppn = ($includeppn + $freight_dipakai) / 1.11;
        $dpp = ($excludeppn * 11) / 12;
        $nilai_ppn = (($dpp * 12)  / 100);
        $total_all = ($excludeppn + $nilai_ppn);

        // $dp_proporsional = ($total_all * $results['persen_dp'] / 100);
        // $retensi_proporsional = ($total_all * $results['persen_retensi'] / 100);
        // $jaminan_proporsional = ($total_all * $results['persen_jaminan'] / 100);

        // $total = ($total_all - $dp_proporsional - $retensi_proporsional);
        $total = ($total_all);
        // $total_tagihan = ($total + $jaminan_proporsional);
        $total_tagihan = ($total);

        $nilai_invoice = $total_tagihan;
        ?>
        <tr>
            <td class="text-right" colspan="6">Total</td>
            <td class="text-right"><?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php if ($is_sj_pertama && !empty($diskon_khusus) && $diskon_khusus > 0) { ?>
            <tr>
                <td class="text-right" colspan="6">Diskon Khusus</td>
                <td class="text-right"><?= number_format($diskon_khusus_dipakai, 2) ?></td>
            </tr>
        <?php } ?>
        <tr>
            <td class="text-right" colspan="6">DPP</td>
            <td class="text-right"><?= number_format($excludeppn, 2) ?></td>
        </tr>
        <!-- <tr>
            <td class="text-right" colspan="6">Other Cost</td>
            <td class="text-right"><?= number_format($ttl_other_cost, 2) ?></td>
        </tr> -->
        <tr>
            <td class="text-right" colspan="6">PPn</td>
            <td class="text-right"><?= number_format($nilai_ppn, 2) ?></td>
        </tr>
        <!-- <tr>
            <td class="text-right" colspan="6">DP Proporsional (<?= number_format($results['persen_dp'], 2) ?>%)</td>
            <td class="text-right"><?= number_format($dp_proporsional, 2) ?></td>
        </tr>
        <tr>
            <td class="text-right" colspan="6">Retensi Proporsional (<?= number_format($results['persen_retensi'], 2) ?>%)</td>
            <td class="text-right"><?= number_format($retensi_proporsional, 2) ?></td>
        </tr> -->
        <!-- <tr>
            <td class="text-right" colspan="6">Total</td>
            <td class="text-right"><?= number_format($total, 2) ?></td>
        </tr> -->
        <!-- <tr>
            <td class="text-right" colspan="6">Jaminan</td>
            <td class="text-right"><?= number_format($jaminan_proporsional, 2) ?></td>
        </tr> -->
        <tr>
            <td class="text-right" colspan="6">Total Tagihan</td>
            <td class="text-right"><?= number_format($total_tagihan, 2) ?></td>
        </tr>
    </tbody>
</table>
<input type="hidden" class="diskon_khusus" name="diskon_khusus" value="<?= $diskon_khusus_dipakai ?>">
<input type="hidden" class="nm_customer" name="nm_customer" value="<?= $results['data_penawaran']->name_customer ?>">
<input type="hidden" class="total_harga_beli" name="total_harga_beli" value="<?= $grand_total_beli ?>">
<input type="hidden" class="tipe_billing" name="tipe_billing" value="<?= $tipe_billing ?>">
<input type="hidden" class="no_so" name="no_so" value="<?= $id_so ?>">
<input type="hidden" class="id_billing" name="id_billing" value="<?= $id_billing ?>">
<input type="hidden" class="tipe_so" name="tipe_so" value="<?= $results['data_so']->tipe_so ?>">
<input type="hidden" class="id_penawaran" name="id_penawaran" value="<?= $results['data_penawaran']->id_penawaran ?>">
<input type="hidden" class="id_customer" name="id_customer" value="<?= $results['data_so']->id_customer ?>">
<input type="hidden" class="nilai_asli" name="nilai_asli" value="<?= $total_tagihan ?>">
<input type="hidden" class="nilai_dpp" name="nilai_dpp" value="<?= $dpp ?>">
<input type="hidden" class="nilai_invoice" name="nilai_invoice" value="<?= $nilai_invoice ?>">
<!-- <input type="hidden" class="persen_invoice" name="persen_invoice" value="<?= $persen_invoice ?>"> -->
<!-- <input type="hidden" class="ppn" name="ppn" value="<?= $results['persen_ppn'] ?>"> -->
<input type="hidden" class="nilai_ppn" name="nilai_ppn" value="<?= $nilai_ppn ?>">
<input type="hidden" class="grand_total" name="grand_total" value="<?= ($total_tagihan) ?>">

<!-- <input type="hidden" name="persen_retensi" class="persen_retensi" value="<?= $results['persen_retensi'] ?>">
<input type="hidden" name="nilai_retensi" class="nilai_retensi" value="<?= $retensi_proporsional ?>">
<input type="hidden" name="persen_jaminan" class="persen_jaminan" value="<?= $results['persen_jaminan'] ?>">
<input type="hidden" name="nilai_jaminan" class="nilai_jaminan" value="<?= $jaminan_proporsional ?>"> -->

<h5>Informasi Jurnal</h5>
<table class="table table-bordered table-hover">
    <thead>
        <tr bgcolor='#9acfea'>
            <th>
                <center>Tanggal</center>
            </th>
            <th>
                <center>Tipe</center>
            </th>
            <th>
                <center>No. COA</center>
            </th>
            <th>
                <center>Nama. COA</center>
            </th>
            <th>
                <center>Debit</center>
            </th>
            <th>
                <center>Kredit</center>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal1" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type1" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa1" name="no_coa[]" value="1102-01-01" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa1" name="nama_coa[]" value="Piutang Dagang" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet1" name="debet[]" value="<?= $total_tagihan ?>" class="form-control" readonly />
                <input type="text" id="debet21" name="debet2[]" value="<?= $total_tagihan ?>" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit1" name="kredit[]" value="0" class="form-control" readonly />
                <input type="text" id="kredit21" name="kredit2[]" value="0" class="form-control" readonly />
            </td>

        </tr>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal2" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type2" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa2" name="no_coa[]" value="2102-01-01" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa2" name="nama_coa[]" value="Uang Muka Penjualan" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet2" name="debet[]" value="0" class="form-control" readonly />
                <input type="text" id="debet22" name="debet2[]" value="0" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit2" name="kredit[]" value="0" class="form-control" readonly />
                <input type="text" id="kredit22" name="kredit2[]" value="0" class="form-control" readonly />
            </td>

        </tr>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal3" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type3" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa3" name="no_coa[]" value="2103-01-01" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa3" name="nama_coa[]" value="PPN Keluaran" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet3" name="debet[]" value="0" class="form-control" readonly />
                <input type="text" id="debet23" name="debet2[]" value="0" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit3" name="kredit[]" value="<?= round($nilai_ppn, 0) ?>" class="form-control" readonly />
                <input type="text" id="kredit23" name="kredit2[]" value="<?= round($nilai_ppn, 0) ?>" class="form-control" readonly />
            </td>

        </tr>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal4" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type4" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa4" name="no_coa[]" value="4101-01-01" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa4" name="nama_coa[]" value="PENDAPATAN PENJUALAN Produk" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet4" name="debet[]" value="0" class="form-control" readonly />
                <input type="text" id="debet24" name="debet2[]" value="0" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit4" name="kredit[]" value="<?= round($excludeppn, 0) ?>" class="form-control" readonly />
                <input type="text" id="kredit24" name="kredit2[]" value="<?= round($excludeppn, 0) ?>" class="form-control" readonly />
            </td>

        </tr>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal5" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type5" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa5" name="no_coa[]" value="5101-01-01" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa5" name="nama_coa[]" value="HPP" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet5" name="debet[]" value="<?= $grand_total_beli ?>" class="form-control" readonly />
                <input type="text" id="debet25" name="debet2[]" value="<?= $grand_total_beli ?>" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit5" name="kredit[]" value="0" class="form-control" readonly />
                <input type="text" id="kredit25" name="kredit2[]" value="0" class="form-control" readonly />
            </td>

        </tr>
        <tr bgcolor='#DCDCDC'>
            <td><input type="date" id="tgl_jurnal6" name="tgl_jurnal[]" value="<?= date('Y-m-d') ?>" class="form-control" readonly /></td>
            <td><input type="text" id="type6" name="type[]" value="JV" class="form-control" readonly /></td>
            <td><input type="text" id="no_coa6" name="no_coa[]" value="1104-01-03" class="form-control" readonly /></td>
            <td><input type="text" id="nama_coa6" name="nama_coa[]" value="Persediaan Barang In Customer" class="form-control" readonly /></td>
            <td><input type="hidden" id="debet6" name="debet[]" value="0" class="form-control" readonly />
                <input type="text" id="debet26" name="debet2[]" value="0" class="form-control" readonly />
            </td>
            <td><input type="hidden" id="kredit6" name="kredit[]" value="<?= $grand_total_beli ?>" class="form-control" readonly />
                <input type="text" id="kredit26" name="kredit2[]" value="<?= $grand_total_beli ?>" class="form-control" readonly />
            </td>

        </tr>

        <tr bgcolor='#DCDCDC'>
            <td colspan="3" align="right"><b>TOTAL</b></td>
            <td align="right"><input type="hidden" id="total" name="total" value="<?= $total_tagihan + $grand_total_beli ?>" class="form-control" readonly />
                <input type="text" id="total31" name="total3" value="<?= $total_tagihan + $grand_total_beli  ?>" class="form-control" readonly />
            </td>
            <td align="right"><input type="hidden" id="total2" name="total2" value="<?= round($nilai_ppn, 0) + round($excludeppn, 0) + $grand_total_beli ?>" class="form-control" readonly />
                <input type="text" id="total41" name="total4" value="<?= round($nilai_ppn, 0) + round($excludeppn, 0) + $grand_total_beli ?>" class="form-control" readonly />
            </td>

        </tr>
</table>


<!-- <script>
    $(document).ready(function() {
        $(document).on('change', 'input[name="tgl_invoice"]', function() {
            const val = $(this).val(); // ambil nilai tgl_invoice
            if (val) {
                // isi semua field tgl_jurnal dengan value yg sama
                $('input[name="tgl_jurnal[]"]').val(val);
            }
        });
    });
</script> -->