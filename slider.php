							<?php
							$slide_width=900;
							$slide_height=675;
							?>
							
							<!-- slider Start -->
							<section id="slider1">
								<div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: <?php echo $slide_width . "px"; ?>; height: <?php echo $slide_height . "px"; ?>;">
								<!-- Slides Container -->
									<div u="slides" style="cursor: move; position: absolute; overflow: hidden; left: 0px; top: 0px; width: <?php echo $slide_width . "px"; ?>; height: <?php echo $slide_height . "px"?>;">
									<?php
										$images = getPropertyImages($link,$pid, 25,"PROMN");
										if($images){
											foreach($images as $image){
												echo "<div>";
												echo "<a id='img_" . $image['img_id'] . "' u='image' href='#'><img  src='" . $image['img_url'] . "' alt=''></a>";
												echo "<img u='thumb' src='" . $image['img_url'] . "' />";
												echo "</div>";
											}
										} else {
											echo "<div>";
											echo "<img src='img/property/pic (1).jpg' alt='No property images found!'>";
											echo "</div>";										
										}																

									?>							
									</div>
									<!--#region Arrow Navigator Skin Begin -->
									<!-- Help: http://www.jssor.com/development/slider-with-arrow-navigator-jquery.html -->
									<style>
										.jctrl{
											display: block;
											position: absolute;
											/* size of arrow element */
											height: 50px;
											width: 50px;
											cursor: pointer;
										}
										
										.jctrl a {
											color: #eee;
											display: inline-block;
											font-size: 3em;
											height: 50px;
											position: absolute;
											text-decoration: none;
										}
										.jleft {
											left: 10px;
											background: url(img/arrow_l.png) no-repeat;
										}
										.jright {
											right: 10px;
											background: url(img/arrow_r.png) no-repeat;
										}

										
										<!--
										.jssora16l { background-position: -19px -42px; }
										.jssora16r { background-position: -79px -42px; }
										.jssora16l:hover { background-position: -139px -42px; }
										.jssora16r:hover { background-position: -199px -42px; }
										.jssora16l.jssora16ldn { background-position: -259px -42px; }
										.jssora16r.jssora16rdn { background-position: -319px -42px; }
										-->
									</style>
									<!-- Arrow Left -->
									<span u="arrowleft" class="jctrl jleft" style="top: 123px; left: 18px;">
									</span>
									<!-- Arrow Right -->
									<span u="arrowright" class="jctrl jright" style="top: 123px; right: 18px;">
									</span>
									<!--#endregion Arrow Navigator Skin End -->
									
									 <!-- Thumbnail Navigator Skin 02 Begin -->
									<div u="thumbnavigator" class="jssort02" style="position: absolute; width: <?php echo $slide_width . "px"; ?>; height: 100px; left:0px; bottom: 0px;">
									<!-- Thumbnail Item Skin Begin -->
									<style>
									/* jssor slider thumbnail navigator skin 02 css */
									/*
									.jssort02 .p (normal)
									.jssort02 .p:hover (normal mouseover)
									.jssort02 .pav (active)
									.jssort02 .pav:hover (active mouseover)
									.jssort02 .pdn (mousedown)
									*/
									.jssort02 .w
									{
									position: absolute;
									top: 0px;
									left: 0px;
									width: 100%;
									height: 100%;
									}
									.jssort02 .c
									{
									position: absolute;
									top: 0px;
									left: 0px;
									width: 95px;
									height: 62px;
									border: #000 0px solid;
									}
									.jssort02 .p:hover .c, .jssort02 .pav:hover .c, .jssort02 .pav .c
									{
									background: url(img/t01.png) center center;
									border-width: 0px;
									top: 2px;
									left: 2px;
									width: 95px;
									height: 62px;
									}
									.jssort02 .p:hover .c, .jssort02 .pav:hover .c {
									top: 0px;
									left: 0px;
									width: 99px;
									height: 64px;
									border: #fff 1px solid;
									}
									</style>
									<div u="slides" style="cursor: move;">
										<div u="prototype" class="p" style="position: absolute; width: 99px; height: 66px; top: 0; left: 0;">
											<div class=w><div u="thumbnailtemplate" style=" width: 100%; height: 100%; border: none;position:absolute; top: 0; left: 0;">
											</div>
										</div>
									<div class=c>
									</div>
									</div>
									</div>
									<!-- Thumbnail Item Skin End -->
									</div>
									<!-- Thumbnail Navigator Skin End -->
								</div>
							</section>
							<!-- slider close -->						
