<?php require_once __DIR__ . "/bootstrap.php"; require_admin_ajax(); // admin only ?>
<?php
	require_once __DIR__ . "/bootstrap.php";
	$link = setConnection();

	$no_recs = true;
	
	if(isset($_REQUEST['pid']) && $_REQUEST['pid']!=""){
		$pid = to_int($_REQUEST['pid'], 0);
		$property = getPropertyByID($link, $pid);
		$property = $property ? $property[0] : "";
		$no_recs = !$property;
	} else {
		$pid="";
		$property="";
		$no_recs = true;
	}
	
	//var_dump($property);
?>
<style type="text/css">
#frm_property .form-control-feedback {
    /* Adjust feedback icon position */
    right: -15px !important;
}
</style>
<script>
 
$(document).ready(function() {
	var pid = '<?php echo $pid;  ?>';
	change_bt();
	
    $('#frm_edit_property')
	.find('[name="district"]')
		.selectpicker()
		.change(function(e) {
			// revalidate the color when it is changed
			$('#frm_edit_property').bootstrapValidator('revalidateField', 'district');
		})
		.end()
	
	
	.bootstrapValidator({
        // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            bedrooms: {
                message: 'The bedrooms count is not valid',
                validators: {
                    notEmpty: {
                        message: 'The bedrooms count is required and cannot be empty'
                    }
                }
            },
            bathrooms: {
                message: 'The bathrooms count is not valid',
                validators: {
                    notEmpty: {
                        message: 'The bathrooms count is required and cannot be empty'
                    }
                }
            },
            dct: {
                message: 'The district is not valid',
                validators: {
					notEmpty: {
						message: 'Please select a valid district'
					}
                }
            }
		}
    })
	.on('success.form.bv', function(e) {
		$("#loadingDiv").modal("show");
		// Prevent form submission
		e.preventDefault();

		// Get the form instance
		var $form = $(e.target);

		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');

		// Use Ajax to submit form data
		//$.post($form.attr('action'), $form.serialize(), function(result) {
			// ... Process the result ...
		//}, 'json');
		
	   $.ajax({
			type: 'POST',
			url: 'ajax_functions.php',
			data: $('#frm_edit_property').serialize(),
			success: function(response) {
				if(response!=="" && response.length>2){
					//Successful response received
					var res = response.split("|");
					if(res[0]=="success"){
						$("#error_msg").removeClass("alert-danger");
						$("#error_msg").addClass("alert alert-success");
						$("#error_msg").html(res[1]);
						cpDone();
					} else {
						$("#error_msg").removeClass("alert-success");
						$("#error_msg").addClass("alert alert-danger");
						$("#error_msg").html("Error occured: " + res[1]);
					}
				} else {
					$("#error_msg").addClass("alert alert-danger");
					$("#error_msg").html("Communication error occured");
					$("#loadingDiv").modal("hide");
				}
				$("#error_msg").show();
				$("#error_msg").css({"display":"inline"});
				//$("#work_area").html(response);
				$("#loadingDiv").modal("hide");
			}

		});		
	});	

	
	$("#btn_cancel_conf").click(function(){
		cpDone();
	});

	
	$("#btn_update").click(function(){
		$("#city").prop( "disabled", false );
		
		var currency = $("#currency").val();
		var price = $("#price").val();		
		if(currency=="LKR" && price <10000){
			$.confirm({
				text: "Something is bit wonkey! " + $("#business_type").val() + " price is " + price + currency +"? Tad off the trolly today are we? Do you really want to add this record as it is?",
				confirm: function() {
					//alert("Form will be submitted by now");
					doUpdate();
				},
				cancel: function() {
					// nothing to do
				}
			});
		} else {
			//alert("No price problems. Form will be submitted by now");
			doUpdate();
		}		
	});

	function doUpdate(){
		if(myDropzone.getQueuedFiles().length<=0){
			$("#frm_edit_property").submit();	
		} else {
			myDropzone.processQueue();
		}		
	}
	
	$("a[name='img_del']").click(function(){
		var img_id = $(this).attr('iid');	
		$.confirm({
			text: "Are you sure you want to delete that image?",
			confirmButtonClass: "btn-danger",
			confirm: function() {
				$.ajax({
					type: 'POST',
					url: 'ajax_functions.php',
					data: {"tsk":"imdl", "pid": pid, "img_id": img_id},
					success: function(response) {
						if(response!=="" && response.length>2){
							//Successful response received
							var res = response.split("|");

							if(res[0]=="success"){
								$("#error_msg").removeClass("alert-danger");
								$("#error_msg").addClass("alert alert-success");
								$("#error_msg").html(res[1]);
								
								//remove image
								$("#img_" + img_id).hide("slow");
							} else {
								$("#error_msg").removeClass("alert-success");
								$("#error_msg").addClass("alert alert-danger");
								$("#error_msg").html("Error occured: " + res[1]);
							}						
						} else {
							$("#error_msg").addClass("alert alert-danger");
							$("#error_msg").html("Communication error occured");
							$("#loadingDiv").modal("hide");
						}
						$("#error_msg").show();
						$("#error_msg").css({"display":"inline"});
					}

				});			
			},
			cancel: function() {
				// nothing to do
			}
		});
	});
	
	$("#del_all_img").click(function(){	
		$.confirm({
			text: "Are you sure you want to delete all images?",
			confirmButtonClass: "btn-danger",
			confirm: function() {
				$.ajax({
					type: 'POST',
					url: 'ajax_functions.php',
					data: {"tsk":"dlai", "pid": pid},
					success: function(response) {
						if(response!=="" && response.length>2){
							//Successful response received
							var res = response.split("|");

							if(res[0]=="success"){
								$("#error_msg").removeClass("alert-danger");
								$("#error_msg").addClass("alert alert-success");
								$("#error_msg").html(res[1]);
								
								//remove image
								//$("#img_" + img_id).hide("slow");
								$("[id^=img_]").hide("slow");
							} else {
								$("#error_msg").removeClass("alert-success");
								$("#error_msg").addClass("alert alert-danger");
								$("#error_msg").html("Error occured: " + res[1]);
							}						
						} else {
							$("#error_msg").addClass("alert alert-danger");
							$("#error_msg").html("Communication error occured");
							$("#loadingDiv").modal("hide");
						}
						$("#error_msg").show();
						$("#error_msg").css({"display":"inline"});
					}

				});			
			},
			cancel: function() {
				// nothing to do
			}
		});
	});

	
	$("a[name='img_fet']").click(function(){
		var img_id = $(this).attr('iid');	
		$.confirm({
			text: "Featured image will be changed to selected. Continue?",
			confirmButtonClass: "btn-warning",
			confirm: function() {
				$.ajax({
					type: 'POST',
					url: 'ajax_functions.php',
					data: {"tsk":"imft", "pid": pid, "img_id": img_id},
					success: function(response) {
						if(response!=="" && response.length>2){
							//Successful response received
							var res = response.split("|");

							if(res[0]=="success"){
								$("#error_msg").removeClass("alert-danger");
								$("#error_msg").addClass("alert alert-success");
								$("#error_msg").html(res[1]);
								
								//remove image
								//$("#img_" + img_id).hide("slow");
							} else {
								$("#error_msg").removeClass("alert-success");
								$("#error_msg").addClass("alert alert-danger");
								$("#error_msg").html("Error occured: " + res[1]);
							}						
						} else {
							$("#error_msg").addClass("alert alert-danger");
							$("#error_msg").html("Communication error occured");
							$("#loadingDiv").modal("hide");
						}
						$("#error_msg").show();
						$("#error_msg").css({"display":"inline"});
					}

				});			
			},
			cancel: function() {
				// nothing to do
			}
		});
	});
	
	var myDropzone = new Dropzone("#dropzone_area",
	{ 
		url: "upload.php",
		headers: { "X-CSRF-Token": window.BB_CSRF },
		autoProcessQueue: false,
		addRemoveLinks: true,
		params: {"prop_id" : pid},
		parallelUploads: 15,
		init: function() {
			this.on("addedfile", function(file) {

			});
		}		
	});

	myDropzone.on("queuecomplete", function(file) {
		$("#error_msg").addClass("alert alert-success");
		$("#error_msg").html("All files sucessfully uploaded!");
		$("#frm_edit_property").submit();
	});
	
	$("#lkr").click(function(){
		change_currency("LKR");
	});

	$("#usd").click(function(){
		change_currency("USD");
	});

	$("#gbp").click(function(){
		change_currency("GBP");
	});
	
	function change_currency(curr){
		var dsp="";
		
		switch (curr){
			case "LKR":
				dsp = "Rs. (LKR)";
				break;
			case "USD":
				dsp = "$ (USD)";
				break;			
			case "GBP":
				dsp = "&pound; (GBP)";
			break;
		}
		
		$("#cl").html(dsp);
		$("#currency").val(curr);
	}

	$("#business_type").change(function(){
		change_bt();
	});
	
	function change_bt(){
		if($("#business_type").val()=="R"){///Rent
			$("#term_section").show();
		} else {
			$("#term_section").hide();
		}
	}
	
	$("#featured").click(function(){
		if($("#featured").prop( "checked" )){
			$("#hdn_featured").prop("disabled",true);
		} else {
			$("#hdn_featured").prop("disabled",false);
		}
	});	
}); 
</script>

