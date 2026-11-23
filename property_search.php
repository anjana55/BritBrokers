<?php require_once __DIR__ . "/bootstrap.php"; require_admin_ajax(); // admin only ?>
<?php
	if(isset($_REQUEST['ru']) && $_REQUEST['ru']!=""){
		// Only the admin screens that use this search may be returned to
		$return_url = in_array($_REQUEST['ru'], array("delete_property.php", "edit_property_list.php"), true) ? $_REQUEST['ru'] : "";
	} else {
		$return_url = "";
	}
	require_once __DIR__ . "/bootstrap.php";
	$link = setConnection();
?>
<style type="text/css">
#frm_property .form-control-feedback {
    /* Adjust feedback icon position */
    right: -15px !important;
}
</style>

<script>
$(document).ready(function() {
	var return_url = <?php echo json_encode($return_url); ?>;
	$("#btn_cancel_conf").click(function(){
		cpDone();
	});

	$("#btn_search").click(function(){
	   $.ajax({
			type: 'POST',
			url: 'ajax_functions.php',
			data: $('#frm_search').serialize(),
			success: function(response) {
				if(response!=="" && response.length>2){
					//Successful response received
					var res = response.split("|");
					new_id = res[0];
					
					if(res[0]=="success"){
						$("#error_msg").removeClass("alert-danger");
						$("#error_msg").addClass("alert alert-success");
						//alert(res[1]);
						if(return_url!=""){
							$.ajax({
								type: 'POST',
								url: return_url,
								data: {pids: res[1]},
								success: function(data){
									$("#work_area").html(data);
								},
								error: function(){
									$("#error_msg").removeClass("alert-success");
									$("#error_msg").addClass("alert alert-danger");
									$("#error_msg").html("Error occured: Could not load page [" + return_url + "]");
								}
							});							
						} else {
							$("#error_msg").html(res[1] + " Loading results...");
						}
					} else {
						$("#error_msg").removeClass("alert-success");
						$("#error_msg").addClass("alert alert-danger");
						$("#error_msg").html("Error occured: " + res[1]);
					}
					$("#loadingDiv").modal("hide");					
				} else {
					$("#error_msg").removeClass("alert-success");
					$("#error_msg").addClass("alert alert-danger");
					$("#error_msg").html("Communication error occured");
					$("#loadingDiv").modal("hide");
				}
				$("#error_msg").show();
				$("#error_msg").css({"display":"inline"});

			},
			error: function(){
				$("#error_msg").removeClass("alert-success");
				$("#error_msg").addClass("alert alert-danger");
				$("#error_msg").html("Communication error occured");
				$("#loadingDiv").modal("hide");
			}
		});
	});
	
	$("#featured").click(function(){
		if($("#featured").prop( "checked" )){
			$("#hdn_featured").prop("disabled",true);
		} else {
			$("#hdn_featured").prop("disabled",false);
		}
	});		
});
</script>

<div class="container-fuid">
	<div class="panel panel-default" style="margin:20px;">
		<div class="panel-heading">
			<h3 class="panel-title">Property Search</h3>
		</div>
		<div class="panel-body">
			<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;" id="frm_search"  method="POST" action="ajax_functions.php">
				<div class="row">
					<div class="col-sm-12 col-md-12">
						<div class="form-group">
							<label class="col-xs-3 control-label" for="property_id">Property ID:&nbsp;</label>
							<div class="col-xs-9">
								<input type="text" name="property_id" id="property_id" class="form-control" size="5">
							</div>
						</div>

						<div class="form-group">
							<label class="col-xs-3 control-label" for="business_type">Business Type:&nbsp;</label>
							<div class="col-xs-9">
								<select name="business_type" id="business_type" class="form-control" placeholder="Select Business Type">
									<option value="" label="">Any</option>
								<?php
									echo fillSelect($link, "business_types", "business_type", "business_type", "bt_id");
								?>
								</select>
							</div>
						</div>		

						<div class="form-group">
							<label class="col-xs-3 control-label" for="property_type">Property Type:&nbsp;</label>
							<div class="col-xs-9">
								<select name="property_type" id="property_type" class="form-control" placeholder="Select Property Type">
									<option value="" label="">Any</option>
								<?php
									echo fillSelect($link, "property_types", "property_type_name", "property_type_name", "pt_id");
								?>
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-xs-3 control-label" for="bedrooms">Bedrooms:&nbsp;</label>
							<div class="col-xs-9">
								<input type="number" name="bedrooms" id="bedrooms" class="form-control" placeholder="No. of bedrooms" size="3" data-bv-integer="true" required="">
							</div>
						</div>

						<div class="form-group">
							<label class="col-xs-3 control-label" for="bathrooms">Bathrooms:&nbsp;</label>
							<div class="col-xs-9">
								<input type="number" name="bathrooms" id="bathrooms" class="form-control" placeholder="No. of bathrooms" size="3" data-bv-integer="true" required="">
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-xs-3 control-label">District</label>
							<div class="col-xs-9 selectContainer">
								<select name="dct" id="district" class="form-control" title="Select a district" >
									<option value=""></option>
									<?php
										echo fillSelect($link, "districts", "district_name", "district_name", "district_id");
									?>
								</select>
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-xs-3 control-label" for="city">City:&nbsp;</label>
							<div class="col-xs-9">
								<select name="city" id="city" class="form-control" placeholder="Select a city" disabled="" >
									<option value=""></option>
								</select>			
							</div>
						</div>

						<div class="form-group">
							<label class="col-xs-3 control-label" for="old">Older than:&nbsp;</label>
							<div class="col-xs-9">
								<select name="older" id="older" class="form-control">
									<option value=""></option>
									<option label="3 Months" value="3">3 Months</option>
									<option label="6 Months" value="6">6 Months</option>
									<option label="12 Months" value="12">12 Months</option>
									<option label="18 Months" value="18">18 Months</option>
									<option label="24 Months" value="24">24 Months</option>
								</select>
							</div>
						</div>
				
						<div class="form-group">
							<label class="col-xs-3 control-label" for="featured">Featured:&nbsp;</label>
							<div class="col-xs-9">
								<div class="col-xs-1">
									<input type="checkbox" name="featured" id="featured" class="form-control" value="Y">
									<input id='hdn_featured'  type='hidden' value='N' name='featured'>
								</div>
								<div class="col-xs-8">&nbsp;</div>
							</div>
						</div>
						
						<input type="hidden" name="tsk" value="srpr">
					</div>
				</div>
			</form>	
		</div>
		<div class="panel-footer">
			<span id="btn_search" class="btn btn-success fileinput-button">
				<i class="icon-search">&nbsp;</i>

				<span class="button">Search</span>
			</span>
			<span id="btn_cancel" class="btn btn-warning fileinput-button">
				<i class="icon-remove"></i>
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
		  Are you sure you want to navigate away from current page?
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
</div>