function bdpModXCore(e){function a(e){var a={formHandle:"",submitHandle:o.submitHandle,submitFlag:"",errorMsgHandle:o.errorMsgHandle,formModalId:"",validation:{rules:{name:{required:!0},email:{required:!0,email:!0},tel:{},message:{}}}};$.extend(a,e,!0),$(a.formHandle).validate(a.validation),$(a.submitHandle,a.formHandle).click(function(e){if(e.preventDefault(),$(a.formHandle).valid()){$("#"+a.formModalId).removeClass("fade").modal("hide");var o=$(a.formHandle).serializeArray(),n={formData:"1",enqtype:a.submitFlag};for(var t in o)n[o[t].name]=o[t].value;bDig.loadModal({content:!1,template:"assets/snippets/bdpweb/theme/jstemps/pleasewait.html",timeOut:!1,tParams:{},removeId:a.formModalId,onComplete:function(){$.ajax({url:window.location.href,type:"POST",data:n,success:function(e){e.output?bDig.loadModal({content:e.output,onComplete:function(){setTimeout(function(){$("#bdModal").modal("hide")},2e3)},tParams:{modId:"bdModal"}}):console&&console.log("There was a problem submitting the form")}})}})}else $(a.errorMsgHandle,a.formHandle).slideDown()})}var o={searchFormHandle:".bdf-searchForm",submitHandle:".bdf-Submit",cancelHandle:".bdf-cancel",detailsMapId:"bdf-propertyMap",detailsStreetViewId:"bdf-sView",detailMainImgCarouselHandle:".bdf-detailMainImg",detailThumbsCarouselHandle:".bdf-detailCarousel",streetviewHideHandle:".bdf-streetViewHide",mapTitleHandle:".bdf-mapTitle",markerIconUrl:!1,enquiryFormHandle:".bdf-enquiryForm",errorMsgHandle:".bdf-errorMsg",sendFriendFormHandle:".bdf-sendFriendForm",homeReportFormHandle:".bdf-homeReportForm",requestViewingFormHandle:".bdf-requestViewingForm",lazyLoadingOuterContainerHandle:".bdf-lazyLoading",lazyLoadingContainerHandle:".bdf-lazyLoadingResContainer",lazyLoadingGraphicHandle:".bdf-lazyLoader",enquiryValidation:{rules:{firstName:{required:!0},lastName:{required:!0},email:{required:!0,email:!0},tel:{},message:{}}}};if($.extend(o,e,!0),$(o.searchFormHandle).each(function(){var e=$(this);$(o.submitHandle,e).click(function(a){a.preventDefault();var o=e.serialize(),n=e.attr("action"),t=n+(n.indexOf("?",0)>-1?"&":"?")+o;window.location.href=t})}),$(o.detailMainImgCarouselHandle).slick({slidesToShow:1,slidesToScroll:1,fade:!0,arrows:!0,asNavFor:o.detailThumbsCarouselHandle}),$(o.detailThumbsCarouselHandle).slick({slidesToShow:4,slidesToScroll:4,asNavFor:o.detailMainImgCarouselHandle,arrows:!1,centerMode:!0,centerPadding:"30px",focusOnSelect:!0,responsive:[{breakpoint:1024,settings:{slidesToShow:3,slidesToScroll:3,centerPadding:"20px"}},{breakpoint:600,settings:{slidesToShow:3,slidesToScroll:2,centerPadding:"15px"}},{breakpoint:480,settings:{slidesToShow:2,slidesToScroll:1,centerPadding:"10px"}}]}),$("#"+o.detailsMapId).length>0){var n=new google.maps.LatLng(propertyMapData.mapCentreLat,propertyMapData.mapCentreLng),t=new google.maps.LatLng(propertyMapData.markerLat,propertyMapData.markerLng),r={zoom:propertyMapData.mapZoom,center:n,mapTypeId:google.maps.MapTypeId[propertyMapData.mapType]},i=new google.maps.Map(document.getElementById(o.detailsMapId),r),l={position:t,map:i,title:$(o.mapTitleHandle).html(),draggable:!1};o.markerIconUrl&&(l[icon]=o.markerIconUrl);var d=new google.maps.Marker(l),s=new google.maps.LatLng(propertyMapData.sViewLat,propertyMapData.sViewLng),m={addressControl:!1,position:s,pov:{heading:propertyMapData.sViewHeading,pitch:propertyMapData.sViewPitch,zoom:propertyMapData.sViewZoom}},p=new google.maps.StreetViewPanorama(document.getElementById(o.detailsStreetViewId),m),c=new google.maps.StreetViewService;c.getPanoramaByLocation(m.position,50,function(e,a){"ZERO_RESULTS"==a?(console,$(o.detailsStreetViewId).hide(),$(o.streetviewHideHandle).hide()):console}),$('a[data-toggle="tab"]').on("shown.bs.tab",function(e){e.target,e.relatedTarget,google.maps.event.trigger(i,"resize"),i.setCenter(n),p.setVisible(!0)})}$(o.enquiryFormHandle).validate(o.enquiryValidation),$(o.submitHandle,o.enquiryFormHandle).click(function(e){e.preventDefault(),$(o.enquiryFormHandle).valid()?bDig.loadModal({content:!1,template:"assets/snippets/bdpweb/theme/jstemps/pleasewait.html",timeOut:!1,tParams:{},onComplete:function(){$.ajax({url:window.location.href,type:"POST",data:{formData:"1",enqtype:"denquiry"},success:function(e){e.output?bDig.loadModal({content:e.output,onComplete:function(){setTimeout(function(){location.reload()},2e3)},tParams:{modId:"bdModal"}}):console&&console.log("There was a problem submitting the form")}})}}):$(o.errorMsgHandle,o.enquiryFormHandle).slideDown()});var g=new a({formHandle:o.sendFriendFormHandle,submitFlag:"sendFriend",formModalId:"sendFriend"}),u=new a({formHandle:o.homeReportFormHandle,submitFlag:"hreport",formModalId:"espchr"}),f=new a({formHandle:o.requestViewingFormHandle,submitFlag:"viewing",formModalId:"bookViewing"});$(o.lazyLoadingOuterContainerHandle).each(function(){var e=30,a=this,n=$(window),t=!1,r=bDig.isIphone()?20:5;n.unbind("scroll"),$(o.lazyLoadingGraphicHandle).hide(),n.scroll(function(){var i=Number($(document).height())-Number(n.height())-r;$(window).scrollTop()>=i&&($(o.lazyLoadingGraphicHandle,a).show(),t&&t.abort(),t=$.ajax({url:window.location.href,type:"POST",data:{startRow:$(".search_result",a).length,lazyLoadRes:1},success:function(t){bDig.checkDebug(t);var r=bDig.grabOutput(t);if(r.length<e)""==r.replace(/ /g,"")&&(n.unbind("scroll"),$(o.lazyLoadingGraphicHandle,a).hide());else{var i=bDig.grabData(t,"finalRes"),l=$("window").scrollTop();$(o.lazyLoadingContainerHandle,a).append(r),$("window").scrollTop(l),$(o.lazyLoadingGraphicHandle,a).hide()}}}))})})}