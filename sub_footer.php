<footer>
	<!-- Modal -->
	<div class="modal fade" id="pop_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Email British Brokers</h4>
		  </div>
		  <div class="modal-body">
			<form name="contact_bb" id="contact_bb" action="#" method="POST" class="form-horizontal">
				<div class="form-group">
					<div class="col-md-4">
						<label for="c_name" class="control-label input-lg">Your Name</label>
					</div>
					<div class="col-md-8">
						<input type="text" id="c_name" name="c_name" placeholder="Your Name" class="form-control input-lg" required>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-4">
						<label for="c_email" class="control-label input-lg">Your Email</label>
					</div>
					<div class="col-md-8">
						<input type="text" id="c_email" name="c_email" placeholder="your_email@domain.com" class="form-control input-lg" required>
					</div>
				</div>
				<div class="form-group">
					<div class="col-md-4">
						<label for="c_message" class="control-label input-lg">Message</label>
					</div>
					<div class="col-md-8">
						<textarea id="c_message" name="c_message" rows="5" placeholder="Your Message" class="form-control input-lg" required></textarea>
					</div>
				</div>
				<input type="hidden" id="c_pid" name="c_pid" value="<?php echo htmlspecialchars((string)($pid ?? ""), ENT_QUOTES); ?>" />
				<div style="position:absolute;left:-10000px;" aria-hidden="true">
					<input type="text" id="c_website" name="c_website" tabindex="-1" autocomplete="off" />
				</div>
			</form>
			<div id="error_msg" class="col-md-12">
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			<button type="submit" class="btn btn-primary" id="msg_send" form="contact_bb">Send</button>
		  </div>
		</div>
	  </div>
	</div>

	<script> 
	jQuery(document).ready(function ($) {
		$("#msg_send").click(function(e){
			if($("#c_message").val()!=="" && $("#c_email").val()!==""){
				e.preventDefault();
				$.ajax({
					type: 'POST',
					url: 'mailman.php',
					data: {
						type: 'contact',
						name: $("#c_name").val(),
						email: $("#c_email").val(),
						message: $("#c_message").val(),
						pid: $("#c_pid").val(),
						website: $("#c_website").val()},
					success: function(response) {
						if(response!=="" && response.length>2){
							$("#error_msg").addClass("alert alert-success");
							var res = response.split("|");
							$("#error_msg").html(res[1]);
							$("#msg_send").hide();
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
				
			}
		});
	});
	
	</script>
  <div class="container">
            <div class="row">
                <div class="col-sm-4 col-md-4">
					<h3>Pages</h3>
					<nav>
						<ul>
							<li><a href="index.php">Home</a></li>
							<li><a href="list_property.php">Properties</a></li>
							<li><a href="tenants.php">Tenants</a></li>
							<li><a href="landlords.php">Landlords</a></li>
							<li><a href="sales_investments.php">Sales and Investments</a></li>
							<li><a href="contact.php">Contact</a></li>
						</ul>
					</nav>
				</div>
				<div class="col-sm-4 col-md-4">
                    <h3>Contact</h3>
                    <address>
						<span><strong>Tel</strong><a href="tel:+94773603029">+94 (0) 773603029</a></span>						
                        <span><strong>Email</strong><a id="btn_email" data-toggle="modal" data-target="#pop_msg">info@britbrokers.com</a></span>
                        <span><strong>Web</strong><a href="britbrokers.com">www.britbrokers.com</a></span>
                    </address>
				</div>
				<div class="col-sm-4 col-md-4">
					<h3>Social Channels</h3>
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
	<!-- Modal -->
	<div class="modal fade" id="cur_converter" tabindex="-1" role="dialog" aria-labelledby="cur_modal" aria-hidden="true">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="cur_modal">Currency Converter</h4>
		  </div>
		  <div class="modal-body text-center">
			<!-- <iframe id='cc' src="https://www.google.com/finance/converter"></iframe>	-->
			<div id="cc"></div>
			<div id="error_msg" class="col-md-12">
			</div>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		  </div>
		</div>
	  </div>
	</div>		
</footer>