<?php
$dept = '';
$bank_id = '';
$accnumber = '';
$accname = '';
if (!isset($data->departement)) {
    $datauser = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();
    $datadept = $this->db->get_where('employee', ['id' => $datauser->employee_id])->row();
    if (!empty($datadept)) {
        $dept = $datauser->department_id;
        $bank_id = $datadept->bank_id;
        $accnumber = $datadept->accnumber;
        $accname = $datadept->accname;
    }
}

$data_user = $this->db->get_where('users', ['id_user' => $this->auth->user_id()])->row();

$metode_pembayaran = (isset($data)) ? $data->metode_pembayaran : 1;



?>

<style>
	.kasbon-section-card {
		background: #fff;
		border: 1px solid #d2d6de;
		border-radius: 4px;
		margin-bottom: 20px;
		box-shadow: 0 1px 3px rgba(0,0,0,0.05);
	}
	.kasbon-section-header {
		background: #f8fafc;
		border-bottom: 1px solid #e9ecef;
		padding: 10px 15px;
		font-size: 14px;
		font-weight: 600;
		color: #337ab7;
		border-top-left-radius: 4px;
		border-top-right-radius: 4px;
	}
	.kasbon-section-body {
		padding: 15px;
	}
	.form-label-custom {
		font-size: 13px;
		font-weight: 600;
		color: #444;
		margin-bottom: 5px;
	}
	.table-pr-custom thead th {
		background-color: #3c8dbc;
		color: #ffffff;
		text-align: center;
		vertical-align: middle;
		border: 1px solid #367fa9;
	}
	.table-pr-custom tbody td {
		vertical-align: middle;
	}
	.table-pr-custom tfoot th {
		background-color: #f4f6f9;
		vertical-align: middle;
	}
	.grand_total_non_pr {
		font-size: 15px;
		font-weight: bold;
		color: #00a65a;
	}
	.doc-preview-card {
		border: 1px solid #d2d6de;
		border-radius: 4px;
		padding: 10px;
		background: #fafafa;
		margin-top: 10px;
	}
</style>

