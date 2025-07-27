<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
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
            <h2>SURAT JALAN</h2>
        </div>
    </div>
    <hr>

    <!-- Keterangan customer, alamat, tanggal dan nomor surat jalan -->
    <div style="display: flex;">
        <div style="width: 60%;">
            <table class="no-border">
                <tr>
                    <td style="width: 80px; vertical-align: top;"><strong>Kepada</strong></td>
                    <td><b><?= $sj['name_customer']; ?></b></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"></td>
                    <td><?= $sj['delivery_address']; ?></td>
                </tr>
            </table>
        </div>
        <div style="width: 40%;">
            <table>
                <tr>
                    <td>Tanggal</td>
                    <td><?= date('d/M/Y', strtotime($sj['delivery_date'])); ?></td>
                    <td>Nomor</td>
                    <td><?= $sj['no_surat_jalan']; ?></td>
                </tr>
                <tr>
                    <td>Driver</td>
                    <td><?= isset($sj['driver_name']) ? $sj['driver_name'] : '-'; ?></td>
                    <td>No Plat</td>
                    <td><?= isset($sj['nopol']) ? $sj['nopol'] : '-'; ?></td>
                </tr>
            </table>
        </div>
    </div>

    <br>

    <!-- Keterangan barang -->
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th width="50px">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Ket / Colly</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $i => $row): ?>
                <tr>
                    <td align="center"><?= $i + 1; ?></td>
                    <td><?= $row['id_product']; ?></td>
                    <td><?= $row['product']; ?></td>
                    <td align="center"><?= number_format($row['qty']); ?></td>
                    <td align="center"><?= strtoupper($row['code']); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>

    <!-- Signature -->
    <div style="display: flex;">
        <div style="width: 60%">
            <table class="no-border" width="100%">
                <tr class="text-center">
                    <td align="center">Disiapkan Oleh</td>
                    <td align="center">Disetujui Oleh</td>
                    <td align="center">Diserahkan Oleh</td>
                    <td align="center">Diterima Oleh</td>
                </tr>
                <tr>
                    <td style="height: 60px;"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="text-center">
                    <td align="center">_____________</td>
                    <td align="center">_____________</td>
                    <td align="center">Tgl__________</td>
                    <td align="center">Tgl__________</td>
                </tr>
            </table>
        </div>
        <div style="width: 40%">
            <table>
                <tr>
                    <td>Keterangan</td>
                </tr>
                <tr>
                    <td style="height: 50px;">Kirim Ke : <b><?= $sj['name_customer']; ?></b><br>
                        <?= $sj['delivery_address']; ?></td>
                </tr>
                <tr>
                    <td>Sales : <?= $sj['nama_sales'] ?></td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>