<div class="panel panel-default" style="margin:20px;">
  <div class="panel-heading">
    <h3 class="panel-title">
	Edit Property
	<?php
	if($no_recs){
		echo "<div class='alert alert-danger' role='alert'>No record specified for editing</div>";
	}else{
		echo " - Property ID " . $pid . " created on " . $property["time_submitted"];
	}
	?>
	</h3>
  </div>
  <div class="panel-body">
	<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;" id="frm_edit_property"  method="POST" action="ajax_functions.php">
		<div class="row">
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label class="col-xs-3 control-label" for="property_id">Property ID:&nbsp;</label>
					<div class="col-xs-9">
						<input type="text" id="pid" class="form-control" value='<?php echo $pid; ?>' size="5" disabled="">
						<input type="hidden" name="pid" value='<?php echo $pid; ?>'>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="status">Status:&nbsp;</label>
					<div class="col-xs-9">
						<select name="status" id="status" class="form-control" placeholder="Select Status">
							<option value="<?php echo $property["status"]; ?>" label="<?php echo $property["status"]; ?>"><?php echo $property["status"]; ?></option>
							<option value="Available Now" label="Available Now">Available Now</option>
							<option value="Available Soon" label="Available Soon">Available Soon</option>
							<option value="Let" label="Let">Let</option>
							<option value="For Sale" label="For Sale">For Sale</option>
							<option value="Sold" label="Sold">Sold</option>
							<option value="Under Offer" label="Under Offer">Under Offer</option>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-xs-3 control-label" for="business_type">Business Type:&nbsp;</label>
					<div class="col-xs-9">
						<select name="business_type" id="business_type" class="form-control" placeholder="Select Business Type">
						<?php
							echo fillSelect($link, "business_types", "business_type", "business_type", "bt_id","",$property["bt_id"]);
							//$link, $table, $text_field="", $label_field="", $value_field="", $filter="", $selected=""
						?>
						</select>
					</div>
				</div>		

				<div class="form-group">
					<label class="col-xs-3 control-label" for="property_type">Property Type:&nbsp;</label>
					<div class="col-xs-9">
						<select name="property_type" id="property_type" class="form-control" placeholder="Select Property Type">
						<?php
							echo fillSelect($link, "property_types", "property_type_name", "property_type_name", "pt_id","",$property["pt_id"]);
						?>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-xs-3 control-label" for="bedrooms">Bedrooms:&nbsp;</label>
					<div class="col-xs-9">
						<input type="number" name="bedrooms" id="bedrooms" class="form-control" placeholder="No. of bedrooms" size="3" value="<?php echo $property["bedrooms"]; ?>" data-bv-integer="true" required="">
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="bathrooms">Bathrooms:&nbsp;</label>
					<div class="col-xs-9">
						<input type="number" name="bathrooms" id="bathrooms" class="form-control" placeholder="No. of bathrooms" size="3" value="<?php echo $property["bathrooms"]; ?>" data-bv-integer="true" required="">
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-xs-3 control-label">District</label>
					<div class="col-xs-9 selectContainer">
						<select name="dct" id="district" class="form-control" title="Select a district" >
							<option value=""></option>
							<?php
								echo fillSelect($link, "districts", "district_name", "district_name", "district_id","",$property["district_id"]);
							?>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-xs-3 control-label" for="city">City:&nbsp;</label>
					<div class="col-xs-9">
						<select name="city" id="city" class="form-control" placeholder="Select a city" disabled="" >
							<option value="<?php echo $property["city_id"]; ?>"><?php echo $property["city_name"]; ?></option>
						</select>			
					</div>
				</div>
			
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label class="col-xs-3 control-label" for="street">Street:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="street" id="street" class="form-control"  placeholder="Street Address"><?php echo $property["street"]; ?></textarea>
					</div>
				</div>		

				<div class="form-group">
					<label class="col-xs-3 control-label" for="keywords">Keywords:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="keywords" id="keywords" class="form-control"  placeholder="Keywords"><?php echo $property["keywords"]; ?></textarea>
					</div>
				</div>	

				<div class="form-group">
					<label class="col-xs-3 control-label" for="description">Description:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="description" id="description" class="form-control"  placeholder="Description" rows="10"><?php echo $property["description"]; ?></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="price">Price :&nbsp;</label>
					<div class="col-xs-9">
						<div class="input-group">
							<input type="number" name="price" id="price" class="form-control" placeholder="Price" required="" value="<?php echo $property["price"]; ?>">						
							<div class="input-group-btn">
							
							<?php
							if(isset($property["currency"])){
								switch ($property["currency"]){
									case "LKR":
										$dsp_cur="Rs. (LKR)";
										break;
									case "USD":
										$dsp_cur="$ (USD)";
										break;
									case "GBP":
										$dsp_cur="&pound; (GBP)";
										break;
								}
							} else {
								$dsp_cur = "Rs. (LKR)";
							}
							?>
							<button id="currency_button" type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><span id="cl"><?php echo $dsp_cur; ?></span><span class="caret"></span></button>
							<ul class="dropdown-menu dropdown-menu-right" role="menu">
							  <li id="lkr"><a href="#">Rs. (LKR)</a></li>
							  <li id="usd"><a href="#">$ (USD)</a></li>
							  <li id="gbp"><a href="#">&pound; (GBP)</a></li>
							</ul>
							</div><!-- /btn-group -->
							<input type="hidden" name="currency" id="currency" value="<?php echo $property["currency"]; ?>">
						</div><!-- /input-group -->
					</div>
				</div>

				<div id="term_section" class="form-group">
					<label class="col-xs-3 control-label">Term:</label>
					<div class="col-xs-9 selectContainer">
						<select name="term" id="term" class="form-control" title="Payment term" >
							<?php
							$options = array( "d"=>"Per day", "w"=>"Per week", "m"=>"Per month", "y"=>"Per year" );

							$output = '';
							foreach($options as $key=>$value) {
								$output .= "<option value='" . $key . "'";
								if($property["term"] == $key){
									$output .= "selected='selected'";
								}
								$output .= ">" . $value . "</option>";
							}
							echo $output;
							?>
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-xs-3 control-label" for="featured">Featured:&nbsp;</label>
					<div class="col-xs-9">
						<div class="col-xs-1">
							<input type="checkbox" name="featured" id="featured" class="form-control" value="Y" 
							<?php
								if($property['featured']=="Y"){
									echo "checked='checked'";
									$hfd="disabled='disabled'";
								} else {
									$hfd="";
								}
							?>
							>
							<input id='hdn_featured'  type='hidden' value='N' name='featured'
							<?php
								echo $hfd;
							?>
							>
						</div>
						<div class="col-xs-8">&nbsp;</div>
					</div>
				</div>
				
				<input type="hidden" name="tsk" value="edpr">
			</div>
			<div class="col-sm-12 col-md-12">
				<div class="panel panel-default">
				  <div class="panel-heading">Features</div>
				  <div class="panel-body">
					<?php
					$features = getFeaturesByPropertyID($link,$pid);
					$selected=array();
					
					if($features){
						foreach($features as $feature){
							//echo $feature["feature_id"];
							array_push($selected,$feature["feature_id"]);
						}
					}

					echo getCheckBoxes($link, "features[]", "checkbox", "feature_types", "feature", "feature_id","",$selected, "frm_edit_property");

					?>
				  </div>
				</div>			
			</div>
			<div class="col-sm-12 col-md-12">
				<div class="panel panel-default">
				  <div class="panel-heading">Video</div>
				  <div class="panel-body">
					<?php
					$videos = getPropertyImages($link,$pid,99,"PROVD");
					//var_dump($videos);
					if($videos){
						foreach($videos as $video){
							$vtag =  $video["img_url"];	
							$vid =  $video["img_id"];	
						}
					} else {
							$vtag =  "";	
							$vid =  "";						
					}
					?>
					<div class="form-group">
						<div class="col-xs-3">
							<label class="col-xs-3 control-label" for="available_features">YouTube video tag:&nbsp;</label>
						</div>
						<div class="col-xs-9">
							<input type="text" name="youtubetag" id="youtubetag" size="10" value="<?php echo $vtag; ?>"/>
							<input type="hidden" name="youtubeid" id="youtubeid" size="10" value="<?php echo $vid; ?>"/>
						</div>
					</div>					
				  </div>
				</div>			
			</div>
			
			<div class="col-sm-12 col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Images</div>
						<div class="panel-body">
						<?php
						$images = getPropertyImages($link,$pid,99,"PROMN");
						$images = array_merge($images, getPropertyImages($link,$pid,99,"PROPN"));
						
						if($images){
							echo "<p><a href='#' name='del_all_img' id='del_all_img' class='btn btn-danger' role='button'>Delete All Images</a></p>";
							foreach($images as $image){
								echo "<div class='col-sm-3 col-md-3' id='img_" . $image['img_id'] . "'>";
								echo "<div class='thumbnail'>";
								echo "<a href='#' class='thumbnail'>";
								echo "<img src='". $image['img_url'] . "' >";
								echo "</a>";
								echo "</div>";
								echo "<span><a href='#' name='img_del' iid='" . $image['img_id'] . "' class='btn btn-danger' role='button'>Delete</a></span>&nbsp;";
								if($image['featured']=="Y"){
									echo "<span class='label label-primary' role='alert'>Featured Image</span>";
								} else {
									echo "<span><a href='#' name='img_fet' iid='" . $image['img_id'] . "' class='btn btn-success' role='button'>Make Featured</a></span>";
								}
								echo "</div>";
							}
						}
						?>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-sm-12 col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading">Upload New Images</div>
						<div class="panel-body">
							<div class="dropzone" id="dropzone_area" name="dropzone_area" >
								Click or Drag your images here to upload.<br/>
							</div>
						</div>
					</div>
				</div>
			</div>			
		</div>
	</form>	
  </div>
  <div class="panel-footer">
 		<span id="btn_update" class="btn btn-success fileinput-button">
			<i class="icon-pencil">&nbsp;</i>

			<span class="button">Update</span>
		</span>
		<span id="btn_cancel" class="btn btn-warning fileinput-button">
			<i class="icon-cog"></i>
			<span class="button" data-toggle="modal" data-target="#leave_confirm">Cancel</span>
		</span>
		<div id="error_msg" role="alert" style="display:none;"></div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="leave_confirm" tabindex="-1" role="dialog" aria-labelledby="lvconfirm" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="lvconfirm">Leave Page?</h4>
      </div>
      <div class="modal-body">
      Are you sure you want to navigate away from current page? All unsaved work will be lost!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-primary" id="btn_cancel_conf">Yes</button>
      </div>
    </div>
  </div>
</div>
<?php
@mysqli_close($link);
?>