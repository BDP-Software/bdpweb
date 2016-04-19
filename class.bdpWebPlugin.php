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

var $homeResTitle = 'streetName';

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
 * sale price options
 */
var $salePriceOptions = '0,50000,75000,100000,125000,150000,175000,200000,225000,250000,275000,300000,325000,350000,400000,450000,500000,600000,700000,800000,900000,1000000,1250000,1500000,2000000,2500000,3000000,3500000,4000000,4500000,5000000,6000000,7500000,10000000,15000000,20000000,30000000,40000000,50000000,75000000,100000000,150000000';

/**
 * sale price options
 */
var $letPriceOptions = '0,200,500,750,1000,1250,1500,2000,2500,3000,4000,5000,10000,20000';

var $maxSalePrice = 1490000;

var $maxLetPrice = 10000;

//set the property type options
var $pTypeArr = array(
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
/*
 * Max Bedrooms
*/
var $bedRoomsMax = 6;


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
		'imgTpls',
		'salePriceOptions',
		'letPriceOptions',
		'maxSalePrice',
		'maxLetPrice',
		'pTypeArr',
		'bedRoomsMax'
	);
	//setup the plugin
	foreach($classMap as $key => $map){
		if($$map){
			$this->$map = $$map;
		}
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
	$prop['pType'] = $prop['typeNames'][0];
	$prop['homeResTitle'] = $prop[$this->homeResTitle];
	$prop['imgPath'] = 'https://bdphq.com/?bdimg='. $prop['imagePath'];
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
			
			$prop['roomText'] .= $this->processModeTpl($this->roomTpl,'roomTpl',$room);
			
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
				$nameParts = explode ('.',$imgTpl);
				
				$useChunk = ((count($nameParts) > 1) ? false : true);
				
				if(!array_key_exists($imgKey,$prop)){
					$prop[$imgKey] = '';
				}
				if($useChunk){
					$prop[$imgKey] .= $this->processTpl($imgTpl,'',$image);
				}
				else{
					$prop[$imgKey] .= $this->processTpl('',$imgTpl,$image);	
				}
				
			}
			//echo $imgKey .' :: '. $imgTpl; 
		}
		else{
			if(!array_key_exists('imgString',$prop)){
				$prop['imgString'] = '';
			}
			
			$prop['imgString'] .= $this->processTpl('','imgstring.html',$image);
		}
	}
	//exit;
	//var_dump($prop); exit;
	
	//set the form handler url
	$prop['formHandlePath'] = $this->modx->makeUrl($this->enqDocId);
	
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
	if(isset($_POST['formData'])){
		
		//setup the request data
		$rData = array(
			'propertyId'=>$this->currentRef,
			'createdDate'=>$requestDate,
			'viewingRequested'=>true,
			'message'=>$_POST['message'],
			'contactData'=>array(
				'firstName'=>$_POST['firstName'],
				'lastName'=>$_POST['lastName'],
				'tel1'=>$_POST['tel'],
				'email'=>$_POST['email'],
			)
		);
		
		//run the api request
		$this->api->newRequest($rData);
		
		$modalOutput = $this->processTpl('','thanks.html',array());
		//send a success message to the user
		$this->jsonEcho($modalOutput);
	}
}

/**
 * handles detail requests
*/
function detailRequest(){
	if(isset($_POST['formData'])){
		//setup the request data
		$rData = array(
			'propertyId'=>$this->currentRef,
			'createdDate'=>$requestDate,
			'detailsRequested'=>true,
			'message'=>$_POST['message'],
			'contactData'=>array(
				'firstName'=>$_POST['firstName'],
				'lastName'=>$_POST['lastName'],
				'name'=>$_POST['name'],
				'tel1'=>$_POST['tel'],
				'email'=>$_POST['email'],
			)
		);
		
		//run the api request
		$this->api->newRequest($rData);
		
		
		$modalOutput = $this->processTpl('','thanks.html',array());
		//send a success message to the user
		$this->jsonEcho($modalOutput);
	}
}

