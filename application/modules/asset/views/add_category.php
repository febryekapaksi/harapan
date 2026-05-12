<?php
$ArrSelect['Y']    = 'Active';
$ArrSelect['N']    = 'Not Active';

$id             = (!empty($data[0]->id))          ? $data[0]->id          : '';
$nm_category    = (!empty($data[0]->nm_category)) ? $data[0]->nm_category : '';
$status         = (!empty($data[0]->status))      ? $data[0]->status      : 'Y';
$coa_debit      = (!empty($data[0]->coa_debit))   ? $data[0]->coa_debit   : '';
$nm_coa_debit   = (!empty($data[0]->nm_coa_debit)) ? $data[0]->nm_coa_debit : '';
$coa_kredit     = (!empty($data[0]->coa_kredit))  ? $data[0]->coa_kredit  : '';
$nm_coa_kredit  = (!empty($data[0]->nm_coa_kredit)) ? $data[0]->nm_coa_kredit : '';
?>
<div class="box box-primary"><br>
    <div class="box-body">
        <div class="form-group row">
            <div class="col-md-3">
                <label>Category Name</label>
            </div>
            <div class="col-md-9">
                <input type="text" class="form-control" id="nm_category" name="nm_category" placeholder="Category Name" value='<?= $nm_category; ?>'>
                <input type="hidden" class="form-control" id="id" name="id" value='<?= $id; ?>'>
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3">
                <label>COA Beban Depresiasi <small class="text-muted">(Debit)</small></label>
            </div>
            <div class="col-md-9">
                <div class="input-group">
                    <input type="text" class="form-control" id="coa_debit" name="coa_debit"
                        placeholder="No. Perkiraan Debit" value='<?= $coa_debit; ?>' readonly>
                    <span class="input-group-addon" style="min-width:200px; text-align:left;" id="nm_coa_debit_show">
                        <?= $nm_coa_debit; ?>
                    </span>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-coa-picker" data-target="debit">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
                <input type="hidden" id="nm_coa_debit" name="nm_coa_debit" value="<?= $nm_coa_debit; ?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3">
                <label>COA Akumulasi Depresiasi <small class="text-muted">(Kredit)</small></label>
            </div>
            <div class="col-md-9">
                <div class="input-group">
                    <input type="text" class="form-control" id="coa_kredit" name="coa_kredit"
                        placeholder="No. Perkiraan Kredit" value='<?= $coa_kredit; ?>' readonly>
                    <span class="input-group-addon" style="min-width:200px; text-align:left;" id="nm_coa_kredit_show">
                        <?= $nm_coa_kredit; ?>
                    </span>
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default btn-coa-picker" data-target="kredit">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
                <input type="hidden" id="nm_coa_kredit" name="nm_coa_kredit" value="<?= $nm_coa_kredit; ?>">
            </div>
        </div>

        <div class="form-group row">
            <div class="col-md-3">
                <label>Status</label>
            </div>
            <div class="col-md-9">
                <?php
                echo form_dropdown('status', $ArrSelect, $status, array('id' => 'status', 'class' => 'form-control input-md chosen-select'));
                ?>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-3"></div>
            <div class="col-md-9">
                <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal COA Picker -->
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
                    <table class="table table-bordered table-hover table-striped" id="tbl_coa">
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

<style type="text/css">
    .chosen-container-active .chosen-single {
        border: none;
        box-shadow: none;
    }

    .chosen-container-single .chosen-single {
        height: 34px;
        border: 1px solid #d2d6de;
        border-radius: 0px;
        background: none;
        box-shadow: none;
        color: #444;
        line-height: 32px;
    }

    .chosen-container-single .chosen-single div {
        top: 5px;
    }

    #tbl_coa tbody tr {
        cursor: pointer;
    }

    #tbl_coa tbody tr:hover {
        background-color: #d9edf7 !important;
    }
</style>
<script>
    swal.close();
    var _coa_target = 'debit';
    var _coa_all = [];

    $(document).ready(function() {
        $('.chosen-select').select2();
        loadCOA();
    });

    function loadCOA() {
        $.ajax({
            url: base_url + 'index.php/' + active_controller + '/get_coa_list',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                _coa_all = res;
                renderCOA(res);
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
        renderCOA(_coa_all);
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
        if (_coa_target === 'debit') {
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