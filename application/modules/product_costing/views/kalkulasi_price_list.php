<div class="box box-primary">
    <div class="box-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="<?= base_url('product_costing/master_persentase') ?>" class="btn btn-warning mb-3">
                <i class="fa fa-cogs"></i> Kelola Persentase
            </a>

            <button id="btn-generate" class="btn btn-success">
                <i class="fa fa-refresh"></i> Generate Ulang Price List
            </button>
        </div>
        <div id="last-update" class="text-muted mb-2">
            <?php
            $last = $this->db->select_max('created_at')->get('master_kalkulasi_price_list')->row('created_at');
            if ($last) {
                echo "Terakhir di-generate: <strong>" . date('d/m/Y H:i', strtotime($last)) . "</strong>";
            }
            ?>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered table-striped">
            <thead class="bg-blue">
                <tr>
                    <th rowspan="2" class="text-center" style="vertical-align: middle;">Produk</th>
                    <?php foreach ($tokoList as $toko): ?>
                        <th colspan="2" class="text-center"><?= $toko['nama'] ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <?php foreach ($tokoList as $toko): ?>
                        <th class="text-center">Cash</th>
                        <th class="text-center">Tempo</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupedData as $product => $hargaPerToko): ?>
                    <tr>
                        <td><?= $product ?></td>
                        <?php foreach ($tokoList as $toko):
                            $harga = isset($hargaPerToko[$toko['nama']]) ? $hargaPerToko[$toko['nama']] : ['cash' => 0, 'tempo' => 0];
                        ?>
                            <td align="right"><?= number_format($harga['cash'], 0, ',', '.') ?></td>
                            <td align="right"><?= number_format($harga['tempo'], 0, ',', '.') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#btn-generate').click(function(e) {
            e.preventDefault();

            swal({
                title: "Generate ulang semua harga?",
                text: "Seluruh data kalkulasi sebelumnya akan dihapus dan dihitung ulang.",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ya, lanjutkan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: base_url + 'product_costing/generate_price_list_ajax',
                        type: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if (res.error) {
                                swal("Gagal!", res.message, "error");
                            } else {
                                swal({
                                    title: "Sukses!",
                                    text: res.message,
                                    type: "success",
                                    timer: 3000,
                                    showConfirmButton: false
                                }, function() {
                                    location.reload();
                                });

                                // Update last update info tanpa reload juga bisa
                                $('#last-update').html('Terakhir di-generate: <strong>' + formatDateTime(res.last_update) + '</strong>');
                            }
                        },
                        error: function() {
                            swal("Gagal!", "Terjadi kesalahan saat proses kalkulasi.", "error");
                        }
                    });
                } else {
                    swal("Dibatalkan", "Kalkulasi tidak dijalankan.", "error");
                    return false;
                }
            });
        });
    });

    function formatDateTime(str) {
        const d = new Date(str);
        return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID');
    }
</script>