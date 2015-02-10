function matchItems(){
	for(var i in allMatches){
		$('#'+allMatches[i][1]).val($('#'+allMatches[i][0]).val());
	}
}

//runs when the main document body is ready to run (uses the jquery framework)
$(document).ready(function(){
	
	/**
	 * sets the billing form
	*/
	function setBillingForm(){
		/**
		 * payment form button
		*/
		$('#satbilling').submit(function(e){
			e.preventDefault();
			$.fancybox.showActivity();
			$.ajax({
				type: "POST",
				url: window.location.href,
				data: $("#satbilling").serialize(),
				success: function(data){
					var output = bDig.grabOutput(data);
					if(output){
						var paySuccess = bDig.grabData(data,'paySuccess')
						if(paySuccess == '1'){
							//payment was successful
							$('#payFormContainer').html(output);
							//do something else possibly??
						}
						else{
							//payment failed, reloading the form and setting the form code
							$('#payFormContainer').html(output);
							setBillingForm();
						}
						// Close
						$.fancybox.hideActivity();
					}
				}
			});
		});
	}
	//set the billing form running
	setBillingForm();
});