<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post" autocomplete="off">
            <div class="form-group row">
                <div class="col-md-12">
                    <div class="col-md-6">
                        <!-- Nomor Muat Kendaraan -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="customer">No Muat Kendaraan</label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_loading" id="no_loading" class="form-control select2" onchange="get_spk()">
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($loading as $load): ?>
                                        <option value="<?= $load['no_loading'] ?>"><?= $load['no_loading'] . " - " . $load['nopol'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Daftar SO Customer -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="">Sales Order</label>
                            </div>
                            <div class="col-md-8">
                                <select name="no_so" id="no_so" class="form-control select2">
                                    <option value="">-- Pilih --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- Tanggal Kirim -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="delivery_date">Tanggal Kirim</label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" name="delivery_date" id="delivery_date" class="form-control"
                                    value="">
                            </div>
                        </div>

                        <!-- Alamat Pengiriman -->
                        <div class="form-group row">
                            <div class="col-md-4">
                                <label for="delivery_address">Alamat Pengiriman</label>
                            </div>
                            <div class="col-md-8">
                                <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="col-md-12">
                        <a href="javascript:void(0);" class="btn btn-sm btn-success" id="detSO"><i class="fa fa-plus"></i> Detail SO</a>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="l">
                                <thead class="bg-blue">
                                    <tr>
                                        <th width='5%' class='text-center'>#</th>
                                        <th>PRODUCT</th>
                                        <th width='15%' class='text-center'>QTY ORDER</th>
                                        <th width='15%' class='text-center'>QTY BOOKING</th>
                                        <th width='15%' class='text-center'>QTY BELUM KIRIM</th>
                                        <th width='15%' class='text-center'>QTY SPK</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
                    <a class="btn btn-default" onclick="window.history.back(); return false;">
                        <i class="fa fa-reply"></i> Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function get_spk() {}
</script>