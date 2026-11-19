<?php
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
    $('#frm_property')
	.find('[name="district"]')
		.selectpicker()
		.change(function(e) {
			// revalidate the color when it is changed
			$('#frm_property').bootstrapValidator('revalidateField', 'district');
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
			data: $('#frm_property').serialize(),
			success: function(response) {
				if(response!=="" && response.length>2){
					$("#error_msg").addClass("alert alert-success");
					var res = response.split("|");
					$("#error_msg").html(res[1]);
				} else {
					$("#error_msg").addClass("alert alert-danger");
					$("#error_msg").html("Communication error occured");
				}
				$("#error_msg").show();
				$("#error_msg").css({"display":"inline"});
				//$("#work_area").html(response);
				$("#loadingDiv").modal("hide");
			}

		});		
	});	

	
	$("#work_area").on("change", "[id^='district']", function() {
		//var did = $(this).children(":selected").attr("id");
		var did = $(this).children(":selected").val();
		//alert(did);
		$("#loadingDiv").modal("show");
		$.ajax({
		  url: "ajax_functions.php",
		  data: { tsk: "dbfc", data: did },
		  success: function(data){
			$("#city")
			.empty()
			//.append('<option>Please select a value</option>')
			;
			//$.each(data, function (key, value) {
				$("#city").append(data);
			//});
			$("#city").prop('disabled', false);
			//$("#work_area").html(data);
			$("#loadingDiv").modal("hide");
		  },
		  error: function(){
			$("#loadingDiv").modal("hide");
		  }
		});
	});
	
	$("#btn_cancel_conf").click(function(){
		location.reload();
	});

	
	$("#btn_addnew").click(function(){
		$("#frm_property").submit();
		/*
		$('#frm_property').submit(function(e){
			alert("submitting....");
			e.preventDefault();
			var $form = $(this);

			alert($form.valid());
		  // check if the input is valid
			if(! $form.valid()) return false;

		   $.ajax({
				type: 'POST',
				url: 'ajax_functions.php',
				data: $('#frm_property').serialize(),
				success: function(response) {
					$("#work_area").html(response);
				}

			});
		});
		*/
	});
});
</script>
<div class="panel panel-default" style="margin:20px;">
  <div class="panel-heading">
    <h3 class="panel-title">Add New Property - Step 1</h3>
  </div>
  <div class="panel-body">
	<form role="form" class="form-horizontal col-xs-12" style="display:inline-block;" id="frm_property"  method="POST" action="ajax_functions.php">
		<div class="row">
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label class="col-xs-3 control-label" for="property_id">Property ID:&nbsp;</label>
					<div class="col-xs-9">
						<input type="text" id="property_id" class="form-control" placeholder="Auto Generate" size="5" disabled="">
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="business_type">Business Type:&nbsp;</label>
					<div class="col-xs-9">
						<select name="business_type" id="business_type" class="form-control" placeholder="Select Business Type">
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
						<?php
							echo fillSelect($link, "property_types", "property_type", "property_type", "pt_id");
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
								echo fillSelect($link, "districts", "district", "district", "district_id");
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
			
			</div>
			<div class="col-sm-6 col-md-6">
				<div class="form-group">
					<label class="col-xs-3 control-label" for="street">Street:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="street" id="street" class="form-control" placeholder="Street Address"></textarea>
					</div>
				</div>		

				<div class="form-group">
					<label class="col-xs-3 control-label" for="keywords">Keywords:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="keywords" id="keywords" class="form-control" placeholder="Keywords"></textarea>
					</div>
				</div>	

				<div class="form-group">
					<label class="col-xs-3 control-label" for="description">Description:&nbsp;</label>
					<div class="col-xs-9">
						<textarea name="description" id="description" class="form-control" placeholder="Description"></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="col-xs-3 control-label" for="price">Price (LKR):&nbsp;</label>
					<div class="col-xs-9">
						<input type="number" name="price" id="price" class="form-control" placeholder="Price in LKR" required=""/>
					</div>
				</div>

				<input type="hidden" name="tsk" value="adpr">
			</div>
		</div>
	</form>	
  </div>
  <div class="panel-footer">
 		<span id="btn_addnew" class="btn btn-success fileinput-button">
			<i class="icon-user">&nbsp;</i>

			<span class="button">Add</span>
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