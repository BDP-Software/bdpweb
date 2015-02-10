<?php
/**
 * BD Properties Statistics Rest Client
*/
class gSatPaymentsRestClient extends bdCoreRestClient{

///////////////////////////////////////////////////////////////////
///parameters//////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////
///variables //////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////


/**
 * constructor
*/
function __construct(){
	parent :: __construct();
}

/**
 * Gets general stats
*/
function getPaymentForm(){
	//run the query
	$output = false;

	try{
		$result = $this->api->get("satpaymentform/",array(),array('Content-Type'=>'application/json'));
		$output = json_decode($result->response,true);
		//unset($output['form']);var_dump($output);
		if($output['sId']){
			$_SESSION['bdSessId'] = $output['sId'];
			//echo 'Session Id: '. $_SESSION['bdSessId'];
		}
		
		//echo $result->response; exit;
		//var_dump($output);
	}
	catch(Exception $e){
		$this->logApiError($e);
	}
	//return the response
	return $output;
}

/**
 * posts the results of the payment form to the server
*/
function postPaymentForm(){
	//run the query
	$output = false;

	try{
		$result = $this->api->post("satpaymentform/",$_POST,array('Content-Type'=>'application/json'));
		$output = json_decode($result->response,true);
		if($output['sId']){
			$_SESSION['bdSessId'] = $output['sId'];
			//echo 'Session Id: '. $_SESSION['bdSessId'];
		}
		//var_dump($result);
		//echo $result->response; exit;
	}
	catch(Exception $e){
		$this->logApiError($e);
	}
	//return the response
	return $output;
}




}
?>