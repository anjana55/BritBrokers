<?php require_once __DIR__ . "/bootstrap.php"; ?>
<?php
page_head(array(
	"title" => "Finding the Right Property to Rent in Colombo. Best selection of Houses & Apartments",
	"description" => "",
));
?>
<meta name="description" content="We at British Brokers know how important it is for you to find the right home.
Experts in the local rental market, we will listen carefully to your needs and help you find the best house or apartment within your budget.">
<meta name="keywords" content="We at British Brokers know how important it is for you to find the right home.
Experts in the local rental market, we will listen carefully to your needs and help you find the best house or apartment within your budget.">

<script src="js/jssor.slider.mini.js"></script>
<script src="js/jquery.flexslider.js"></script>
<script>
    jQuery(document).ready(function ($) {
		var _SlideshowTransitions = [{$Duration:1000,$Delay:60,$Cols:1,$Rows:1,$Opacity:2}];
        var options = {
            $DragOrientation: 3,
            $AutoPlay: true,
            $SlideDuration: 1500,
            $AutoPlayInterval: 4500,
            $SlideshowOptions: {                                //Options which specifies enable slideshow or not
                $Class: $JssorSlideshowRunner$,                 //Class to create instance of slideshow
                $Transitions: _SlideshowTransitions,            //Transitions to play slide, see jssor slideshow transition builder
                $TransitionsOrder: 1,                           //The way to choose transition to play slide, 1 Sequence, 0 Random
                $ShowLink: 2,                                   //0 After Slideshow, 2 Always
                $ContentMode: false                             //Whether to trait content as slide, otherwise trait an image as slide
            }
        };	
        var jssor_slider1 = new $JssorSlider$('slider1_container', options);
		//responsive code begin
		//you can remove responsive code if you don't want the slider scales while window resizes
		function ScaleSlider() {
			var parentWidth = jssor_slider1.$Elmt.parentNode.clientWidth;
			if (parentWidth)
				jssor_slider1.$ScaleWidth(Math.min(parentWidth, 1920));
			else
				window.setTimeout(ScaleSlider, 30);
		}
		ScaleSlider();

		$(window).bind("load", ScaleSlider);
		$(window).bind("resize", ScaleSlider);
		$(window).bind("orientationchange", ScaleSlider);
		//responsive code end    

		$('.flexslider').flexslider();		
	});
</script>
<?php page_body("home"); ?>
<?php
require_once __DIR__ . "/bootstrap.php";
$link = setConnection();

include SITE_ROOT . "/inc/set_post_vars.php";

?>


    <!-- content begin -->
    <div id="container">
	<!-- slider -->
		<section id="slider" >
			<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 1920px; height: 672px;">
			<!-- Slides Container -->
				<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: 1920px; height: 672px;">
					<div>				
						<img src="img/tenants-lg-1.jpg" alt="">
					</div>
					<div><img src="img/tenants-lg-2.jpg" alt=""></div>
					<div><img src="img/tenants-lg-3.jpg" alt=""></div>
					<div><img src="img/tenants-lg-4.jpg" alt=""></div>
				</div>
			</div>
		</section>
		<!-- slider close -->
		
		<!-- Search Begins -->
		<section id="property-search">
			<div class="container-fluid">
			<?php
				include SITE_ROOT . "/templates/search_box.php";
			?>
			</div>
		</section>
		<!-- Search Ends -->	
		
        <div class="container">
			<div class="content">
				<div class="col-sm-12 col-md-12">
					<br>
					<h4 class="page_title">Tenants</h4>
					<p>Finding the right property at the right price is no easy task. Even when you have decided on the area, identifying a property with everything you want can take a little time. We know how important it is for your chosen property to feel like home.</p>				
				</div>
				<div class="col-sm-8 col-md-8">
					<h4>Helping you find the perfect place</h4>

					<p>It is our job to help you find the perfect place and the way we do this is by spending time at the beginning of your search understanding what you need, and perhaps more importantly, what you want.</p>
					
					<div class="clearfix"></div>
					<h4>Knowledgeable advice</h4>

					<p>British Brokers has a great deal of knowledge about the rental market and value of property in and around Colombo. We can therefore advise you on what is available and give you a good indication of what type of property your budget can secure.</p>
					
					<div class="clearfix"></div>
					<h4>Help for first-time renters</h4>

					<p>For many tenants it may be the first time you are renting in Colombo. But you can rest assured that British Brokers has the experience and professionalism to make the process a simple one. We’ll take you through all the things you need to know and be on hand to offer assistance whenever it is needed.</p>
					
					<div class="clearfix"></div>
					<h4>Pre-selected properties</h4>

					<p>Offering accompanied viewings and flexible appointment times, our friendly and punctual staff is here to show you only the properties relevant to you search. We pre-select all of our properties, taking on only the best, whatever the asking price.</p>

					<div class="clearfix"></div>
					<h4>Legal Services</h4>

					<p>While we can provide standard legal documentation and help you through the process, you may have specific questions which are best addressed by our legal partners. We work with excellent lawyers who will answer your questions and ensure that you are comfortable with any lease agreement before signing.</p>

					<div class="clearfix"></div>
					<h4>Furniture-making & interior design</h4>

					<p>Whether renting a fully, semi, or unfurnished property, we often find that our clients like to add a touch of their own.</p>

					<p>Working with some great furniture-makers and interior-designers, we can help you to realise the potential of any property at a highly competitive price.</p>
					<p><b>We won’t just show you any property, we’ll show you the property.</b></p>
					<br/>
				</div>
				<div class="col-sm-4 col-md-4">	
					<div class="featured-box filled">
						<h4>Why find your property through British Brokers?</h4>

						<ul>
							<li>Fantastic selection of pre-selected properties</li>
							<li>Experts in the Colombo real estate market</li>
							<li>Thorough understanding of expat tenants’ needs</li>
							<li>Professional and reliable staff who listen to your requirements</li>
							<li>Highly experienced negotiators working towards a great deal</li>
						</ul>
						
						<a href="contact.php">Click here</a> to send you property requirements
					</div>
				</div> 
			</div>
        </div>
        <section id="testimonial" data-speed="6" data-type="background">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 hidden-md hidden-sm hidden-xs">				
						<div class="flexslider testi-slider">
							<ul class="slides">
								<li>'Lyndon at British Brokers is on my go-to list for real estate agents who can consistently provide beautiful, quality homes for expats in Colombo. He has a good instinct for selecting properties to show me which would be appropriate for my clients and will work tirelessly with me through sometimes difficult negotiations with landlords until just the right deal is made. Lyndon and his staff are excellent to work with and I believe we will have a relationship with British Brokers for a very long time.'<span>Patty DeRousse - Embassy Housing Co-ordinator</span>
								</li>
								<li>'Working with British Brokers was a unique experience, in that they understood our needs and made propositions only that were aligned to our specific requirements.I have since then had no hesitation recommending British Brokers, as they can guarantee carefully selected premises of high quality for residence and office alike.'<span>Sanjay Chandraratna - Director Sales & Marketing - American & Efird Lanka</span>
								</li>
								<li>'British Brokers immediately understood the level of finishing standard I wanted, the type of place, and the areas that suited me best. Your persistence in accommodating your client proved to be fantastic. British Brokers’ attitude is very good, you present well, and your client communications are excellent. Well done! Keep it up!'<span>Delores de Battista</span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</section>		
    </div>
    <!-- content close -->

<?php
page_end(true, array("pid" => $pid ?? ""));
@mysqli_close($link);
?>
