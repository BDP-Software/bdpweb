/*
 * BDP Modx Core JS
 * V 1.0.0
 * Last Modified 06/02/2015
 * dependancies
 ** Google Maps API V3
 ** Slick Slider - https://github.com/kenwheeler/slick
*/
    
/**
 * BDP ModX Core
*/
function bdpModXCore(options){
	
	//define default settings
	var settings = {
		'searchFormHandle' : '.bdf-searchForm', //search form handle
		'searchFormButtonHandle' : '.bdf-Submit', //search form submit button handle
		'detailsMapId' : 'bdf-propertyMap', //id of the map ont he details page
		'detailsStreetViewId' : 'bdf-sView', //id of the streetview container
		'detailMainImgCarouselHandle' : '.bdf-detailMainImg', //main image carousel handle
		'detailThumbsCarouselHandle' : '.bdf-detailCarousel', //thumbnail carousel handle
		'streetviewHideHandle' : '.bdf-streetViewHide', //anything with this handle is hidden when streetview can't find data
		'mapTitleHandle' : '.bdf-mapTitle', //contents of this tag populates the map marker title
		'markerIconUrl' : false, //sets a custom map marker icon
		'mapTriggerHandle' : '.bdf-mapTrigger'
	};
	
	//integrate the options
	$.extend(settings,options,true);
	
	/**
	 * search forms
	*/
	$(settings.searchFormHandle).each(function(){
		var form = $(this);
		$(settings.searchFormButtonHandle,form).click(function(e){
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
	$(settings.detailMainImgCarouselHandle).slick({
	    slidesToShow: 1,
	    slidesToScroll: 1,
	    fade: true,
	    arrows: true,
	    asNavFor: settings.detailThumbsCarouselHandle
	});
	/**
	 * Detail Page Carousel Images
	*/
	$(settings.detailThumbsCarouselHandle).slick({
	    slidesToShow: 4,
	    slidesToScroll: 4,
	    asNavFor: settings.detailMainImgCarouselHandle,
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
	
	/**
	 * Google Maps
	*/
	//console.log(propertyMapData);
	//set the latlng object for the map centre
	var latlngMap = new google.maps.LatLng(propertyMapData.mapCentreLat,propertyMapData.mapCentreLng);
	//set the latlng object for the marker
	var latlngMarker = new google.maps.LatLng(propertyMapData.markerLat,propertyMapData.markerLng);
	//set the map options
	var myOptions = {
		zoom: propertyMapData.mapZoom,
		center: latlngMap,
		mapTypeId: google.maps.MapTypeId[propertyMapData.mapType]
	};
	
	var map = new google.maps.Map(document.getElementById(settings.detailsMapId),myOptions);
				
	//set the marker image	
	var markerData = {
		position: latlngMarker, 
		map: map, 
		title:$(settings.mapTitleHandle).html(),
		draggable: false
	};
	if(settings.markerIconUrl){
		markerData[icon] = settings.markerIconUrl;
		
	}
	
	
	var marker = new google.maps.Marker(markerData);
	/**
	var marker = new google.maps.Marker({
		position: latlngMap, 
		map: map		
	});
	*/			
	//street view code goes here
	var latlngSView = new google.maps.LatLng(propertyMapData.sViewLat,propertyMapData.sViewLng);
	var panoramaOptions = {
		addressControl : false,
		position: latlngSView,
		pov: {
			heading: propertyMapData.sViewHeading,
			pitch: propertyMapData.sViewPitch,
			zoom: propertyMapData.sViewZoom
		}
	};
	var panorama = new  google.maps.StreetViewPanorama(document.getElementById(settings.detailsStreetViewId), panoramaOptions);
	var client = new google.maps.StreetViewService();
	client.getPanoramaByLocation(panoramaOptions.position, 50, function(data,status){
		if(status == 'ZERO_RESULTS'){
			$(settings.detailsStreetViewId).hide();
			$(settings.streetviewHideHandle).hide();
		}
	});
	$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
		e.target // newly activated tab
		e.relatedTarget // previous active tab
		//resize the google map on tab load 
		google.maps.event.trigger(map, 'resize');
		map.setCenter(latlngMap);
	});
}

/*
 * jquery on document ready functions
*/
$(document).ready(function(){
	var bdpModX = new bdpModXCore();	
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


   
	
	