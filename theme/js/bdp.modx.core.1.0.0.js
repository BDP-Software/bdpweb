/*
 * BDP Modx Core JS
 * V 1.0.0
 * Last Modified 06/02/2015
 * dependancies
	** Bootstrap
	** jQuery
	** Google Maps API V3
	** Slick Slider - https://github.com/kenwheeler/slick
	** jQuery Validate
*/
    
/**
 * BDP ModX Core
*/
function bdpModXCore(options){
	
	//define default settings
	var settings = {
		'searchFormHandle' : '.bdf-searchForm', //search form handle
		'submitHandle' : '.bdf-Submit', //form submit button handle
		'cancelHandle' : '.bdf-cancel', //form cancel button handle
		'detailsMapId' : 'bdf-propertyMap', //id of the map ont he details page
		'detailsStreetViewId' : 'bdf-sView', //id of the streetview container
		'detailMainImgCarouselHandle' : '.bdf-detailMainImg', //main image carousel handle
		'detailThumbsCarouselHandle' : '.bdf-detailCarousel', //thumbnail carousel handle
		'streetviewHideHandle' : '.bdf-streetViewHide', //anything with this handle is hidden when streetview can't find data
		'mapTitleHandle' : '.bdf-mapTitle', //contents of this tag populates the map marker title
		'markerIconUrl' : false, //sets a custom map marker icon
		'enquiryFormHandle' : '.bdf-enquiryForm',
		'errorMsgHandle' : '.bdf-errorMsg',
		'sendFriendFormHandle' : '.bdf-sendFriendForm',
		'homeReportFormHandle' : '.bdf-homeReportForm',
		'requestViewingFormHandle' : '.bdf-requestViewingForm',
		'enquiryValidation' : {
			rules : {
				name : {
					required: true,
				},
				email : {
					required: true,
					email: true
				},
				tel : {
					
				},
				message : {
					
				},
			}
			
		}
	};
	
	//integrate the options
	$.extend(settings,options,true);
	
	/**
	 * search forms
	*/
	$(settings.searchFormHandle).each(function(){
		var form = $(this);
		$(settings.submitHandle,form).click(function(e){
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
	if($('#'+settings.detailsMapId).length > 0){
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
				if(console){
					console.log('Unable to retrieve Streetview data, removing Streetview functionality');
				}
				$(settings.detailsStreetViewId).hide();
				$(settings.streetviewHideHandle).hide();
			}
			else{
				if(console){
					//console.log('Streetview should now appear');
					
				}
				
			}
		});
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			e.target // newly activated tab
			e.relatedTarget // previous active tab
			//resize the google map on tab load 
			google.maps.event.trigger(map, 'resize');
			map.setCenter(latlngMap);
			panorama.setVisible(true);
		});
	}
	
	/**
	 * Enquiry Form
	*/
	
	//set form validation (jQuery validation)
	$(settings.enquiryFormHandle).validate(settings.enquiryValidation);
	
	//set the submit button
	$(settings.submitHandle,settings.enquiryFormHandle).click(function(e){
		e.preventDefault();
		//check that the form is valid, if not show the error message
		if($(settings.enquiryFormHandle).valid()){
			//the form fields are valid, post to the server and display the response
			//console.log('The enquiry form should now send');
			bDig.loadModal({
				content : false,
				template : 'assets/snippets/bdpweb/theme/jstemps/pleasewait.html',
				timeOut : false,
				tParams : {},
				onComplete : function(){
					//console.log('the modal should now have appeared');
					$.ajax({
						url : window.location.href,
						type : 'POST',
						data : {
							formData : '1',
							enqtype : 'denquiry'
						},
						success : function(data){
							if(data.output){
								
								//load a modal with the success message
								bDig.loadModal({
									content : data.output,
									onComplete : function(){
										setTimeout(function(){
											location.reload();
										}, 2000);
									},
									tParams : {
										modId : 'bdModal'
									}
									
								});
								
							}
							else{
								if(console){
									console.log('There was a problem submitting the form');
								}
							}
						}
					});
				}
			});
		}
		else{
			$(settings.errorMsgHandle,settings.enquiryFormHandle).slideDown();
		}
		
	});
	
	var sendFriendSetup = new popupFormHandle({
		formHandle : settings.sendFriendFormHandle,
		submitFlag : 'sendFriend',
		formModalId : 'sendFriend'
	});
	
	var homeReportSetup = new popupFormHandle({
		formHandle : settings.homeReportFormHandle,
		submitFlag : 'hreport',
		formModalId : 'espchr'
	});
	
	var requestViewingSetup = new popupFormHandle({
		formHandle : settings.requestViewingFormHandle,
		submitFlag : 'viewing',
		formModalId : 'bookViewing'
	});
	
	/**
	 * handles popup forms
	*/
	function popupFormHandle(options){
		
		var formSettings = {
			formHandle : '',
			submitHandle : settings.submitHandle,
			submitFlag : '',
			errorMsgHandle : settings.errorMsgHandle,
			formModalId : '',
			validation : {
				rules : {
					name : {
						required: true,
					},
					email : {
						required: true,
						email: true
					},
					tel : {
						
					},
					message : {
						
					},
				}
				
			}
		};
		
		$.extend(formSettings,options,true);
		
		//set form validation (jQuery validation)
		$(formSettings.formHandle).validate(formSettings.validation);
		
		//set the submit button
		$(formSettings.submitHandle,formSettings.formHandle).click(function(e){
			e.preventDefault();
			//check that the form is valid, if not show the error message
			if($(formSettings.formHandle).valid()){
				//the form fields are valid, post to the server and display the response
				//console.log('The enquiry form should now send');
				$('#'+formSettings.formModalId).removeClass("fade").modal("hide");
				bDig.loadModal({
					content : false,
					template : 'assets/snippets/bdpweb/theme/jstemps/pleasewait.html',
					timeOut : false,
					tParams : {},
					onComplete : function(){
						//console.log('the modal should now have appeared');
						$.ajax({
							url : window.location.href,
							type : 'POST',
							data : {
								formData : '1',
								enqtype : formSettings.submitFlag
							},
							success : function(data){
								if(data.output){
									
									//load a modal with the success message
									bDig.loadModal({
										content : data.output,
										onComplete : function(){
											setTimeout(function(){
												 $('#bdModal').modal('hide');
											}, 2000);
										},
										tParams : {
											modId : 'bdModal'
										}
										
									});
									
								}
								else{
									if(console){
										console.log('There was a problem submitting the form');
									}
								}
							}
						});
					}
				});
			}
			else{
				$(formSettings.errorMsgHandle,formSettings.formHandle).slideDown();
			}
			
		});
	}
	
	
}




   
	
	