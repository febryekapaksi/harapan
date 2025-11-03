<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box box-primary">
    <form id="data-form" enctype="multipart/form-data">
        <div class="box-header">
            <span class="pull-left">
                <label for="tgl_butuh"><b>Tanggal Dibutuhkan</b></label>
                <?php
                $tgl_now = date('Y-m-d');
                $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
                echo form_input(array('id' => 'tgl_butuh', 'name' => 'tgl_butuh', 'class' => 'form-control text-center tgl changeSaveDate', 'readonly' => 'readonly', 'placeholder' => 'Tanggal Dibutuhkan'), $tgl_next_month);
                ?>
            </span>
            <span class="pull-left">
                <label><b>Tingkat PR</b></label>
                <select name="tingkat_pr" id="" class="form-control tingkat_pr">
                    <option value="1">Normal</option>
                    <option value="2">Urgent</option>
                </select>
            </span>
            <span class="pull-right">
                <button type="button" class="btn btn-sm btn-primary" id="autoPropose">Set Auto Propose</button>
                <button type="button" class="btn btn-sm btn-danger" id="autoDelete">Clear Purpose Request</button>
            </span>
        </div>
        <div class="box-body">
            <table class="no-border" style="margin-bottom: 10px; width:20%;">
                <tr>
                    <td style="width: 5%;"><label for="">Kategori</label></td>
                    <td style="width: 10%;">
                        <select name='category' id='category' class='form-control input-sm chosen-select'>
                            <?php
                            foreach ($category as $val => $valx) {
                                $selected = ($valx['id'] == '8') ? 'selected' : '';
                                echo "<option value='" . $valx['id'] . "' " . $selected . " data-nm_category='" . strtoupper($valx['nm_category']) . "'>" . strtoupper($valx['nm_category']) . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </table>

            <div class="table-responsive">
                <table id="example1" class="table table-bordered table-striped" width='100%'>
                    <thead>
                        <tr class='bg-blue'>
                            <th class="text-center" width='4%'>#</th>
                            <th class="text-center">Kode Barang</th>
                            <th class="text-center">Nama Barang</th>
                            <th class="text-center" width='10%'>Inventory Type</th>
                            <th class="text-center no-sort" width='7%'>Stock</th>
                            <th class="text-center no-sort" width='7%'>Kebutuhan 1 Bulan</th>
                            <th class="text-center no-sort" width='7%'>Max Stock</th>
                            <th class="text-center no-sort" width='8%'>Propose Purchase</th>
                            <th class="text-center no-sort" width='8%'>Unit</th>
                            <th class="text-center no-sort" width='8%'>Propose Purchase (Packing)</th>
                            <th class="text-center no-sort" width='5%'>Unit Packing</th>
                            <th class="text-center no-sort" width='8%'>Spec</th>
                            <th class="text-center no-sort" width='8%'>Info</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <br>
            <div class="text-center">
                <button type="button" class="btn btn-md btn-success" id="saveRequest"><i class="fa fa-cart-plus"></i> Purchase Request</button>
            </div>
        </div>
    </form>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/jquery-inputmask/jquery.inputmask.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('.tgl').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });
        moneyFormat('.moneyFormat');

        var category = $("#category").val();
        DataTables(category);
        $(document).on('change', '#category', function() {
            var category = $("#category").val();
            DataTables(category);
        });


        $(document).on('click', '#autoUpdate', function() {
            var inventory = $('#category').val();
            var nm_category = $('#category').find(':selected').data('nm_category')

            if (inventory == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Filter category terlebih dahulu ...',
                    type: "warning"
                });
                return false;
            }
            swal({
                    title: "Are you sure?",
                    text: "Update otomatis Category " + nm_category + " !",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: base_url + active_controller + '/auto_update_rutin/' + inventory,
                            type: "POST",
                            cache: false,
                            dataType: 'json',
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000
                                    });
                                    // window.location.href = base_url + active_controller + 'add_new';
                                    DataTables(inventory);
                                } else if (data.status == 0) {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        return false;
                    }
                });
        });

        $(document).on('click', '#autoDelete', function() {
            var id_category = $('#category').val();
            var nm_category = $('#category').find(':selected').data('nm_category')

            if (id_category == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Pilih Category Terlebih Dahulu ...',
                    type: "warning"
                });
                return false;
            }
            swal({
                    title: "Are you sure?",
                    text: "Clear Propose Category " + nm_category + " !",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $.ajax({
                            url: base_url + active_controller + '/clear_update_reorder/' + id_category,
                            type: "POST",
                            cache: false,
                            dataType: 'json',
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000
                                    });
                                    // window.location.href = base_url + active_controller + 'add_new';
                                    DataTables(id_category);
                                } else if (data.status == 0) {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                        return false;
                    }
                });
        });

        $(document).on('change', '.changeSave', function() {
            var id = $(this).data('id');
            var qty_satuan = $(this).val();
            if (qty_satuan == '' || qty_satuan == null) {
                qty_satuan = 0;
            } else {
                qty_satuan = qty_satuan.split(',').join('');
                qty_satuan = parseFloat(qty_satuan);
            }

            var konversi = $(this).data('konversi');
            if (konversi == '' || konversi == 0 || konversi == null) {
                konversi = 1;
            }

            var nilai = (qty_satuan / konversi);

            var qty = $('.purchase_' + id).val().split(",").join("");

            var nomor = $(this).data('no');
            var id_material = $(this).data('id');
            var input_pack = $('#purchase_pack_' + nomor).val().split(",").join("");
            var purchase = $('#purchase_' + nomor).val().split(",").join("");
            if ($(this).hasClass('input_qty_packing')) {
                purchase = (input_pack * konversi);
            }
            var purchase_pack = $('#purchase_pack_' + nomor).val().split(",").join("");
            if ($(this).hasClass('input_qty_satuan')) {
                purchase_pack = (purchase / konversi);
            }
            // var tanggal 	= $('#tanggal_'+nomor).val();
            var tanggal = $('#tgl_butuh').val();
            var satuan = $('#satuan_' + nomor).val();
            var spec = $('#spec_' + nomor).val();
            var info = $('#info_' + nomor).val();

            $.ajax({
                url: base_url + active_controller + 'save_reorder_change',
                type: "POST",
                data: {
                    "id_material": id_material,
                    "purchase": purchase,
                    "purchase_pack": purchase_pack,
                    "tanggal": tanggal,
                    "spec": spec,
                    "info": info,
                    "satuan": satuan
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    console.log(data.pesan)
                },
                error: function() {
                    console.log('error connection serve !')
                }
            });
        });

        $(document).on('click', '#saveRequest', function() {
            var category = $('#category').val();
            var tingkat_pr = $('.tingkat_pr').val();

            if (category == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Pilih Category Terlebih Dahulu ...',
                    type: "warning"
                });
                return false;
            }

            var valid = 0;
            check_inputed_qty_stock().then(function(data) {
                if (data > 0) {
                    swal({
                            title: "Are you sure?",
                            text: "Membuat semua Propose berdasarkan Category !!!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Yes, Process it!",
                            cancelButtonText: "No, cancel process!",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                $.ajax({
                                    url: base_url + active_controller + '/save_reorder_all',
                                    type: "POST",
                                    data: {
                                        'category': category,
                                        'tingkat_pr': tingkat_pr
                                    },
                                    cache: false,
                                    dataType: 'json',
                                    success: function(data) {
                                        if (data.status == 1) {
                                            swal({
                                                title: "Save Success!",
                                                text: data.pesan,
                                                type: "success",
                                                timer: 7000
                                            });
                                            window.location.href = base_url + active_controller
                                        } else if (data.status == 0) {
                                            swal({
                                                title: "Save Failed!",
                                                text: data.pesan,
                                                type: "warning",
                                                timer: 7000
                                            });
                                        }
                                    },
                                    error: function() {
                                        swal({
                                            title: "Error Message !",
                                            text: 'An Error Occured During Process. Please try again..',
                                            type: "warning",
                                            timer: 7000
                                        });
                                    }
                                });
                            } else {
                                swal("Cancelled", "Data can be process again :)", "error");
                                return false;
                            }
                        });
                } else {
                    swal({
                        title: 'Warning !',
                        text: 'Please fill qty pack at least at 1 product !',
                        type: 'warning'
                    });
                }
            }).fail(function(error) {
                swal({
                    title: 'Error !',
                    text: 'Please try again later !',
                    type: 'error'
                });
            });

        });

        $(document).on('change', '.changeSaveDate', function() {
            var tanggal = $('#tgl_butuh').val();
            var id_category = $('#category').val();

            if (id_category == '0') {
                swal({
                    title: "Error Message!",
                    text: 'Pilih Category Terlebih Dahulu ...',
                    type: "warning"
                });
                return false;
            }

            $.ajax({
                url: base_url + active_controller + '/save_reorder_change_date',
                type: "POST",
                data: {
                    "tanggal": tanggal,
                    "id_category": id_category,
                },
                cache: false,
                dataType: 'json',
                success: function(data) {
                    console.log(data.pesan)
                },
                error: function() {
                    console.log('error connection serve !')
                }
            });
        });

        $(document).on('change', '.input_qty_packing', function() {
            var id = $(this).data('id');
            var qty_packing = $(this).val();
            if (qty_packing == '' || qty_packing == null) {
                qty_packing = 0;
            } else {
                qty_packing = qty_packing.split(',').join('');
                qty_packing = parseFloat(qty_packing);
            }

            var konversi = $(this).data('konversi');
            if (konversi == '' || konversi == 0 || konversi == null) {
                konversi = 1;
            }

            var nilai = (qty_packing * konversi);

            $('.purchase_' + id).val(nilai.toLocaleString());
        });

        $(document).on('change', '.input_qty_satuan', function() {
            var id = $(this).data('id');
            var qty_satuan = $(this).val();
            if (qty_satuan == '' || qty_satuan == null) {
                qty_satuan = 0;
            } else {
                qty_satuan = qty_satuan.split(',').join('');
                qty_satuan = parseFloat(qty_satuan);
            }

            var konversi = $(this).data('konversi');
            if (konversi == '' || konversi == 0 || konversi == null) {
                konversi = 1;
            }

            var nilai = (qty_satuan / konversi);

            $('.purchase_pack_' + id).val(nilai.toLocaleString());
        });
    })

    function check_inputed_qty_stock() {
        // Create a deferred object (promise)
        var deferred = $.Deferred();

        // Perform the AJAX request
        $.ajax({
            url: siteurl + active_controller + 'check_inputed_qty_stock', // Replace with your API endpoint
            method: 'POST', // Use POST, GET, PUT, DELETE, etc. as per your requirement
            dataType: 'json', // Change the data type as per your API response
            success: function(response) {
                // Resolve the deferred object with the response data
                deferred.resolve(response.jumlah_data);
            },
            error: function(error) {
                // Reject the deferred object with the error message
                deferred.reject(error);
            }
        });

        // Return the promise object
        return deferred.promise();
    }

    function DataTables(category = null) {
        var dataTable = $('#example1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave": true,
            "bAutoWidth": true,
            "destroy": true,
            "responsive": true,
            "aaSorting": [
                [2, "asc"]
            ],
            "columnDefs": [{
                "targets": 'no-sort',
                "orderable": false,
            }],
            "sPaginationType": "simple_numbers",
            "iDisplayLength": 10,
            "aLengthMenu": [
                [10, 20, 50, 100, 150],
                [10, 20, 50, 100, 150]
            ],
            "ajax": {
                url: base_url + active_controller + '/server_side_reorder_point',
                type: "post",
                data: function(d) {
                    d.category = category
                },
                cache: false,
                error: function() {
                    $(".my-grid-error").html("");
                    $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                    $("#my-grid_processing").css("display", "none");
                }
            },
            "drawCallback": function() {
                moneyFormat('.moneyFormat');
            }
        });
    }

    function moneyFormat(e) {
        $(e).inputmask({
            alias: "decimal",
            digits: 2,
            radixPoint: ".",
            autoGroup: true,
            placeholder: "0",
            rightAlign: false,
            allowMinus: false,
            integerDigits: 13,
            groupSeparator: ",",
            digitsOptional: false,
            showMaskOnHover: true,
        })
    }

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
</script>