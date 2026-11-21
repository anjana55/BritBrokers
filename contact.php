<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "Contact Us by Email, Telephone or Live Chat.",
	"description" => "Contact British Brokers by email, telephone, or live chat about your real estate needs.",
));
?>
<script>
$(document).ready(function(){
	$("#contact").submit(function(e){
		return false;
	});
	
	$("#send").click(function(){
		if($("#email").val()!=="" && $("#message").val()!==""){
			$("#loadingDiv").modal("show");
			
		   $.ajax({
				type: 'POST',
				url: 'mailman.php',
				data: {
					type: 'contact',
					name: $("#name").val(),
					email: $("#email").val(),
					phone: $("#phone").val(),
					message: $("#message").val(),
					website: $("#website").val()},
				success: function(response) {
					$("#pop_msg").modal("show");
					if(response!=="" && response.length>2){

					} else {

					}
					$("#loadingDiv").modal("hide");
				}

			});		
		}
	});
});
</script>

<?php page_body("home"); ?>
<style type="text/css" >
    .wrapper
    {
        min-height:100%;
        width:100%;
        position:fixed;
        display:inline-block;
		top: 0px;
		right: -500px;
		z-index: -1;
    }
    .content
    {
        min-height:500px;
        width:80%;
        position:relative;
        float:right;
    }
</style>
<div style="height:80px;display:block;"></div>
<div class="container">
	
	<div class="col-xs-10">
		<h4 class="page_title">Contact</h4>
		<div class="">
			<form id="contact" name="form1" method="post" action="#">

				<div style="position:absolute;left:-10000px;" aria-hidden="true">
					<label for="website">Leave this empty</label>
					<input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
				</div>

				<div class="col-xs-12 col-sm-4">
					<label>Name</label>
					<input type="text" class="full" name="name" id="name" />
				</div>

				<div class="col-xs-12 col-sm-4">
					<label>Email <span class="req">*</span></label>
					<input type="text" class="full" name="email" id="email" required/>
					<div id="error_email" class="error">Please check your email</div>
				</div>

				<div class="col-xs-12 col-sm-4">
					<label>Phone </label>
					<input type="text" class="full" name="phone" id="phone" />
					<div id="error_phone" class="error">Please check your phone number</div>
				</div>
				
				<div class="col-sm-12 col-md-12">
					<label>Message <span class="req">*</span></label>
					<textarea cols="10" rows="10" name="message" id="message" class="full" required></textarea>
					<div id="error_message" class="error">Please check your message</div>
					<div id="mail_success" class="success">Thank you. Your message has been sent.</div>
					<div id="mail_failed" class="error">Error, email not sent</div>
					<br/><br/>
					<p id="btnsubmit">
						<input type="submit" id="send" value="Send" class="btn btn-default" />
					</p>
				</div>
				<?php
				if(isset($_REQUEST['id'])){
					$id = urlencode($_REQUEST['id']);
				} else {
					$id="";
				}
				echo "<input type='hidden' name='reference_id' value='" . $id . "'>";
				?>

			</form>
		</div>
	   <div id="sidebar" class="col-sm-12 col-md-12">
			<!-- widget category -->
			<!-- widget tags -->
			<!-- widget text -->

			<div class="widget widget-text">
				<address>
				<span><strong>Phone</strong><a href="tel:+94773603029" data-rel="external">+94 (0) 773603029</a></span>
					<span><strong>Email</strong><a href="mailto:info@britbrokers.com" data-rel="external">info@britbrokers.com</a></span>
					<span><strong>Skype</strong><a href="skype:live:britbrokers" data-rel="external">live:britbrokers</a></span>
					<span><strong>Web</strong><a href="britbrokers.com">www.britbrokers.com</a></span>
				</address>

				<div class="social-icons">
					<a href="https://www.facebook.com/Britbrokers">
						<img src="img/social-icons/facebook.png" alt=""></a>
					<a href="https://twitter.com/BritishBrokers">
						<img src="img/social-icons/twitter.png" alt=""></a>
					<a href="#">
						<img src="img/social-icons/youtube.png" alt=""></a>
				</div>
			</div>

		</div>		
	</div>
	<div class="col-xs-2">
	</div>	
	<div class="wrapper">
		<div class="content">
			<img class="img-responsive" src="img/contact.jpg"/>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="pop_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Contact British Brokers</h4>
		  </div>
		  <div class="modal-body">
			<div id="error_msg" class="col-md-12">
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		  </div>
		</div>
	  </div>
	</div>	
</div>

 <?php
page_end(false);
?>
