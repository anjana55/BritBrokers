// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
// written by Liontheme
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

jQuery(document).ready(function() {
	/*
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// change menu on mobile version
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	domready(function(){
		selectnav('mainmenu', {
			label: 'Menu',
			nested: true,
			indent: '-'
		});
	});
	*/



	
	// (gallery filtering with isotope removed: no page has a #gallery)

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// paralax background
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	$window = jQuery(window);
   	jQuery('section[data-type="background"]').each(function(){
    var $bgobj = jQuery(this); // assigning the object
                    
    jQuery(window).scroll(function() {
	var yPos = -($window.scrollTop() / $bgobj.data('speed')); 
	var coords = '50% '+ yPos + 'px';
	$bgobj.css({ backgroundPosition: coords });
		
	});
 	});
	document.createElement("article");
	document.createElement("section");
	
	// (prettyPhoto lightbox removed: no page uses it)

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// scroll to top
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	jQuery().UItoTop({ easingType: 'easeInOutExpo' });
	  
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// gallery hover
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	jQuery('.gallery .item').hover(function() {
	jQuery(this).stop().animate({opacity: .5}, 100);
	}, function() {
	jQuery(this).stop().animate({opacity: 1});}, 100);
	
	
	jQuery('.img-hover').hover(function() {
	jQuery(this).stop().animate({opacity: .5}, 100);
	}, function() {
	jQuery(this).stop().animate({opacity: 1});}, 100);
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// resize
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	

	
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// show / hide slider navigation
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	if ( $( ".slider-info" ).length ) {
		jQuery(".slider-info").hide();
		jQuery('.main-slider.flexslider').flexslider({
			animation: "slide",
			directionNav: false,
			
			
			
			start: function () {
					jQuery(".slider-info").animate({"margin-bottom": "60px", "opacity": "toggle"},500,'easeOutBack');
			  },
			  after: function () {
					jQuery(".slider-info").animate({"margin-bottom": "60px", "opacity": "toggle"},500,'easeOutBack');
			  },
			  before: function () {
					jQuery(".slider-info").animate({"margin-bottom": "-200px", "opacity": "toggle"},500,'easeOutBack');
			  }
		});

	
	
		jQuery('.testi-slider.flexslider').flexslider({
			animation: "fade",
			controlNav: false,
			slideshowSpeed: 15000,
		  });
	}
	
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// lazyload
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	 jQuery(function() {
          jQuery("img").lazyload({
              effect : "fadeIn",
			  effectspeed: 900 
          });
      });
	  
	  
	  	// --------------------------------------------------
		// tabs
		// --------------------------------------------------
		jQuery('.lt_tab').find('.lt_tab_content .lt_content').hide();
		jQuery('.lt_tab').find('.lt_tab_content .lt_content:first').show();
		
		jQuery('.lt_nav li').click(function() {
			jQuery(this).parent().find('li span').removeClass("active");
			jQuery(this).find('span').addClass("active");
			jQuery(this).parent().parent().find('.lt_tab_content .lt_content').hide();
		
			var indexer = jQuery(this).index(); //gets the current index of (this) which is #nav li
			jQuery(this).parent().parent().find('.lt_tab_content .lt_content:eq(' + indexer + ')').fadeIn(); //uses whatever index the link has to open the corresponding box 
		});

});

	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
	// lazyload
	// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 	
	    jQuery(document).ready(function() {
        var $header = $("header"),
            $clone = $header.before($header.clone().addClass("clone"));
        
        jQuery(window).on("scroll", function() {
            var fromTop = jQuery(window).scrollTop();
            console.log(fromTop);
            jQuery("body").toggleClass("down", (fromTop > 240));
        });
    });


