<?php require_once __DIR__ . "/functions.php"; require_admin_ajax(); // admin only ?>
<?php
	include_once("functions.php");
	$link = setConnection();
	
	if(isset($_REQUEST['id']) && $_REQUEST['id']!=""){
		$prop_id = to_int($_REQUEST['id'], 0) ?: "";
	} else {
		$prop_id="";
	}
?>
<?php
//include "header.php";
?>
<style type="text/css">
#frm_property .form-control-feedback {
    /* Adjust feedback icon position */
    right: -15px !important;
}
</style>
<script>

$(document).ready(function() {
	function send_data(){
		$.ajax({
			type: 'POST',
			url: 'ajax_functions.php',
			data: $('#frm_features').serialize(),
			success: function(response) {
				if(response!=="" && response.length>2){
					//Successful response received
					var res = response.split("|");
					st = res[0];
					message = res[1];
					if(st=="success"){
						$("#error_msg").addClass("alert alert-success");
						$("#error_msg").removeClass("alert-danger");
						$("#error_msg").html(message);
						$("#all_set").modal("show");
					} else {
						$("#error_msg").addClass("alert alert-danger");
						$("#error_msg").removeClass("alert-success");
						$("#error_msg").html(message);
					}
				} else {
					$("#error_msg").addClass("alert alert-danger");
					$("#error_msg").html("Communication error occured");
				}
				$("#error_msg").show();
				$("#error_msg").css({"display":"inline"});
				//$("#work_area").html(response);
				$("#loadingDiv").hide();
			}
		});	
	}
	
	var prop_id="<?php echo $prop_id; ?>";
	
	if(prop_id==""){
		$("#top_msg").show("slow");
	} else {
		$("#top_msg").hide();
	}
	
	var myDropzone = new Dropzone("#dropzone_area",
	{ 
		url: "upload.php",
		headers: { "X-CSRF-Token": window.BB_CSRF },
		autoProcessQueue: false,
		addRemoveLinks: true,
		params: {"prop_id" : prop_id},
		parallelUploads: 15,
		init: function() {
			this.on("addedfile", function(file) {

			});
		}		
	});

	myDropzone.on("queuecomplete", function(file) {
		$("#error_msg").addClass("alert alert-success");
		$("#error_msg").html("All files sucessfully uploaded! Writing features list in to database...");
		send_data();
	});

	myDropzone.on("complete", function(file) {
	  //myDropzone.removeFile(file);
	});

    $("#image-dropzone").sortable({
        items:'.dz-preview',
        cursor: 'move',
        opacity: 0.5,
        containment: '#image-dropzone',
        distance: 20,
        tolerance: 'pointer'
    });
	
	$("#btn_upload").click(function(){
		$("#dropzone_area").addClass("dropzone");
		$("#dropzone_area").show("slow");
		$("#btn_upload").hide("slow");
	});
	
		
	$("#btn_cancel_conf").click(function(){
		location.reload();
	});
	
	$("#btn_save").click(function(){
		$("#loadingDiv").modal("show");
		if(myDropzone.getQueuedFiles().length<=0){
			send_data();
		} else {
			myDropzone.processQueue();
		}
	});
	
	$("#btn_ok").click(function(){
		document.location.href = "./control_panel.php";
	});
});
</script>
<div class="panel panel-default" style="margin:20px;">
  <div class="panel-heading">
    <h3 class="panel-title">Add New Property - Step 2</h3>
	<div id="top_msg" role="alert" class="alert alert-danger" style="display:none;">Invalid request!</div>
  </div>
  <div class="panel-body">
	<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;background-color:#fff;" id="frm_features"  method="POST" action="ajax_functions.php">
		<div class="row">
			<div class="col-sm-12 col-md-12">
				<div class="form-group">
					<label class="col-xs-3 control-label" for="property_id">Property ID:&nbsp;</label>
					<div class="col-xs-9">
						<input type="text" id="property_id" class="form-control" placeholder="Auto Generate" size="5" value="<?php echo $prop_id; ?>" disabled="">
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="available_features">Available Features:&nbsp;</label>
					<div class="col-xs-9">
						<?php
							//echo fillSelect($link, "feature_types", "feature", "feature", "feature_id");
							echo getCheckBoxes($link, "features[]", "checkbox-inline", "feature_types", "feature", "feature_id");
						?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-xs-3 control-label" for="available_features">YouTube video tag:&nbsp;</label>
					<div class="col-xs-9">
						<input type="test" name="youtubetag" id="youtubetag" size="10" />
					</div>
				</div>
				
				<div class="col-xs-3">
					<span id="btn_upload" class="btn btn-success fileinput-button">
						<i class="icon-upload">&nbsp;</i>

						<span class="button">Upload Images</span>
					</span>
				</div>
				<div class="col-xs-9">
					<div id="dropzone_area" name="dropzone_area" style="display:none;">
						Click or Drag your images here to upload.<br/>
					</div>
				</div>
				<input type="hidden" name="prop_id" value="<?php echo $prop_id; ?>">
				<input type="hidden" name="tsk" value="adft">
			</div>
		</div>	
	</form>	
  </div>
  <div class="panel-footer">
 		<span id="btn_save" class="btn btn-success fileinput-button">
			<i class="icon-save">&nbsp;</i>

			<span class="button">Save</span>
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

<!-- All Set Modal -->
<div class="modal fade" id="all_set" tabindex="-1" role="dialog" aria-labelledby="asconfirm" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="lvconfirm">All Set</h4>
      </div>
      <div class="modal-body">
      Everything written in to database successfully!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn_ok">OK</button>
      </div>
    </div>
  </div>
</div>
<?php
@mysqli_close($link);
?>