<form action="" id="frm_data" enctype="multipart/form-data">
    <input type="hidden" id="id" name="id" value="<?php echo set_value('id', isset($data->id) ? $data->id : ''); ?>">
    <input type="hidden" id="departement" name="departement" value="<?php echo ($data_user->department_id) ?>">
    <input type="hidden" id="nama" name="nama" value="<?php echo (isset($data->nama) ? $data->nama : $this->auth->user_name()); ?>">
    <input type="hidden" name="" class="stsview" value="<?= (isset($stsview)) ? $stsview : null ?>">

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">
                <i class="fa fa-eye"></i> Detail Pengajuan Kasbon
            </h3>
        </div>

        <div class="box-body" style="padding: 20px;">
            <?php if (isset($data->st_reject) && $data->st_reject !== ''): ?>
                <div class="alert alert-danger alert-dismissible" style="border-radius: 4px;">
                    <h4><i class="icon fa fa-ban"></i> Alasan Penolakan!</h4>
                    <?= $data->st_reject; ?>
                </div>
            <?php endif; ?>

            <!-- SECTION 1: INFORMASI UTAMA KASBON -->
            <div class="kasbon-section-card">
                <div class="kasbon-section-header">
                    <i class="fa fa-info-circle"></i> Informasi Pengajuan Kasbon
                </div>
                <div class="kasbon-section-body">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">No Dokumen</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-file-text-o"></i></span>
                                    <input type="text" class="form-control" id="no_doc" name="no_doc" value="<?php echo (isset($data->no_doc) ? $data->no_doc : ""); ?>" placeholder="Automatic" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label-custom">Keperluan <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
                                    <input type="text" class="form-control" id="keperluan" name="keperluan" value="<?php echo (isset($data->keperluan) ? $data->keperluan : ''); ?>" placeholder="Keperluan Kasbon" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label-custom">Keterangan <span class="text-red">*</span></label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan Kasbon" required><?php echo (isset($data->keterangan) ? $data->keterangan : ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">Tanggal <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control tanggal" id="tgl_doc" name="tgl_doc" value="<?php echo (isset($data->tgl_doc) ? $data->tgl_doc : date("Y-m-d")); ?>" placeholder="Tanggal Dokumen" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label-custom">Nominal Kasbon <span class="text-red">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><b>Rp</b></span>
                                    <input type="text" class="form-control divide text-right" id="jumlah_kasbon" name="jumlah_kasbon" value="<?php echo (isset($data->jumlah_kasbon) ? $data->jumlah_kasbon : '0'); ?>" placeholder="Nominal Kasbon" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label-custom">COA <span class="text-red">*</span></label>
                                <select name="coa" class="form-control chosen_select" id="coa" required>
                                    <option value="" disabled selected>- Pilih COA -</option>
                                    <?php foreach ($list_coa_kasbon as $c): ?>
                                        <option value="<?= $c->no_perkiraan ?>" <?= (isset($data->coa) && $data->coa == $c->no_perkiraan) ? 'selected' : '' ?>>
                                            <?= $c->no_perkiraan . ' - ' . $c->nama; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Dokumen -->
                    <div class="row" style="margin-top: 10px; padding-top: 15px; border-top: 1px dashed #e9ecef;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">Dokumen 1 (Lampiran Utama)</label>
                                <input type="hidden" name="filename" id="filename" value="<?= (isset($data->doc_file) ? $data->doc_file : ''); ?>">
                                <input type="file" name="doc_file" id="doc_file" class="form-control" style="height: auto; padding: 6px 12px;">
                                <?php if (isset($data->doc_file) && $data->doc_file != ''): ?>
                                    <div style="margin-top: 5px;">
                                        <a href="<?= base_url('uploads/expense/' . $data->doc_file) ?>" download target="_blank" class="btn btn-xs btn-info">
                                            <i class="fa fa-download"></i> Unduh Dokumen 1 (<?= $data->doc_file ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">Dokumen 2 (Lampiran Tambahan)</label>
                                <input type="hidden" name="filename2" id="filename2" value="<?= (isset($data->doc_file_2) ? $data->doc_file_2 : ''); ?>">
                                <input type="file" name="doc_file_2" id="doc_file_2" class="form-control" style="height: auto; padding: 6px 12px;">
                                <?php if (isset($data->doc_file_2) && $data->doc_file_2 != ''): ?>
                                    <div style="margin-top: 5px;">
                                        <a href="<?= base_url('uploads/expense/' . $data->doc_file_2) ?>" download target="_blank" class="btn btn-xs btn-info">
                                            <i class="fa fa-download"></i> Unduh Dokumen 2 (<?= $data->doc_file_2 ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: REKENING BANK TRANSFER -->
            <div class="kasbon-section-card transfer_ke_cont">
                <div class="kasbon-section-header">
                    <i class="fa fa-credit-card"></i> Rekening Tujuan Transfer
                </div>
                <div class="kasbon-section-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label-custom">Nama Bank</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-university"></i></span>
                                    <input type="text" class="form-control" id="bank_id" name="bank_id" value="<?php echo (isset($data->bank_id) ? $data->bank_id : $bank_id); ?>" placeholder="Nama Bank">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label-custom">Nomor Rekening</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" class="form-control" id="accnumber" name="accnumber" value="<?php echo (isset($data->accnumber) ? $data->accnumber : $accnumber); ?>" placeholder="Nomor Rekening">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label-custom">Nama Pemilik Rekening</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control" id="accname" name="accname" value="<?php echo (isset($data->accname) ? $data->accname : $accname); ?>" placeholder="Nama Pemilik Rekening">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: KASBON PR NON PO -->
            <div class="kasbon-section-card">
                <div class="kasbon-section-header">
                    <i class="fa fa-shopping-cart"></i> Detail Kasbon PR Non PO
                </div>
                <div class="kasbon-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label-custom">No. PR Non PO</label>
                                <?php if (isset($data->id_pr)): ?>
                                    <input type="text" name="no_pr" id="search_pr_non_po" class="form-control" placeholder="- No PR -" value="<?= (isset($data->id_pr)) ? $data->id_pr : null ?>" readonly>
                                <?php else: ?>
                                    <select name="no_pr" id="search_pr_non_po" class="form-control chosen_select">
                                        <option value="">- Pilih No PR -</option>
                                        <?php foreach ($list_pr_non_po as $item_pr_non_po): ?>
                                            <option value="<?= $item_pr_non_po['no_pr'] ?>"><?= $item_pr_non_po['no_pr'] . ' - ' . $item_pr_non_po['keterangan'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="file_name" class="file_name">
                                    <input type="hidden" name="doc_pr" class="doc_pr">
                                    <input type="hidden" name="to_doc_pr" class="to_doc_pr">
                                <?php endif; ?>
                                <input type="hidden" name="tipe_pr" id="tipe_pr" value="<?= (isset($list_detail_pr_kasbon[0]['tipe_pr'])) ? $list_detail_pr_kasbon[0]['tipe_pr'] : null ?>">
                                <span class="help-block text-muted" style="font-size: 12px; margin-top: 4px;"><i class="fa fa-info-circle"></i> Pilih No. PR untuk memuat daftar material</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="margin-top: 10px;">
                        <table class="table table-bordered table-striped table-hover table-pr-custom">
                            <thead>
                                <tr>
                                    <th width="5%">No.</th>
                                    <th width="30%">Material Name</th>
                                    <th width="10%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="20%">Harga Satuan</th>
                                    <th width="20%">Total</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list_barang_pr">
                                <?php
                                $grand_total_non_pr = 0;
                                if (isset($list_detail_pr_kasbon)) {
                                    $no = 1;
                                    foreach ($list_detail_pr_kasbon as $detail_pr) :
                                        $readonly = '';
                                        if (($mod == '_fin' || $mod == '_mgt')) {
                                            $readonly = 'readonly';
                                        }
                                        echo '<tr class="detail_pr_' . $detail_pr['id'] . '">';
                                        echo '<td class="text-center">' . $no . '</td>';
                                        echo '<td>' . $detail_pr['nm_material'] . '</td>';
                                        echo '<td class="text-center">' . number_format($detail_pr['qty']) . ' <input type="hidden" class="qty_' . $detail_pr['id'] . '" value="' . $detail_pr['qty'] . '"></td>';
                                        echo '<td class="text-center">' . $detail_pr['satuan'] . '</td>';
                                        echo '<td class="text-center"><input type="text" name="price_input_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right price_input price_input_' . $detail_pr['id'] . ' autonum" data-no="' . $detail_pr['id'] . '" value="' . $detail_pr['harga'] . '" ' . $readonly . '></td>';
                                        echo '<td class="text-center"><input type="text" name="grand_total_' . $detail_pr['id'] . '" class="form-control form-control-sm text-right grand_total_' . $detail_pr['id'] . ' autonum" value="' . $detail_pr['total_harga'] . '" ' . $readonly . '></td>';
                                        echo '<td class="text-center">';
                                        if (!($mod == '_fin' || $mod == '_mgt')) {
                                            if (!isset($stsview) || $stsview == '') {
                                                echo '<button type="button" class="btn btn-xs btn-danger del_detail" data-no="' . $detail_pr['id'] . '" title="Hapus Item"><i class="fa fa-trash"></i></button>';
                                            }
                                        }
                                        echo '</td>';
                                        echo '</tr>';

                                        $grand_total_non_pr += $detail_pr['total_harga'];
                                        $no++;
                                    endforeach;
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right" colspan="5" style="font-size: 14px;">Grand Total :</th>
                                    <th class="text-right grand_total_non_pr"><?= number_format($grand_total_non_pr, 2) ?></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: PREVIEW DOKUMEN (IF AVAILABLE) -->
            <?php if (isset($data)): ?>
                <?php if (!empty($data->doc_file) || !empty($data->doc_file_2)): ?>
                    <div class="kasbon-section-card">
                        <div class="kasbon-section-header">
                            <i class="fa fa-paperclip"></i> Preview Lampiran Dokumen
                        </div>
                        <div class="kasbon-section-body">
                            <div class="row">
                                <?php if (!empty($data->doc_file)): ?>
                                    <div class="col-md-6">
                                        <div class="doc-preview-card">
                                            <p><b>Dokumen 1:</b> <?= $data->doc_file ?></p>
                                            <?php if (strpos($data->doc_file, 'pdf') !== false): ?>
                                                <iframe src="<?= base_url('uploads/expense/' . $data->doc_file) ?>#toolbar=0&navpanes=0" style="width:100%; height:380px; border:1px solid #ddd;" frameborder="0"></iframe>
                                            <?php else: ?>
                                                <a href="<?= base_url('uploads/expense/' . $data->doc_file) ?>" target="_blank">
                                                    <img src="<?= base_url('uploads/expense/' . $data->doc_file) ?>" class="img-responsive img-thumbnail" style="max-height: 380px; margin: 0 auto;">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($data->doc_file_2)): ?>
                                    <div class="col-md-6">
                                        <div class="doc-preview-card">
                                            <p><b>Dokumen 2:</b> <?= $data->doc_file_2 ?></p>
                                            <?php if (strpos($data->doc_file_2, 'pdf') !== false): ?>
                                                <iframe src="<?= base_url('uploads/expense/' . $data->doc_file_2) ?>#toolbar=0&navpanes=0" style="width:100%; height:380px; border:1px solid #ddd;" frameborder="0"></iframe>
                                            <?php else: ?>
                                                <a href="<?= base_url('uploads/expense/' . $data->doc_file_2) ?>" target="_blank">
                                                    <img src="<?= base_url('uploads/expense/' . $data->doc_file_2) ?>" class="img-responsive img-thumbnail" style="max-height: 380px; margin: 0 auto;">
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- BOX FOOTER -->
        <div class="box-footer" style="padding: 15px 20px; background-color: #f9fafb; border-top: 1px solid #eee;">
            <div class="pull-right">
                <?php
                if (isset($data)) {
                    if (($data->status == 0 || $data->status == 1) && $stsview == '') {
                        if (($mod == '_fin' || $mod == '_mgt')) {
                            echo '<a class="btn btn-primary btn-flat" href="#" id="approve" onclick="data_approve(' . $data->id . ',' . ($data->status + 1) . ')"><i class="fa fa-check-square-o"></i> Setujui (Approve)</a> ';
                            echo '<a class="btn btn-danger btn-flat" onclick="data_reject()"><i class="fa fa-ban"></i> Tolak (Reject)</a> ';
                            $stsview = 'view';
                        } else {
                            echo '<button type="submit" name="save" class="btn btn-success btn-flat stsview" id="submit"><i class="fa fa-save"></i> Simpan Data</button> ';
                        }
                    } else {
                        echo '<button type="submit" name="save" class="btn btn-success btn-flat stsview" id="submit"><i class="fa fa-save"></i> Simpan Data</button> ';
                    }
                } else {
                    echo '<button type="submit" name="save" class="btn btn-success btn-flat stsview" id="submit"><i class="fa fa-save"></i> Simpan Data</button> ';
                }
                ?>
                <a class="btn btn-default btn-flat" onclick="window.history.back(); return false;"><i class="fa fa-reply"></i> Kembali</a>
            </div>
        </div>
    </div>
</form>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script type="text/javascript">
    var url_save = siteurl + 'expense/kasbon_save/';
    var url_approve = siteurl + 'expense/kasbon_approve/';

    var mod = '<?= $mod ?>';
    if (mod !== '') {
        $('input').attr('readonly', true);
        $('textarea').attr('readonly', true);
        $('input[type="file"]').prop('disabled', true);
    }

    $('.divide').divide();

    $('.autonum').autoNumeric('init');
    $('.chosen_select').chosen({
        width: '100%'
    });

    var stsview = $('.stsview').val();
    if (stsview == 'view') {
        $(".stsview").addClass("hidden");
        $("#frm_data :input").prop("disabled", true);
    }

    $(document).on('change', '.metode_pembayaran', function() {
        var metode_pembayaran = $(this).val();

        if (metode_pembayaran == 1) {
            $('.transfer_ke_cont').show();

            $('#bank_id').prop('required', true);
            $('#accnumber').prop('required', true);
            $('#accname').prop('required', true);
        } else {
            $('.transfer_ke_cont').hide();

            $('#bank_id').prop('required', false);
            $('#accnumber').prop('required', false);
            $('#accname').prop('required', false);
        }
    });


    $('#frm_data').on('submit', function(e) {
        e.preventDefault();
        var errors = "";
        if ($("#filename").val() == "") {
            if ($('#doc_file').get(0).files.length === 0) {
                errors = "Dokumen 1 harus diupload";
            }
        }

        // var metode_pembayaran = $('.metode_pembayaran').val();

        if ($("#jumlah_kasbon").val() == "0") errors = "Jumlah Kasbon tidak boleh kosong";
        if ($("#keperluan").val() == "") errors = "keperluan tidak boleh kosong";
        if ($("#tgl_doc").val() == "") errors = "Tanggal Transaksi tidak boleh kosong";
        // if (metode_pembayaran == "") errors = "Pilih metode pembayaran";
        // if (metode_pembayaran == 1) {
        // 	var bank_id = $('#bank_id').val();
        // 	var accnumber = $('#accnumber').val();
        // 	var accname = $('#accname').val();

        // 	if (bank_id == '' || accnumber == '' || accname == '') {
        // 		errors = "Pastikan data transfer terisi";
        // 	}
        // }

        var price_no_input = 0;
        $('.price_input').each(function() {
            var value = parseFloat($(this).val());
            if (isNaN(value)) {
                price_no_input += 1;
            }
        });

        if (price_no_input > 0) {
            errors = "Please make sure all material price is filled !";
        }
        if (errors == "") {
            swal({
                    title: "Anda Yakin?",
                    text: "Data Akan Disimpan!",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "Ya, simpan!",
                    cancelButtonText: "Tidak!",
                    closeOnConfirm: false,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formdata = new FormData($('#frm_data')[0]);
                        $.ajax({
                            url: url_save,
                            dataType: "json",
                            type: 'POST',
                            data: formdata,
                            processData: false,
                            contentType: false,
                            success: function(msg) {
                                if (msg['save'] == '1') {
                                    swal({
                                        title: "Sukses!",
                                        text: "Data Berhasil Di Simpan",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    window.location = siteurl + 'expense/kasbon';
                                } else {
                                    swal({
                                        title: "Gagal!",
                                        text: "Data Gagal Di Simpan",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                };

                            },
                            error: function(msg) {
                                swal({
                                    title: "Gagal!",
                                    text: "Ajax Data Gagal Di Proses",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                            }
                        });
                    }
                });

            //			data_save();
        } else {
            swal({
                title: 'Error !',
                text: errors,
                type: 'error'
            });
        }
    });

    function number_format(number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function(n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    $(document).on('change', '#search_pr_non_po', function(e) {
        // e.preventDefault();
        const no_pr = $(this).val();

        // if (e.keyCode == '13') {

        // } else {
        // 	$('#search_pr_non_po').val(no_pr);
        // }

        $.ajax({
            type: "POST",
            url: siteurl + active_controller + 'get_pr_non_po',
            data: {
                'no_pr': no_pr
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result.sts == '1') {
                    $('.list_barang_pr').html(result.hasil);
                    $('#tipe_pr').val(result.tipe_pr);
                    $('.autonum').autoNumeric();
                    $('.grand_total_non_pr').html(number_format(result.grand_total, 2));
                } else {
                    swal({
                        title: 'Error !',
                        text: result.pesan,
                        type: 'error'
                    });
                }
            },
            error: function(result) {
                swal({
                    title: 'Error !',
                    text: 'Error occured, please try again later !',
                    type: 'error'
                });
            }
        });
    });

    $(document).on('click', '.del_detail', function() {
        var no = $(this).data('no');

        $('.detail_pr_' + no).remove();
    });

    $(document).on('change', '.price_input', function() {
        var no = $(this).data('no');
        var nilai = $(this).val();
        if (nilai == null || nilai == '') {
            var nilai = 0;
        } else {
            var nilai = nilai.split(',').join('');
            nilai = parseFloat(nilai);
        }

        var qty = $('.qty_' + no).val();

        // alert(nilai);
        // alert(qty);

        var total = parseFloat(nilai * qty);

        $('.grand_total_' + no).autoNumeric('set', total);

        hitung_grand_total_non_pr();
    })

    function getNum(val) {
        if (isNaN(val) || val == '') {
            return 0;
        }
        return parseFloat(val);
    }

    function hitung_grand_total_non_pr() {
        var grand_total = 0;
        $('.price_input').each(function() {
            var value = $(this).val();
            value = value.replace(/,/g, '');
            value = parseFloat(value);

            var no = $(this).data('no');
            var qty = $('.qty_' + no).val();

            grand_total += (value * qty);
        });

        $('.grand_total_non_pr').html(number_format(grand_total, 2));
    }

    $(function() {
        $(".tanggal").datepicker({
            todayHighlight: true,
            format: "yyyy-mm-dd",
            showInputs: true,
            autoclose: true
        });
    });

    function data_approve() {
        swal({
                title: "Anda Yakin?",
                text: "Data Akan Diupdate!",
                type: "info",
                showCancelButton: true,
                confirmButtonText: "Ya, setuju!",
                cancelButtonText: "Tidak!",
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(isConfirm) {
                if (isConfirm) {
                    id = $("#id").val();
                    $.ajax({
                        url: url_approve + id,
                        dataType: "json",
                        type: 'POST',
                        success: function(msg) {
                            if (msg['save'] == '1') {
                                swal({
                                    title: "Sukses!",
                                    text: "Data Berhasil Di Update",
                                    type: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                window.location = siteurl + 'expense/kasbon<?= $mod ?>';
                            } else {
                                swal({
                                    title: "Gagal!",
                                    text: "Data Gagal Di Update",
                                    type: "error",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            };

                        },
                        error: function(msg) {
                            swal({
                                title: "Gagal!",
                                text: "Ajax Data Gagal Di Proses",
                                type: "error",
                                timer: 1500,
                                showConfirmButton: false
                            });

                        }
                    });
                }
            });
    }

    function data_reject() {
        swal({
                title: "Perhatian",
                text: "Berikan alasan penolakan",
                type: "input",
                showCancelButton: true,
                closeOnConfirm: false,
                closeOnCancel: true
            },
            function(inputValue) {
                if (inputValue === false) return false;
                if (inputValue === "") {
                    swal.showInputError("Tuliskan alasan anda");
                    return false
                }

                swal({
                        title: "Anda Yakin?",
                        text: "Data Akan Tolak!",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Ya, tolak!",
                        cancelButtonText: "Tidak!",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            id = $("#id").val();
                            $.ajax({
                                url: base_url + 'expense/reject/',
                                data: {
                                    'id': id,
                                    'reason': inputValue,
                                    'table': 'tr_kasbon'
                                },
                                dataType: "json",
                                type: 'POST',
                                success: function(msg) {
                                    if (msg['save'] == '1') {
                                        swal({
                                            title: "Sukses!",
                                            text: "Data Berhasil Di Tolak",
                                            type: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        window.location = siteurl + 'expense/kasbon<?= $mod ?>'
                                    } else {
                                        swal({
                                            title: "Gagal!",
                                            text: "Data Gagal Di Tolak",
                                            type: "error",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                    };
                                    console.log(msg);
                                },
                                error: function(msg) {
                                    swal({
                                        title: "Gagal!",
                                        text: "Ajax Data Gagal Di Proses",
                                        type: "error",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    console.log(msg);
                                }
                            });
                        }
                    });

            });
    }
</script>