	<script>
$(document).ready(function() {	
	$("#district").change(function() {
		//var did = $(this).children(":selected").attr("id");
		var did = $(this).children(":selected").val();
		//alert(did);
		$("#loadingDiv").show("slow");
		$.ajax({
		  url: "ajax_functions.php",
		  data: { tsk: "dbfc", value: did },
		  success: function(response){
			$("#city")
			.empty()
			.append("<option value=''>Any</option>");
			//$.each(response, function (key, value) {
				$("#city").append(response);
			//});
			$("#city").prop('disabled', false);
			//$("#work_area").html(response);
			$("#loadingDiv").hide();
		  },
		  error: function(){
			$("#loadingDiv").hide();
		  }
		});
	});
});
</script>
<div id="property-search">
	<form id="search" method="POST" action="property_list.php">
		<h3><i class="icon-search icon-1x"></i>Find Your Home</h3>
		<div class="group">
			<span class="label">District</span>
			<select name="src_district" id="district" title="Select a district" >
			  <option value="" selected>Any</option>
				<?php
					echo fillSelect($link, "districts", "district_name", "district_name", "district_id","");
				?>
			</select>
		</div>
		
		<div class="group">
			<span class="label">City</span>
			<select name="src_city" id="city" disabled="" >
				<option value="">Any</option>
			</select>
		</div>
		
		<div class="group">
			<span class="label">For</span>
			<select name="src_for" id="for" >
				<option value="">Any</option>
				<?php
					echo fillSelect($link, "business_types", "business_type", "business_type", "bt_id","");
				?>			
			</select>
		</div>
		<div class="group">
			<span class="label">Type</span>
			<select name="src_type" id="property_type" >
				<option value="">Any</option>
				<?php
				echo fillSelect($link, "property_types", "property_type_name", "property_type_name", "pt_id","");
				?>
			</select>
		</div>

		<div class="group half">
			<span class="label">Beds</span>
			<select name="src_bedrooms">
			  <option value="">Any</option>
			  <option value="1">1</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			  <option value="5+">5+</option>
			</select>
		</div>

		<div class="group half last">
			<span class="label">Baths</span>
			<select name="src_bathrooms">
			  <option value="">Any</option>
			  <option value="1">1</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			  <option value="5+">5+</option>
			</select>
		</div>

		<input id="btn_search" type="submit" value="Search Now" class="btn btn-default">
	</form>
</div>