/**
 * handles send friend requests
*/
function sendFriendRequest(){
	if(isset($_POST['formData'])){
		//run the api request
		//setup the request data
		$rData = array(
			'propertyId'=>$this->currentRef,
			'createdDate'=>$requestDate,
			'sendFriend'=>true,
			'message'=>$_POST['message'],
			'friendData'=>array(
				'name'=>$_POST['friendsName'],
				'email'=>$_POST['friendsName'],
			),
			'contactData'=>array(
				'firstName'=>$_POST['firstName'],
				'firstName'=>$_POST['lastName'],
				'tel1'=>$_POST['tel'],
				'email'=>$_POST['email'],
			)
		);
		
		//run the api request
		$this->api->newRequest($rData);
		
		$modalOutput = $this->processTpl('','thanks.html',array());
		//send a success message to the user
		$this->jsonEcho($modalOutput);
	}
}

/**
 * viewing request logic and form
*/
function hReport(){
	if(isset($_POST['formData'])){
		
		//setup the request data
		$rData = array(
			'propertyId'=>$this->currentRef,
			'createdDate'=>$requestDate,
			'hrRequest'=>true,
			'message'=>$_POST['message'],
			'contactData'=>array(
				'firstName'=>$_POST['firstName'],
				'lastName'=>$_POST['lastName'],
				'name'=>$_POST['name'],
				'tel1'=>$_POST['tel'],
				'email'=>$_POST['email'],
			)
		);
		
		//run the api request
		$this->api->newRequest($rData);
		
		
		$modalOutput = $this->processTpl('','thanks.html',array());
		//send a success message to the user
		$this->jsonEcho($modalOutput);
	}
}





/**
 * runs the search form stuff
*/
function searchForm(){
	
	
	//set the form fields
	
	
	$this->searchParams = array();
	//create the search params
	foreach($_GET as $key => $sParam){
		$this->searchParams[$key] = array(
			'Value'=>$sParam
		);
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Sale Price Options
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	//set the price array
	$priceArr = explode(',',$this->salePriceOptions);
	//loop the array and create the options - min then max
	$this->minPriceOptionsSale = '';
	$this->maxPriceOptionsSale = '';
	
	foreach($priceArr as $price){
		$selected = false;
		$doBreak = false;
		if($this->searchParams['pricefrom']['Value'] == $price){
			$selected = true;
		}
		if($price > $this->maxSalePrice){
			$doBreak = true;
		}
		$this->minPriceOptionsSale .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
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
		if($price > $this->maxSalePrice){
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
		$this->maxPriceOptionsSale .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
		if($doBreak){
			break;
		}
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/// Let Price Options
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	//set the price array
	$priceArr = explode(',',$this->letPriceOptions);
	//loop the array and create the options - min then max
	$this->minPriceOptionsLet = '';
	$this->maxPriceOptionsLet = '';
	
	foreach($priceArr as $price){
		$selected = false;
		$doBreak = false;
		if($this->searchParams['pricefrom']['Value'] == $price){
			$selected = true;
		}
		if($price > $this->maxLetPrice){
			$doBreak = true;
		}
		$this->minPriceOptionsLet .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
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
		if($price > $this->maxLetPrice){
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
		$this->maxPriceOptionsLet .= $this->optionField($price,$this->prepareCurrency($price,0),$selected);
		if($doBreak){
			break;
		}
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
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
	
	
	$this->pTypeOptions = '';
	//min price options
	
	
	foreach($this->pTypeArr as $option){
		$selected = false;
		if($this->searchParams['property_type']['Value'] == $option['value']){
			$selected = true;
		}
		$this->pTypeOptions .= $this->optionField($option['value'],$option['label'],$selected);
	}
	
	
	
	
	
	//integrate the outer template	
	$display = $this->processModeTpl($this->tpl,'tpl',array(
		'minPriceOptionsSale'=>$this->minPriceOptionsSale,
		'maxPriceOptionsSale'=>$this->maxPriceOptionsSale,
		'minPriceOptionsLet'=>$this->minPriceOptionsLet,
		'maxPriceOptionsLet'=>$this->maxPriceOptionsLet,
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
	
	unset($params['q']);
	
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
		
		
		if($_POST['enqtype'] == 'sendFriend'){
			$this->sendFriendRequest();
		}
		
		if($_POST['enqtype'] == 'denquiry'){
			$this->detailRequest();
		}
		
		if($_POST['enqtype'] == 'viewing'){
			$this->viewingRequest();
		}
		
		if($_POST['enqtype'] == 'hreport'){
			$this->hReport();
		}
	}
	//var_dump($prop);
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