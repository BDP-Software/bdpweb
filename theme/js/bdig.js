/*
 * JS to be found in both the CMS and Front End
*/
function bDig(){

//Variables
var basePath;
var fullPagePath;

/*
 * jquery on document ready functions
*/
$(document).ready(function(){
	
	//set the base path
	bDig.getBasePath();
	
	//set the full page path (ajax friendly)
	bDig.getFullPath();
	
	//set the get forms on page load
	bDig.setGForms();
	
	//set search forms on page load
	bDig.setSForms();

});

/**
 * grabs the root base path
*/
this.getBasePath = function(){
	if(!(this.basePath)){
		this.basePath = $('base').attr('href');
	}
}

/**
 * grabs the file path - using the get var
*/
this.getFullPath = function(){
	//remove the basepath from the href
	fullPagePath = $('base').attr('href') + '?a='+ window.location.href.replace($('base').attr('href'),'');
	return fullPagePath;
}

/*
 * checks a response for debug html
*/
this.checkDebug = function(data){
	var dbOutput = (data.debugOutput ? data.debugOutput : $(data).find('debug').text());
	if(dbOutput.length > 0){
		//check if the debug panel is already open, if so add to the existing panel
		if($('#bd_debug').length > 0){
			//grab the internal output
			$('#debug_output').append($(dbOutput).find('#debug_output').html());
		}else{
			dbOutput = $(dbOutput).addClass('hide');
			$('body').prepend(dbOutput);
			
			$('#bd_debug').show('slow');
		}
	}
}
	
/*
 * grabs the output from an echo'd set of xml
*/
function grabOutput(data){
	
	var output = ((data.output != undefined) ? data.output : $(data).find('htmlOutput').text());
	return output;
}
this.grabOutput = grabOutput;

/*
 * grabs the data of a given node
*/
this.grabData = function(data,nodeName){	
	var output = ((data[nodeName] != undefined) ? data[nodeName] : $(data).find(nodeName).text());
	return output;
}

/**
 * set any search get forms for blogs
*/
this.setGForms = function (){
	$('.bdig_gform').click(function(e){
		e.preventDefault();
		//put the full path together
		output = getSearchQString($(this).attr('value'));
		//relocate to the output path
		window.location.href = output;
	});
	$('.bdig_gform_reset').click(function(e){
		e.preventDefault();
		//put the full path together
		output = getSearchQString($(this).attr('value'),true);
		//relocate to the output path
		window.location.href = output;
	});
}

/**
 * returns a query string for a search form
 * @param formId id of the form
*/
function getSearchQString(formId,doReset){
	
	var newQueryArr = getSearchQArr(formId,doReset);
	var outputQString = '';
	//create the new query string, filter blank values
	for(i in newQueryArr){
		if(newQueryArr[i]){
			outputQString = outputQString + '&' + i +'=' + newQueryArr[i];
		}
	}
	var queryArr = window.location.href.split( '&' );
	var queryArrOrig = queryArr.shift();
	//put the full path together
	output = queryArrOrig + outputQString;
	output = output.replace(/"/g,'');
	return output;
}
this.getSearchQString = getSearchQString;

/**
 * returns a query string for a search form
 * @param formId id of the form
*/
function getSearchQArr(formId,doReset){
	var formArr =  $(':input:not(.nogo), select', $('#'+ formId)).serializeArray();
	var queryArr = window.location.href.split( '&' );
	var queryArrOrig = queryArr.shift();
	var formArrOut = new Object;
	var queryArrOut = new Object;
	var i=0;
	var sp = [];
	
	var newQueryArr;
	var output = '';
	var cBoxes = new Array;
	var reset = (doReset ? true : false);
	
	//set an array of form values
	for(i in formArr){
		formArrOut[formArr[i].name] = formArr[i].value;
		if(reset){
			formArrOut[formArr[i].name] = '';
		}
	}
	//gather the checkboxes - add blank values for empty checkboxes
	$('input[type=checkbox]', $('#'+ formId)).each(function(){
		if(!formArrOut[$(this).attr('name')]){
			formArrOut[$(this).attr('name')] = '';
		}
	});
	//grab an array of the current query string
	for(i in queryArr){
		sp = queryArr[i].split('=');
		queryArrOut[sp[0]] = sp[1];
	}
	delete queryArrOut['resgroup'];
	
	
	
	//put an array of new query values
	newQueryArr = $.extend(queryArrOut,formArrOut);
	
	//console.log(output);
	
	//return the output
	return newQueryArr;
}
this.getSearchQArr = getSearchQArr;


/**
 * sets search forms
*/
this.setSForms = function(){
	
	//set the autocomplete cells
	$(".bdig_thelp").each(function(){
		//set the auto complete
		$(this).autocomplete({
			//multiple: true
			source: fullPagePath + '&' + $(this).attr('id') + 'jax=1',
			dataType: "json",
			success: function(data) {
				return $.map(data, function(row) {
					
					return {
						data: row,
						value: row.name,
						label: row.name,
						result: row.name
					}
				});
			},
			select: function( event, ui ) {
				
			},
			open: function() {
				//$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
			},
			close: function() {
				//$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
			}
		});
	});
	
}


	/**
	 * fires a given variable and calls a callback
	 *@param string of vars or a single var name
	 *@param cBack mixed cakkback function
	*/
	this.sjb = function sjb(vars,cBack,timeOut){
		var doVars;
		if(typeof vars == 'string'){
			doVars = (vars.indexOf('=') == -1 ? vars+'=1' : vars);
		}
		else{
			doVars = vars;
		}
		
		$.ajax({
			type: "POST",
			url:window.location.href,
			data: doVars,
			success: function(data){
				bDig.checkDebug(data);
				$.fancybox({
					content : grabOutput(data),
					scrolling : 'no',
					padding : 0,
					showCloseButton : (timeOut ? false : true),
					onComplete : function(){
						if (typeof cBack == "function"){ 
							cBack(data);
						}
						else{
							eval(cBack);
						}
						if(timeOut){
							setTimeout(function(){
								$.fancybox.close();
							},timeOut);
						}
					}
				});
			}
		});
	}
	
	/**
	 * modal equivalent to sjb
	 *@param string of vars or a single var name
	 *@param cBack mixed cakkback function
	*/
	this.sjm = function sjm(options){
		var settings = {
			timeOut : false
		};
		
		$.extend(true,settings,options);
		
		var doVars;
		if(typeof settings.vars == 'string'){
			doVars = (settings.vars.indexOf('=') == -1 ? settings.vars+'=1' : settings.vars);
		}
		else{
			doVars = settings.vars;
		}
		
		$.ajax({
			type: "POST",
			url:window.location.href,
			data: doVars,
			success: function(data){
				bDig.checkDebug(data);
				var modalSettings = settings;
				modalSettings.content = bDig.grabOutput(data)
				bDig.loadModal(modalSettings);
			}
		});
	}
	
	/**
	 * loads a modal
	*/
	this.loadModal = function loadModal(options){
		var settings = {
			content : false,
			template : 'js/jstemps/confirmbox.html',
			timeOut : false,
			tParams : {
				title : 'Please Confirm',
				msg : 'Are you sure?',
				modId : 'bdModal'
			}
		};
		var modalContent = '';
		//extend the settings with custom options
		$.extend(true,settings,options);
		
		if(settings.content){
			modalContent = settings.content;
			runModal();
		}
		else{
			//console.log(bla);
			$.get(settings.template, function(template) {
				modalContent = Mustache.render(template, settings.tParams);
				runModal();
			});
		}
		
		function runModal(){
			if (!($('#bdModalContainer').length)){
				$('body').prepend('<div id="bdModalContainer"></div>');
			}
			var existingModal = (settings.removeId ? $('#'+settings.removeId) : $('#'+settings.tParams.modId));
			if(existingModal.length > 0){
				existingModal.on('hidden.bs.modal', function (e) {
					//progressNewModal();
				});
				//console.log(existingModal);
				existingModal.removeClass("fade").modal("hide");
				progressNewModal();
			}
			else{
				progressNewModal();
			}
			
			/**
			 * progresses the modal to completion
			*/
			function progressNewModal(){
				
				$('#bdModalContainer').html(modalContent);
				var myModal = $('#'+settings.tParams.modId);
				//run the on complete function
				bDig.handleCallBack(settings.onComplete,myModal);
				//modal code goes here
				myModal.modal();
				
				myModal.addClass("fade").modal("show");
				//if a timeout is specified, run the timeout and close
				if(settings.timeOut){
					setTimeout(function(){
						$('#'+settings.tParams.modId).modal('hide');
						$('body').removeClass('modal-open');
						$('.modal-backdrop').remove();
					},settings.timeOut);
				}
			}
			
		}
		
	}
	
	/**
	 * function to handle callback parameters and arguments
	*/
	function handleCallBack(cBack,data,data2){
		if (typeof cBack == "function"){ 
			cBack(data,data2);
		}
		else{
			eval(cBack);
		}
	}
	this.handleCallBack = handleCallBack;
	
	/**
	 * detects if the broswer is an iphone
	*/
	function isIphone(){
		if (navigator && navigator.userAgent && navigator.userAgent != null) {
			var strUserAgent = navigator.userAgent.toLowerCase();
			var arrMatches = strUserAgent.match(/(iphone|ipod|ipad)/);
			if (arrMatches) 
				 return true;
		} // End if (navigator && navigator.userAgent) 

		return false;
	}
	this.isIphone = isIphone;
}
var bDig = new bDig();