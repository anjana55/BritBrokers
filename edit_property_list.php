<?php
	$no_recs = true;
	
	if(isset($_REQUEST['pids']) && $_REQUEST['pids']!=""){
		$pids = json_decode($_REQUEST['pids']);
		//var_dump($pids);
		if($pids){
			$no_recs = false;
		} else {
			$no_recs = true;
		}
	} else {
		$no_recs = true;
	}
	include_once("functions.php");
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
	$("#btn_cancel_conf").click(function(){
		location.reload();
	});	
	
	$("span[name='btn_edit']").click(function(){
		$("#loadingDiv").modal("show");
		var pid = $(this).attr("pid");

		$.ajax({
			type: 'POST',
			url: 'edit_property.php',
			data: {'pid':pid},
			success: function(response) {
				$("#work_area").html(response);
				$("#loadingDiv").modal("hide");
			},
			error: function(){
				$("#error_msg").removeClass("alert-success");
				$("#error_msg").addClass("alert alert-danger");
				$("#error_msg").html("Communication error occured");
				$("#loadingDiv").modal("hide");
			}
		});	
	})
});
</script>


<div class="container-fuid">
	<div class="panel panel-default" style="margin:20px;">
		<div class="panel-heading">
			<h3 class="panel-title">Edit Property</h3>
		</div>
		<div class="panel-body">
			<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;" id="frm_edit"  method="POST" action="ajax_functions.php">
				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table table-hover">
								<tr>
									<th>Action</th>
									<th>Property ID</th>
									<th>Type</th>
									<th>For</th>
									<th>Bedroom(s)</th>
									<th>Bathroom(s)</th>
									<th>District</th>
									<th>City</th>
									<th>Street</th>
									<!--
									<th>Keywords</th>
									<th>Description</th>
									-->
									<th>Price</th>
									<th>Date Added</th>
								</tr>
							<?php
							if($no_recs){
								echo "<div class='alert alert-danger' role='alert'>No records found!</div>";
							} else {
								foreach($pids as $pid){
									//echo $pid->property_id . "</br>";
									$property_details = getPropertyByID($link, $pid->property_id);
									$property_details = $property_details[0];
									echo "<tr>";
									echo "<td>";
									echo "<span name='btn_edit' pid='" . $property_details['property_id'] . "' class='btn btn-danger fileinput-button'>";
									echo "<i class='icon-edit'>&nbsp;</i>";
									echo "<span class='button'>Edit</span>";
									echo "</span>";
									echo "</td>";
									echo "<td>" . $property_details['property_id'] . "</td>";
									echo "<td>" . $property_details['property_type_name'] . "</td>";
									echo "<td>" . $property_details['business_type'] . "</td>";
									echo "<td class='text-center'>" . $property_details['bedrooms'] . "</td>";
									echo "<td class='text-center'>" . $property_details['bathrooms'] . "</td>";
									echo "<td>" . $property_details['district_name'] . "</td>";
									echo "<td>" . $property_details['city_name'] . "</td>";
									echo "<td>" . $property_details['street'] . "</td>";
									//echo "<td>" . $property_details['keywords'] . "</td>";
									//echo "<td>" . $property_details['description'] . "</td>";
									echo "<td> " . $property_details['currency'] . ". " . number_format($property_details['price'],0,".",",") . "</td>";
									echo "<td>" . $property_details['time_submitted'] . "</td>";
									//echo "<td>" . var_export($property_details,true) . "</td>";
									echo "</tr>";
								}
							}
							?>
							</table>
							<input type="hidden" name="tsk" value="pred"/>
						</div> 						
					</div>
				</div>
			</form>	
		</div>
		<div class="panel-footer">
			<span id="btn_cancel" class="btn btn-warning fileinput-button">
				<i class="icon-remove"></i>
				<span class="button" data-toggle="modal" data-target="#leave_confirm">Cancel</span>
			</span>
			<div id="error_msg" role="alert" style="display:none;"></div>
		</div>		
	</div>
	
	<?php
	@mysqli_close($link);
	?>
</div>