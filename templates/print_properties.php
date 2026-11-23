<?php
						if($properties){
							$x=0;
							foreach($properties as $property){
								//var_dump($property);
								if($x>3){
									//echo "</div>";//Closing row
									//echo "<div class='row'>";
									$x=0;
								}
								
								
								echo "<div class='property-item property-for-sale col-xs-12 col-sm-6 col-md-4 col-lg-3'>";
									
									echo "<div class='info-1'>\n";
									if($property['status']=="Let" || $property['status']=="Sold"){
										if($property['business_type']=='S'){
											echo "<div class='for-sale'>\n";
											//echo "For Sale";
										} else if ($property['business_type']=='R'){
											echo "<div class='for-rent'>\n";
											//echo "For Rent";
										}
										echo $property['status'];
										echo "</div>\n"; //Sale/Rent
									}
									//if($property['featured']=="Y"){
										//echo "<div class='featured'>\n";
										//echo "Featured";
										//echo "</div>";
									//}

									//echo "<div class='reference'>\n";
									//echo $property['property_id'];
									//echo "</div>";									
										
									echo "</div>\n"; //Info-1
									//Image
									$images = getPropertyImages($link,$property['property_id'], 1, "PROTH");
									if($images){
										$images = $images[0];
										$image_url = $images['img_url'];
									} else {
										$image_url="img/property/pic (1).jpg";
									}
									echo "<a href='" . e(property_url($property)) . "'>";
									echo "<img src='" . $image_url . "' data-original='" . $image_url . "' alt='" . truncate($property['description'],150,"...") . "' class='img-responsive' style='min-width:100%;' >";
									echo "</a>";
									
									//Price
									if($property['currency']!=="LKR"){
										$currency = $property['currency'];
									} else {
										$currency = "Rs.";
									}
									echo "<div class='price'>";
									if($property['price']>1000000){
										$price = $property['price']/1000000;
										$display_price = $currency . " " . number_format($price,2,".",",") . "<abbr title='Millions'>m</abbr>";
									} else {
										$price = $property['price'];
										$display_price =  $currency . " " . number_format($price,0,".",",");
									}
									echo $display_price;
									if($property['business_type']=='R'){
										$options = array( "d"=>"P/D", "w"=>"P/W", "m"=>"P/M", "y"=>"P/Y" );
										$options1 = array( "d"=>"Per day", "w"=>"Per week", "m"=>"Per month", "y"=>"Per year" );
										echo "<span class='contents'><abbr title='" . $options1[$property['term']] . "'> " . $options[$property['term']] . "</abbr></span>"; 
									}
									echo "</div>\n";									
									
									//Description
									echo "<div class='contents' style='line-height: 1.3em;'>";
									echo "<div class='name'>" . $property['city_name'] . "</div>";
									echo truncate($property['description'],145,"...");
									echo "<a class='contact' href='" . e(property_url($property)) . "'>More</a>";									
									echo "</div>";
									
									echo "<div class='info'>";
									echo "	<div class='features'>";
									echo "		<span class='" . $property['property_type'] . "'>" . $property['property_type_name'] . "</span>";
									echo "		<span class='bath'>" . $property['bathrooms'] . "</span>";
									echo "		<span class='bed'>" . $property['bedrooms'] . "</span>";
									echo "	</div>";
									echo "</div>";
								echo "</div>";
								$x++;
							}
							
							//Printing pagination
							
							if($pagination && $total_records && $max_records_per_page && $current_page && $max_pages_display && $total_records > $max_records_per_page){
								$total_pages=ceil($total_records/$max_records_per_page);

								$start_page = ($current_page - round(($max_pages_display/2),0,PHP_ROUND_HALF_DOWN));

								if($start_page<=0){
									$start_page=1;
								}

								$end_page = ($start_page + $max_pages_display)-1;

								if($end_page>$total_pages){
									$end_page = $total_pages;
								}

								if((($end_page-$max_pages_display)+1)>0){
									$start_page=($end_page-$max_pages_display)+1;
								}							
								echo "<!-- Navigation Starts -->";
								/*
								echo "Total Rec: " . $total_records;
								echo " Max records per page: " . $max_records_per_page;
								echo " Max pages display:" . $max_pages_display;
								echo " Total pages: " . $total_pages;
								echo " Current Page: " . $current_page;
								echo " Start page: " . $start_page;
								echo " End Page: " . $end_page;
								*/
								// Keep the current search in the page links (URL-encoded and HTML-escaped)
								$query_params = $_GET;
								unset($query_params['pg']);
								$params = $query_params ? e("&" . http_build_query($query_params)) : "";
								
								echo "<nav style='clear:both;'>";
								echo "  <ul class='pagination pagination-lg'>";
								if($current_page<=1){
									echo "	<li class='disabled'>";
								} else {
									echo "	<li>";
								}
								echo "	  <a href='" . $page_name . "&pg=" . ($current_page-1) . $params . "' aria-label='Previous'>";
								echo "		<span aria-hidden='true'>&laquo;</span>";
								echo "	  </a>";
								echo "	</li>";
								
								for($x=$start_page;$x<=$end_page;$x++){
									//echo "	<li><a href='" . $page_name . "?pg=" . $x . "'>" . $x . "</a></li>";
									if($x==$current_page){
										echo "<li class='active'>";
									} else {
										echo "<li>";
									}
									echo "<a href='" . $page_name . "?pg=" . $x . $params . "'>" . $x . "</a></li>";
								}
								
								if($current_page>=$total_pages){
									echo "	<li class='disabled'>";
								} else {
									echo "	<li>";
								}
								echo "	  <a href='" . $page_name . "?pg=" . ($current_page+1) . $params . "' aria-label='Next'>";
								echo "		<span aria-hidden='true'>&raquo;</span>";
								echo "	  </a>";
								echo "	</li>";
								echo "  </ul>";
								echo "</nav>";
								echo "<!-- Navigation Ends -->";
							}
						} else {
							echo "<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'>";
							echo "No properties found!";
							echo "</div>";
						}
?>