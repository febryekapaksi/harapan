<?php
$ENABLE_ADD     = has_permission('Penawaran.Add');
$ENABLE_MANAGE  = has_permission('Penawaran.Manage');
$ENABLE_VIEW    = has_permission('Penawaran.View');
$ENABLE_DELETE  = has_permission('Penawaran.Delete');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Sales Quotation</title>
    <style>
        /* Hilangkan margin browser saat print */
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

            /* Hindari pemotongan baris tabel saat print */
            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
            }

            /* Sembunyikan elemen yang tidak perlu dicetak */
            .no-print {
                display: none !important;
            }
        }

        /* Untuk tampilan di layar tetap nyaman */
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
    $p = $results['data_penawaran'];
    $details = $results['data_penawaran_detail'];
    ?>

    <!-- Header dan Judul -->
    <div style="display: flex;">
        <div style="width: 60%;">
            <strong>PT. NUSA BANGUN OETAMA</strong><br>
            JALAN DAAN MOGOT KM.11 NO.45<br>
            KEDAUNG KALI ANGKE CENGKARENG<br>
            JAKARTA BARAT
        </div>
        <div class="text-right" style="width: 40%;">
            <h2>Sales Quotation</h2>
        </div>
    </div>

    <br>
    <!-- Keterangan customer, tanggal, dan nomor penawaran -->
    <div style="display: flex;">
        <div style="width: 60%;">
            <table class="no-border">
                <tr>
                    <td style="width: 60px; vertical-align: top;"><strong>Order By :</strong></td>
                    <td>
                        <?= strtoupper($p->name_customer) ?> <br>
                        <?= $p->address_office ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Quote To :</strong></td>
                    <td> <?= strtoupper($p->name_customer) ?><br>
                        <?= $p->address_office ?></td>
                </tr>
            </table>
        </div>
        <div style="width: 40%;">
            <table class="no-border">
                <tr>
                    <td>Quote Date</td>
                    <td>: <?= date('j F Y', strtotime($p->quotation_date)) ?></td>
                </tr>
                <tr>
                    <td>Quote Number</td>
                    <td>: <?= $p->id_penawaran ?></td>
                </tr>
            </table>
        </div>
    </div>

    <br>
    <!-- Keterangan barang -->
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Item Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Disc</th>
                <th>Tax</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0;
            foreach ($details as $d): ?>
                <tr>
                    <td><?= $d->id_product ?></td>
                    <td><?= $d->product_name ?></td>
                    <td class="text-center"><?= $d->qty ?></td>
                    <td class="text-right"><?= number_format($d->price_list) ?></td>
                    <td class="text-center"><?= $d->diskon ?>%</td>
                    <td class="text-center"></td>
                    <td class="text-right">
                        <?php $amount = $d->qty * $d->harga_penawaran;
                        echo number_format($amount);
                        $total += $amount; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <table>
        <tr>
            <td width="10%">Say</td>
            <td width="60%"><strong><em><?= ucfirst(number_to_words($total)) ?> Rupiah</em></strong></td>
            <td width="15%">Sub Total</td>
            <td width="15%" class="text-right"><?= number_format($total) ?></td>
        </tr>
        <tr>
            <td colspan="2" rowspan="5" style="vertical-align: top;">
                <strong>Description</strong><br>
                CL <?= number_format($p->total_penawaran) ?><br>
                <?= strtoupper($p->tipe_bayar) ?>
            </td>
            <td>Discount</td>
            <td class="text-right"><?= $p->total_diskon_persen ?>%</td>
        </tr>
        <tr>
            <td>DPP</td>
            <td class="text-right"><?= number_format($p->dpp) ?></td>
        </tr>
        <tr>
            <td>PPN 11%</td>
            <td class="text-right"><?= number_format($p->ppn) ?></td>
        </tr>
        <tr>
            <td>Freight</td>
            <td class="text-right">0</td>
        </tr>
        <tr class="bold">
            <td>Total Invoice</td>
            <td class="text-right"><?= number_format($p->grand_total) ?></td>
        </tr>
    </table>

    <!-- Terbilang nominal & Rincian Biaya dll -->

    <br><br>
    <!-- Signature -->
    <table class="no-border">
        <tr class="text-center">
            <td>Prepared By</td>
            <td>Approved By</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
        </tr>
        <tr class="text-center">
            <td><?= ucfirst($p->created_by) ?></td>
            <td><?= ($p->level_approval == 'M') ? ucfirst($p->approved_by_manager)  : ucfirst($p->approved_by_direksi) ?></td>
        </tr>
        <tr class="text-center">
            <td>Date: <?= date('j F Y', strtotime($p->created_at)) ?></td>
            <td>Date: <?= ($p->level_approval == 'M') ? date('j F Y', strtotime($p->approved_at_manager))  : date('j F Y', strtotime($p->approved_at_direksi)) ?></td>
        </tr>
    </table>

</body>

</html>

<!-- page script -->
<script>
    // window.print();
</script>