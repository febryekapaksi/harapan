<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran</title>
    <style>
        body {

            font-family: monospace;
            font-size: 7px;
            box-sizing: border-box;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }

        .footer-space {
            height: 20px;
        }
    </style>
</head>

<body>
    <div class="text-center">
        <b>PT SURYA BANGUN FAJAR</b><br>
        Jl. Kalijaga No.35, Pegambiran, Lemahwungkuk<br>
        Cirebon, Jawa Barat 45113
    </div>

    <hr>
    <div class="text-center">
        <b>BUKTI PENERIMAAN</b><br>
        <?= $header->kd_pembayaran ?><br>
        Tanggal: <?= $header->created_on ?>
    </div>
    <hr>

    <table>
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td><?= $header->nm_customer ?></td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td><?= $header->keterangan ?></td>
        </tr>
    </table>

    <hr>

    <?php
    $grand_total = 0;
    foreach ($details as $d): ?>
        <b><?= $d->no_invoice ?></b><br>
        <?php
        $items = $this->db
            ->where('id_invoice', $d->no_invoice)
            ->get('tr_invoice_sales_detail')->result();
        ?>
        <?php
        foreach ($items as $i): ?>
            <?php
            $total_item = round(($i->harga * $i->qty),   -2);
            $grand_total += $total_item
            ?>
            <table>
                <tr>
                    <td>• <?= $i->nm_produk ?></td>
                    <td><?= number_format($total_item, 0) ?></td>
                </tr>
            </table>
        <?php endforeach; ?>
        <hr>
    <?php endforeach; ?>

    <table>
        <tr>
            <td><b>Total Invoice</b></td>
            <td class="text-right"><b><?= number_format($header->grand_total, 0) ?></b></td>
        </tr>
        <tr>
            <td><b>Pembayaran Diterima</b></td>
            <td class="text-right"><b><?= number_format($header->jumlah_pembayaran_idr, 0) ?></b></td>
        </tr>
    </table>
    <hr>

    <table>
        <tr>
            <td><b>Total Pembayaran Sebelumnya</b></td>
            <td class="text-right"><b><?= number_format($total_pembayaran_sebelumnya, 0) ?></b></td>
        </tr>
        <tr>
            <td><b>Kekurangan Pembayaran</b></td>
            <td class="text-right"><b><?= number_format($total_kurang_pembayaran, 0) ?></b></td>
        </tr>
    </table>
    <hr>
    <div class="text-center">
        Terima kasih
    </div>
    <div class="footer-space"></div>
</body>

</html>