 <?php
	$ENABLE_ADD     = has_permission('master_suplier.Add');
	$ENABLE_MANAGE  = has_permission('master_suplier.Manage');
	$ENABLE_VIEW    = has_permission('master_suplier.View');
	$ENABLE_DELETE  = has_permission('master_suplier.Delete');
	foreach ($results['category'] as $category) {
	}
	?>
 <div class="box box-primary">
 	<div class="box-body">
 		<form id="data-form" method="post">
 			<div class="col-sm-12">
 				<div class="input_fields_wrap2">

 					<div class="row">
 						<div class="form-group row">
 							<div class="col-md-4">
 								<label for="customer">ID Kategori Customer</label>
 							</div>
 							<div class="col-md-6">
 								<input type="text" class="form-control" id="" value="<?= $category->id_category_customer ?>" readonly required name="id_category_customer" placeholder="Id Category Suplier">
 							</div>
 						</div>
 						<div class="form-group row">
 							<div class="col-md-4">
 								<label for="customer">Kategori Customer</label>
 							</div>
 							<div class="col-md-6">
 								<input type="text" class="form-control" id="" value="<?= $category->name_category_customer ?>" required name="name_category_customer" placeholder="Category Supliery">
 							</div>
 						</div>
 						<div class="form-group row" hidden>
 							<div class="col-md-4">
 								<label for="customer">Kode Kategori Customer</label>
 							</div>
 							<div class="col-md-6">
 								<input type="text" class="form-control" id="" value="<?= $category->customer_code ?>" required name="customer_code" placeholder="Category Suplier Code">
 							</div>
 						</div>
 					</div>
 				</div>
 			</div>
 			<div class="col-sm-3">
 			</div>
 	</div>
 </div>


 <hr>
 <center>
 	<!--<button type="submit" class="btn btn-primary btn-sm add_field_button2" name="save"><i class="fa fa-plus"></i>Add Main Produk</button>
					--><button type="submit" class="btn btn-success btn-sm" name="save" id="simpan-com"><i class="fa fa-save"></i>Simpan</button>
 </center>

 </form>



 </div>
 </div>




 <script type="text/javascript">
 	//$('#input-kendaraan').hide();
 	var base_url = '<?php echo base_url(); ?>';
 	var active_controller = '<?php echo ($this->uri->segment(1)); ?>';

 	$(document).ready(function() {

 		///INPUT PERKIRAAN KIRIM


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
 			Template += '<input type="text" class="form-control input-sm" name="data1[' + loop + '][name_compotition]" id="data1_' + loop + '_name_compotition" label="FALSE" div="FALSE">';
 			Template += '</td>';
 			Template += '<td align="center"><button type="button" class="btn btn-sm btn-danger" title="Hapus Data" data-role="qtip" onClick="return DelItem(' + loop + ');"><i class="fa fa-trash-o"></i></button></td>';
 			Template += '</tr>';
 			$('#list_payment').append(Template);
 			$('input[data-role="tglbayar"]').datepicker({
 				format: 'dd-mm-yyyy',
 				autoclose: true
 			});
 		});



 		$('#simpan-com').click(function(e) {
 			e.preventDefault();
 			var deskripsi = $('#deskripsi').val();
 			var image = $('#image').val();
 			var idtype = $('#inventory_1').val();

 			var data, xhr;
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
 						var baseurl = siteurl + 'master_customers/saveEditCategory';
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

 									if (data.status == 2) {
 										swal({
 											title: "Save Failed!",
 											text: data.pesan,
 											type: "warning",
 											timer: 7000,
 											showCancelButton: false,
 											showConfirmButton: false,
 											allowOutsideClick: false
 										});
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
 						return false;
 					}
 				});
 		});

 	});

 	function DelItem(id) {
 		$('#list_payment #tr_' + id).remove();

 	}
 </script>