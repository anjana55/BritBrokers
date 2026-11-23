
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">	
	<link rel="icon" 
      type="image/png" 
      href="favicon.ico">
<?php
	// Bundles built by bin/build-assets.mjs (npm run build). ?v= changes when a bundle changes, so browsers
	// can cache them for a year (see .htaccess) and still get new versions straight away.
	$asset = function($file){ return $file . "?v=" . @filemtime(SITE_ROOT . "/" . $file); };
	?>
	<link rel="stylesheet" href="<?php echo $asset("css/site.min.css"); ?>">
	<script src="<?php echo $asset("js/site.min.js"); ?>"></script>
<?php if(page_meta("admin")): ?>
	<link rel="stylesheet" href="<?php echo $asset("css/admin.min.css"); ?>">
	<script src="<?php echo $asset("js/admin.min.js"); ?>"></script>
<?php elseif(page_meta("validator")): ?>
	<script src="<?php echo $asset("js/validator.min.js"); ?>"></script>
<?php endif; ?>
	
	
	<!--Start of Tawk.to Script-->
	<script type="text/javascript">
	var $_Tawk_API={},$_Tawk_LoadStart=new Date();
	(function(){
	var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
	s1.async=true;
	s1.src='https://embed.tawk.to/556311b0e193f4637073677f/default';
	s1.charset='UTF-8';
	s1.setAttribute('crossorigin','*');
	s0.parentNode.insertBefore(s1,s0);
	})();
	
	function toggleChat() {
		$_Tawk.toggle(); return false;
	}
	</script>
	<!--End of Tawk.to Script-->
	
	<style>
	/* Sticky footer styles
	-------------------------------------------------- */
	html {
	  position: relative;
	  min-height: 100%;
	}
	body {
	  /* Margin bottom by footer height */
	  margin-bottom: 60px;
	}
	.footer {
	  position: absolute;
	  bottom: 0;
	  width: 100%;
	  /* Set the fixed height of the footer here */
	  height: 60px;
	  background-color: #f5f5f5;
	}
	</style>	
