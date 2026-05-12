<?php
// View ini di-load via AJAX ke dalam modal
// $item = null (tambah baru) atau array data (edit)
// $kode_baru = kode yang akan digunakan
$is_edit      = !empty($item);
$id           = $is_edit ? $item['id'] : 0;
$sudah_jurnal = false;

if ($is_edit) {
    $CI = &get_instance();
    $sudah_jurnal = $CI->db->get_where(
        'amortisasi_schedule',
        array('amortisasi_id' => $id, 'flag' => 'Y')
    )->num_rows() > 0;
}
?>

<form id="form-amortisasi" autocomplete="off">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="row">
        <!-- Kode (readonly) -->
        <div class="col-md-4">
            <div class="form-group">
                <label>Kode <small class="text-muted">(auto)</small></label>
                <input type="text" class="form-control" name="kode" value="<?= $kode_baru ?>" readonly>
            </div>
        </div>

        <!-- Nama Item -->
        <div class="col-md-8">
            <div class="form-group">
                <label>Nama Item / Kontrak <span class="text-red">*</span></label>
                <input type="text" class="form-control" name="nama_item"
                    placeholder="Contoh: Sewa Kantor Lantai 2, Premi Asuransi Kendaraan"
                    value="<?= $is_edit ? htmlspecialchars($item['nama_item']) : '' ?>" required>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Total Biaya -->
        <div class="col-md-4">
            <div class="form-group">
                <label>Total Biaya <span class="text-red">*</span></label>
                <div class="input-group">
                    <span class="input-group-addon">Rp</span>
                    <input type="text" class="form-control text-right" id="inp_total_debit" name="total_debit"
                        placeholder="0"
                        value="<?= $is_edit ? number_format($item['total_debit'], 0, '.', ',') : '' ?>"
                        <?= ($is_edit && $sudah_jurnal) ? 'readonly' : '' ?> required>
                </div>
                <?php if ($is_edit && $sudah_jurnal) : ?>
                    <small class="text-warning"><i class="fa fa-lock"></i> Sudah ada jurnal, nilai tidak bisa diubah.</small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tanggal Mulai -->
        <div class="col-md-4">
            <div class="form-group">
                <label>Tanggal Mulai <span class="text-red">*</span></label>
                <input type="text" class="form-control datepicker-form" id="inp_tgl_mulai" name="tgl_mulai"
                    placeholder="yyyy-mm-dd"
                    value="<?= $is_edit ? $item['tgl_mulai'] : '' ?>"
                    <?= ($is_edit && $sudah_jurnal) ? 'readonly' : '' ?> required>
                <small class="text-muted">Pro-rata otomatis jika mulai bukan tgl 1</small>
            </div>
        </div>

        <!-- Tanggal Selesai -->
        <div class="col-md-4">
            <div class="form-group">
                <label>Tanggal Selesai <span class="text-red">*</span></label>
                <input type="text" class="form-control datepicker-form" id="inp_tgl_selesai" name="tgl_selesai"
                    placeholder="yyyy-mm-dd"
                    value="<?= $is_edit ? $item['tgl_selesai'] : '' ?>"
                    <?= ($is_edit && $sudah_jurnal) ? 'readonly' : '' ?> required>
            </div>
        </div>
    </div>

    <!-- Preview perhitungan -->
    <div id="preview_hitung" class="alert alert-info" style="display:none; margin-bottom:10px;">
        <i class="fa fa-calculator"></i> <span id="preview_text"></span>
    </div>

    <hr style="margin:10px 0;">
    <p class="text-muted" style="margin-bottom:8px;">
        <i class="fa fa-info-circle"></i> <b>Mapping Akun Jurnal</b>
        &nbsp;<small>Jurnal bulanan: (D) Akun Biaya &nbsp;|&nbsp; (K) Akun Neraca/Biaya Dibayar Dimuka</small>
    </p>

    <div class="row">
        <!-- COA Biaya (Debit) -->
        <div class="col-md-6">
            <div class="form-group">
                <label>Akun Biaya <small class="text-muted">(Debit)</small> <span class="text-red">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="coa_debit" name="coa_debit"
                        placeholder="No. Perkiraan"
                        value="<?= $is_edit ? $item['coa_debit'] : '' ?>" readonly>
                    <span class="input-group-addon" style="min-width:150px; text-align:left;" id="nm_coa_debit_show">
                        <?= $is_edit ? $item['nm_coa_debit'] : '' ?>
                    </span>
                    <?php if (!($is_edit && $sudah_jurnal)) : ?>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-coa-picker" data-target="biaya">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    <?php endif; ?>
                </div>
                <input type="hidden" id="nm_coa_debit" name="nm_coa_debit" value="<?= $is_edit ? $item['nm_coa_debit'] : '' ?>">
            </div>
        </div>

        <!-- COA Neraca (Kredit) -->
        <div class="col-md-6">
            <div class="form-group">
                <label>Akun Neraca / Biaya Dibayar Dimuka <small class="text-muted">(Kredit)</small> <span class="text-red">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="coa_kredit" name="coa_kredit"
                        placeholder="No. Perkiraan"
                        value="<?= $is_edit ? $item['coa_kredit'] : '' ?>" readonly>
                    <span class="input-group-addon" style="min-width:150px; text-align:left;" id="nm_coa_kredit_show">
                        <?= $is_edit ? $item['nm_coa_kredit'] : '' ?>
                    </span>
                    <?php if (!($is_edit && $sudah_jurnal)) : ?>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default btn-coa-picker" data-target="neraca">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    <?php endif; ?>
                </div>
                <input type="hidden" id="nm_coa_kredit" name="nm_coa_kredit" value="<?= $is_edit ? $item['nm_coa_kredit'] : '' ?>">
            </div>
        </div>
    </div>

    <!-- Keterangan -->
    <div class="form-group">
        <label>Keterangan <small class="text-muted">(opsional)</small></label>
        <textarea class="form-control" name="keterangan" rows="2"
            placeholder="Catatan tambahan..."><?= $is_edit ? htmlspecialchars($item['keterangan']) : '' ?></textarea>
    </div>

    <div class="modal-footer" style="padding:10px 0 0 0; border-top:1px solid #eee; margin-top:10px;">
        <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-times"></i> Batal
        </button>
        <button type="submit" class="btn btn-success" id="btn-simpan-form">
            <i class="fa fa-save"></i> Simpan
        </button>
    </div>
