<?php
/**
 * @name bdpWeb
 * @version 1.0.1
 * @author Michael Barcroft
 */

//log the start time 
$snippetStartTime = microtime(true);
 
/**
 * mode
 * possible values: results, details, searchform, formHandler (viewing requests)
 * default is results
*/
$mode = ($mode ? $mode : 'results');

/**
 * tmp
 * name of the results chunk
*/
//$this->tmp = '';


/**
 * outerTmp
 * name of the outer chunk
*/
//$outerTmp = '';

/**
 * rooms template
 * repeating html for each room description
*/
//$roomTpl = '';

/**
 * results template
*/
//$sResTpl search results template, also set by $this->tmp in results mode

/**
 * request viewing template
 * viewing form html
*/
//$rViewingTpl = '';

/**
 * request hr template
 * hr form html
*/
//$hrTpl = '';

/** 
 * detail page id
*/
//$detailPageId

/**
 * results page id
*/
// $resPageId = '';


/**
 * enquiry handler id
*/
//$enqDocId

/*
 * searchParams
 * search parameters in the same form that the BDP system uses
 * see i.bdphq.com for more details.
*/
//$searchParams = '';

/**
 * Includes Path
*/
$incPath = 'assets/snippets/bdpweb/';

/**
 * sets the display to blank
*/
$display = '';

//Load BD Lite
define("incPath","");

//load standard bd functions & utilities
require_once($incPath . 'bdlite/class.universal.php');
require_once($incPath . 'bdlite/class.uni.php');
 
//load the BDP Rest Client
require_once($incPath .'/restclient/class.bdCoreRestClient.php');
require_once($incPath .'/restclient/class.gPropsRestClient.php');

//load the core web plugin
require_once($incPath .'/class.bdpWebPlugin.php');


//set the consurction map
$constructionMap = array(
	'modx',
	'detailPageId',
	'resPageId',
	'tpl',
	'outerTpl',
	'roomTpl',
	'rViewingTpl',
	'hrTpl',
	'detailImageData',
	'searchParams',
	'enqDocId',
	'sResTpl',
	'mode'
);

//run the utilities class
$plugin = new bdpWebPlugin();

//setupt the plugin
foreach($constructionMap as $key => $map){
	$plugin->$map = $$map;
}

//start the plugin
$display = $plugin->startUp();
 
//return the output
return $display;