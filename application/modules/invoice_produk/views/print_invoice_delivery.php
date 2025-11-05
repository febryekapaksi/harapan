<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>INVOICE</title>
    <style>
        @page {
            size: A4;
            margin: 5mm;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 5mm;
                font-family: Arial, sans-serif;
                font-size: 10px;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px;
            }

            .no-border td {
                border: none !important;
            }

            .text-right {
                text-align: right;
            }

            .text-center {
                text-align: center;
            }

            .bold {
                font-weight: bold;
            }

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 5mm;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
        }

        .no-border td {
            border: none;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php
    $invoice = $data_invoice;
    $detail = $data_invoice_detail;
    $total = 0;
    ?>

    <!-- Header -->
    <div style="display: flex;">
        <div class="text-left" style="width: 140px;">
            <img src="<?= base_url('assets/images/logo_sbf.png') ?>" alt="" width="100" height="60">
        </div>
        <div style="width: 60%;">
            <strong>PT Surya Bangun Fajar</strong><br>
            Jl. Kalijaga No.35 Kel. Pegambiran Kec. Lemahwungkuk<br>
            Kota Cirebon Jawa Barat 45113<br>
            Indonesia
        </div>
        <div class="text-right" style="width: 40%;">
            <h2>INVOICE</h2>
        </div>
    </div>
    <hr>

    <!-- Info Customer -->
    <table class="no-border" style="margin-bottom: 10px;">
        <tr>
            <td style="width: 20%;">Customer</td>
            <td>: <?= strtoupper($data_customer->name_customer) ?></td>
            <td style="width: 20%;">Invoice No</td>
            <td>: <?= $id_invoice ?></td>
        </tr>
        <tr>
            <td>Address</td>
            <td>: <?= $data_customer->address_office ?></td>
            <td>Tax Invoice No</td>
            <td>: <?= $invoice->tax_invoice_no ?></td>
        </tr>
        <tr>
            <td>NPWP</td>
            <td>: <?= $data_customer->npwp ?></td>
            <td>PO No</td>
            <td>: <?= $data_so->po_no ?></td>
        </tr>
        <tr>
            <td>Phone</td>
            <td>: <?= $data_customer->telephone ?></td>
            <?php
            $baseTs   = strtotime($data_delivery->delivery_date);
            $rawTop   = isset($data_penawaran->jumlah_top) ? trim($data_penawaran->jumlah_top) : '';
            if ($rawTop === '-' || $rawTop === '' || !preg_match('/^\d+$/', $rawTop)) {
                $dueTs = $baseTs;
            } else {
                $dueTs = strtotime('+' . $rawTop . ' days', $baseTs);
            }
            ?>
            <td>Due Date</td>
            <td>: <?= date('d F Y', $dueTs); ?></td>
        </tr>
        <tr>
            <td>Fax</td>
            <td>: <?= $data_customer->fax ?></td>
            <td>Delivery Date</td>
            <td>: <?= date('d F Y', strtotime($data_delivery->delivery_date)) ?></td>
        </tr>
    </table>

    <!-- Tabel Produk -->
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Product Code</th>
                <th>Description</th>
                <th>Qty</th>
                <th>UOM</th>
                <th>Unit Price</th>
                <th>Discount</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($detail as $item):
                $total += $item->subtotal;
                $code = '';
                $inv = $this->db->get_where('new_inventory_4', ['code_lv4' => $item->id_produk])->row();
                if ($inv) {
                    $code = $inv->code_lv4;
                }
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= $code ?></td>
                    <td><?= $item->nm_produk ?></td>
                    <td class="text-center"><?= number_format($item->qty) ?></td>
                    <td class="text-center"><?= strtoupper($item->uom) ?></td>
                    <td class="text-right"><?= number_format($item->harga_penawaran, 2) ?></td>
                    <td class="text-right">0</td>
                    <td class="text-right"><?= number_format($item->subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Total -->
    <br>
    <table>
        <?php
        $vat = $invoice->nilai_ppn;
        $dpp = ($total);
        $total_all = $dpp + $vat;
        $total_tagihan = $total_all;
        ?>
        <tr>
            <td width="10%">Terbilang</td>
            <td width="35%"><strong><em><?= ucfirst(rupiah_to_words($invoice->nilai_invoice)) ?> Rupiah</em></strong></td>
            <td rowspan="2" class="text-center"><small>Pembayaran Non-Tunai dapat ditransfer ke<br>Rek Bank A/N: PT. SURYA BANGUN FAJAR
                </small></td>
            <td width="10%" class="text-right">Total</td>
            <td width="15%" class="text-right"><?= number_format($total, 2) ?></td>
        </tr>
        <tr>
            <td colspan="2" rowspan="5"></td>
            <td class="text-right">DPP</td>
            <td class="text-right"><?= number_format($invoice->nilai_dpp, 2) ?></td>
        </tr>
        <!-- <tr>
            <td colspan="3" class="text-right">DP (<?= number_format($persen_dp, 2) ?>%)</td>
            <td class="text-right"><?= number_format($nilai_dp, 2) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="text-right">Retensi (<?= number_format($persen_retensi, 2) ?>%)</td>
            <td class="text-right"><?= number_format($nilai_retensi, 2) ?></td>
        </tr> -->
        <tr>
            <td rowspan="2" class="text-center">BCA : 001-07-49-611</td>
            <td class="text-right">PPn (12%)</td>
            <td class="text-right"><?= number_format($vat, 2) ?></td>
        </tr>
        <!-- <tr>
            <td class="text-right">Subtotal</td>
            <td class="text-right"><?= number_format($invoice->grand_total, 2) ?></td>
        </tr> -->
        <!-- <tr>
            <td colspan="3" class="text-right">Jaminan</td>
            <td class="text-right"><?= number_format($nilai_jaminan, 2) ?></td>
        </tr> -->
        <tr class="bold">
            <td class="text-right">Total Tagihan</td>
            <td class="text-right"><?= number_format($invoice->nilai_invoice, 2) ?></td>
        </tr>
    </table>

    <!-- Signature -->
    <br><br>
    <table class="no-border">
        <tr class="text-center">
            <td>Dibuat Oleh:</td>
            <td>Diperiksa Oleh:</td>
            <td>Disetujui Oleh:</td>
            <td>Penerima:</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="text-center">
            <td><?= $this->auth->user_name() ?></td>
            <td></td>
            <td></td>
            <td><?= strtoupper($data_customer->name_customer) ?></td>
        </tr>
    </table>

    <br><br>
    <div align="right"><b>Doc. No: FR-FIN-08-00</b></div>
</body>

</html>