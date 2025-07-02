<?php
$ENABLE_ADD     = has_permission('Sales_Order.Add');
$ENABLE_MANAGE  = has_permission('Sales_Order.Manage');
$ENABLE_VIEW    = has_permission('Sales_Order.View');
$ENABLE_DELETE  = has_permission('Sales_Order.Delete');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Sales Order</title>
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
    $p = $results['data_so'];
    $details = $results['data_so_detail'];
    ?>

    <!-- Header dan Judul -->
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
            <h2>SALES ORDER</h2>
        </div>
    </div>
    <hr>
    <!-- Keterangan customer, tanggal, dan nomor penawaran -->
    <div style="display: flex;">
        <div style="width: 50%;">
            <table class="no-border">
                <tr>
                    <td style="width: 80px; vertical-align: top;"><strong>Diorder oleh</strong></td>
                    <td>
                        <?= strtoupper($p->name_customer) ?> <br>
                        <?= $p->address_office ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Dikirim ke</strong></td>
                    <td>
                        <?= strtoupper($p->name_customer) ?><br>
                        <?= $p->address_office ?>
                    </td>
                </tr>
            </table>
        </div>
        <div style="width: 50%;">
            <table class="no-border">
                <tr>
                    <td>Tgl SO </td>
                    <td>: <?= date('j F Y', strtotime($p->quotation_date)) ?></td>
                    <td>Nomor SO </td>
                    <td>: <?= $p->no_so ?></td>
                </tr>
                <tr>
                    <td>Termin </td>
                    <td>: </td>
                    <td>Salesman </td>
                    <td>: <?= $p->nama_sales ?></td>
                </tr>
                <tr>
                    <td>PO. No. </td>
                    <td>: <?= $p->id_penawaran ?></td>
                    <td>Payment </td>
                    <td>: <?= strtoupper($p->tipe_bayar) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <br>
    <!-- Keterangan barang -->
    <table>
        <thead>
            <tr>
                <th width="50px">No</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Harga Penawaran</th>
                <th>Discount</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0;
            $no = 0;
            foreach ($details as $d):
                $no++; ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td><?= $d->product ?></td>
                    <td class="text-center"><?= $d->qty_order ?></td>
                    <td class="text-center"><?= strtoupper($d->unit) ?></td>
                    <td class="text-right"><?= number_format($d->harga_penawaran) ?></td>
                    <td class="text-center"><?= $d->diskon_persen ?>%</td>
                    <td class="text-right">
                        <?php $amount = $d->qty_order * $d->harga_penawaran;
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
            <td width="10%">Terbilang</td>
            <td width="60%"><strong><em><?= ucfirst(number_to_words($total)) ?> Rupiah</em></strong></td>
            <td width="15%">Total Harga Penawaran</td>
            <td width="15%" class="text-right"><?= number_format($total) ?></td>
        </tr>
        <tr>
            <td colspan="2" rowspan="5" style="vertical-align: top;">
                <strong>Keterangan</strong><br>
                CL : <?= number_format($p->total_penawaran) ?><br>
                <?= strtoupper($p->tipe_bayar) ?>
            </td>
            <td>Discount</td>
            <td class="text-right"><?= $p->total_diskon_persen ?>%</td>
        </tr>
        <tr>
            <td>Freight</td>
            <td class="text-right"><?= number_format($p->freight) ?></td>
        </tr>
        <tr class="bold">
            <td>Total Order</td>
            <td class="text-right"><?= number_format($p->grand_total) ?></td>
        </tr>
    </table>

    <!-- Terbilang nominal & Rincian Biaya dll -->

    <br><br>
    <!-- Signature -->
    <table class="no-border">
        <tr class="text-center">
            <td>Dibuat Oleh:</td>
            <td>Diperiksa Oleh:</td>
            <td>Disetujui Oleh:</td>
            <td>Salesman:</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="text-center">
            <td></td>
            <td></td>
            <td></td>
            <td><?= $p->nama_sales ?></td>
        </tr>
    </table>

</body>

</html>

<!-- page script -->
<script>
    // window.print();
</script>