<?php
require_once __DIR__ . "/auth.php";
require_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php
include "header.php";
?>
<title>British Brokers Real Estate & Lettings Agent specialise in Property across Colombo, Sri Lanka.</title>
<meta name="description" content="British Brokers – The Home of Colombo Property.  Offering our clients the finest properties in the market, cutting edge technology, high profile marketing and professional credentials, British Brokers is Colombo’s leading property agent.">
<meta name="keywords" content="Contact British Brokers by email, telephone, or live chat about your real estate needs.">
<script>
$(document).ready(function() {
	$("#btn_add").click(function(){
		$.ajax({
		  url: "add_new_property.php",
		  data: { name: "John", time: "2pm" },
		  success: function(data){
			$("#work_area").html(data);
		  }
		});			
	});

	$("#btn_delete").click(function(){
		$.ajax({
		  url: "property_search.php",
		  data: { action: "delete", ru: "delete_property.php"},
		  success: function(data){
			$("#work_area").html(data);
		  }
		});			
	});

	$("#btn_edit").click(function(){
		$.ajax({
		  url: "property_search.php",
		  data: {  action: "edit", ru: "edit_property_list.php"},
		  success: function(data){
			$("#work_area").html(data);
		  }
		});			
	});
	
	$("#btn_logoff").click(function(){
		$.ajax({
		  url: "logoff.php",
		  success: function(data){
			var url = "http://britbrokers.com";
			$(location).attr('href',url);
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
		  data: { tsk: "dbfc", value: did },
		  success: function(response){
			$("#city")
			.empty()
			//.append('<option>Please select a value</option>')
			;
			//$.each(response, function (key, value) {
				$("#city").append(response);
			//});
			$("#city").prop('disabled', false);
			//$("#work_area").html(response);
			$("#loadingDiv").modal("hide");
		  },
		  error: function(){
			$("#loadingDiv").modal("hide");
		  }
		});
	});
	
	$("#work_area").on("change", "[id^='property_id']", function() {
		var len = $(this).val().length;

		if(len>0){
			$("#business_type").attr("disabled", true);
			$("#property_type").attr("disabled", true);
			$("#bedrooms").attr("disabled", true);
			$("#bathrooms").attr("disabled", true);
			$("#district").attr("disabled", true);
			$("#city").attr("disabled", true);
			
			$("#error_msg").removeClass("alert-success");
			$("#error_msg").addClass("alert alert-danger");
			$("#error_msg").html("Switched to Property ID search. Rest of the fields are dissabled. Clear Property ID to re-enable.");
			$("#error_msg").show("slow");
		} else {
			$("#business_type").removeAttr("disabled");
			$("#property_type").removeAttr("disabled");
			$("#bedrooms").removeAttr("disabled");
			$("#bathrooms").removeAttr("disabled");
			$("#district").removeAttr("disabled");
			$("#city").removeAttr("disabled");

			$("#error_msg").hide();
		}
	});		
});
</script>
</head>
<body id="home" >
<?php
include "navigation.php";
?>
<section>
	<div class="row btn-toolbar" style="margin:20px;">
		<span id="btn_add" class="btn btn-success fileinput-button" >
			<i class="icon-user" >&nbsp;</i>
			<span class="button" >Add New Property</span>
		</span>
		<span id="btn_edit" class="btn btn-warning fileinput-button" >
			<i class="icon-edit" ></i>
			<span class="button" >Edit Property</span>
		</span>
		<span id="btn_delete" class="btn btn-danger fileinput-button" >
			<i class="icon-trash" ></i>
			<span class="button" >Delete Property</span>
		</span>
		<span id="btn_logoff" class="btn btn-info fileinput-button" >
			<i class="icon-off" ></i>
			<span class="button" >Logoff</span>
		</span>		
	</div>
</section>

<section id="work_area">
<?php
include "stats.php";
?>
</section>
<?php
include "footer.php";
?>
</body>
</html>