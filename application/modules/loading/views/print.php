<?php
$ENABLE_ADD     = has_permission('Loading.Add');
$ENABLE_MANAGE  = has_permission('Loading.Manage');
$ENABLE_VIEW    = has_permission('Loading.View');
$ENABLE_DELETE  = has_permission('Loading.Delete');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Draft Muatan</title>
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
    $header = $results['loading'];
    $detail = $results['detail'];
    $kendaraan = $results['kendaraan'];
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
            <h2>DRAFT MUATAN</h2>
        </div>
    </div>
    <hr>

    <!-- Keterangan pengiriman, tanggal dan nopol truk -->
    <div style="display: flex;">
        <div style="width: 80%;">
            <table class="no-border">
                <tr>
                    <td style="width: 120px; vertical-align: top;"><strong>Pengiriman</strong></td>
                    <td>: <?= $header['pengiriman'] ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Tanggal Muat</strong></td>
                    <td>: <?= date('d/ M/ Y', strtotime($header['tanggal_muat'])) ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><strong>Kendaraan & Nopol</strong></td>
                    <td>: <?= $kendaraan['jenis'] . " - " . $kendaraan['nopol'] ?></td>
                </tr>
            </table>
        </div>
    </div>
    <br>
    <!-- Keterangan barang -->
    <table>
        <thead>
            <tr>
                <th width="40px">No</th>
                <th>No SO</th>
                <th hidden>Customer</th>
                <th>Produk</th>
                <th>Qty Muat</th>
                <th>Berat (Kg)</th>
                <th>Qty Aktual</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grouped = [];
            foreach ($detail as $row) {
                $grouped[$row['no_delivery']][] = $row;
            }
            $total_berat = 0;
            $no = 1;
            foreach ($grouped as $no_delivery => $rows):
                $customer_name = $rows[0]['customer'];
            ?>
                <tr style="background-color:#f0f0f0; font-weight:bold;">
                    <td colspan="8"><strong>No SPK: <?= $no_delivery ?> - <?= $customer_name ?></strong></td>
                </tr>
                <?php
                foreach ($rows as $row):
                    $total_berat += $row['jumlah_berat'];
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= $row['no_so'] ?></td>
                        <td hidden><?= $row['customer'] ?></td>
                        <td><?= $row['product'] ?></td>
                        <td class="text-center"><?= $row['qty_muat'] ?></td>
                        <td class="text-center"><?= $row['jumlah_berat'] ?></td>
                        <td class="text-center"><?= isset($row['qty_aktual']) ? $row['qty_aktual'] : '' ?></td>
                        <td class="text-center"><?= isset($row['keterangan']) ? $row['keterangan'] : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right" colspan="3"><strong>Total Berat</strong></td>
                <td class="text-center" colspan="4"><strong><?= number_format($total_berat, 2) ?></strong></td>
            </tr>
            <tr>
                <td class="text-right" colspan="3"><strong>Kapasitas</strong></td>
                <td class="text-center" colspan="4"><strong><?= number_format($kendaraan['kapasitas'], 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <br><br>
    <!-- Signature -->
    <table class="no-border">
        <tr class="text-center">
            <td>Dibuat Oleh:</td>
            <td>Diperiksa Oleh:</td>
            <td>Disetujui Oleh:</td>
        </tr>
        <tr>
            <td style="height: 50px;"></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="text-center">
            <td>( ____________ )</td>
            <td>( ____________ )</td>
            <td>( ____________ )</td>
        </tr>
    </table>
</body>

</html>

<!-- page script -->
<script>
    // window.print();
</script>