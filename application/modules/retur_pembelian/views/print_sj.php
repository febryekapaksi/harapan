<?php $h = $retur['header']; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan - <?= $h['no_retur'] ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; }
        .header p { margin: 2px 0; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px 5px; vertical-align: top; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 5px 8px; }
        .data-table th { background: #f0f0f0; text-align: center; }
        .text-center { text-align: center; }
        .ttd-table { width: 100%; margin-top: 40px; }
        .ttd-table td { text-align: center; padding-top: 60px; vertical-align: bottom; }
        .ttd-line { border-top: 1px solid #000; display: inline-block; width: 150px; margin-top: 5px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()" style="padding:8px 16px; cursor:pointer;">
            <strong>&#128438; Print</strong>
        </button>
        <button onclick="window.close()" style="padding:8px 16px; cursor:pointer;">Tutup</button>
    </div>

    <div class="header">
        <h2>SURAT JALAN PENGEMBALIAN BARANG</h2>
        <p>Retur Pembelian</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>No. Retur:</strong> <?= $h['no_retur'] ?><br>
                <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($h['tgl_retur'])) ?><br>
                <strong>No. Invoice:</strong> <?= $h['no_invoice'] ?>
            </td>
            <td width="50%">
                <strong>Kepada:</strong><br>
                <?= $h['nama_supplier'] ?><br>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th width="80">Satuan</th>
                <th width="80">Qty</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 0; foreach ($retur['detail'] as $d): $no++; ?>
            <tr>
                <td class="text-center"><?= $no ?></td>
                <td><?= $d['kode_barang'] ?></td>
                <td><?= $d['nama_barang'] ?></td>
                <td class="text-center"><?= $d['satuan'] ?: '-' ?></td>
                <td class="text-center"><?= number_format($d['qty_retur']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><strong>Alasan:</strong> <?= $h['kategori_alasan'] ?> - <?= $h['keterangan_alasan'] ?></p>

    <table class="ttd-table">
        <tr>
            <td>
                Pengirim<br><br><br><br>
                <span class="ttd-line"></span><br>
                (__________________)
            </td>
            <td>
                Penerima<br><br><br><br>
                <span class="ttd-line"></span><br>
                (__________________)
            </td>
            <td>
                Diketahui<br><br><br><br>
                <span class="ttd-line"></span><br>
                (__________________)
            </td>
        </tr>
    </table>
</body>
</html>
