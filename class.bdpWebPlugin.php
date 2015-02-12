<?php
/**
 * BDP Web Plugin Class
*/ 
class bdpWebPlugin extends uni{

//bdp base url, used for BDP sourced assets such as images
var $bdpUrl = 'https://api.bdphq.com/';

//carousel image data
/**
 * potential remove the below
var $carouselThumbData = 'w=104&h=85&q=80';
var $detailImageData = 'w=512&h=421&q=80';
var $proPicParams = 'w=160&h=120&q=80';
*/

//set the default templates path
var $defTplPath = 'assets/snippets/bdpweb/chunks/';

//curency setup
var $cSymbolPosition = 'pre';
var $currencySymbol = '&pound;';

//template map
var $tplMap = array(
	'sideBarSearch',
	'defaultSearch',
	'homeRes',
	'defaultResGrid',
	'defaultResOuterGrid',
	'detailPage',
);

var $modeMap = array(
	'home' => array(
		'tpl'=>'homeInner.html',
		'outerTpl'=>'homeOuter.html',
		'func'=>'searchResults',
		'searchParams'=>'nres=12&ord=decprice'
	),
	'results' => array(
		'tpl'=>'sresInner.html',
		'outerTpl'=>'sresOuter.html',
		'func'=>'searchResults',
		'searchParams'=>'nres=30&ord=decprice'
	),
	'details' => array(
		'tpl'=>'details.html',
		'roomTpl'=>'room.html',
		'hrTpl'=>'bdpEspcHr.html',
		'func'=>'detailsPage',
	),
	'formHandler' => array(
		'func'=>'formHandler'
	),
	'searchForm' => array(
		'tpl'=>'searchform.html',
		'func'=>'searchForm'
	),
	'sideSearch' => array(
		'tpl'=>'sideBarSearchForm.html',
		'func'=>'searchForm'
	)
);



/**
 * startup function
*/
function startUp(){
	
	
	//set the includes path
	$incPath = MODX_BASE_PATH .'assets/snippets/';
	
	//include the config file
	include($incPath . 'bdpweb_cfg/cfg.php');

	//class map
	$classMap = array(
		'defaultMapZoom',
		'defaultStreetViewZoom',
		'defaultStreetViewHeading',
		'defaultStreetViewPitch',
		'defauldMapType',
		'detailPageId',
		'resPageId',
		'resPageIdLettings',
		'chunkFolder',
		'imgTpls'
	);
	//setup the plugin
	foreach($classMap as $key => $map){
		$this->$map = $$map;
	}

	
	//run the class
	$api = new gPropsRestClient();
	//set the api key
	//$api->apiKey = 'your api key';
	$api->apiKey = $apiKey;
	//set the shared secret
	//$api->sharedSecret = 'your shared secret';
	$api->sharedSecret = $sharedSecret;
	//set the api path
	$api->apiPath = 'https://bdphq.com/restapi';

	//set the firm id
	$api->accId = $accId;
	$api->forceInsecure = true;
	//start the api client
	$api->startUp();

	if($_SESSION['bdSessId']){
		$api->bdSessId = $_SESSION['bdSessId'];
	}

	//set the api to the plugin
	$this->api = $api;
	
	//define the ordering options
	$this->orderingOptions = $orderingOptions;
	
	$snippetEndTime = microtime(true);
	$totalSnippetTime = $snippetEndTime - $snippetStartTime;
	//$display .= 'Snippet Time: '. $totalSnippetTime .' milliseconds.';
	//$display .= 'API Time: '. $apiTimeTaken .' millisecopnds';
	
	if(array_key_exists($this->mode,$this->modeMap)){
		$this->modeData = $this->modeMap[$this->mode];
		//run the mode function
		if(method_exists($this,$this->modeData['func'])){
			$func = $this->modeData['func'];
			$display = $this->$func();
		}
	}
	
	
	return $display;
}


/**
 * applies standard logic to a property
*/
function applyLogic($prop){
	$prop['detailPath'] = $this->modx->makeUrl($this->detailPageId,'',array('p'=>$prop['property_id']));
	//echo "making url with id: ". $this->detailPageId;
	return $prop;
}

/**
 * applies detailed logic to the property
*/
function applyDetailLogic($prop){
	
	//run map logic
	$prop = $this->parseMapData($prop);
	
	//set the rooms
	$prop['roomText'] == '';
	//if the property has rooms, create the room text
	if(is_array($prop['rooms'])){
		foreach($prop['rooms'] as $room){
			$feetWidth = $this->metersToFeetInches($room['roomWidth']);
			$feetLength = $this->metersToFeetInches($room['roomLength']);
			$room['roomDimensions'] = (is_numeric($room['roomWidth']) && is_numeric($room['roomLength']) ? '('. number_format($room['roomWidth'],2,'.',',') .'m x '. number_format($room['roomLength'],2,'.',',') .'m / '. $feetWidth['feet'] ."'".$feetWidth['inches'] .'" x '. $feetLength['feet'] ."'".$feetLength['inches'] .'")' : '');
			
			$prop['roomText'] .= $this->modx->getChunk($this->roomTpl,$room);
			
		}
	}
	
	//parse the negotiator 
	$negotiator = $prop['staffData']['negDetails'];
	$negotiator['proPicParsed'] = ($negotiator['proPic'] ? $this->bdpUrl .'?bdimg='. $negotiator['proPic'] .'&'. $this->proPicParams.'&fname='. $negotiator['proPic'] : '');
	
	
	foreach($negotiator as $key => $negParts){
		$prop['staffNeg_'.$key] = $negParts;
	}
	
	//check first for the espc home report id
	if($prop['escpHrId']){
		//set espc frame to true, a fancy box will appear containing the espc frame
		$prop['hrEspcFrame'] = true;
		$prop['hrPath'] = 'https://memberportal.espc.com/HomeReports/RequestHomeReport.aspx?id='. $prop['escpHrId'];
	}
	elseif($prop['espcId']){
		//set espc tab to true, the home report button will open a new tab, no fancybox
		$prop['hrEspcTab'] = true;
		$prop['hrPath'] = 'http://www.espc.co.uk/Buying/'. $prop['espcId'] .'/requestHomeReport.html';
	}
	else{
		//if neither the espc id or the homereport id ref are available use the custom in-house form
		$prop['hrBasic'] = true;
	}
	
	
	//set the image carousel
	//$prop = $this->grabDetailImages($prop);
	foreach($prop['images'] as $key => $image){
		if(is_array($this->imgTpls)){
			foreach($this->imgTpls as $imgKey => $imgTpl){
				if(!array_key_exists($imgKey,$prop)){
					$prop[$imgKey] = '';
				}
				$prop[$imgKey] .= $this->processTpl('',$imgTpl,$image);
			}
			//echo $imgKey .' :: '. $prop[$imgKey]; exit;
		}
		else{
			if(!array_key_exists('imgString',$prop)){
				$prop['imgString'] = '';
			}
			
			$prop['imgString'] .= $this->processTpl('','imgstring.html',$image);
		}
	}
	
	
	//set the form handler url
	$prop['formHandlePath'] = $this->modx->makeUrl($this->enqDocId);
	
	return $prop;
}

/*
* @depreciated 01/12/2014
 * grabs and creates the images string
*/
function grabDetailImages($prop){
	
	$prop['showImageCarousel'] = false;
	//set the carousel string to blank by default
	$prop['tinyImageString'] = '';
		
	//set a counter for the thumbnails outputted - counts the thumbnails that pass all checks
	$thumbsCount = 0;
	$outputCount = 0;
	
	//if property images have been found, loop through tehm creating the carousel list
	if($prop['images']){
		$miniString = '';
		$shadowString = '';
		foreach($prop['images'] as $key => $image){
			//set the alt tag
			//$altTag = ($image['altTag'] ? $image['altTag'] : 'image '. $key+1);
			
			//grab the image path#
			$imgPath = $image['url'];
			$imgFile = basename($imgPath);
			
			//set the thumbnail path
			$carouselPath = $imgPath . ($this->carouselThumbData ? '&'. $this->carouselThumbData : '') . '&fname=/'. $imgFile;
						
			//set the large image path
			$largePath = $imgPath . ($this->detailImageData ? '&'. $this->detailImageData : '') . '&fname=/'. $imgFile;
			if($outputCount == 0){
				$prop['primaryImagePath'] = $largePath;
			}
			
			
			//set the popup image path
			$popupPath = $imgPath .'&w=1024&q=80&fname=/'. $imgFile;
			
			//put the string together	
			$miniString .= $this->liTag($this->aTag($this->imgTag($carouselPath,$altTag),$largePath));
		
			//create the large string
			$largeString .= $this->liTag($this->aTag($this->imgTag($largePath,$altTag),$popupPath .'" rel="detailimage'));
			
			//create the shadow string
			$shadowString .= $this->aTag($this->imgTag($popupPath,$altTag),$popupPath .'" rel="detailpopup');
			
			$outputCount++;
		}
		
		$detailAtts = $this->qStrArr($this->detailImageData);
		$carouselAtts = $this->qStrArr($this->carouselThumbData);
		if($detailAtts['w'] && $carouselAtts['w']){
			$this->minCarouselImages = $detailAtts['w']/$carouselAtts['w'];
		}
		
		
		$prop['tinyImageString'] = $miniString;
		$prop['shadowString'] = $shadowString;
		$prop['largeImageString'] = $largeString;
		$prop['showImageCarousel'] = ($outputCount >= $this->minCarouselImages);
		//set basic image tsring to true when there is more than one image and less than the carousel requires
		$prop['basicImageString'] = (($outputCount < $this->minCarouselImages) and ($outputCount > 1));
		
		//print_r($prop);
		
	}
	return $prop;
}


/**
 * parses map data for a property
*/
function parseMapData($prop){
	//set the default map data
	$mapData = array(
		'mapZoom'=>$this->defaultMapZoom,
		'mapCentreLat'=>$prop['lat'],
		'mapCentreLng'=>$prop['lng'],
		'markerLat'=>$prop['lat'],
		'markerLng'=>$prop['lng'],
		'sViewLat'=>$prop['lat'],
		'sViewLng'=>$prop['lng'],
		'sViewZoom'=>$this->defaultStreetViewZoom,
		'sViewHeading'=>$this->defaultStreetViewHeading,
		'sViewPitch'=>$this->defaultStreetViewPitch,
		'mapType'=>$this->defauldMapType,
		);
	
	//grab the stored map data
	$storedData = json_decode($prop['mapData'],true);
	$storedData = (is_array($storedData) ? $storedData : array());
	//merge the map arrays
	$outputMapData = array_merge($mapData,$storedData);
	$processedMapData = array();
	foreach($outputMapData as $key=>$data){
		$processedMapData[$key] = ($key == 'mapType' ? strtoupper($data) : floatval($data));
	}
	
	//convert the output array back in to json for output
	$prop['outputMapDataString'] = json_encode($processedMapData);
	
	return $prop;
}

/**
 * viewing request logic and form
*/
function viewingRequest(){
	//check if the actual form was posted
	if(isset($_POST['formname_viewing_form'])){
	
	}
	else{
		//output the form
		$display = $this->modx->getChunk($this->rViewingTpl,array());
		
	}
	return $this->jsonEcho($display);
}

/**
 * viewing request logic and form
*/
function hReport(){
	//var_dump($this->prop); exit;
	//check if the actual form was posted
	$display = $this->modx->getChunk($this->hrTpl,$this->prop);
	return $this->jsonEcho($display);
}






/**
 * runs the search form stuff
*/
function searchForm(){
	$this->maxPrice = 1490000;
	$this->bedRoomsMax = 6;
	
	//set the form fields
	//minPriceOPtions
	$priceOptions = '0,50000,75000,100000,125000,150000,175000,200000,225000,250000,275000,300000,325000,350000,400000,450000,500000,600000,700000,800000,900000,1000000,1250000,1500000,2000000,2500000,3000000,3500000,4000000,4500000,5000000,6000000,7500000,10000000,15000000,20000000,30000000,40000000,50000000,75000000,100000000,150000000';
	//set the price array
	$priceArr = explode(',',$priceOptions);
	//loop the array and create the options - min then max
	$this->minPriceOptions = '';
	$this->maxPriceOptions = '';
	
	$this->searchParams = array();
	//create the search params
	foreach($_GET as $key => $sParam){
		$this->searchParams[$key] = array(
			'Value'=>$sParam
		);
	}
	
	//min price options
	foreach($priceArr as $price){
		$selected = false;
		$doBreak = false;
		if($this->searchParams['pricefrom']['Value'] == $price){
			$selected = true;
		}
		if($price > $this->maxPrice){
			$doBreak = true;
		}
		$this->minPriceOptions .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
		if($doBreak){
			break;
		}
	}
	
	
	$maxSet = false;
	$priceTiers = count($priceArr);
	$pI = 0;
	//max price options
	foreach($priceArr as $price){
		$pI++;
		$selected = false;
		$doBreak = false;
		if(($this->searchParams['priceto']['Value'] == $price) && $price){
			$maxSet = true;
			$selected = true;
			//echo "selecting 1";
		}
		if($price > $this->maxPrice){
			$doBreak = true;
			if(!$maxSet){
				//echo "selecting 2";
				$selected = true;
			}
		}
		if(($pI >= $priceTiers) && !$maxSet){
			$selected = true;
		}
		//echo $price .' :: '. $selected ."\n";
		$this->maxPriceOptions .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
		if($doBreak){
			break;
		}
	}
	
	$this->minBedOptions = '';
	$this->maxBedOptions = '';
	
	//set the bedroom dropdowns (min then max)
	for($i=0;$i<$this->bedRoomsMax+1;$i++){
		$selected = false;
		if($this->searchParams['bedRoomsMin']['Value'] == $i){
			$selected = true;
		}
		$this->minBedOptions .= $this->optionField($i,$i,$selected);
	}
	$maxSet = false;
	for($i=0;$i<$this->bedRoomsMax+1;$i++){
		$selected = false;
		if(($this->searchParams['bedRoomsMax']['Value'] == $i) && $i){
			$maxSet = true;
			$selected = true;
		}
		if(($i == $this->bedRoomsMax) && (!$maxSet)){
			$selected = true;
		}
		$this->maxBedOptions .= $this->optionField($i,$i,$selected);
	}
	
	//set the property type options
	$pTypeArr = array(
		array(
			'label'=>'Any',
			'value'=>'',
		),
		array(
			'label'=>'Houses',
			'value'=>'46',
		),
		array(
			'label'=>'Flats / Apartments',
			'value'=>'47',
		),
		array(
			'label'=>'Not Specified',
			'value'=>'308',
		),
		array(
			'label'=>'Garage / Parking',
			'value'=>'83',
		),
		array(
			'label'=>'Bungalows',
			'value'=>'60',
		),
		array(
			'label'=>'Reirement Property',
			'value'=>'84',
		),
		array(
			'label'=>'Land',
			'value'=>'71',
		),
		array(
			'label'=>'Character Property',
			'value'=>'80',
		),
		array(
			'label'=>'Guest House',
			'value'=>'67',
		),
		array(
			'label'=>'House / FLat Share',
			'value'=>'86',
		),
	);
	$this->pTypeOptions = '';
	//min price options
	foreach($pTypeArr as $option){
		$selected = false;
		if($this->searchParams['property_type']['Value'] == $option['value']){
			$selected = true;
		}
		$this->pTypeOptions .= $this->optionField($option['value'],$option['label'],$selected);
	}
	
	
	
	
	
	//integrate the outer template	
	$display = $this->processModeTpl($this->tpl,'tpl',array(
		'minPriceOptions'=>$this->minPriceOptions,
		'maxPriceOptions'=>$this->maxPriceOptions,
		'minBedOptions'=>$this->minBedOptions,
		'maxBedOptions'=>$this->maxBedOptions,
		'pTypeOptions'=>$this->pTypeOptions,
		'formActionPath'=>$this->modx->makeUrl($this->resPageId,'','','full'),
		'formActionPathLettings'=>$this->modx->makeUrl($this->resPageIdLettings,'','','full')
	));	
	
	return $display;
}


/**
 * converts meters to feet
*/
function metersToFeetInches($meters){
	
	$valInFeet = $meters*3.2808399;
	$valFeet = (int)$valInFeet;
	$valInches = round(($valInFeet-$valFeet)*12);
	if($valInches == 12){
		$valFeet = $valFeet + 1;
		$valInches = 0;
	}
	return array(
		'feet'=>$valFeet,
		'inches'=>$valInches
	);
}


/**
 * outputs an option field
*/
function optionField($value='',$label='',$selected=false){
	return '<option value="'. $value .'" '. ($selected ? 'selected = "selected"' : '') .'>'. $label .'</option>';
}


/*
 * prepares a number or string as a currency string
 * @param $val string the cutrrency string or number
 * @param $decPlaces int the num,ber of decimal places to show, default is 2
*/
function prepareCurrency($val=0,$decPlaces = 2){
	$val = floatval(preg_replace("[^-0-9\.]","",$val));
	$val = number_format($val,$decPlaces,'.',',');
	$display = ($this->cSymbolPosition == 'pre' ? $this->currencySymbol : '') . $val . ($this->cSymbolPosition == 'post' ? $this->currencySymbol : '');
	return $display;
}


/**
 * processes a tpl based on a mode
*/
function processModeTpl($tpl,$tplKey,$data){
	return $this->processTpl($tpl,$this->modeData[$tplKey],$data);
}

/**
 * processes the templates and checks for a fallback 'default' alternative
*/
function processTpl($tpl,$tplFile,$data){
	if($tpl){
		$display = $this->modx->getChunk($tpl,$data);	
	}
	elseif($tplFile){
		$content = false;
		//echo 'File: '. $this->modeData[$tplKey];
		if($this->chunkFolder){
			//echo "trying to get content from here: ". MODX_BASE_PATH . $this->defTplPath . $this->chunkFolder .'/'. $this->modeData[$tplKey]; 
			$content = file_get_contents(MODX_BASE_PATH . $this->defTplPath . $this->chunkFolder .'/'. $tplFile);
		}
		if(!$content){
			$content = file_get_contents(MODX_BASE_PATH . $this->defTplPath . $tplFile);
		}
		$chunk = $this->modx->newObject('modChunk');
		$display = $chunk->process($data, $content);
	}
	return $display;
}

################################################################################################################
### Specific Mode Functions
################################################################################################################
/**
 * outputs standard search results
*/
function searchResults(){
	
	//grab the default search params (set in the snippet call)
	$callParams = $this->qStrArr($this->searchParams);
	//default params as set by default for a home output
	$defaultParams = $this->qStrArr($this->modeData['searchParams']);
	//merge the call params with the default params
	$defaultParams = array_merge($defaultParams,$callParams);
	//set the get parameters
	$getParams = $_GET;
	//merge the get parameters with the default paramaters
	$params = array_merge($defaultParams,$getParams);
	
	//set the results numbers
	$nres = ($defaultParams['nres'] ? $defaultParams['nres'] : 30);
	
	if(isset($_POST['startRow'])){
		$page = ceil($_POST['startRow']/$nres)+1;
		$params['resgroup'] = $page;
	}
	
	//put the query string back together
	$qString = $this->arrQStr($params);
	
	//grab all property data with an askig price over 600k
	$preTime = microtime(true);
	$props = $this->api->getProperties($qString);
	$postTime = microtime(true);
	//echo json_encode($props); exit;
	$apiTimeTaken = $postTime - $preTime;
	
	//$display = 'API Time: '. $timeTaken .' seconds.';
	
	$resultsOutput = '';
	
	//echo 'template: '. $this->tmp;
	if(is_array($props['properties'])){
		foreach($props['properties'] as $key => $prop){
			
			//run logic on the property
			$prop = $this->applyLogic($prop);
						
			//$display .= $key .'_'. $prop['streetName'];
			$resultsOutput .= $this->processModeTpl($this->tpl,'tpl',$prop);	
		}
	}
	else{
		$resultsOutput .='Results could not be parsed';
	}
	
	if(isset($_POST['lazyLoadRes'])){
		if($props['nRes'] <= $nres){
			$resultsOutput = '';
		}
		$this->jsonEcho($resultsOutput);
	}
	
	//put the ordering fields together
	$orderingFields = '';
	if(is_array($this->orderingOptions)){
		foreach($this->orderingOptions as $option){
			//detect if the field is active
			$selected  = false;
			if(isset($params['ord'])){
				if($params['ord'] == $option['value']){
					$selected = true;
				}
			}
			//create the field
			$orderingFields .= '<option value="'. $option['value'] .'" '. ($selected ? 'selected = "selected"' : '') .'>'. $option['label'] .'</option>';
		}
	}
	
	//echo 'Mode: '. $this->mode;
	
	//integrate the outer template
	$display = $this->processModeTpl($this->outerTpl,'outerTpl',array(
		'resTotal'=>$props['nRes'],
		'resOutput'=>$resultsOutput,
		'orderingFields'=>$orderingFields,
		'lazyLoadingPath'=>$this->modx->makeUrl($this->enqDocId,'','','full')
	));
	
	return $display;
}

/**
 * outputs details page
*/
function detailsPage(){
	//set the property id
	$pId = intval($_GET['p']);
	$this->currentRef = $pId;
		
	//check for a request to show the viewing form
		
	//grab all property data with an askig price over 600k
	$preTime = microtime(true);
	$prop = $this->api->getProperty($pId);
	$postTime = microtime(true);
		
	$apiTimeTaken = $postTime - $preTime;
		
	//run standard logic on the property
	$prop = $this->applyLogic($prop);
		
	//run detail logic on the property
	$prop = $this->applyDetailLogic($prop);
		
	if(isset($_POST['enqtype'])){
		$this->prop = $prop;
		if($_POST['enqtype'] == 'viewing'){
			$this->viewingRequest();
		}
		
		if($_POST['enqtype'] == 'hreport'){
			$this->hReport();
		}
	}
	
	//integrate the outer template
	$display = $this->processModeTpl($this->tpl,'tpl',$prop);	
	
	return $display;
}

/**
 * form handler function
*/
function formHandler(){
	if(isset($_POST['enqtype'])){
		if($_POST['enqtype'] == 'viewing'){
			$this->viewingRequest();
		}
	}
}




}
?>