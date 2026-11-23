<?php require_once __DIR__ . "/bootstrap.php"; require_admin_ajax(); // admin only ?>
<?php
	$no_recs = true;
	
	if(isset($_REQUEST['pids']) && $_REQUEST['pids']!=""){
		// Search results arrive as JSON: [{"property_id": 12}, ...]. Keep only whole-number IDs.
		$pids = array();
		$decoded = json_decode((string)$_REQUEST['pids'], true);
		foreach((is_array($decoded) ? $decoded : array()) as $row){
			$id = to_int(is_array($row) ? ($row['property_id'] ?? "") : $row, 0);
			if($id > 0){
				$pids[] = (object)array("property_id" => $id);
			}
		}
		//var_dump($pids);
		if($pids){
			$no_recs = false;
		} else {
			$no_recs = true;
		}
	} else {
		$no_recs = true;
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
    $('#select_all').click(function(event) {  //on click 
        if(this.checked) { // check select status
            $('.checkbox').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"               
            });
        }else{
            $('.checkbox').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
            });         
        }
    });
	
	$("#btn_cancel_conf").click(function(){
		cpDone();
	});	
	
	$("#btn_delete_conf").click(function(){
		$("#loadingDiv").modal("show");
	   $.ajax({
			type: 'POST',
			url: 'ajax_functions.php',
			data: $('#frm_delete').serialize(),
			success: function(response) {
				if(response!=="" && response.length>2){
					//Successful response received
					var res = response.split("|");
					new_id = res[0];
					
					if(res[0]=="success"){
						$("#error_msg").removeClass("alert-danger");
						$("#error_msg").addClass("alert alert-success");
						//$res = json_decode(res[1]);
						$("#error_msg").html(res[1]);
						cpDone();
					} else {
						$("#error_msg").removeClass("alert-success");
						$("#error_msg").addClass("alert alert-danger");
						$("#error_msg").html("Error occured: " + res[1]);
					}
					$("#loadingDiv").modal("hide");
					$('#delete_confirm').modal('hide')
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
	})
});
</script>


<div class="container-fuid">
	<div class="panel panel-default" style="margin:20px;">
		<div class="panel-heading">
			<h3 class="panel-title">Delete Property</h3>
		</div>
		<div class="panel-body">
			<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;" id="frm_delete"  method="POST" action="ajax_functions.php">
				<div class="row">
					<div class="col-xs-12">
						<div class="table-responsive">
							<table class="table table-hover">
								<tr>
									<th><input type="checkbox" name="select_all" id="select_all"></th>
									<th>Property ID</th>
									<th>Type</th>
									<th>For</th>
									<th>Bedroom(s)</th>
									<th>Bathroom(s)</th>
									<th>District</th>
									<th>City</th>
									<th>Street</th>
									<th>Keywords</th>
									<th>Description</th>
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
									echo "<td><input class='checkbox' type='checkbox' name='pid[]' value='" . $property_details['property_id'] . "' /></td>";
									echo "<td>" . $property_details['property_id'] . "</td>";
									echo "<td>" . $property_details['property_type_name'] . "</td>";
									echo "<td>" . $property_details['business_type'] . "</td>";
									echo "<td class='text-center'>" . $property_details['bedrooms'] . "</td>";
									echo "<td class='text-center'>" . $property_details['bathrooms'] . "</td>";
									echo "<td>" . $property_details['district_name'] . "</td>";
									echo "<td>" . $property_details['city_name'] . "</td>";
									echo "<td>" . $property_details['street'] . "</td>";
									echo "<td>" . $property_details['keywords'] . "</td>";
									echo "<td>" . $property_details['description'] . "</td>";
									echo "<td> Rs. " . number_format($property_details['price'],0,".",",") . "</td>";
									echo "<td>" . $property_details['time_submitted'] . "</td>";
									//echo "<td>" . var_export($property_details,true) . "</td>";
									echo "</tr>";
								}
							}
							?>
							</table>
							<input type="hidden" name="tsk" value="prdl"/>
						</div> 						
					</div>
				</div>
			</form>	
		</div>
		<div class="panel-footer">
			<span id="btn_delete" class="btn btn-success fileinput-button">
				<i class="icon-trash">&nbsp;</i>

				<span class="button" data-toggle="modal" data-target="#delete_confirm">Delete</span>
			</span>
			<span id="btn_cancel" class="btn btn-warning fileinput-button">
				<i class="icon-remove"></i>
				<span class="button" data-toggle="modal" data-target="#leave_confirm">Cancel</span>
			</span>
			<div id="error_msg" role="alert" style="display:none;"></div>
		</div>		
	</div>
	
	<!-- Modal Leave confirm -->
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
	
	<!-- Delete Confirm -->
	<div class="modal fade" id="delete_confirm" tabindex="-1" role="dialog" aria-labelledby="dlconfirm" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
			<h4 class="modal-title" id="lvconfirm">Delete Permanently?</h4>
		  </div>
		  <div class="modal-body">
		  Are you sure you want to delete selected properties <b>PERMANENTLY</b>!
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
			<button type="button" class="btn btn-danger" id="btn_delete_conf">DELETE</button>
		  </div>
		</div>
	  </div>
	</div>	
	<?php
	@mysqli_close($link);
	?>
</div>