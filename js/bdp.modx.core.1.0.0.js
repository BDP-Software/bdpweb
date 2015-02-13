/*
 * BDP Modx Core JS
 * V 1.0.0
 * Last Modified 06/02/2015
 * dependancies
 ** Google Maps API V3
 ** Slick Slider - https://github.com/kenwheeler/slick
*/
    
/*
 * jquery on document ready functions
*/
$(document).ready(function(){	
	/**
	 * search forms
	*/
	$('.bdf_searchForm').each(function(){
		var form = $(this);
		$('.bdf_Submit',form).click(function(e){
			e.preventDefault();
			var sendData = form.serialize();
			var actionPath = form.attr('action');
			var resPath = actionPath + (actionPath.indexOf("?", 0) > -1 ? '&' : '?') + sendData;
			//relocate the user
			window.location.href = resPath;
		});
	});	
	
	/**
	 * Main Detail Page Image 
	*/
	$('.bdf_detailMainImg').slick({
	    slidesToShow: 1,
	    slidesToScroll: 1,
	    fade: true,
	    arrows: true,
	    asNavFor: '.bdf_detailCarousel'
	});
	/**
	 * Detail Page Carousel Images
	*/
	$('.bdf_detailCarousel').slick({
	    slidesToShow: 4,
	    slidesToScroll: 4,
	    asNavFor: '.bdf_detailMainImg',
	    arrows: false,
	    centerMode: true,
	    centerPadding: '30px',
	    focusOnSelect: true,
	    responsive: [
	    {
	      breakpoint: 1024,
	      settings: {
	        slidesToShow: 3,
	        slidesToScroll: 3,
	        centerPadding: '20px'
	      }
	    },
	    {
	      breakpoint: 600,
	      settings: {
	        slidesToShow: 3,
	        slidesToScroll: 2,
	        centerPadding: '15px'
	      }
	    },
	    {
	      breakpoint: 480,
	      settings: {
	        slidesToShow: 2,
	        slidesToScroll: 1,
	        centerPadding: '10px'
	      }
	    }
	  ]
	});
	
	
});
	
	
	
	
	
	/* map contact 
    $("#map").gmap3({
        map: {
            options: {
              center: [55.956357, -2.776160],
              zoom: 12,
              scrollwheel: false
            }  
         },
        marker:{
            latLng: [55.956357, -2.776160],
            options: {
             icon: new google.maps.MarkerImage(
               "https://dl.dropboxusercontent.com/u/29545616/Preview/location.png",
               new google.maps.Size(48, 48, "px", "px")
             )
            }
         }
    });
*/

    /* carousel single */
    $('#slider-property').carousel({
        interval: 6500
    })


    /* map property */
    $('a[href="#location"]').on('shown.bs.tab', function(){
        $("#map-property").gmap3({
            map: {
                options: {
                  center: [55.956357,-2.776160],
                  zoom: 13,
                  scrollwheel: false
                }  
             },
            marker:{
                latLng: [55.956357,-2.776160],
                options: {
                 icon: new google.maps.MarkerImage(
                   "https://dl.dropboxusercontent.com/u/29545616/Preview/location.png",
                   new google.maps.Size(48, 48, "px", "px")
                 )
                }
             }
        });
    })
	
	
	