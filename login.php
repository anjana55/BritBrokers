<?php
require_once __DIR__ . "/bootstrap.php";
start_secure_session();
if(is_admin()){
	header("Location: control_panel.php");
	exit();
}
page_head(array(
	"title" => "Admin login - British Brokers",
	"csrf" => true,
));
?>
<script>
$(document).ready(function() {
	$(".btn-success").click(function(){
		var usr=$("#user_name").val();
		var pwd=$("#user_pass").val();
		
		$.ajax({
			type: "POST",
			url: "authenticate.php",
			data: { user_id: usr, password: pwd }
		})
		.done(function( msg ) {
			if(msg=="true"){
				var url = "control_panel.php";
				$(location).attr('href',url);
			} else if(msg=="locked"){
				$("#notice").addClass("light_error");
				$("#notice").html("Too many failed attempts. Please wait 15 minutes and try again.");
				$("#notice").show("slow");
			} else {
				$("#notice").addClass("light_error");
				$("#notice").html("Login failed! Incorrect user name or password.");
				$("#notice").show("slow");
			}
		});

	});
	
	$("#user_pass").keypress(function( event ) {
		if ( event.which == 13 ) {
			$(".btn-success").click();
		}
	});
});
</script>
<?php page_body("home"); ?>
    <!-- slider -->
    <section id="login_main">
		<div class="area">
			<img src="img/slider-home/pic%20(1).jpg" alt="">
			<div class="fixed">
				<h1>Login</h1>
				<div class="text">
					<input class="form-control" type="text" name="user_name" id="user_name" placeholder="User Name">
					<div style="width:100%;display:block;padding:5px;"></div>
					<input class="form-control" type="password" name="user_pass" id="user_pass" placeholder="Password">
					<div id="notice">Please note that all unauthorized access attempts will be monitored and reported.</div>
				</div>
				<span id="btn_login" class="btn btn-success fileinput-button" >
					<i class="icon-user" ></i>
					<span class="button" >Login</span>
				</span>			
			</div>
		</div>		
    </section>
    <!-- slider close -->

<?php page_end(false); ?>
