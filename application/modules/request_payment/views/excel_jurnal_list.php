<?php
$label_from = !empty($tgl_from) ? date('d F Y', strtotime($tgl_from)) : 'All';
$label_to   = !empty($tgl_to) ? date('d F Y', strtotime($tgl_to)) : 'All';

$filename = "Payment Jurnal ({$label_from} - {$label_to}).xls";

if (ob_get_length()) {
    ob_end_clean();
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Jurnal</title>
</head>

<body>
    <h5>Payment Jurnal (<?= $label_from ?> - <?= $label_to ?>)</h5>
    <table border="1">
        <thead>
            <tr>
                <th style="text-align: center;">#</th>
                <th style="text-align: center;">No Transaksi</th>
                <th style="text-align: center;">Keperluan</th>
                <th style="text-align: center;">Tgl Jurnal</th>
                <th style="text-align: center;">Jumlah</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (!empty($results)) {
                foreach ($results as $record) {
                    $status = ($record->sts == 1) ? 'Sudah Jurnal' : 'Belum Jurnal';
                    echo '<tr>';
                    echo '<td style="text-align: center;">' . $no . '</td>';
                    echo '<td style="text-align: left;">' . $record->id_payment . '</td>';
                    echo '<td style="text-align: left;">' . $record->keperluan . '</td>';
                    echo '<td style="text-align: center;">' . $record->tgl_jurnal . '</td>';
                    echo '<td style="text-align: right;">' . number_format($record->total, 2) . '</td>';
                    echo '<td style="text-align: center;">' . $status . '</td>';
                    echo '</tr>';
                    $no++;
                }
            }
            ?>
        </tbody>
    </table>
</body>

</html>
