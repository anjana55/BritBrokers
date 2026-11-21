<script>
$(document).ready(function(){

	$("[name='src_for']").click(function(){
		refreshLists();
	});

	function refreshLists(){
		$("#loadingDiv").modal("show");
		$.ajax({
			type: 'POST',
			url: 'ajax_functions.php',
			data: $('#frm_search').serialize(),
			success: function(response) {
				if(response!==""){
					var res = response.split("|");
					//alert(res[0]);
					$("#mnp").html("<option value=''>Any</option>" + res[0]);
					$("#mxp").html("<option value=''>Any</option>" + res[1]);
				} else {
					$("#mnp").html("");
				}
				$("#loadingDiv").modal("hide");
			}
		});
	};
	
	$('#kwd').bind('keypress', function (event) {
		var regex = new RegExp("^[a-zA-Z0-9_\x20]+$");
		var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
		if (!regex.test(key)) {
		   event.preventDefault();
		   return false;
		}
	});
	
	$( "#frm_search" ).submit(function( event ) {
		if($("#mnp").val()!="" && $("#mxp").val() !="" &&  parseInt($("#mnp").val())> parseInt($("#mxp").val())){
			$("#prc_error").html("'From' price should be less than 'To' price!");
			$("#prc_error").show();
			event.preventDefault();
		} else {
			$("#prc_error").html("");
			$("#prc_error").hide();
		}
	});
});

</script>
<?php
//include "set_post_vars.php";
?>
<div class="container-fluid" style="padding-bottom:20px;">
	<div class="search-box">
		<form name="frm_search" id="frm_search" method="GET" action="list_property.php">
			<div class="xs-col-12 container">
				<h3><i class="icon-search icon-1x"></i>Find Your Property</h3>
			</div>
			<div class="col-md-6 col-sm-12">
				<div class='col-md-4 col-xs-12 search-box' id="property_types">
					<?php
						echo getRadioButtons($link, "src_for", "radio-inline", "business_types", "business_type", "bt_id","",$bt,"",4,"search-label");
					?>				
				</div>
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Location </span>
					</div>
					<div class="col-xs-8">
						<select name="src_city" id="city" class="form-control">
							<option value="">Any</option>
							<option label='Colombo 1' value='21'>Colombo 1</option>
							<option label='Colombo 2' value='22'>Colombo 2</option>
							<option label='Colombo 3' value='23'>Colombo 3</option>
							<option label='Colombo 4' value='24'>Colombo 4</option>
							<option label='Colombo 5' value='8'>Colombo 5</option>
							<option label='Colombo 6' value='5'>Colombo 6</option>
							<option label='Colombo 7' value='25'>Colombo 7</option>
							<option label='Colombo 8' value='26'>Colombo 8</option>
							<option label='Battaramulla' value='15'>Battaramulla</option>
							<option label='Kotte' value='66'>Kotte</option>
							<option label='Nawala' value='82'>Nawala</option>
							<option label='Pelawatta' value='85'>Pelawatta</option>
							<option label='Rajagiriya' value='92'>Rajagiriya</option>
							<option label='Galle' value='267'>Galle</option>
							<?php
							if($t_city!=""){
								echo "<option disabled>──────────</option>";
								echo "<option label='" . $t_city . "' value='" . $city . "' selected>" . $t_city . "</option>";
							}
							?>
						</select>
					</div>
				</div>
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Type </span>
					</div>
					<div class="col-xs-8">
						<select name="src_type" id="property_type" class="form-control">
							<option value="">Any</option>
							<?php
							echo fillSelect($link, "property_types", "property_type_name", "property_type_name", "pt_id","",$pt);
							?>
						</select>
					</div>
				</div>
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Price </span>
					</div>
					<div class="col-md-1 col-xs-2"><span class="search-label">&nbsp;From&nbsp;</span></div>
					<div class="col-md-3 col-xs-6">
						<select name="mnp" id="mnp" class="form-control" style="margin-bottom:10px;">
							<option value="">Any</option>
							<?php
							if($bt=="R"){
								$fltr=array("type" => "RENT");
							} else {
								$fltr=array("type" => "SALE");
							}
							echo fillSelect($link, "price_ranges", "price", "price", "price",array("min_max" => "MIN") + $fltr ,"",true);
							
							if($min_price!=""){
								echo "<option disabled>──────────</option>";
								echo "<option label='" . nice_number($min_price) . "' value='" . $min_price . "' selected>" . nice_number($min_price) . "</option>";
							}
							?>								
						</select>
					</div>
					<div class="hidden-lg hidden-md col-xs-4">
						&nbsp;
					</div>
					<div class="col-md-1 col-xs-2"><span class="search-label">&nbsp;To&nbsp;</span></div>
					<div class="col-md-3 col-xs-6">						
						<select name="mxp" id="mxp" class="form-control">
							<option value="">Any</option>
							<?php
							echo fillSelect($link, "price_ranges", "price", "price", "price",array("min_max" => "MAX") + $fltr ,"",true);
							
							if($max_price!=""){
								echo "<option disabled>──────────</option>";
								echo "<option label='" . nice_number($max_price) . "' value='" . $max_price . "' selected>" . nice_number($max_price) . "</option>";
							}
							?>	
						</select>
					</div>
					<span id="prc_error" class="badge col-md-offset-6"></span>
				</div>
			</div>
			<div class="col-md-6 col-sm-12">
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Furnished</span>
					</div>
					<div class="col-xs-8">
						<select name="src_furnished" class="form-control">
						  <option value="">Any</option>
						  <option value="20">Furnished</option>
						  <option value="22">Unfurnished</option>
						  <option value="21">Semi-Furnished</option>
							<?php
							if($fur!=""){
								echo "<option disabled>─────</option>";
								$feat = getFeatureByID($link,$fur);
								echo "<option label='" . $feat['feature'] . "' value='" . $fur . "' selected>" . $feat['feature'] . "</option>";
							}
							?>			  
						</select>
					</div>
				</div>		
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Min Beds</span>
					</div>
					<div class="col-xs-8">
						<select name="src_bedrooms" class="form-control">
						  <option value="">Any</option>
						  <option value="1">1</option>
						  <option value="2">2</option>
						  <option value="3">3</option>
						  <option value="4">4</option>
						  <option value="5">5</option>
						  <option value="5+">5+</option>
							<?php
							if($bdr!=""){
								echo "<option disabled>─────</option>";
								echo "<option label='" . $bdr . "' value='" . $bdr . "' selected>" . $bdr . "</option>";
							}
							?>			  
						</select>
					</div>
				</div>
				<div class='col-xs-12 search-box'>
					<div class="col-xs-4">
						<span class="search-label">Keywords</span>
					</div>
					<div class="col-xs-8">
						<input type="text" name="kwd" id="kwd" class="form-control" value="<?php echo e($keywords) ?>">
					</div>
				</div>
				<div class='col-xs-12'>
						<input type="hidden" name="sby" id="sby" class="form-control" value="<?php echo $sort_by ?>">
						<input type="hidden" name="sor" id="sor" class="form-control" value="<?php echo $sort_order ?>">
						<a href="javascript:void(0);" onClick="javascript:$('#frm_search').submit();" ><div class="btn-default search text-center" style="height:24px;margin-bottom:10px;width:100px;">&nbsp;Search&nbsp;</div></a>
				</div>
			</div>
			<input type="hidden" name="tsk" value="dbfp">
		</form>
	</div>
</div>