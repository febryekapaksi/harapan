<?php $h = $retur['header']; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan Retur - <?= $h['no_retur'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
        
        .container { width: 100%; max-width: 800px; margin: 0 auto; }
        
        /* HEADER */
        .header { display: table; width: 100%; margin-bottom: 15px; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .company-name { font-size: 16px; font-weight: bold; color: #1a237e; }
        .company-address { font-size: 10px; color: #333; line-height: 1.4; }
        .sj-title { font-size: 20px; font-weight: bold; text-align: center; }
        .sj-subtitle { font-size: 14px; font-weight: bold; text-align: center; border: 2px solid #000; display: inline-block; padding: 2px 15px; margin-top: 3px; }
        
        /* INFO */
        .info-section { display: table; width: 100%; margin-bottom: 15px; border-top: 1px solid #000; padding-top: 8px; }
        .info-left { display: table-cell; width: 50%; vertical-align: top; }
        .info-right { display: table-cell; width: 50%; vertical-align: top; }
        .info-table { border-collapse: collapse; font-size: 11px; }
        .info-table td { padding: 2px 5px; }
        .info-table td:first-child { font-weight: bold; }
        
        /* DATA TABLE */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 4px 6px; }
        .data-table th { background: #f5f5f5; font-weight: bold; text-align: center; font-size: 11px; }
        .data-table td { font-size: 11px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* FOOTER / TTD */
        .footer-section { display: table; width: 100%; margin-top: 20px; }
        .ttd-area { display: table-cell; vertical-align: top; }
        .ttd-left { width: 60%; }
        .ttd-right { width: 40%; border: 1px solid #000; padding: 8px; font-size: 10px; }
        .ttd-table { width: 100%; }
        .ttd-table td { text-align: center; padding-top: 50px; vertical-align: bottom; font-size: 10px; width: 25%; }
        .ttd-label { font-weight: bold; margin-bottom: 40px; display: block; }
        .ttd-line { border-bottom: 1px solid #000; display: inline-block; width: 90px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:15px;">
        <button onclick="window.print()" style="padding:8px 20px; cursor:pointer; font-size:13px;">&#128438; Print</button>
        <button onclick="window.history.back()" style="padding:8px 20px; cursor:pointer; font-size:13px;">Kembali</button>
    </div>

    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <div class="header-left">
                <div style="display:inline-block; vertical-align:middle; margin-right:10px;">
                    <img src="<?= base_url('assets/images/logo_sbf.png') ?>" alt="Logo" width="80" height="50">
                </div>
                <div style="display:inline-block; vertical-align:middle;">
                    <div class="company-name">PT Surya Bangun Fajar</div>
                    <div class="company-address">
                        Jl. Kalijaga No.35 Kel. Pegambiran Kec. Lemahwungkuk<br>
                        Kota Cirebon Jawa Barat 45113<br>
                        Indonesia
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="sj-title">SURAT JALAN</div>
                <div class="sj-subtitle">RETUR</div>
            </div>
        </div>

        <!-- INFO SECTION -->
        <div class="info-section">
            <div class="info-left">
                <table class="info-table">
                    <tr>
                        <td>Kepada</td>
                        <td><strong><?= strtoupper($h['nama_supplier']) ?></strong></td>
                    </tr>
                </table>
            </div>
            <div class="info-right">
                <table class="info-table" style="border-collapse:collapse;">
                    <tr>
                        <td style="border:1px solid #000; padding:3px 6px;">Tanggal</td>
                        <td style="border:1px solid #000; padding:3px 6px;"><?= date('d/M/Y', strtotime($h['tgl_retur'])) ?></td>
                        <td style="border:1px solid #000; padding:3px 6px;">Nomor</td>
                        <td style="border:1px solid #000; padding:3px 6px;"><?= $h['no_retur'] ?></td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000; padding:3px 6px;">Driver</td>
                        <td style="border:1px solid #000; padding:3px 6px;"></td>
                        <td style="border:1px solid #000; padding:3px 6px;">No Plat</td>
                        <td style="border:1px solid #000; padding:3px 6px;"></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- DATA TABLE -->
        <table class="data-table">
            <thead>
                <tr>
                    <th width="35">No</th>
                    <th width="110">Kode Barang</th>
                    <th>Nama Barang</th>
                    <th width="55">Qty</th>
                    <th width="60">Satuan</th>
                    <th width="70">Ket / Colly</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 0; foreach ($retur['detail'] as $d): $no++; ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td><?= $d['kode_barang'] ?></td>
                    <td><?= strtoupper($d['nama_barang']) ?></td>
                    <td class="text-center"><?= number_format($d['qty_retur'], 0) ?></td>
                    <td class="text-center"><?= strtoupper($d['satuan'] ?: '-') ?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
                <?php for ($i = $no; $i < 5; $i++): ?>
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- FOOTER / TTD -->
        <div class="footer-section">
            <div class="ttd-area ttd-left">
                <table class="ttd-table">
                    <tr>
                        <td><span class="ttd-label">Disiapkan Oleh</span></td>
                        <td><span class="ttd-label">Disetujui Oleh</span></td>
                        <td><span class="ttd-label">Diserahkan Oleh</span></td>
                        <td><span class="ttd-label">Diterima Oleh</span></td>
                    </tr>
                    <tr>
                        <td><span class="ttd-line"></span></td>
                        <td><span class="ttd-line"></span></td>
                        <td>Tgl<span class="ttd-line"></span></td>
                        <td>Tgl<span class="ttd-line"></span></td>
                    </tr>
                </table>
            </div>
            <div class="ttd-area ttd-right">
                <strong>Keterangan</strong><br><br>
                Kirim Ke : <strong><?= strtoupper($h['nama_supplier']) ?></strong><br>
                <?php if (!empty($h['keterangan_alasan'])): ?>
                <br>Alasan: <?= $h['kategori_alasan'] ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
