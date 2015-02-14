# BDP MOdx Plugin v1.0.0 

What is this?
A MODx snippet to allow you to use the BDP property software API. It contains a PHP API client, relevant JS and Twitter Bootstrap-compatible templates which you can optionally override. It is designed to plug straight in to a Bootstrap installation and work out-of-the-box.

Please feel free to adapt and improve and commit for the benefit of all.

This is licensed under an MIT standard license. Please see the separate document.

*Steps to implement:*

Clone this GitHub repo into your MODx assets/snippets directory

Create a new folder at the same level called 'bdpweb_cfg'.
Put the example config file from the repo in to the new folder and call it cfg.php
Enter the correct credentials and ModX details in to the config file.

Include BDP JS File, ideally in the footer
Run the BDP Modx JS
	Either put the following code in a script tag in the dom, or in a js file, ideally called in the footer.
Include CSS File, in the header

Theme Dependancies:-
	Google Maps API V3 - Google Maps
	Slick Slider - https://github.com/kenwheeler/slick - Property Detail Carousel
	Font Awesome - Icons for buttons
	jQuery Validation	http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js - Validation on forms
	Mustache JS - https://cdnjs.cloudflare.com/ajax/libs/mustache.js/0.8.1/mustache.min.js ( https://github.com/janl/mustache.js) - Browser side JS template merging
	

	
	
/*
 * jquery on document ready functions
*/
$(document).ready(function(){
	var bdpModX = new bdpModXCore({});	
});

	
