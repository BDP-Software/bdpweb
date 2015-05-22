<?php
/**
* BDP Api Key
*/
$apiKey = 'API KEY HERE';
/**
* BDP Api Shared Secret
*/
$sharedSecret = 'SHARED SECRET HERE';
/**
* BDP Account Id
*/
$accId = 'ACC ID HERE';
/**
* page ids
*/
//detail page id
$detailPageId = 'DETAIL PAGE ID HERE';
$resPageIdSales = 'SALES SEARCH RESULTS PAGE ID HERE';
$resPageIdLettings = 'LETTINGS SEARCH RESULTS PAGE ID HERE';
/**
* ordring options
*/
$orderingOptions = array(
	array(
	'label'=>'Descending price',
	'value'=>'decprice'
	),
	array(
	'label'=>'Ascending price',
	'value'=>'ascprice'
	),
	array(
	'label'=>'Newest to Oldest',
	'value'=>'latest'
	),
);

/**
 * sale price options
*/
//$salePriceOptions = '0,50000,75000,100000,125000,150000,175000,200000,225000,250000,275000,300000,325000,350000,400000,450000,500000,600000,700000,800000,900000,1000000,1250000,1500000,2000000,2500000,3000000,3500000,4000000,4500000,5000000,6000000,7500000,10000000,15000000,20000000,30000000,40000000,50000000,75000000,100000000,150000000';

/**
 * sale price options
*/
//$letPriceOptions = '0,200,500,750,1000,1250,1500,2000,2500,3000,4000,5000,10000,20000';

//$maxSalePrice = 1490000;

//$maxLetPrice = 10000;

//set the property type options
/*
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
*/

/*
 * Max Bedrooms
*/
//$bedRoomsMax = 6;




/**
* Map Setup
*/
$defaultMapZoom = '17';
//default streetview zoom
$defaultStreetViewZoom = '1';
//default streetview heading
$defaultStreetViewHeading = '34';
//default streetview pitch
$defaultStreetViewPitch = '10';
//default map type
$defauldMapType = 'SATELLITE';
?>