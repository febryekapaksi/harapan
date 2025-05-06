<div class="box box-primary">
    <div class="box-body">
        <form id="form-komisi">
            <table class="table table-bordered" id="table-komisi">
                <thead class="bg-blue">
                    <tr>
                        <th class="text-center" width="30%">Dari</th>
                        <th class="text-center" width="30%">Sampai</th>
                        <th class="text-center" width="30%">Koefisien</th>
                        <th class="text-center" width="10%">
                            <button type="button" id="add-row" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Add</button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($komisi as $i => $row): ?>
                        <tr>
                            <td><input type="number" step="0.01" name="data[<?= $i ?>][dari]" value="<?= $row['dari'] ?>" class="form-control text-center" /></td>
                            <td><input type="number" step="0.01" name="data[<?= $i ?>][sampai]" value="<?= $row['sampai'] ?>" class="form-control text-center" /></td>
                            <td><input type="number" step="0.01" name="data[<?= $i ?>][koefisien]" value="<?= $row['koefisien'] ?>" class="form-control text-center" /></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>

            <input type="hidden" name="komisi_type" value="<?= $mode ?>">
            <button type="submit" id="save" class="btn btn-success"><i class="fa fa-save"></i> Simpan</button>
        </form>
    </div>
</div>


<script>
    $(document).ready(function() {
        let rowIndex = <?= count($komisi) ?>;

        $('#add-row').click(function() {
            let html = `
          <tr>
            <td><input type="number" step="0.01" name="data[${rowIndex}][dari]" class="form-control text-center" /></td>
            <td><input type="number" step="0.01" name="data[${rowIndex}][sampai]" class="form-control text-center" /></td>
            <td><input type="number" step="0.01" name="data[${rowIndex}][koefisien]" class="form-control text-center" /></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="fa fa-trash"></i></button></td>
        </tr>
    `;
            $('#table-komisi tbody').append(html);
            rowIndex++;
        });

        $(document).on('click', '.btn-remove', function() {
            $(this).closest('tr').remove();
        });

        $('#form-komisi').on('submit', function(e) {
            e.preventDefault();

            swal({
                title: "Simpan perubahan?",
                text: "Perubahan akan langsung tersimpan.",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Ya, Simpan!",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    const formData = new FormData($('#form-komisi')[0]);
                    $.ajax({
                        url: "<?= base_url('master_komisi/save') ?>",
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
                                    window.location.reload()

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