</form>

<!-- Modal COA Picker (sama persis dengan pola di add_category.php) -->
<div class="modal fade" id="ModalCOA" tabindex="-1">
    <div class="modal-dialog" style="width:60%;">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-search"></i> Pilih COA</h4>
            </div>
            <div class="modal-body">
                <input type="text" id="coa_search" class="form-control" placeholder="Cari no. perkiraan atau nama COA...">
                <br>
                <div style="max-height:350px; overflow-y:auto;">
                    <table class="table table-bordered table-hover table-striped" id="tbl_coa_picker">
                        <thead>
                            <tr class="bg-blue">
                                <th>No. Perkiraan</th>
                                <th>Nama COA</th>
                            </tr>
                        </thead>
                        <tbody id="coa_list_body">
                            <tr>
                                <td colspan="2" class="text-center">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #tbl_coa_picker tbody tr {
        cursor: pointer;
    }

    #tbl_coa_picker tbody tr:hover {
        background-color: #d9edf7 !important;
    }
</style>

<script>
    var _coa_target = 'biaya';
    var _coa_all = [];

    // Datepicker – format yyyy-mm-dd langsung (sesuai format DB)
    $('.datepicker-form').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function() {
        hitungPreview();
    });

    // Format angka
    $('#inp_total_debit').on('blur', function() {
        var val = parseFloat($(this).val().replace(/,/g, '')) || 0;
        $(this).val(val > 0 ? val.toLocaleString('en-US') : '');
        hitungPreview();
    });

    // Trigger preview saat tanggal berubah manual
    $('#inp_tgl_mulai, #inp_tgl_selesai').on('change', function() {
        hitungPreview();
    });

    // Preview perhitungan otomatis
    function hitungPreview() {
        var total = parseFloat($('#inp_total_debit').val().replace(/,/g, '')) || 0;
        var mulai = $('#inp_tgl_mulai').val();
        var selesai = $('#inp_tgl_selesai').val();

        if (total <= 0 || !mulai || !selesai) {
            $('#preview_hitung').hide();
            return;
        }

        var d1 = new Date(mulai);
        var d2 = new Date(selesai);
        if (isNaN(d1.getTime()) || isNaN(d2.getTime()) || d2 <= d1) {
            $('#preview_hitung').hide();
            return;
        }

        var totalBulan = (d2.getFullYear() - d1.getFullYear()) * 12 + (d2.getMonth() - d1.getMonth()) + 1;
        var perBulan = Math.round(total / totalBulan);
        var hariMulai = d1.getDate();
        var hariDlmBln = new Date(d1.getFullYear(), d1.getMonth() + 1, 0).getDate();
        var proRata = (hariMulai == 1) ?
            perBulan :
            Math.round(perBulan * (hariDlmBln - hariMulai + 1) / hariDlmBln);

        var txt = '<b>' + totalBulan + ' bulan</b> &nbsp;|&nbsp; Per bulan: <b>Rp ' + perBulan.toLocaleString('en-US') + '</b>';
        if (hariMulai != 1) {
            txt += ' &nbsp;|&nbsp; Bulan pertama (pro-rata tgl ' + hariMulai + '): <b>Rp ' + proRata.toLocaleString('en-US') + '</b>';
        }

        $('#preview_text').html(txt);
        $('#preview_hitung').show();
    }

    // Jalankan preview jika edit (data sudah ada)
    hitungPreview();

    // -----------------------------------------------------------------------
    // COA Picker – sama persis dengan pola add_category.php
    // -----------------------------------------------------------------------
    function loadCOA() {
        $.ajax({
            url: base_url + 'index.php/' + active_controller + '/get_coa_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                _coa_all = res;
                renderCOA(res);
            },
            error: function() {
                $('#coa_list_body').html('<tr><td colspan="2" class="text-center text-danger">Gagal memuat data COA.</td></tr>');
            }
        });
    }

    function renderCOA(data) {
        var html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="2" class="text-center">Data tidak ditemukan</td></tr>';
        } else {
            $.each(data, function(i, row) {
                html += '<tr data-no="' + row.no_perkiraan + '" data-nama="' + row.nama + '">' +
                    '<td>' + row.no_perkiraan + '</td>' +
                    '<td>' + row.nama + '</td>' +
                    '</tr>';
            });
        }
        $('#coa_list_body').html(html);
    }

    $(document).on('click', '.btn-coa-picker', function() {
        _coa_target = $(this).data('target');
        $('#coa_search').val('');
        if (_coa_all.length === 0) {
            loadCOA();
        } else {
            renderCOA(_coa_all);
        }
        $('#ModalCOA').modal('show');
    });

    $(document).on('keyup', '#coa_search', function() {
        var q = $(this).val().toLowerCase();
        var filtered = _coa_all.filter(function(r) {
            return r.no_perkiraan.toLowerCase().indexOf(q) >= 0 ||
                r.nama.toLowerCase().indexOf(q) >= 0;
        });
        renderCOA(filtered);
    });

    $(document).on('click', '#coa_list_body tr', function() {
        var no = $(this).data('no');
        var nama = $(this).data('nama');
        if (_coa_target === 'biaya') {
            $('#coa_debit').val(no);
            $('#nm_coa_debit').val(nama);
            $('#nm_coa_debit_show').text(nama);
        } else {
            $('#coa_kredit').val(no);
            $('#nm_coa_kredit').val(nama);
            $('#nm_coa_kredit_show').text(nama);
        }
        $('#ModalCOA').modal('hide');
    });
</script>