<?php
include_once "functions.php";
include_once "header.php";
$link = setConnection()
?>

<div class="container-fluid">
	<div calss="row">
		<div class="col-lg-3 col-md-6">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-star-empty fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT * FROM `properties` WHERE `time_submitted` >= curdate() - INTERVAL DAYOFWEEK(curdate())+6 DAY AND `time_submitted` < curdate() - INTERVAL DAYOFWEEK(curdate())-1 DAY", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>
							</div>
							<div>New additions!</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-3 col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-home fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT property_id FROM properties", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>
							</div>
							<div>Properties Listed!</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-warning">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-tag fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT property_id FROM properties WHERE business_type='S'", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>							
							</div>
							<div>For sale!</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-danger">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-tags fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT property_id FROM properties WHERE business_type='R'", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>								
							</div>
							<div>For rent!</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-success">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-picture fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT img_id FROM images WHERE img_type='PROMN'", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>								
							</div>
							<div>Standard Images uploaded!</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-danger">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-camera fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT img_id FROM images WHERE img_type='PROPN'", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>								
							</div>
							<div>Panaroma Images uploaded!</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="col-lg-3 col-md-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-facetime-video fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$properties_count = runQuery($link, "SELECT img_id FROM images WHERE img_type='PROVD'", "RC");
							
							if($properties_count){
								echo $properties_count;
							} else {
								echo "0";
							}
							?>								
							</div>
							<div>Videos uploaded!</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-usd fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$usd = runQuery($link, "SELECT LKR FROM rates WHERE 1", "RA");
							
							if($usd){
								echo $usd[0]['LKR'];
							} else {
								echo "N/A";
							}
							?>							
							</div>
							<div>Todays USD Rate</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="row">
						<div class="col-xs-3">
							<i class="icon-usd fa fa-5x" style="padding-left:10px;"></i>
						</div>
						<div class="col-xs-9 text-right">
							<div class="huge">
							<?php
							$old_props = getOldProperties($link, 6, true, $max_rec='');
							
							if($old_props){
								echo $old_props[0]['record_count'];
							} else {
								echo "N/A";
							}
							?>							
							</div>
							<div>Older Properties (> 6 months)</div>
						</div>
					</div>
				</div>
			</div>
		</div>				
	</div>
</div>

<?php
@mysqli_close($link);
?>