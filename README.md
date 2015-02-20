# BDP MODx Plugin v1.0.0 

This is licensed under an MIT standard license. Please see the separate document.

You will need valid BDP Rest API credentials to access this API. Contact BDP if you require these.

What is this?
A MODx snippet to allow you to use the BDP property software API. It contains a PHP API client, relevant JS and Twitter Bootstrap-compatible templates which you can optionally override. It is designed to plug straight in to a Bootstrap installation and work out-of-the-box.

The plugin provides default, bootstrap compatible chunks (ModX templates) and a basic theme for styling and enabling javascript functionality. The plugin can be used to simply provide data to custom chunks and the chunks can be used without including the provided theme.

Please feel free to adapt and improve and commit for the benefit of all.

*Steps to implement:*

1) Clone this GitHub repo into your MODx assets/snippets directory

2) Create a new folder at the same level called 'bdpweb_cfg'.

3) Copy the contents from the example_cfg folder to this new config folder

4) Enter the correct BDP API credentials and account Id for connecting to BDP

5) Create (if you haven't already) a resource to hold search results, a separate resource for lettings search results (if required) and a result for property details. Put the resource ids in the config file.

6) Enter the snippet call on the relevant resources, chunks or templates in the ModX installation. See examples below for typical use.


##Information about search parameters
http://i.bdphq.com/controlling-output-using-the-url/

The search parameters discussed in the BDP system can be used when calling the snippet in results and home mode, this will filter the results displayed.

If you wish to modify the markup provided, create a new chunk in MODx and define it when calling the snippet. All default chunks can be found in the chunks folder provided. 

## Available Snippet Modes

###Homepage
####Example Implementation
[[!bdpWeb? &mode = `home`]]

or

[[!bdpWeb? &mode = `home` &searchParams=`nres=12&ord=decprice`]] //limiting the results to a set of 12 in descending order of price

####Available Parameters
tpl - Inner property chunk. Default: 'homeInner.html'

outerTpl - Outer property chunk. Default: 'homeOuter.html'

searchParams - Default Search Parameters. Defauylt : 'nres=12&ord=decprice'


###Search Form
[[!bdpWeb? &mode=`searchForm`]]

###Search Results
[[!bdpWeb? &mode=`results`]]

###Property Details
[[!bdpWeb? &mode=`details`]]

## Implementation of the Provided Theme
The theme has the following dependancies:

	Google Maps API V3 - Google Maps
	
	Slick Slider - https://github.com/kenwheeler/slick - Property Detail Carousel
	
	Font Awesome - Icons for buttons
	
	jQuery Validation	http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js - Validation on forms
	
	Mustache JS - https://cdnjs.cloudflare.com/ajax/libs/mustache.js/0.8.1/mustache.min.js ( https://github.com/janl/mustache.js) - Browser side JS template merging

All files in the css and js folders within the snippet must be included in the dom. For best performance include the css in the header and js in the footer.	

The BDP JS library must be initiated with the following script:
$(document).ready(function(){
	var bdpModX = new bdpModXCore({});	
});
This can be included ina s cript tag within the page footer or in a sourced javascript file.

###Example Header Includes
```
<!-- BDP Modx Theme Dependancy - Fonts -->
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

<!-- BDP Modx Theme Dependancy - Slick Slider CSS -->
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/jquery.slick/1.4.1/slick.css"/>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/jquery.slick/1.4.1/slick-theme.css"/>

<!-- Optional BDP ModX Theme -->
<link href="/modx/assets/snippets/bdpweb/theme/css/bdp.modx.css.option1.v1.0.0.css" rel="stylesheet" media="screen">

<!-- BDP Modx Theme Dependancy - Google Maps API V3 -->
<script type="text/javascript" src="http://www.google.com/jsapi?autoload={'modules':[{name:'maps',version:3,other_params:'sensor=false'}]}"></script>
```


###Example Footer Includes
```
<!-- Core Bootstrap -->
<script src="/modx/assets/components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- Include Slick Slider -->
<script type="text/javascript" src="//cdn.jsdelivr.net/jquery.slick/1.4.1/slick.min.js"></script>
<!-- jQuery Validation -->
<script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"></script>
<!-- Mustache Template Rendering -->
<script src="//cdnjs.cloudflare.com/ajax/libs/mustache.js/0.8.1/mustache.min.js"></script>
<!-- Core BDP JS -->
<script src="/modx/assets/snippets/bdpweb/theme/js/bdig.js"></script>
<!-- BDP Modx JS -->
<script src="/modx/assets/snippets/bdpweb/theme/js/bdp.modx.core.1.0.0.js"></script>
<!-- Start the BDP ModX Theme JS -->
<script type="text/javascript">
/*
 * jquery on document ready functions
*/
$(document).ready(function(){
	var bdpModX = new bdpModXCore({});	
});
</script>
```
