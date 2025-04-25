<div class="box box-primary">
    <div class="box-body">
        <form id="form-persentase">
            <table class="table table-bordered" id="table-persentase">
                <thead>
                    <tr>
                        <th width="75px">Urutan</th>
                        <th>Nama Toko</th>
                        <th>Cash (%)</th>
                        <th>Tempo (%)</th>
                        <th width="50px">
                            <button type="button" id="add-row" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Add</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($persentase as $i => $row): ?>
                        <tr>
                            <td><input type="number" name="data[<?= $i ?>][urutan]" class="form-control" value="<?= $row['urutan'] ?>"></td>
                            <td>
                                <input type="text" name="data[<?= $i ?>][nama]" class="form-control" value="<?= $row['nama'] ?>">
                                <input type="hidden" name="data[<?= $i ?>][id]" value="<?= $row['id'] ?>">
                            </td>
                            <td><input type="number" name="data[<?= $i ?>][cash]" class="form-control" value="<?= $row['cash'] ?>"></td>
                            <td><input type="number" name="data[<?= $i ?>][tempo]" class="form-control" value="<?= $row['tempo'] ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" id="save" class="btn btn-success"><i class="fa fa-save"></i> Simpan Semua</button>
            <a onclick="window.history.back(); return false;" class="btn btn-default">Kembali</a>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        let rowIndex = <?= count($persentase) ?>;

        $('#add-row').click(function() {
            let html = `
        <tr>
            <td><input type="number" name="data[new_${rowIndex}][urutan]" class="form-control"></td>
            <td><input type="text" name="data[new_${rowIndex}][nama]" class="form-control"></td>
            <td><input type="number" name="data[new_${rowIndex}][cash]" class="form-control"></td>
            <td><input type="number" name="data[new_${rowIndex}][tempo]" class="form-control"></td>
            <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fa fa-trash"></i></button></td>
        </tr>
    `;
            $('#table-persentase tbody').append(html);
            rowIndex++;
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
        });

        $('#form-persentase').on('submit', function(e) {
            e.preventDefault();

            swal({
                title: "Simpan perubahan?",
                text: "Perubahan akan langsung tersimpan permanen.",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    const formData = new FormData($('#form-persentase')[0]);
                    $.ajax({
                        url: "<?= base_url('product_costing/save_persentase') ?>",
                        type: "POST",
                        data: formData,
                        cache: false,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 1) {
                                swal({
                                    title: "Berhasil!",
                                    text: res.message,
                                    type: "success",
                                    timer: 3000,
                                    showConfirmButton: false
                                }, function() {
                                    window.location.href = "<?= base_url('product_costing/list_price_list') ?>";

                                });
                            } else {
                                swal("Gagal!", res.message, "error");
                            }
                        },
                        error: function() {
                            swal("Gagal!", "Terjadi kesalahan saat menyimpan.", "error");
                        }
                    });
                }
            });
        });
    });
</script>