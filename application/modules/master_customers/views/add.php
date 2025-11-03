<?php
$cus = null;

if (!empty($results['cus']) && is_array($results['cus'])) {
    foreach ($results['cus'] as $item) {
        $cus = $item;
        break; // ambil satu saja
    }
}
if (!empty($results['rate']) && is_array($results['rate'])) {
    foreach ($results['rate'] as $rate) {
    }
}

$readonly = isset($results['mode']) && $results['mode'] === 'view' ? 'readonly' : '';
$disabled = isset($results['mode']) && $results['mode'] === 'view' ? 'disabled' : '';
?>
<div class="box box-primary">
    <div class="box-body">
        <form id="data-form" method="post">
            <div class="row">
                <!-- Header Customer -->
                <div class="col-sm-12">
                    <center>
                        <h3>DETAIL IDENTITAS CUSTOMER</h3>
                    </center>
                    <br>
                    <div class="col-sm-6">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="">Id Customer</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="id_customer" value='<?= isset($cus->id_customer) ? $cus->id_customer : '' ?>' required name="id_customer" readonly placeholder="Id Customer">
                            </div>
                        </div>

                        <div class="form-group row" hidden>
                            <div class="col-md-6">
                                <label for="id_category_customer">Category Customer</label>
                            </div>
                            <div class="col-md-6">
                                <select id="id_category_customer" name="id_category_customer" class="form-control select">
                                    <option value="">--Pilih--</option>
                                    <?php foreach ($results['category'] as $category) {
                                    ?>
                                        <option value="<?= $category->id_category_customer ?>" <?= (isset($cus) && $cus->id_category_customer == $category->id_category_customer) ? 'selected' : '' ?>><?= $category->name_category_customer ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Nama Customer <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="name_customer"
                                    value="<?= isset($cus->name_customer) ? $cus->name_customer : '' ?>"
                                    required name="name_customer" placeholder="Nama Customer" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Telephone <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="telephone"
                                    value="<?= isset($cus->telephone) ? $cus->telephone : '' ?>"
                                    required name="telephone" placeholder="Nomor Telephone" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer"></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="telephone_2"
                                    value="<?= isset($cus->telephone_2) ? $cus->telephone_2 : '' ?>"
                                    name="telephone_2" placeholder="Nomor Telephone" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Fax</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="fax" name="fax"
                                    value="<?= isset($cus->fax) ? $cus->fax : '' ?>"
                                    placeholder="Nomor Fax" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Email <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="email" required name="email"
                                    value="<?= isset($cus->name_customer) ? $cus->name_customer : '' ?>"
                                    placeholder="email@domain.address" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Tanggal Mulai <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="date" class="form-control" id="start_date" required name="start_date"
                                    value="<?= isset($cus->start_date) ? $cus->start_date : '' ?>"
                                    placeholder="Tanggal Mulai" <?= $readonly ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="id_category_customer">Sales</label>
                            </div>
                            <div class="col-md-6">
                                <select id="id_karyawan" name="id_karyawan" class="form-control select" required <?= $disabled ?>>
                                    <option value="">--pilih--</option>
                                    <?php foreach ($results['karyawan'] as $karyawan) {
                                        $selected = (isset($cus) && $cus->id_karyawan == $karyawan->id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $karyawan->id ?>" <?= $selected ?>>
                                            <?= ucfirst(strtolower($karyawan->nm_karyawan)) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Channel Pemasaran <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>
                                            <input type="checkbox" class="checkbox-control" id="chanel_toko" name="chanel_toko"
                                                value="Toko dan User"
                                                <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Toko dan User') !== false) ? 'checked' : '' ?>
                                                <?= $disabled ?>> Toko dan User
                                        </label>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-md-5">
                                        <label>
                                            <input type="checkbox" class="checkbox-control" id="chanel_project" name="chanel_project"
                                                value="Project"
                                                <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? 'checked' : '' ?>
                                                onclick="togglePersentaseInput()" <?= $disabled ?>> Project
                                        </label>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="input-group">
                                            <input type="text" class="form-control input-sm divide" name="persentase" id="persentase"
                                                value="<?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? $cus->persentase : '' ?>"
                                                <?= (isset($cus) && strpos($cus->chanel_pemasaran, 'Project') !== false) ? '' : 'disabled' ?> <?= $disabled ?>>
                                            <span class="input-group-addon"><i class="fa fa-percent"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="customer">Status <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>
                                            <input type="radio" class="radio-control" id="activation" name="activation"
                                                value="aktif"
                                                <?= (isset($cus) && $cus->activation == 'aktif') ? 'checked' : '' ?>
                                                required <?= $disabled ?>> Aktif
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label>
                                            <input type="radio" class="radio-control" id="activation" name="activation"
                                                value="inaktif"
                                                <?= (isset($cus) && $cus->activation == 'inaktif') ? 'checked' : '' ?>
                                                required <?= $disabled ?>> Non aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <!-- Provinsi -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Provinsi <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select id="id_prov" name="id_prov" class="form-control select" onchange="get_kota()" required <?= $disabled ?>>
                                    <option value="">--Pilih--</option>
                                    <?php foreach ($results['prov'] as $prov) {
                                        $selected = (isset($cus) && $cus->id_prov == $prov->id_prov) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $prov->id_prov ?>" <?= $selected ?>><?= $prov->provinsi ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Kabupaten/Kota -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Kabupaten/Kota <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select id="id_kabkot" name="id_kabkot" class="form-control select" onchange="get_kec()" required <?= $disabled ?>>
                                    <option value="">--Pilih--</option>
                                    <?php foreach ($results['kabkot'] as $kabkot) {
                                        $selected = (isset($cus) && $cus->id_kabkot == $kabkot->id_kabkot) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $kabkot->id_kabkot ?>" <?= $selected ?>><?= $kabkot->kabkot ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Kecamatan -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Kecamatan <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select id="id_kec" name="id_kec" class="form-control select" required <?= $disabled ?>>
                                    <option value="">--Pilih--</option>
                                    <?php foreach ($results['kec'] as $kec) {
                                        $selected = (isset($cus) && $cus->id_kec == $kec->id_kec) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $kec->id_kec ?>" <?= $selected ?>><?= $kec->kecamatan ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Alamat <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <textarea name="address_office" id="address_office" class="form-control required w70" placeholder="Alamat" <?= $readonly ?>><?= isset($cus->address_office) ? $cus->address_office : '' ?></textarea>
                            </div>
                        </div>

                        <!-- Kode Pos -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Kode Pos</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="zip_code" name="zip_code"
                                    value="<?= isset($cus->zip_code) ? $cus->zip_code : '' ?>" placeholder="Kode Pos" <?= $readonly ?>>
                            </div>
                        </div>

                        <!-- Longitude -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Longitude <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="longitude" name="longitude"
                                    value="<?= isset($cus->longitude) ? $cus->longitude : '' ?>" required placeholder="Longitude" <?= $readonly ?>>
                            </div>
                        </div>

                        <!-- Latitude -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Latitude <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="latitude" name="latitude"
                                    value="<?= isset($cus->latitude) ? $cus->latitude : '' ?>" required placeholder="Latitude" <?= $readonly ?>>
                            </div>
                        </div>

                        <!-- Mulai Usaha Sejak -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Mulai Usaha Sejak</label>
                            </div>
                            <div class="col-md-6">
                                <select name="tahun_mulai" class="form-control select" <?= $disabled ?>>
                                    <option value="">-- Pilih Tahun --</option>
                                    <?php
                                    $currentYear = date("Y");
                                    for ($year = $currentYear; $year >= $currentYear - 50; $year--) {
                                        $selected = (isset($cus) && $year == $cus->tahun_mulai) ? 'selected' : '';
                                        echo "<option value='$year' $selected>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Kategori Customer -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Kategori Customer <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select name="kategori_cust" id="kategori_cust" class="form-control select" <?= $disabled ?>>
                                    <option value="">-- Pilih --</option>
                                    <option value="Distributor" <?= (isset($cus) && $cus->kategori_cust == 'Distributor') ? 'selected' : '' ?>>Distributor</option>
                                    <option value="Retail" <?= (isset($cus) && $cus->kategori_cust == 'Retail') ? 'selected' : '' ?>>Retail</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kategori Toko -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Kategori Toko</label>
                            </div>
                            <div class="col-md-6">
                                <select name="kategori_toko" id="kategori_toko" class="form-control select" <?= $disabled ?>>
                                    <option value="">-- Pilih --</option>
                                    <?php
                                    $opsiToko = ['Toko 1', 'Toko 2', 'Toko 3', 'Retail'];
                                    foreach ($opsiToko as $kategori) {
                                        $selected = (isset($cus) && $cus->kategori_toko == $kategori) ? 'selected' : '';
                                        echo "<option value=\"$kategori\" $selected>$kategori</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Penilaian Customer -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>PENILAIAN CUSTOMER</h3>
                    </center>
                    <br>
                    <div class="col-sm-6">
                        <!-- Bayar 3 Bulan On Time -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Bayar 3 Bulan On Time</label>
                            </div>
                            <div class="col-md-6">
                                <?php $ontime = isset($rate->ontime) ? $rate->ontime : ''; ?>
                                <label><input type="radio" name="data4[ontime]" value="Yes" <?= $ontime === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>> Yes</label>
                                <label><input type="radio" name="data4[ontime]" value="No" <?= $ontime === 'No' ? 'checked' : '' ?> <?= $disabled ?>> No</label>
                                <label><input type="radio" name="data4[ontime]" value="New" <?= $ontime === 'New' ? 'checked' : '' ?> <?= $disabled ?>> New</label>
                            </div>
                        </div>

                        <!-- Toko Milik Sendiri -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Toko Milik Sendiri</label>
                            </div>
                            <div class="col-md-6">
                                <?php $toko = isset($rate->toko_sendiri) ? $rate->toko_sendiri : ''; ?>
                                <label><input type="radio" name="data4[toko_sendiri]" value="Yes" <?= $toko === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>> Yes</label>
                                <label><input type="radio" name="data4[toko_sendiri]" value="No" <?= $toko === 'No' ? 'checked' : '' ?> <?= $disabled ?>> No</label>
                            </div>
                        </div>

                        <!-- Armada Pickup -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Armada Pickup</label>
                            </div>
                            <div class="col-md-6">
                                <input type="number" min="0" class="form-control" name="data4[armada_pickup]" value="<?= isset($rate->armada_pickup) ? $rate->armada_pickup : '' ?>" <?= $readonly ?>>
                            </div>
                        </div>

                        <!-- Armada Truck -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Armada Truck</label>
                            </div>
                            <div class="col-md-6">
                                <input type="number" min="0" class="form-control" name="data4[armada_truck]" value="<?= isset($rate->armada_truck) ? $rate->armada_truck : '' ?>" <?= $readonly ?>>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <!-- Attitude -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Attitude</label>
                            </div>
                            <div class="col-md-6">
                                <?php $attitude = isset($rate->attitude) ? $rate->attitude : ''; ?>
                                <label><input type="radio" name="data4[attitude]" value="Yes" <?= $attitude === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>> Yes</label>
                                <label><input type="radio" name="data4[attitude]" value="No" <?= $attitude === 'No' ? 'checked' : '' ?> <?= $disabled ?>> No</label>
                                <label><input type="radio" name="data4[attitude]" value="New" <?= $attitude === 'New' ? 'checked' : '' ?> <?= $disabled ?>> New</label>
                            </div>
                        </div>

                        <!-- Luas Tanah -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Luas Tanah</label>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="number" min="0" class="form-control" name="data4[luas_tanah]" value="<?= isset($rate->luas_tanah) ? $rate->luas_tanah : '' ?>" id="luas_tanah" <?= $readonly ?>>
                                    <span class="input-group-addon"><b>M²</b></span>
                                </div>
                            </div>
                        </div>

                        <!-- PBB -->
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>PBB</label>
                            </div>
                            <div class="col-md-6">
                                <?php $pbb = isset($rate->pbb) ? $rate->pbb : ''; ?>
                                <label><input type="radio" name="data4[pbb]" value="Yes" <?= $pbb === 'Yes' ? 'checked' : '' ?> <?= $disabled ?>> Yes</label>
                                <label><input type="radio" name="data4[pbb]" value="No" <?= $pbb === 'No' ? 'checked' : '' ?> <?= $disabled ?>> No</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Existing Customer -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>SUPPLIER EXISTING CUSTOMER</h3>
                    </center>
                    <br>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <table class='table table-bordered table-striped'>
                                <thead>
                                    <tr class='bg-blue'>
                                        <td align='center'><b>Nama PT</b></td>
                                        <td align='center'><b>PIC</b></td>
                                        <td align='center'><b>No Telepon</b></td>
                                        <td style="width: 50px;" align='center'>
                                            <?php
                                            if (empty($results['mode']) || $results['mode'] !== 'view') {
                                                echo form_button([
                                                    'type' => 'button',
                                                    'class' => "btn btn-sm btn-success $disabled",
                                                    'content' => 'Add',
                                                    'id' => 'add-existing'
                                                ]);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody id='list_existing'>
                                    <?php
                                    $loop = 0;
                                    if (!empty($results['exis']) && is_array($results['exis'])) {
                                        foreach ($results['exis'] as $exis) {
                                            $loop++;
                                            $pt   = isset($exis->existing_pt) ? $exis->existing_pt : '';
                                            $pic  = isset($exis->existing_pic) ? $exis->existing_pic : '';
                                            $telp = isset($exis->existing_telp) ? $exis->existing_telp : '';

                                            echo "<tr id='tr_existing_$loop'>";
                                            echo "<td><input type='text' class='form-control input-sm' name='existing[$loop][existing_pt]' value='$pt' id='existing_{$loop}_pt' required $readonly></td>";
                                            echo "<td><input type='text' class='form-control input-sm' name='existing[$loop][existing_pic]' value='$pic' id='existing_{$loop}_pic' required $readonly></td>";
                                            echo "<td><input type='text' class='form-control input-sm' name='existing[$loop][existing_telp]' value='$telp' id='existing_{$loop}_telp' required $readonly></td>";

                                            if (empty($results['mode']) || $results['mode'] !== 'view') {
                                                echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' onClick='return DelExisting($loop);'><i class='fa fa-trash-o'></i></button></td>";
                                            }

                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Category Customer -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>CATEGORY CUSTOMER</h3>
                    </center>
                    <br>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <table class='table table-bordered table-striped'>
                                <thead>
                                    <tr class='bg-blue'>
                                        <td align='center'><b>Category Customer</b></td>
                                        <td style="width: 50px;" align='center'>
                                            <?php
                                            if (empty($results['mode']) || $results['mode'] !== 'view') {
                                                echo form_button([
                                                    'type'    => 'button',
                                                    'class'   => 'btn btn-sm btn-success',
                                                    'content' => 'Add',
                                                    'id'      => 'add-category'
                                                ]);
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody id='list_category'>
                                    <?php
                                    $loop = 0;
                                    if (!empty($results['cate']) && is_array($results['cate'])) {
                                        foreach ($results['cate'] as $cate) {
                                            $loop++;
                                            $selectedVal = isset($cate->name_category_customer) ? $cate->name_category_customer : '';

                                            echo "<tr id='tr_$loop'>";
                                            echo "<td>";
                                            echo "<select id='data2_{$loop}_id_category_customer' name='data2[$loop][id_category_customer]' class='form-control select' required $disabled>";
                                            echo "<option value='$selectedVal' selected>$selectedVal</option>";

                                            if (!empty($results['category']) && is_array($results['category'])) {
                                                foreach ($results['category'] as $category) {
                                                    $optionVal = $category->name_category_customer;
                                                    $isSelected = ($optionVal === $selectedVal) ? "selected" : "";
                                                    echo "<option value='$optionVal' $isSelected>$optionVal</option>";
                                                }
                                            }

                                            echo "</select>";
                                            echo "</td>";

                                            if (empty($results['mode']) || $results['mode'] !== 'view') {
                                                echo "<td align='center'>
                                    <button type='button' class='btn btn-sm btn-danger' title='Hapus Data' onClick='return DelItem2($loop);'>
                                        <i class='fa fa-trash-o'></i>
                                    </button>
                                </td>";
                                            }

                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PIC -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>PIC</h3>
                    </center>
                    <br>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <table class='table table-bordered table-striped'>
                                <thead>
                                    <tr class='bg-blue'>
                                        <td align='center'><b>Nama PIC</b></td>
                                        <td align='center'><b>Nomor Telp</b></td>
                                        <td align='center'><b>Email</b></td>
                                        <td align='center'><b>Jabatan</b></td>
                                        <td style="width: 50px;" align='center'>
                                            <?php if (empty($results['mode']) || $results['mode'] !== 'view') : ?>
                                                <?= form_button(['type' => 'button', 'class' => 'btn btn-sm btn-success', 'content' => 'Add', 'id' => 'add-payment']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </thead>
                                <tbody id='list_payment'>
                                    <?php
                                    $loop = 0;
                                    $fields = ['name_pic', 'phone_pic', 'email_pic', 'position_pic'];
                                    $defaultRows = [['name' => 'PIC'], ['name' => 'Owner'], ['name' => 'KA Toko']];

                                    if (!empty($results['pic']) && is_array($results['pic'])) {
                                        foreach ($results['pic'] as $pic) {
                                            $loop++;
                                            echo "<tr id='tr_$loop'>";
                                            foreach ($fields as $field) {
                                                $value = isset($pic->$field) ? $pic->$field : '';
                                                $readonlyAttr = ($field === 'position_pic') ? 'readonly' : '';
                                                echo "<td><input type='text' class='form-control input-sm' name='data1[$loop][$field]' value='" . htmlspecialchars($value, ENT_QUOTES) . "' id='data1_{$loop}_$field' $readonlyAttr $disabled required></td>";
                                            }
                                            if (empty($results['mode']) || $results['mode'] !== 'view') {
                                                echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' onclick='return DelItem($loop);'><i class='fa fa-trash-o'></i></button></td>";
                                            }
                                            echo "</tr>";
                                        }
                                    } else {
                                        foreach ($defaultRows as $index => $row) {
                                            $loop = $index + 1;
                                            echo "<tr id='tr_$loop'>";
                                            foreach ($fields as $field) {
                                                $value = ($field === 'position_pic') ? $row['name'] : '';
                                                $readonlyAttr = ($field === 'position_pic') ? 'readonly' : '';
                                                echo "<td><input type='text' class='form-control input-sm' name='data1[$loop][$field]' id='data1_{$loop}_$field' value='" . htmlspecialchars($value, ENT_QUOTES) . "' $readonlyAttr required></td>";
                                            }
                                            echo "<td align='center'><button type='button' class='btn btn-sm btn-danger' title='Hapus Data' onclick='return DelItem($loop);'><i class='fa fa-trash-o'></i></button></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- INFORMASI PEMBAYARAN -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>INFORMASI PEMBAYARAN</h3>
                    </center>
                    <br>
                    <div class="col-sm-6">
                        <div class="col-md-12">
                            <label>
                                <h4>Informasi Bank</h4>
                            </label>
                        </div>

                        <?php
                        $bankFields = [
                            ['label' => 'Nama Bank', 'name' => 'name_bank'],
                            ['label' => 'Nomor Akun', 'name' => 'no_rekening'],
                            ['label' => 'Nama Akun', 'name' => 'nama_rekening'],
                            ['label' => 'Swift Code', 'name' => 'swift_code'],
                        ];

                        foreach ($bankFields as $field) {
                            $val = isset($cus->{$field['name']}) ? htmlspecialchars($cus->{$field['name']}) : '';
                            echo '
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label>' . $field['label'] . '</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="' . $val . '" placeholder="' . $field['label'] . '" ' . $disabled . '>
                                    </div>
                                </div>';
                        }
                        ?>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Alamat Bank</label>
                            </div>
                            <div class="col-md-6">
                                <textarea name="alamat_bank" id="alamat_bank" class="form-control w70" placeholder="Alamat Bank" <?= $disabled ?>><?= isset($cus->alamat_bank) ? htmlspecialchars($cus->alamat_bank) : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="col-md-12">
                            <label>
                                <h4>Informasi Pajak</h4>
                            </label>
                        </div>

                        <?php
                        $pajakFields = [
                            ['label' => 'Nomor NPWP/KTP', 'name' => 'npwp', 'required' => true],
                            ['label' => 'Nama NPWP/KTP', 'name' => 'npwp_name', 'required' => true],
                            ['label' => 'Alamat NPWP/KTP', 'name' => 'npwp_address', 'required' => true],
                        ];

                        foreach ($pajakFields as $field) {
                            $val = isset($cus->{$field['name']}) ? htmlspecialchars($cus->{$field['name']}) : '';
                            $required = $field['required'] ? 'required' : '';
                            echo '
                                <div class="form-group row">
                                    <div class="col-md-6">
                                        <label>' . $field['label'] . ' <span class="text-red">*</span></label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="' . $val . '" placeholder="' . $field['label'] . '" ' . $required . ' ' . $disabled . '>
                                    </div>
                                </div>';
                        }
                        ?>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Term Of Payment <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select id="payment_term" name="payment_term" class="form-control select" required <?= $disabled ?>>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($results['payment_terms'] as $terms): ?>
                                        <option value="<?= htmlspecialchars($terms->id) ?>" <?= (isset($cus->payment_term) && $cus->payment_term == $terms->id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($terms->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Nominal DP <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="nominal_dp" id="nominal_dp" value="<?= isset($cus->nominal_dp) ? htmlspecialchars($cus->nominal_dp) : '' ?>" required <?= $disabled ?>>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label>Sisa Pembayaran <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-6">
                                <select id="sisa_pembayaran" name="sisa_pembayaran" class="form-control select" required <?= $disabled ?>>
                                    <?php
                                    $options = ['15 After Delifery', '30 After Delifery'];
                                    $current = isset($cus->sisa_pembayaran) ? $cus->sisa_pembayaran : '';
                                    echo "<option value='$current'>$current</option>";
                                    foreach ($options as $opt) {
                                        if ($opt !== $current) {
                                            echo "<option value='$opt'>$opt</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INFORMASI INVOICE -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>INFORMASI INVOICE</h3>
                    </center>
                    <br>

                    <!-- Hari Terima -->
                    <div class="col-sm-12">
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label>Hari Terima <span class="text-red">*</span></label>
                            </div>
                            <div class="col-md-9">
                                <?php
                                $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
                                foreach ($days as $day) {
                                    $checked = isset($cus->$day) && $cus->$day === 'Y' ? 'checked' : '';
                                    echo "
                        <label>
                            <input type='checkbox' class='radio-control hari-checkbox' id='$day' name='$day' value='Y' $checked $disabled> " . ucfirst($day) . "
                        </label>&nbsp;
                    ";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Waktu Penerimaan Invoice -->
                    <div class="col-sm-12">
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <label>Waktu Penerimaan Invoice</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-2">
                                    <label>Start</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" id="start_recive" name="start_recive"
                                        value="<?= isset($cus->start_recive) ? htmlspecialchars($cus->start_recive) : '' ?>"
                                        placeholder="Start Time" required <?= $disabled ?>>
                                </div>
                                <div class="col-md-2">
                                    <label>End</label>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" id="end_recive" name="end_recive"
                                        value="<?= isset($cus->end_recive) ? htmlspecialchars($cus->end_recive) : '' ?>"
                                        placeholder="End Time" required <?= $disabled ?>>
                                </div>
                            </div>
                        </div>

                        <!-- Alamat Invoice -->
                        <div class="col-sm-6">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label>Alamat Invoice</label>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <textarea name="address_invoice" id="address_invoice" class="form-control required w70" placeholder="Alamat" <?= $disabled ?>><?= isset($cus->adress_invoice) ? htmlspecialchars($cus->adress_invoice) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PERSYARATAN PEMBAYARAN -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <h3>PERSYARATAN PEMBAYARAN</h3>
                    </center>
                    <br>
                    <div class="col-sm-12">
                        <?php
                        $terms = [
                            'invoice' => 'Invoice',
                            'sj' => 'SJ',
                            'faktur' => 'Faktur Pajak'
                        ];
                        foreach ($terms as $field => $label) {
                            $checked = (isset($cus->$field) && $cus->$field === 'Y') ? 'checked' : '';
                            echo "
                                <div class='col-sm-4'>
                                    <div class='form-group row'>
                                        <div class='col-md-12'>
                                            <label>
                                                <input type='checkbox' class='radio-control payterm-checkbox' id='$field' name='$field' value='Y' $checked $disabled> $label
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            ";
                        }
                        ?>
                    </div>
                </div>

                <!-- Button -->
                <div class="col-sm-12">
                    <hr>
                    <center>
                        <button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i> Simpan</button>
                    </center>
                    <br>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/js/number-divider.min.js') ?>"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.divide').divide();
        var base_url = '<?php echo base_url(); ?>';
        var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

        $('.select').select2({
            width: '100%',
        });

        var max_fields2 = 10; //maximum input boxes allowed
        var wrapper2 = $(".input_fields_wrap2"); //Fields wrapper
        var add_button2 = $(".add_field_button2"); //Add button ID

        //console.log(persen);

        var x2 = 1; //initlal text box count
        $(add_button2).click(function(e) { //on add input button click
            e.preventDefault();
            if (x2 < max_fields2) { //max input box allowed
                x2++; //text box increment

                $(wrapper2).append('<div class="row">' +
                    '<div class="col-xs-1">' + x2 + '</div>' +
                    '<div class="col-xs-3">' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[produk]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[costcenter]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mesin]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '<div class="input-group">' +
                    '<input type="text" name="hd' + x2 + '[mold_tools]"  class="form-control input-sm" value="">' +
                    '</div>' +
                    '</div>' +
                    '<a href="#" class="remove_field2">Remove</a>' +
                    '</div>'); //add input box
                $('#datepickerxxr' + x2).datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true
                });
            }
        });

        $(wrapper2).on("click", ".remove_field2", function(e) { //user click on remove text
            e.preventDefault();
            $(this).parent('div').remove();
            x2--;
        })

        $('#add-payment').click(function() {
            var jumlah = $('#list_payment').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_payment tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][name_pic]" id="data1_' + loop + '_name_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][phone_pic]" id="data1_' + loop + '_phone_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][email_pic]" id="data1_' + loop + '_email_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][position_pic]" id="data1_' + loop + '_position_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_payment').append(Template);
        });
        $('#add-category').click(function() {
            var jumlah = $('#list_category').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_category tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<select id="data2_' + loop + '_id_category_customer" name="data2[' + loop + '][id_category_customer]" class="form-control select" required>';
            Template += '<option value="">--pilih--</option>';
            Template += '<?php foreach ($results['category'] as $category) { ?>';
            Template += '<option value="<?= $category->name_category_customer ?>"><?= ucfirst(strtolower($category->name_category_customer)) ?></option>';
            Template += '<?php } ?>';
            Template += '</select>';
            Template += '</td>';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem2(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_category').append(Template);
        });
        $('#add-existing').click(function() {
            var jumlah = $('#list_existing').find('tr').length;
            if (jumlah == 0 || jumlah == null) {
                var ada = 0;
                var loop = 1;
            } else {
                var nilai = $('#list_existing tr:last').attr('id');
                var jum1 = nilai.split('_');
                var loop = parseInt(jum1[1]) + 1;
            }
            Template = '<tr id="tr_' + loop + '">';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pt]" id="data3_' + loop + '_existing_pt" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_pic]" id="data3_' + loop + '_existing_pic" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="left">';
            Template += '<input type="text" class="form-control input-sm" name="data3[' + loop + '][existing_telp]" id="data3_' + loop + '_existing_telp" label="FALSE" div="FALSE">';
            Template += '</td>';
            Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem3(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
            Template += '</tr>';
            $('#list_existing').append(Template);
        });


        $('#data-form').submit(function(e) {
            e.preventDefault();

            const checkboxes = document.querySelectorAll(".hari-checkbox");
            const paytermcbb = document.querySelectorAll(".payterm-checkbox");
            const oneChecked = Array.from(checkboxes).some(cb => cb.checked);
            const onePaytermcbb = Array.from(paytermcbb).some(cb => cb.checked);

            // Jika salah satu checkbox group belum dipilih, tampilkan alert dan hentikan proses
            if (!oneChecked) {
                alert("Pilih minimal satu hari terima!");
                return false; // ini penting
            }

            if (!onePaytermcbb) {
                alert("Pilih minimal satu syarat pembayaran!");
                return false; // ini penting
            }

            // Jika validasi lolos, baru tampilkan swal konfirmasi
            swal({
                    title: "Are you sure?",
                    text: "You will not be able to process again this data!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-danger",
                    confirmButtonText: "Yes, Process it!",
                    cancelButtonText: "No, cancel process!",
                    closeOnConfirm: true,
                    closeOnCancel: false
                },
                function(isConfirm) {
                    if (isConfirm) {
                        var formData = new FormData($('#data-form')[0]);
                        var baseurl = siteurl + 'master_customers/save'; // ganti jika rename


                        $.ajax({
                            url: baseurl,
                            type: "POST",
                            data: formData,
                            cache: false,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(data) {
                                if (data.status == 1) {
                                    swal({
                                        title: "Save Success!",
                                        text: data.pesan,
                                        type: "success",
                                        timer: 7000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                    window.location.href = base_url + active_controller;
                                } else {
                                    swal({
                                        title: "Save Failed!",
                                        text: data.pesan,
                                        type: "warning",
                                        timer: 7000,
                                        showCancelButton: false,
                                        showConfirmButton: false,
                                        allowOutsideClick: false
                                    });
                                }
                            },
                            error: function() {
                                swal({
                                    title: "Error Message !",
                                    text: 'An Error Occured During Process. Please try again..',
                                    type: "warning",
                                    timer: 7000,
                                    showCancelButton: false,
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            }
                        });
                    } else {
                        swal("Cancelled", "Data can be process again :)", "error");
                    }
                });
        });

    });

    function togglePersentaseInput() {
        const projectCheckbox = document.querySelector("input[name='chanel_project']");
        const persentaseInput = document.getElementById("persentase");

        if (projectCheckbox && projectCheckbox.checked) {
            persentaseInput.disabled = false;
            persentaseInput.required = true;
            persentaseInput.focus();
        } else {
            persentaseInput.disabled = true;
            persentaseInput.required = false;
            persentaseInput.value = ""; // Kosongkan kalau tidak aktif
        }
    }

    function get_kota() {
        const id_prov = $("#id_prov").val();

        $.ajax({
            type: "GET",
            url: siteurl + 'master_customers/getkota',
            data: {
                id_prov: id_prov
            },
            success: function(html) {
                $("#id_kabkot").html(html);
                $("#id_kec").html("<option value=''>--Pilih--</option>");
            }
        });
    }


    function get_kec() {
        var id_kabkot = $("#id_kabkot").val();
        $.ajax({
            type: "GET",
            url: siteurl + 'master_customers/getkecamatan',
            data: {
                id_kabkot: id_kabkot
            },
            success: function(html) {
                $("#id_kec").html(html);
            }
        });
    }

    function DelItem(id) {
        $('#list_payment #tr_' + id).remove();

    }

    function DelItem2(id) {
        $('#list_category #tr_' + id).remove();

    }

    function DelItem3(id) {
        $('#list_existing #tr_' + id).remove();
    }
</script>