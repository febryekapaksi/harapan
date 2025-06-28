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
        Tanggal: <?= date('d/m/Y', strtotime($header->tgl_pembayaran)) ?>
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

    <?php foreach ($details as $d): ?>
        <b><?= $d->no_invoice ?></b><br>
        <?php
        $items = $this->db
            ->where('id_invoice', $d->no_invoice)
            ->get('tr_invoice_sales_detail')->result();
        ?>
        <?php foreach ($items as $i): ?>
            <div class="item-row">
                <div>
                    <?= $i->nm_produk ?><br>
                    (<?= number_format($i->qty, 2) ?> x <?= number_format($i->harga, 0) ?>) x <?= $i->disc ?>%
                </div>
                <div class="text-right">
                    <?php
                    $total_item = round(($i->harga * $i->qty) * (1 + ($i->disc / 100)), -2); // sesuai logika customermu
                    ?>
                    <?= number_format($total_item, 0) ?>
                </div>
            </div>
        <?php endforeach; ?>
        <hr>
    <?php endforeach; ?>

    <table>
        <?php if ($freight > 0): ?>
            <tr>
                <td>Freight</td>
                <td class="text-right"><?= number_format($freight, 0) ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td>Subtotal</td>
            <td class="text-right"><?= number_format($subtotal, 0) ?></td>
        </tr>
        <tr>
            <td>DPP</td>
            <td class="text-right"><?= number_format(round($dpp), 0) ?></td>
        </tr>
        <tr>
            <td>PPn</td>
            <td class="text-right"><?= number_format(round($ppn), 0) ?></td>
        </tr>
        <tr>
            <td colspan="2">
                <hr>
            </td>
        </tr>
        <tr>
            <td><b>Grand Total</b></td>
            <td class="text-right"><b><?= number_format($grand_total, 0) ?></b></td>
        </tr>
    </table>

    <hr>
    <div class="text-center">
        Terima kasih
    </div>
    <div class="footer-space"></div>
</body>

</html>