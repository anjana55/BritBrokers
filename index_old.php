<!DOCTYPE html>
<html lang="en">
<?php
include "header.php";
?>
<body id="home">
<?php
include "navigation.php";
include_once "functions.php";
$link = setConnection();
?>
<!-- slider -->
    <section id="slider">
                    <div class="flexslider main-slider">
                        <ul class="slides">
                            <li class="slide-for-sale">
                                <div class="container">
                                	<div class="slider-info">
                                    <div class="inner">
                                    <h1>Homes</h1>
                                    <div class="text">Make it your own and call it a 'Home'! </div>
                                    <span class="price">Rs. 125m</span><span class="more">more &gt;</span> </div>
                                </div>
                                </div>
                                <img src="img/slider-home/main_img1.jpg" alt="">
                            </li>
							
							<!--
							<li class="slide-for-sale">
							<div class="container">
                                	<div class="slider-info">
                                    <div class="inner">
                                    <h1>Condotels</h1>
                                    <div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</div>
                                    <span class="price">$125,000</span><span class="more">more &gt;</span> </div>
                                </div>
                                </div>
                                <img src="img/slider-home/pic%20(1).jpg" alt="">
                            </li>
                            
                            <li class="slide-for-rent">
                                <div class="container">
                                	<div class="slider-info">
                                    <div class="inner">
                                    <h1>Real Estate</h1>
                                    <div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</div>
                                    <span class="price">$125,000</span><span class="more">more &gt;</span> </div>
                                </div>
                                </div>
                                <img src="img/slider-home/pic%20(2).jpg" alt="">
                            </li>
                            
                            <li class="slide-for-sale">
                                <div class="container">
                                	<div class="slider-info">
                                    <div class="inner">
                                    <h1>Apartment</h1>
                                    <div class="text">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</div>
                                    <span class="price">$125,000</span><span class="more">more &gt;</span> </div>
                                </div>
                                </div>
                                <img src="img/slider-home/pic%20(3).jpg" alt="">
                            </li>
							-->
                        </ul>
        </div>
    </section>
    <!-- slider close -->

    <div class="clearfix"></div>


    <!-- content begin -->
    <div id="content">
        <div class="container"></div>

        <!-- paralax background -->

        <div class="container">
			<div class="row">
                <div class="col-sm-4 col-md-4">
                    <div class="featured-box">
                        <i class="icon-thumbs-up icon-2x"></i>
                        <div class="text">
                            <h3>Pixel Perfect</h3>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 col-md-4">
                    <div class="featured-box">
                        <i class="icon-random icon-2x"></i>
                        <div class="text">
                            <h3>Responsive</h3>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 col-md-4">
                    <div class="featured-box">
                        <i class="icon-magic icon-2x"></i>
                        <div class="text">
                            <h3>Easy To Custom</h3>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                        </div>
                    </div>
                </div>
            </div>
            
            <hr class="blank">
			
            
            <div class="row">
                <div class="col-sm-9 col-md-9">
                    <h3>Recent Properties</h3>
                    <div class="row">
						<?php
						$pagination=false;
						$properties = getProperties($link,'','','','','','','','','','','','','','',0,8);
						//$properties = getProperties($link,$bt,$pt, $features, $min_bdrm, $max_bdrm, $min_btrm, $max_btrm, $min_price, $max_price, $keywords, $district, $city, $sort_by, $sort_order, 8 );
						include "print_properties.php";
						?>
					</div>
                </div>

                <div class="col-sm-3 col-md-3">
					<div class="widget">
					<?php
						include("search_box.php");
					?>
					</div>
                  
					<div class="widget">
						<h3>Agents</h3>
                        <ul class="agent-list">
                            <li>
                                <img src="img/agent_pic_1.jpg" alt="">
                                <div class="info">
                                    <span class="agent-name">John Smith</span>
                                    <span class="agent-phone">222 8405</span>
                                    <span class="agent-email"><a href="#">contact@example.com</a></span>
                                </div>
                            </li>
                            <li>
                                <img src="img/agent_pic_2.jpg" alt="">
                                <div class="info">
                                    <span class="agent-name">Sarah Michelle</span>
                                    <span class="agent-phone">222 8405</span>
                                    <span class="agent-email"><a href="#">contact@example.com</a></span>
                                </div>
                            </li>
                            <li>
                                <img src="img/agent_pic_4.jpg" alt="">
                                <div class="info">
                                    <span class="agent-name">Michael Smith</span>
                                    <span class="agent-phone">222 8405</span>
                                    <span class="agent-email"><a href="#">contact@example.com</a></span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
		
        
        <section id="testimonial" data-speed="6" data-type="background">
        <div class="container">
            <div class="row">
                <div class="flexslider testi-slider">
                    <ul class="slides">
                        <li>"Fantastic... and thanks for the help, really, I really appreciate the service and concern."<span>John Smith</span>
                        </li>
                        <li>"You have real done a great job on theme design and it is very pretty."<span>Anita Ross</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    
 		<hr class="blank">   
    
    	<div class="container">
            <div class="row">
				<div class="col-sm-6 col-md-6">
                  <h3>Who We Are</h3>
                <img src="img/sample-pic-2.jpg" alt="" class="pull-left">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</div>
                
              <div class="col-sm-6 col-md-6">
                    <h3>Why Choose Us</h3>
                  <div class="lt_tab">
                        <ul class="lt_nav">
                            <li><span class="active">Competitive Price</span></li>
                            <li><span>Nice Reputation</span></li>
                            <li><span>Guarantee</span></li>
                        </ul>

                        <div class="lt_tab_content">

                            <div id="tab1" class="lt_content">
                          <img src="img/sample-pic-1.jpg" alt="" class="pull-left">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur. </div>

                            <div id="tab2" class="lt_content">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
                            </div>

                            <div id="tab3" class="lt_content">
                                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. 
                            </div>

                        </div>

                    </div>
              </div>
                
            </div>
        </div>
    </div>
    <!-- content close -->

<?php
include "sub_footer.php";
include "footer.php";
@mysqli_close($link);
?>
</body>
</html>