<?php

/**
 * htmlGrab - class for grabbing html entities from the system and integrating them with php
 * @author Michael Barcroft
 * @copyright 2009 Michael Barcroft - MB Digital
 */
 
class htmlGrab extends uni
{
/*
 * constructor, runs the class operations
 * @param $path string, path to tht html file from the jetinc folder
 * @param $vars mixed, either an object or array of values
 * @param $useString boolean, when set to true the $path will be treated as an html string
*/
function htmlGrab($path='templates/index.html',$vars=null,$funcProvider=null,$useString=false,$clearXml=false){
	//store all templates in here for later use
	global $templatesCache;
	$this->clearXml = $clearXml;
	
	
	if(is_object($vars)){
		//echo "vars is an object<br/>";
		$this->vars = $vars;
	}
	elseif(is_array($vars)){
		foreach($vars as $key => $value){
			if($key != ''){
				$this->vars->$key = $value;
			}
		}
	}
	//set input vars to class vars
	$this->funcProvider = $funcProvider;
	if($useString){
		$fContents = $path;
	}
	elseif(is_numeric($path)){
		global $cSnippets;
		if(!is_array($cSnippets)){
			$cSnippets = array();
			$contentSnippets = $this->RetrieveResults('mbd_csnippets');
			if($contentSnippets){
				foreach($contentSnippets['SearchResults'] as $snippet){
					$cSnippets[$snippet['snippet_id']] = $snippet['scontent'];
				}
			}
		}
		if(isset($cSnippets[$path])){
			$fContents = $cSnippets[$path];
		}
		else{
			$this->debug('content snippet id '. $path .' could not be found.',true);
		}
	}
	else{
		//merge with the html
		if(!isset($templatesCache[incPath . $path])){
			$fileContents = file_get_contents(incPath . $path);
			$templatesCache[incPath . $path] = $fileContents;
		}
		else{
			$fileContents = $templatesCache[incPath . $path];
		}
		if($fileContents){
			$fContents = $fileContents;
		}
		else{
			$this->debug('unable to load: '. incPath . $path,true);
			//echo 'unable to load: '. incPath . $path .'<br/>';
			$this->errorMsg = 'unable to load: '. incPath . $path;
		}
	}
	if($fContents){
		
		$fContents = preg_replace_callback("/(\<\<.*?\>\>)/s",array($this, 'doTranslate'),$fContents);
		
		if($clearXml){
			$this->outputHtml = preg_replace_callback("/(\{.*?\})/s",array($this, 'doValues'),$fContents);
		}
		else{
			$this->outputHtml = preg_replace_callback("/(\{\{.*?\}\})/s",array($this, 'doValues'),$fContents);
		}
	}
}

/*
 * gets the values for the template
*/
function doTranslate($matches){
	
	//grab the text from the match
	$cLength = 2;
	$text = trim(substr(substr($matches[0], $cLength),0, -1 * $cLength));
	//run a translate match on the text
	$text = $this->tText($text);
	return $text;
}

/*
 * gets the values for the template
*/
function doValues($matches){
	$cLength = ($this->clearXml ? 1 : 2);
	//remove the curly brackets from the statement
	$value = trim(substr(substr($matches[0], $cLength),0, -1 * $cLength));
	//set the value to a class var
	$this->value = $value;
	if($this->clearXml){
		//echo "\n" .$matches[0] ."\n";
		$value = strip_tags($value);
		$this->value = $value;
		
	}
	//explode the value to check for a function name
	$theFuncs = explode(" ",$value);
	$theFunc = $theFuncs[0];
	
	//switch for various boolean
	switch(true){
		case (substr($value,-1) == ")"):{
			$value = substr($value,0,-1);
			$funcData = explode('(',$value);
			$funcName = $funcData[0];
			$param = str_replace("'",'',$funcData[1]);
			if(method_exists($this->funcProvider,$funcName)){
				if($param == ''){
					return $this->funcProvider->$funcName();
				}
				else{
					$params = explode(',',$param);
					switch(count($params)){
						case 1:{
							return $this->funcProvider->$funcName($params[0]);
						}
						break;
						case 2:{
							return $this->funcProvider->$funcName($params[0],$params[1]);
						}
						break;
						case 3:{
							return $this->funcProvider->$funcName($params[0],$params[1],$params[2]);
						}
						break;
						case 4:{
							return $this->funcProvider->$funcName($params[0],$params[1],$params[2],$params[3]);
						}
						break;
						case 5:{
							return $this->funcProvider->$funcName($params[0],$params[1],$params[2],$params[3],$params[4]);
						}
						break;
						case 6:{
							return $this->funcProvider->$funcName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
						}
						break;
					}
				}
			}
			else{
				$this->debug('Function '. $funcName .' could not be found.',true);
			}
		}
		break;
		case (method_exists($this,$theFunc)):{
			$reverseBool = (substr($theFuncs[1],0,1) != '!');
			return $this->$theFunc($reverseBool);
		}
		default:{
			//return a straight forward  variable
			return $this->doValue($value);
		}
		break;
	}
} 



/*
 * parses the function strings for using the appropriate functions
 * also parses the output string contaiing the mebedded [[]] values
 * modified to handle single function parameters - Michael Barcroft 28/05/09
*/
function parseFunc()
{
$bool = explode("(",$this->value);
$bool = explode(")",$bool[1]);
$bool = trim($bool[0]);
$output = strpos($this->value,"?")+1;
$output = trim(substr($this->value,$output));

//parse the utput string for the [[ ]] embedded values
$output = preg_replace_callback("/(\[\[.*?\]\])/s",array($this, 'doValues'),$output);

return array("bool"=>$bool,"output"=>$output);
} 
 

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// mbD Boolean functions go here 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 * mbDBool - allows very simple boolean to be used within the html templates, to be avoided but sometimes useful
*/
function mbDBool($straight = true)
{
$theData = $this->parseFunc();
//echo "bool is: '". $this->vars->$bool ."', the output is: ". $output ."<br/>";
//set the bool to the opposite if the revers is selected
$theBool = ($straight ? ($this->doValue($theData['bool'])) : (!($this->doValue($theData['bool']))));
if($theBool)
	{
	$display = $theData['output'];
	}
else
	{
	$display ='';
	}
return $display;
}

/**
 * formats the date
 * @param $date int unix timestamp
*/
function bdDate($date){
	$output='';
	$theData = $this->parseFunc();
	$params = str_getcsv($theData['bool']);
	$dateSkew = ($params[2] ? $params[2] : 0);
	$dDate = $this->doValue($params[0]);
	$dFormat = trim($params[1], '"');  
	if($dDate){
		$output = date($dFormat,$dDate+$dateSkew);
	}
	return $output;
}

/* 
 * mbDString -  checks for the bool value to be a string of length, usefule for checking whether a value exists before attempting to output it
*/
function mbDString()
{
//echo "mbDString is running:<br/>";
$theData = $this->parseFunc();
//echo "chekcing the bool: ". $this->vars->$theData['bool'] ." has length<br/>";
if($this->vars->$theData['bool'] != '')
	{
	$display = $theData['output'];
	}
else
	{
	$display ='';
	}
return $display;
}

/*
 * returns a straight forward value for the value container
*/
function doValue($value){
	//atempt to explode the value, if a "." exists, drill down the inner values
	$valArray = explode('.',$value);
	if(count($valArray) > 1){
		$runningVal = $this->vars;
		foreach($valArray as $val){
			if(is_array($runningVal)){
				$runningVal = $runningVal[$val];
			}
			else{
				$runningVal = $runningVal->$val;
			}
		}
		$display = $runningVal;
	}
	else{
		if($this->clearXml){
			//echo $value ."\n";
			$display = strip_tags(str_replace('><','> <',$this->xmlEntities($this->vars->$value)));
		}
		else{
			$display = $this->vars->$value;
		}
	}
	return $display;
}

/**
 * convert html entities to xml entities
 * @param $str string input string containing html entities
 * @return string with xml friendly utf8 entities
*/
private function xmlEntities($str){
    $xml = array('&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;','&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;','&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;','&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;','&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;','&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;','&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;','&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;','&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;','&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;','&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;','&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;','&#252;','&#253;','&#254;','&#255;','&#8217;','&#145;');
    $html = array('&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;','&cent;','&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;','&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;','&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;','&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;','&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;','&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;','&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;','&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;','&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;','&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;','&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;','&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;','&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;','&rsquo;','&lsquo;');
    $str = str_replace($html,$xml,$str);
    $str = str_ireplace($html,$xml,$str);
    return $str;
} 

/*
 * get value if array
*/
function arrayVsalue($value)
{
$valArray = explode('.',$value);
if(count($valArray) > 1)
	{
	//check the inner value
	if($this->vars->$valArray[0])
		{
		$innerVal = $this->vars->$valArray[0];
		
		//check the inner value exists
		if(is_array($innerVal))
			{
			
			$display = $innerVal[$valArray[1]];
			}
		else
			{
			$display = $innerVal->$valArray[1];
			}
		}
	}
else
	{
	$display = $this->vars->$value;
	}
}


}
 ?>