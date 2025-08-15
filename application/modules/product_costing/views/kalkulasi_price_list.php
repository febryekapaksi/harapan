<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

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
        <div class="mb-3">
            <label for="filter_kategori">Filter Kategori:</label>
            <select id="filter_kategori" class="form-control" style="width: 250px;">
                <option value="">-- Semua Kategori --</option>
                <?php foreach ($kategoriList as $kategori): ?>
                    <option value="<?= $kategori['code_lv2'] ?>"><?= $kategori['nama'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="box-body">
        <table id="priceListTable" class="table table-bordered table-striped">
            <thead class="bg-blue">
                <tr>
                    <th rowspan="2" class="text-center" style="vertical-align: middle; min-width: 250px">Produk</th>
                    <th colspan="2" class="text-center" style="vertical-align: middle;">Dropship</th>
                    <?php foreach ($tokoList as $toko): ?>
                        <th colspan="2" class="text-center"><?= $toko['nama'] ?></th>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <th class="text-center">Cash</th>
                    <th class="text-center">Tempo</th>
                    <?php foreach ($tokoList as $toko): ?>
                        <th class="text-center">Cash</th>
                        <th class="text-center">Tempo</th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groupedData as $product => $hargaPerToko): ?>
                    <?php
                    $productData = $this->db
                        ->select('code_lv2')
                        ->where('nama', $product)
                        ->get('new_inventory_4')
                        ->row();
                    $code_lv2 = $productData ? $productData->code_lv2 : '';
                    ?>
                    <tr data-code_lv2="<?= $code_lv2 ?>">
                        <td><?= $product ?></td>
                        <td align="right">
                            <?= isset($hargaPerToko['dropship_price']) ? number_format($hargaPerToko['dropship_price'], 0, ',', '.') : '-' ?>
                        </td>
                        <td align="right">
                            <?= isset($hargaPerToko['dropship_tempo']) ? number_format($hargaPerToko['dropship_tempo'], 0, ',', '.') : '-' ?>
                        </td>
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
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        var table = $('#priceListTable').DataTable({
            scrollX: true,
            ordering: false
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex, rowData, counter) {
            var selectedCode = $('#filter_kategori').val();
            var rowCodeLv2 = $(table.row(dataIndex).node()).data('code_lv2');

            if (!selectedCode || rowCodeLv2 == selectedCode) {
                return true;
            }
            return false;
        });

        $('#filter_kategori').on('change', function() {
            table.draw(); // trigger ulang filter
        });

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