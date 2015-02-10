<?php
error_reporting(E_ALL & ~E_NOTICE);
/**
 * Universal - Universal class of php functions
 * @author Michael Barcroft
 * @copyright 2008 Michael Barcroft - Jet IB Ltd
 */
class Universal
{
/**
     * Retrievs results from the database - removes the need for using mysql statements
     * @param string $table table or tables accessed in the database, if multiple separate by commas as if part of a mysql statement
     * @param string $Columns comma separated string of the columns data is required from
     * @param string $FilterSearch filter command for the search - text to appear after the "where" in the mysql statement, i.e. name="michael"
     * @param string $sort column to sort the results by
     * @param string $order order the results will be returned in i.e. desc or asc
     * @version 2.1 - updated 06/01/09
     * @todo allow direct specification on which tables need to be active 
     * @return array array of results and number of rows - Numrows and SearchResults, search result is a numbered array, each row a mysql array of a specific return row from the database
     * @uses retrieving data from the database
     */
function RetrieveResults($table,$Columns='*',$FilterSearch='',$sort='',$order='',$groupBy=false)
{
//set the return
$return = false;

$tables=explode(",",$table);
if(!isset($this->dontCheckActive)){ $this->dontCheckActive = false;}

$Filter ="";
if($this->dontCheckActive != true)
	{
	$Filter = $tables[0] .".active='1'";
	}
if($FilterSearch != "")
	{
	$Filter .= ($Filter == "" ? $FilterSearch : " and ". $FilterSearch);
	}
if($Filter != "")
	{
	$Filter = "where ". $Filter;
	}
//check to see if there is a spare and at the end of the filter
$Filter = trim($Filter);
if(strtoupper(substr($Filter,-3)) == "AND")
	{
	$Filter = substr($Filter,0,-3);
	}
//--------------------------------------------------------------------------------
//connect to the database
$connect = $this->databaseConnect();

$orderBy = ((($sort!= '') or ($order != '')) ? ' ORDER BY ' : '');
//clean the filter
$Filter = $this->cleanFilter($Filter);
//put the final query together
$query="SELECT ". $Columns ." FROM ". $table ." ". $Filter . ($groupBy ? ' group by '. $groupBy .' ' : ''). $orderBy . $sort ." ". $order ."";

if($connect)
	{
	if($result=mysql_query($query))
		{
		$num=mysql_numrows($result);
		mysql_close();
		$i =0;
		while ($i < $num)
			{
			$ThisRow = mysql_fetch_assoc($result);
			$AllRows[$i] = $this->stripslashes_deep($ThisRow);
			$i++;
			}
		if ($num) 
			{
			$return = array("SearchResults"=>$AllRows,"Numrows" => $num);
			}
		//debug the query
		$this->debug($query);
		}
	else
		{
		//debug the query announcing an error
		$this->debug($query .'<br>Error: '. mysql_error(),false,true);
		//log the error
		$this->doLog('Retrieve Results',$table,'unknown',$query,0,mysql_error());
		}
	}
//output the return
return $return;
}

/*
 * cleans the search filter
 *@param $filter string the filter to clean
 *@return string the cleaned filter
*/
function cleanFilter($filter)
{
$output = preg_replace('/\s+/', ' ', $filter);
$output = str_replace('ANDAND','AND',$output);
$output = str_replace("AND AND",'AND',$output);
$output = str_replace("AND  AND",'AND',$output);
$output = str_replace('andand','and',$output);
$output = str_replace("and and",'and',$output);
$output = str_replace('and AND','and',$output);
$output = str_replace('AND and','and',$output);
return $output;
}

/**
 * Reduces a posted or get var to a simple string
 *@param $key string the post key to check
 *@param $isPost boolean when true grabs the var from the post, otherwise uses get
 *@return mixed cleaned string on success and false on failure
*/
function pSimpleString($key='',$isPost=true){
	$output = false;
	if($isPost){
		if(isset($_POST[$key])){
			$output = $this->cleanString($_POST[$key]);
		}
	}
	else{
		if(isset($_GET[$key])){
			$output = $this->cleanString($_GET[$key]);
		}
	}
	return $output;
}

/**
 *Removes all bad characters from a string
 *@param $str string The string to clean
 *@return string Cleaned string
*/
function cleanString($str){
	return preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", strip_tags($str));
}

/**
 * recursivly digs throuygh an array and cleans each entry - good for large posted arrays of data (api)
*/
function deepCleanString($value){
	//$value = is_array($value) ? array_map(array($this,'stripslashes_deep'), $value) : stripslashes($value);
	if(is_array($value)){
		$value = array_map(array($this,'deepCleanString'), $value);
	}
	else{
		$value = preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", strip_tags($value));
	}
	return $value;
}

/*
 * gets a single row from the database, assumes the given filter should return a single row
 * @param string $table table or tables accessed in the database, if multiple separate by commas as if part of a mysql statement
  * @param string $filter filter command for the search - text to appear after the "where" in the mysql statement, i.e. name="michael", default is ''
  * @param string $columns comma separated string of the columns data is required from, default is '*'
 * @return array the first row selected as an associative array, returns false if no array could be returned
*/
function retrieveRow($table,$filter='',$columns='*')
{
$return = false;
$results = $this->RetrieveResults($table,$columns,$filter);
if($results)
	{
	$return=$results['SearchResults'][0];
	}
return $return;
}

/**
     * Gets the path in the address bar and removes a specified variable
     * @param string $Variable the variable to be removed from the paths
     * @version 2.0 - updated 21/07/08
     * @todo bring into sink with equivalent function in numberSearch
     * @return string string of the current path without variable if specified
     * @uses getting the path from the 
     */
function GetFileAndPathExplodeSection($Variable=null)
{
$currentFile = $_SERVER["SCRIPT_NAME"];
$parts = Explode('/', $currentFile);
$currentFile = $parts[count($parts) - 1]; 
$currentPath = $_SERVER["QUERY_STRING"];
$currentPath2 = ($Variable != null ? explode($Variable, $currentPath) : array($currentPath));
$FilePath = $currentFile ."?". $currentPath2[0];

if ($currentPath2[0] != $currentPath)
	{
	$FilePath = substr($FilePath,0,strlen($FilePath)-1);
	}
return $FilePath;
}

/*
 * grabs the full current path and reutrns it as a string, html entiries are not returns, simple ampersand only
*/
function grabFullPath()
{
$pagePath = '';
//check if the system is running in front mode
if(mode == 'front')
	{
	global $gPagePath;
	$pagePath = $gPagePath;
	//check if the $_GET variable has more than just the manditory variables set
	foreach($_GET as $key => $value)
		{
		if($key != 'a')
			{
			$pagePath .= '&'. $key .'='. $value;
			}
		}
	}
else
	{
	$currentFile = $_SERVER["SCRIPT_NAME"];
	$parts = Explode('/', $currentFile);
	$currentFile = $parts[count($parts) - 1]; 
	$pagePath = $currentFile . ($_SERVER["QUERY_STRING"] ? '?'. $_SERVER["QUERY_STRING"] : '');
	}
return $pagePath;
}


/**
     * Gets the ip address of the current user
     * @version 2.0 - updated 21/07/08
     * @return string ip address of current user
     * @uses loggin the ip address when a suer logs into the system
     */
function getIp()
{
if (isset($_SERVER['HTTP_X_FORWARD_FOR'])) 
	{
	$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	} 
else 
	{
	$ip = $_SERVER['REMOTE_ADDR'];
	}
if($ip == "")
	{
	return "unknown";
	}
else
	{
	return $ip;
	}
}

/**
     * Re-orders an array - useful for multi-dimensional arrays
     * @param array $records the array that needs to be ordered
     * @param string $field the filed the array is to take its order from
     * @param string $reverse default is false, if true it will reverse the order for the chosen field
     * @version 2.0 - updated 21/07/08
     * @return  array  array in its new order
     * @uses reordering an array when displaying results
     */
function record_sort($records, $field, $reverse=false, $numerical = false)
{
$hash = array();
if(is_array($records))
	{
	foreach($records as $key => $record)
	    {
	    $hash[$record[$field] .".". $key] = $record;
	    }
	($reverse)? krsort($hash,($numerical ? SORT_NUMERIC : SORT_REGULAR)) : ksort($hash,($numerical ? SORT_NUMERIC : SORT_REGULAR));
	$records = array();
	foreach($hash as $record)
	    {
	    $records []= $record;
	    }   
	return $records;
	}
else
	{
	return false;
	}
}

/**
     * Removes duplicate entries in mutli-dimensional arrays
     * @param array $array the array in question
     * @param string $row_element the array key to create unique entries from
     * @version 2.0 - updated 21/07/08
     * @return array the array with duplicate entries removed
     * @uses it is sometimes useful to retrieve many results from a database and having used the array fro one purpose remove duplicates and use fro another - often used to avoid a second database query and hence keep server load down
     */
function remove_dups($array, $row_element)
{  
if(is_array($array))
	{
	$new_array[0] = $array[0];
	foreach ($array as $current) 
		{
	    $add_flag = 1;
	    foreach ($new_array as $tmp) 
			{
	        if ($current[$row_element]==$tmp[$row_element]) {
	                $add_flag = 0; break;
	            }
	        }
	        if ($add_flag) $new_array[] = $current;
	    }
	return $new_array;
	}
else
	{
	return false;
	}
} // end function remove_dups



/**
     * Removes elements of chosen value from 2d arrays
     * @param array $arr the array the element is to be removed from
     * @param string $val the value to be removed from the array
     * @version 2.0 - updated 21/07/08
     * @return array the array with the specified valuse removed
     * @uses useful filter, not often used
     */
function remove_element($arr, $val)
{
foreach ($arr as $key => $value)
	{
	if ($arr[$key] == $val)
		{
		unset($arr[$key]);
		}
	}
return $arr = array_values($arr);
}

/**
     * Shuffles the order of 2d arrays
     * @param array $array the array to be shuffled
     * @version 2.0 - updated 21/07/08
     * @return array the array in a shuffled order
     * @uses useful method to randomize results prior to printing them - used for tagcloud functions
     */
function shuffle_assoc($array) 
{
if (count($array)>1) 
	{ //$keys needs to be an array, no need to shuffle 1 item anyway
    $keys = array_rand($array, count($array));
	foreach($keys as $key)
		{
        $new[$key] = $array[$key];
		}
    $array = $new;
    }
return $array; //because it's a wannabe shuffle(), which returns true
} 

/**
     * Traces multidimnsional arrays into a list format for easyreading
     * @param array $theArray the array to be printed
     * @version 2.1 - updated 07/08/09
     * @return html html list outlining the contents of the array
     * @uses debugging - very useful method toseeing what really has populated an array
     */
function traceArray($theArray)
{
$display = "";
if(is_array($theArray))
	{
	$display .= "<ul>";
	foreach($theArray as $key => $value)
		{
		if(is_array($value))
			{
			$value = $this->traceArray($value);
			}
		$value = (gettype($value) == 'object' ? 'object' : $value);
		$display .= "<li>". $key ."=>". $value ."</li>";
		}
	$display .="</ul>";
	}
else
	{
	$display .= "no array given";
	}
return $display;
}

/* 
 * sets a session var to the system session holder, when posted from an ajax function
*/
function ajaxVar($varName=null,$varValue=null)
{
//set output to false by default
$output = false;
if($varName != null)
	{
	$output = $this->sessionVar($varName,$varValue);
	}
elseif(isset($_POST['setsessvar']))
	{
	//check if the var name and value is set
	if(isset($_POST['varname']))
		{
		if(isset($_POST['varvalue']))
			{
			$output = $this->sessionVar($_POST['varname'],$_POST['varvalue']);
			}
		else
			{
			$output = $this->sessionVar($_POST['varname']);
			}
		//in ajax mode so echo the output and exit
		echo $output;
		exit;
		}
	}
return $output;
}

/*
 * sets r retrieves a project session
 * all project session data should be contained inside the projectName session, this separates the session data from other projects on the same
*/
function sessionVar($varName=null,$varValue=null)
{
//grab the project details for the session name
$projDetails = $this->getCompanyInfo();
$sessHandler = str_replace(' ','',$projDetails['projectName']);
if($varValue === null)
	{}
else
	{
	//set the var
	$_SESSION[$sessHandler][$varName] = $varValue;
	}
//return the value of the session
return $_SESSION[$sessHandler][$varName];
}



/**
     * Gets the ajax pocket setting 9in it's very early stages!)
     * @version 1.0 - updated 12/08/08
     *@param $pocketId the id ref for the pocket in question
     * @return boolean true for the pocket to be open and false on failure
     * @uses find out whether an expandable pocket should be open or closed on page load - set by setPocket()
     *@todo create a separate ajax class, or pcket class to tore and build on pocket functions - look at creating ajax loaders for data
     */
function getPocket($pId,$pMod='')
{
$returnBool = false;
if(isset($_SESSION['ajaxPockets']))
	{
	$allPockets = $_SESSION['ajaxPockets'];
	$thePocket = $allPockets[$pMod][$pId];
	if($thePocket == "1")
		{
		$returnBool = true;
		}
	}
return $returnBool;
}

/**
     * truncatess the string to a certain amount of characters at the nearest word end
     * @version 1.0 - updated 12/08/08
     *@param $words the words to be truncated
     *@param $chars the nearest number of characters the string should be truncated to
     *@param $append string will be appended to the end of the truncayted string, the default is blank
     * @return string the truncated string of words
     * @uses good when creating smaple text or text appended with "more>" at the end
     */
function truncChars($words,$chars,$append='')
{
$newString = $words;
if(strlen($words) > $chars)
	{
	// take absolute truncate
	$trunc1 = substr($words,0,$chars);
	$lastSpace = strlen($words) - ((strlen($words) - strlen($trunc1)) + (strpos(strrev($trunc1)," ")));
	$newString = substr($words,0,$lastSpace) . $append;
	}
return $newString;
}

/**
     * similar to trunc chars, but instead of truncating to a set number of characters, it truncates to a set number of words
     * @version 1.0 - updated 12/08/08
     *@param $phrase string the paragraph or phrase to be truncated
     *@param $max_words the number of words the string should be truncated to
     * @return string the truncated string of words
     * @uses good when creating smaple text or text appended with "more>" at the end
     */
function trunc($phrase, $max_words)
{
$phrase_array = explode(' ',$phrase);
if(count($phrase_array) > $max_words && $max_words > 0)
	{
	$phrase = implode(' ',array_slice($phrase_array, 0, $max_words));
	return $phrase;
	}
else
	{
	return $phrase;
	}
}


/**
     * replaces possible bad characters with comments for php - useful for inputs to sql
     * @version 1.0 - updated 06/02/09
     *@param $theIn string containing characters that require changing
     * @return string corrected string containing "safe" characters
     * @uses useful for preparing strings prior to input into databases
     */
function replaceBad($theIn)
{
return str_replace("'","\'",$theIn);
}

/**
     * validates whether an email is a valid email address
     * @version 1.0 - updated 06/02/09
     *@param $email string the email to be checked
     * @return boolean true when teh email appears to be a correctly formatted email, false otherwise
     * @uses useful for form validation
     */
function isEmail($email=null){
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
}

//filters mulidimensional arrays
/**
     * filters a multi dimensional array for rows containing the specified key with the specified value, can either keep or remove the specified rows
     * @version 1.0 - updated 06/02/09
     *@param $array array the multidimensional array for filtering
     *@param $row_element string the key or the secondary array to search for
     *@param $value the value to search for int he secondary array
     *@param $keep boolean if true (default) rows containing the specified value will be kept, other wise they will be removed
     * @return array array containing filtered rows
     * @uses useful for complex aray filtering, usually when filtering results after performing a mysql search
     */
function arrayFilter($array,$row_element,$value,$keep=true)
{
$outArrTrue = array();
$outArrFalse = array();
if(is_array($array))
	{
	foreach($array as $key => $sub)
		{
		//echo "row element is: ". $sub[$row_element] .", the value is: ". $value ." and keep is: ". $keep .", size: ". $sub['size'] ."<br/>";
		if(strtoupper($sub[$row_element]) == strtoupper($value))
			{
			array_push($outArrTrue,$sub);
			}
		else
			{
			array_push($outArrFalse,$sub);
			}
		}
	}
if($keep)
	{
	return $outArrTrue;
	}
else
	{
	return $outArrFalse;
	}
}


/*check File
 *Checks the existense of a file, returns true if the ile exists and is over 11b in size
 * @param $path string the relative path to the file being checked
*/
function checkFile($path)
{
$returnBool = false;
$fileName = explode("/",$path);
if(file_exists($path) and $fileName[count($fileName)-1] != "")
	{
	if(filesize($path)>11)
		{
		$returnBool = true;
		}
	}
return $returnBool;
}

/*
 * headerRefresh
 * gets the current page and does a header locate to that page (quick way of refreshing the page after updating the database)
 * need to have a look - there might be a better ajax refresh??
 */
function headerRefresh()
{
//$this->debug("should relocate to: ". baseUrl . $this->grabFullPath(),true);
header('Location: '. baseUrl .(mode == 'admin' ? 'admin/' : '') . $this->grabFullPath());
}


/*
 * appends either a ? or a &amp; on a path depending on what's suitable
*/
function appendGet($path)
{
if(strpos($path,'?') === false)
	{
	$display = $path ."?";
	}
else
	{
	$display = $path ."&amp;";
	}
return $display;
}

/*
 * retrieves the basic site information, contacts email, address etc
 * @return array returns an array of the company informtion
*/
function getCompanyInfo()
{
if(!(is_array($this->companyInfo)))
	{
	$CompanyDetails = $this->RetrieveResults('mbd_companyinfo','*',"id = '1'",'id','asc');
	$this->companyInfo = $CompanyDetails['SearchResults'][0];
	}
return $this->companyInfo;
}



/*
 * doMySql
 * performs any mysql query - stay away from using this unless necassary - this will not contribute toi logs etc
 *@param $theQuery string the mysql query
  *@param $debug boolean set to false by default, otherwise will provide data on the success or failure of the mysql query
  *@param $doLog boolean set to true by default, when true calls the log function to put a log entry in the log table
  *@param $disconnect boolean set to true by default, when false, the disconnect command will not be called by default
 * returns the mySQL return on success and flase on failure
 *@updated 12/08/2009
*/
function doMySql($theQuery,$debug=false,$doLog=true,$disconnect=true)
{
//connect to the database
$this->databaseConnect();
//echo "the query: ". $theQuery ."<br/>";
if($result=mysql_query($theQuery))
	{
	$bool = $result;
	$this->debug($theQuery .", ". $result ." - success!",$debug);
	}
else
	{
	$this->debug('This mySql function has a problem: '. $theQuery .'<br>Error: '. mysql_error(),true);
	$bool = false;
	}
if($disconnect)
	{
	//dissconnect to the database
	$this->databaseDissconnect();
	}
return $bool;
}

/*
 * doLog
 * enters a log into the log table
 * @uses for loggin when entires in the database are created or changed - very useful for tracking anomolies in running sites
 * returns true on success and false on failure
*/
function doLog($logType="misc",$table="unknown",$entryId="unknown",$sqlUsed="unknown entry",$success=1,$failText=''){
	$doLog = true;
	if($doLog){
		//connect to the database
		$this->databaseConnect();
		if(mode == "admin")
			{
			$user_id = $_SESSION['adminUser']['user_id'];
			}
		//run a back trace to retrieve the calling function
		$backTrace = debug_backtrace();
		//$backTrace = $backTrace[$traceIndex];
		//parse the file

		//parse the previous file
		$prevFileData = explode('jetinc\\',$backTrace[1]['file']);
		$prevFileData = str_replace('\\','/',$prevFileData);
		$prevFile = $prevFileData[1];

		//put the debug valuse together
		//immediate function
		$function1['funcName'] = $backTrace[1]['function'];
		$function1['className'] = $backTrace[2]['class'];
		$function1['lineNo'] = $backTrace[1]['line'];
		$f1File = explode('jetinc\\',$backTrace[1]['file']);
		$f1File = str_replace('\\','/',$f1File);
		$function1['file'] = $f1File[1];

		//second function function
		$function2['funcName'] = $backTrace[2]['function'];
		$function2['className'] = $backTrace[3]['class'];
		$function2['lineNo'] = $backTrace[2]['line'];
		$f2File = explode('jetinc\\',$backTrace[2]['file']);
		$f2File = str_replace('\\','/',$f2File);
		$function2['file'] = $f2File[1];

		//third function function
		$function3['funcName'] = $backTrace[3]['function'];
		$function3['className'] = $backTrace[4]['class'];
		$function3['lineNo'] = $backTrace[3]['line'];
		$f3File = explode('jetinc\\',$backTrace[3]['file']);
		$f3File = str_replace('\\','/',$f3File);
		$function3['file'] = $f3File[1];

		$trace1 = $function1['funcName'] .' called on line no. '. $function1['lineNo'] .', class: '. $function1['className'] .', on file: '. $function1['file'];
		$trace2 = $function2['funcName'] .' called on line no. '. $function2['lineNo'] .', class: '. $function2['className'] .', on file: '. $function2['file'];
		$trace3 = $function3['funcName'] .' called on line no. '. $function3['lineNo'] .', class: '. $function3['className'] .', on file: '. $function3['file'];

		$columns = '`log_id`, 
					`logType`, 
					`table`, 
					`entry_id`, 
					`sqlUsed`, 
					`user_id`, 
					`userType`, 
					`userIP`,
					`success`, 
					`failText`,
					`trace1`,
					`trace2`,
					`trace3`,
					`datecreated`';
					
		$values = "NULL, 
				  '". $logType ."', 
				  '". $table ."', 
				  '". $entryId ."', 
				  '". addslashes($sqlUsed) ."', 
				  '". $user_id ."', 
				  '". $userType ."', 
				  '". $_SERVER["REMOTE_ADDR"] ."',
				  '". $success ."', 
				  '". addslashes($failText) ."',
				  '". $trace1 ."',
				  '". $trace2 ."',
				  '". $trace3 ."',
				  '". time() ."'";
				  
		//put the full query together
		$query = "INSERT INTO `mbd_logs` (". $columns .") VALUES (". $values .")";
		//echo $query ."<br/>";
		//exit;
		if(mysql_query($query))
			{
			$this->debug($query);
			$bool = mysql_insert_id();
			}
		else
			{
			//echo "There has been a problem with the update query: ". $query ."<br>Error: ". mysql_error() ."<br>";
			$this->debug($query .'<br>Error: '. mysql_error(),false,true);
			$bool = false;
			}
		return $bool;
	}

}


/* 
  * connects to the database
  *@param $theHost string the host to connect the database to, by default it's set to null and will use the value set in the config
  *@param $theDatabase string the database to connect to, if left blank uses the value set in the config
  *@param $theUser string the username used to connect to the database, if left blank uses the value set in the config
  *@param $thePassword string the password used to connect to the database, by default uses the value set in the config
*/
function databaseConnect($theHost=null,$theDatabase=null,$theUser=null,$thePassword=null)
{
//set variables if not useing defaults
$theHost = ($theHost == null ? theHost : $theHost);
$theDatabase = ($theDatabase == null ? theDatabase : $theDatabase);
$theUser = ($theUser == null ? theUser : $theUser);
$thePassword = ($thePassword == null ? thePassword : $thePassword);
$returnBool = false;
//connect to mysql
if(mysql_connect($theHost,$theUser,$thePassword)){
	mysql_set_charset('utf8'); 
	//select the database
	if(mysql_select_db($theDatabase)){
		$returnBool = true;
	}
	else{
		$this->debug('Bad config settings, cannot select database: '. $theDatabase,true);
	}
}
else{
	$this->debug('Bad config settings, cannot connect to host: '. $theHost,true);
}
return $returnBool;
}

/* 
  * dissconnects from the database
*/
function databaseDissconnect()
{
@mysql_close();
}

/* 
  * arranges multidimensional arrays to their keys
  *@param $array array the array to rearrange
  *@param $newKey string the key to arrange the new array by
  * @uses for arranging results by id key, useful when requiring linked results from multiple tables when also requireing unlinked results
*/
function arrangeByKey($array,$newKey)
{
$outArr = array();
if(is_array($array))
	{
	foreach($array as $value)
		{
		$outArr[$value[$newKey]] = $value;
		}
	}
return $outArr;
}

/* 
  * removes the slashes from an array
  *@param $value array the array to rearrange
  * @uses for removing all slahes put into a string - mainly for clean removal from a database and usually embedded in retrieveResults
*/
function stripslashes_deep($value)
{
//$value = is_array($value) ? array_map(array($this,'stripslashes_deep'), $value) : stripslashes($value);
if(is_array($value))
	{
	$value = array_map(array($this,'stripslashes_deep'), $value);
	}
else
	{
	$value = stripslashes($value);
	$value = str_replace(array(' & '),' &amp; ', $value);
	}
return $value;
}

/*
 * setParam
 * sets a class parameter
 *@param $name string the name of the class var
 *@param $value the value the paramter will be set to (usually the default value)
 *@param $overWrite boolean when set to truw the function will overwrite any preset value, otherwise the value will only be set when the value is undefined. Default is false
*/
function setParam($name,$value,$overWrite=false)
{
if($overWrite)
	{
	$this->$name = $value;
	}
else
	{
	$this->$name = (!isset($this->$name) ? $value : $this->$name);
	}
}

/**
     * Updates a row in the database
     * @param string $table the table containing the row to be updated
     * @param string $updatestuff section of the mysql query showing teh columns and their respective values (eg. foreName='michael', surName='barcroft')
     * @param string $identity the value of eh id reference for the row to be modified
     * @param string $identify the name of the id reference field (eg. client_id), sometimes set as a class variable by UpdateStart()
     * @version 2.0 - updated 17/08/08
     * @todo analysis of logging function - should be separate logging table - want to start llogging all database activity
     * @return boolean true on success and false on failure, also on success halts remaining process and outputs mysql failure code with the query for debugging
     * @uses updating rows in the database - removes the need for repetative mysql code
     */
function AdminReplace($table,$updatestuff,$identity,$identify=null,$debug=false)
{
if($identify != null)
	{
	$this->indentify = $identify;
	}
return $this->AdminReplace2($table,$updatestuff,$this->indentify ." = '". $identity ."'",$debug,$identity);
}

/**
     * Updates a row in the database - similar functions to AdminReplace, however it allows a more flexible filter for mass updates
     * @param string $table the table containing the row to be updated
     * @param string $updatestuff section of the mysql query showing teh columns and their respective values (eg. foreName='michael', surName='barcroft')
     * @param string $identity the value of eh id reference for the row to be modified
     * @param string $identify the name of the id reference field (eg. client_id), sometimes set as a class variable by UpdateStart()
     * @version 2.1 - updated 10/09/2008
     * @return boolean true on success and false on failure, also on success halts remaining process and outputs mysql failure code with the query for debugging
     * @uses updating rows in the database - removes the need for repetative mysql code
     */
function AdminReplace2($table,$updatestuff,$filter,$debug=false,$idVal="unknown")
{
//connect to the database
$this->databaseConnect();
$query = "UPDATE ". $table ." SET ". $updatestuff ." WHERE ". $filter;
//echo "Update query: ". $query ."<br>";
if(mysql_query($query))
	{
	$this->debug($query);
	//log the entry 
	$logType = ($updatestuff == "active = '0'" ? "delete" : "update");
	$this->doLog($logType,$table,$idVal,$query);
	return true;
	}
else
	{
	$this->debug($query .'<br>Error: '. mysql_error(),false,true);
	$this->doLog($logType,$table,$idVal,$query,0,addslashes(mysql_error()));
	return false;
	}
	
//dissconnect from the database
$this->databaseDissconnect();
}



/**
     * Creates a new row in the database
     * @param string $table the table containing the row to be created
     * @param string $columns comma separated string of the columns data is to be inserted into - must match the $values string or the mysql will fail (eg. `foreName`, `surName`)
     * @param string $values comma separated string of the values for the new row (eg. 'michael','barcroft')
     * @version 2.0 - updated 17/08/08
     * @todo analysis of logging function - should be separate logging table - want to start llogging all database activity
     * @return string the id value for the new value created, on failure it will output the mysql query and failure statement
     * @uses creating new rows in the database - removes the need for repetative mysql code
     */
function Insert($table,$columns,$values)
{
$query = "INSERT INTO `". $table ."` (". $columns .") VALUES (". $values .")";
//connect to the database
$connect = $this->databaseConnect();
if($connect)
	{
	if(mysql_query($query))
		{
		$LatestID = mysql_insert_id();
		$this->debug($query .'<br/>The latest id is: '. $LatestID);
		//log the entry 
		$this->doLog("insert",$table,$LatestID,$query);
		return $LatestID;
		}
	else
		{
		//echo 'Error: '. mysql_error() ,'<br/>';
		$this->debug($query .'<br>Error: '. mysql_error(),false,true);
		//log the entry 
		$this->doLog("insert",$table,$LatestID,$query,0,addslashes(mysql_error()));
		}
	}
//dissconnect from the database
$this->databaseDissconnect();
}

/*
 * forces a debug and echos teh debug to the screen, useful when a fatal erro has occurred
 *@param $dbText string the debuging text to appear ont he screen
 *@param $kill boolean when set to false the php will carry on as long as it can, otherwsie the php will cancel at the end of the function
*/
function fdebug($dbText,$kill=true)
{
//assign this debug script
$this->debug($dbText,true,false,2);
//grab the force debug header
$this->baseUrl = baseUrl;
$html = new htmlGrab('jetengine/lib/bddebug/view/force_header.html',$this,$this);
$display = $html->outputHtml; 
$display .= $this->outputDebug();
echo $display;
if($kill)
	{
	exit;
	}
}


/*
 * debugs an array and forces it to output
 *@param $arr array the array to trace
 *@param $notForce boolean when set to true, it forces the debug
*/
function dbArr($arr,$force=true)
{
if(($_SESSION['adminUser']['accessLevel'] == '3'))
	{
	$theArray = $this->traceArray($arr);
	$this->debug($theArray,$force,false,2,false);
	}
}

/*
 * adds a line to the debugging output
 *@param $dbText string the debuging text to appear ont he screen
 *@param $force boolean when set to true the debug text appears as blue and the debugging screen appears even when not requested
 *@param $error boolean when set to true the debug text appears as blue and the debugging screen appears even when not requested
 *@param $traceIndex int the level to which the backtrace should run, when debug called from a wrapper function, i.e. dbArr it should be set to 2 to show the funtion calling dbArr
*/
function debug($dbText,$force=false,$error=false,$traceIndex=1,$doEntities=true)
{
//set the session type
$this->checkSession = (mode == 'front' ? 'frontBdDebug' : 'cmsBdDebug');

$errorBool = ($_SESSION['adminUser']['accessLevel'] == '3' and ($force or $error or $_SESSION[$this->checkSession]));

//check that an admin user at developer level is logged in
if($errorBool )
	{
	//gather the global debug array
	global $bdDebug;
	$bdDebug['runErrors'] = (!isset($bdDebug['runErrors']) ? false : $bdDebug['runErrors']);
	$bdDebug['forceDebug'] = (!isset($bdDebug['forceDebug']) ? false : $bdDebug['forceDebug']);
	if($error)
		{
		$bdDebug['runErrors'] = true;
		}
	if($force or $error)
		{		
		$bdDebug['forceDebug'] = true;
		}

	$backTrace = debug_backtrace();
	
	//set the trace limit
	$limit = 10;
	//set teh trace details array
	$traceDetails = array();
	$limit = ($limit < count($backTrace) ? $limit : count($backTrace));
	//loop the backtrace
	for($i=$limit-1;$i>0;$i--){
		$subDetails = array();
		$subDetails['rowClass'] = ($i == '1' ? 'headrow' : 'minorrow');
		$subDetails['function'] = $backTrace[$i]['function'];
		$subDetails['className'] = $backTrace[$i]['class'];
		$subDetails['lineNo'] = $backTrace[$i-1]['line'];
		$subDetails['theFile'] = $backTrace[$i-1]['file'];
		$traceDetails[$i] = $subDetails;
	}

	
	//gather the executed time so far
	global $startTime;
	$execTime = microtime(true) - $startTime;
	$execTime = round($execTime,4);
	//create debug array
	$debugData = array("function"=>$backTrace[$traceIndex]['function'],
					   "text"=>($doEntities ? (!is_array($dbText) ? htmlentities($dbText): $dbText): $dbText),
					   "error"=>$error,
					   "force"=>$force,
					   "tree"=>$traceDetails,
					   "execTime"=>$execTime
					   );
	if(function_exists('memory_get_usage')){
		$debugData["memUsage"]=round(memory_get_usage(1)/1048576,2) .'mb';
	}
	//add to the array
	array_push($bdDebug,$debugData);
	}
}

/*
 * outputs the debug text
*/
function outputDebug(){
	//run minimise maximise check
	if(isset($_POST['debug_expand'])){
		$_SESSION['toggleExpand'] = (($_SESSION['toggleExpand']) ? false : true);
		echo ($_SESSION['toggleExpand'] ? 'minimise' : 'maximise');
		exit;
	}
	if(isset($_POST['debug_close'])){
		unset($_SESSION[$this->checkSession]);
		echo 'Debugging has been switched off';
		exit;
	}

	//set teh debug template
	$debugTemplate = 'jetengine/lib/bddebug/view/debug_row.html';
	$debugSubTemplate = 'jetengine/lib/bddebug/view/debug_sub_row.html';
	
	//set the session type
	$this->checkSession = (mode == 'front' ? 'frontBdDebug' : 'cmsBdDebug');
	
	//gather the global debug array
	global $bdDebug;
	$debugBool = (($_SESSION['adminUser']['accessLevel'] == '3') and ($bdDebug['forceDebug'] or $_SESSION[$this->checkSession]));
	
	if($debugBool){
		foreach($bdDebug as $key=>$dData){
			if(is_numeric($key)){
				$dData['treeDetails'] = '';
				//loop through the debug tree and output the rows
				foreach($dData['tree'] as $data){
					//parse the file
					$data['theFile'] = explode('jetinc\\',$data['theFile']);
					$data['theFile'] = str_replace('\\','/',$data['theFile'][1]);
					//integrate the sub row html
					$html = new htmlGrab($debugSubTemplate,$data);
					$dData['treeDetails'] .= $html->outputHtml;
				}			
				$html = new htmlGrab($debugTemplate,$dData);
				$this->dbOutput .= $html->outputHtml;
			}
		}
		$this->expandDebug = $_SESSION['toggleExpand'];
		$this->bdcss = (mode == 'front' ? baseUrl : '') . incPath .'jetengine/lib/bddebug/css/bddebug.css';
		$this->bdJava = (mode == 'front' ? baseUrl : '') . incPath .'jetengine/lib/bddebug/java/bddebug.js';
		$this->bdLogo = (mode == 'front' ? baseUrl : '') . incPath .'jetengine/lib/bddebug/images/logo.png';
		//grab the execution time - approx
		global $startTime;
		$execTime = microtime(true) - $startTime;
		$this->execTime = $this->getTimeTaken();
		//check that mem-usage exists - not in late version of php4
		if(function_exists('memory_get_usage')){
			$this->memUsage = round(memory_get_usage(true)/1024,2);
		}
		
		$html = new htmlGrab('jetengine/lib/bddebug/view/debugholder.html',$this,$this);
		$display = $html->outputHtml; 
		return $display;
	}
}

/**
 * returns the time in seconds 
*/
function getTimeTaken(){
	global $startTime;
	$execTime = microtime(true) - $startTime;
	return round($execTime,4);
}

/*
 * echos and outputs the content in as xml for parsing by javascript
 *@param $content html content to echo
 *@param $exit boolean default is true, will exit php when the function is complete
*/
function ajaxEcho($content='',$exit=true){
	$this->htmlOutput = ($content ? $content : $this->htmlOutput);
	$this->extraNodes = '';	
	//loop through the ajax nodes and create each node
	if(is_array($this->ajaxNodes)){
		foreach($this->ajaxNodes as $nodeName => $nodeValue){
			$this->extraNodes .= '<'. $nodeName .'><![CDATA['. $nodeValue .']]></'. $nodeName .'>';
		}
	}
	
	//set the debug output
	$this->debugString = $this->outputDebug();
	//integreate the html
	$html = new htmlGrab('jetengine/system_html/ajax_holder.xml',$this);
	//set the header
	header ("content-type: text/xml; charset=utf-8");
	//echo the output
	echo $html->outputHtml;
	//if exit is true, exit from php
	if($exit){
		exit;
	}
}

/**
 * echos a json output to the screen
*/
function jsonEcho($content='',$exit=true){
	$outputArr = array();
	//add any root level extra nodes
	if(is_array($this->ajaxNodes)){
		$outputArr = $this->ajaxNodes;
	}
	//set the debug node
	$outputArr['debugOutput'] = $this->outputDebug();
	
	$content = str_replace(array("\r","\n","\t"),'',$content);
	$content = preg_replace( '/\s+/', ' ', $content);
	$content = preg_replace( "/\n/", '', $content);
	
	//set the provided display
	$outputArr['output'] = $content;
	
	//set the output
	$output = json_encode($outputArr);
	//check if running in jsonP mode
	$output = ($this->jsonP ? 'var '. $this->jsonP .' = '. $output .';' : $output);
	
	
	header('Cache-Control: no-cache, must-revalidate');
	header('content-type: application/json; charset=utf-8');
	echo $output;
	exit;
}


/*
 * handles non fatal errors
*/
function doErrors($errno, $errstr, $errfile, $errline)
{
$errorTypes = array('2'=>'E_WARNING',
					'8'=>'E_NOTICE',
					'256'=>'E_USER_ERROR',
					'512'=>'E_USER_WARNING',
					'1024'=>'E_USER_NOTICE',
					'4096'=>'E_RECOVERABLE_ERROR',
					'8191'=>'E_ALL');
if(array_key_exists($errno,$errorTypes))
	{
	$errorType = $errorTypes[$errno];
	}
else
	{
	$errorType = 'Unknown Error: '. $errno;
	}
$error = $errorType .'<br/>'. $errstr .'<br/>file: '. $errfile .' on line '. $errline;
//echo $error .'<br/><br/>';
//call the error function
//$this->debug($error);
//return true to block the server error handler
return true;
}

/*
 * generates a password
 @param $length integer the length of the generated password
 @param $strength integer the higher the number the more complex the generated password, for just numbers and letters (acase sensitive) suggest using 4 - default
*/
function generatePassword($length=9, $strength=4) 
{
$vowels = 'aeuy';
$consonants = 'bdghjmnpqrstvz';
if ($strength & 1) 
	{
	$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
if ($strength & 2) 
	{
	$vowels .= "AEUY";
	}
if ($strength & 4) 
	{
	$consonants .= '23456789';
	}
if ($strength & 8) 
	{
	$consonants .= '@#$%';
	}
 $password = '';
$alt = time() % 2;
for ($i = 0; $i < $length; $i++) 
	{
	if ($alt == 1) 
		{
		$password .= $consonants[(rand() % strlen($consonants))];
		$alt = 0;
		} 
	else 
		{
		$password .= $vowels[(rand() % strlen($vowels))];
		$alt = 1;
		}
	}
return $password;
}


/*
 * makes sure a date is a timestamp - useful when reformatting dates
 *@param $date string date of any type
 *@return unix timestamp
*/
function dateStamp($date='')
{
if(is_numeric($date))
	{
	$output = $date;
	}
else
	{
	$output = strtotime($date);
	}
return $output;
}


/*
 * copies a full folder and its contents
 *@param $source string The path to the source folder
 *@param $target string The path to the target folder
 *@param $replace boolean when set to true existing files will be replaces, otherwise files will not overwrite
*/
function fullCopy($source,$target,$replace=true)
{
if ( is_dir( $source ) )
	{	
    @mkdir( $target );
    $d = dir( $source );
    while ( FALSE !== ( $entry = $d->read() ) )
       {
	   //check that the file is a valie file and not an svn folder!
       if ( $entry == '.' || $entry == '..' || $entry == '.svn' )
           {
           continue;
           }
		$Entry = $source . '/' . $entry;          
		if (is_dir($Entry))
			{
			$this->fullCopy( $Entry, $target . '/' . $entry,$replace);
			continue;
			}
		//if replace is false, check the file doesn't exist before creating
		if(!$replace)
			{
			$doFile = !($this->checkFile($target . '/' . $entry));
			}
		else
			{
			$doFile = true;
			}
		if($doFile)
			{
			copy( $Entry, $target . '/' . $entry );
			}
		}
    $d->close();
    }
else
    {
    copy( $source, $target );
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// creates very simple tags and elements for html
//////////////////////////////////////////////////////////////////////////////////////////////////////////////



/*
 * outputs a simple list tag
 * @param $content string html contents for the tag
*/
function liTag($content='',$id='',$class='')
{
return '<li'. ($id ? ' id="'. $id .'"' : '') . ($class ? ' class="'. $class .'"' : '') .'>'. $content .'</li>';
}

/*
 * outputs a simple ul tag
 * @param $content string html contents for the tag
 * @param $id string optional id attribute for the ul tag
 * @param $class string optional class attribute for the ul tag
*/
function ulTag($content='',$id='',$class='')
{
return '<ul'. ($id ? ' id="'. $id .'"' : '') . ($class ? ' class="'. $class .'"' : '') .'>'. $content .'</ul>';
}

/*
 * outputs a simple a tag
 * @param $content string html contents for the tag
 * @param $id string html id for the tag, if left blank, the id will not appear
 * @param $title string html title for the tag, if left blank, the title tag will not appear
 * @param $href string html href for the tag, if left blank the tag will not appear
 * @param $class string html class for the tag, if left blank the tag will not appear
 * @param $target string html target for the tag, if left blank the tag will not appear
*/
function aTag($content='',$href='',$id='',$title='',$target='',$class='')
{
return '<a'. ($id ? ' id="'. $id .'"' : '') . ($title ? ' title="'. $title .'"' : '') . ($class ? ' class="'. $class .'"' : '') . ($href ? ' href="'. $href .'"' : '') . ($target ? ' target="'. $target .'"' : '') .'>'. $content .'</a>';
}

/*
 * outputs a simple img tag
 * @param $src string html source path for the image
 * @param $alt string html alt tag for the image, if left blank the tag will not appear, however it is strongly recommended that he tag be used
 * @param $id string html id for the tag, if left blank, the id will not appear
*/
function imgTag($src='',$alt='',$id='',$class='')
{
return '<img src="'. $src .'" '. ($alt != '' ? ' alt="'. $alt .'"' : '') . ($id ? ' id="'. $id .'"' : '') . ($class ? ' class="'. $class .'"' : '') .'/>';
}


/*
 * outputs a simple div tag
 * @param $content string html contents for the tag
 * @param $id string optional id for the tag
 * @param $class string optional class for the tag
*/
function divTag($content='',$id='',$class='')
{
return '<div'. ($id ? ' id="'. $id .'"' : '') . ($class ? ' class="'. $class .'"' : '') .'>'. $content .'</div>';
}

/**
 * outputs a hidden input field
 * @param $value string field value
 * @param $name string name and id of the input tag
*/
function hiddenInputField($name='',$value=''){
	return '<input type="hidden" value="'. $value .'" name="'. $name .'" id="'. $name .'"/>';
}

/**
 * outputs a text area tag
 * @param $value string field value
 * @param $name string name and id of the input tag
*/
function textAreaTag($name='',$value='',$class=''){
	return '<textarea name="'. $name .'" id="'. $name .'" '. ($class ? 'class="'. $class .'" ' : '') .'>'. $value .'</textarea>';
}

/*
 * removes a section of the query string
*/
function remQStringVar($key,$url='',$forceAmpersand=false){
	if($url == ''){
		$parts = explode('/', $_SERVER["SCRIPT_NAME"]);
		$url = $parts[count($parts) - 1] . ($_SERVER["QUERY_STRING"] ? '?'. $_SERVER["QUERY_STRING"] : '');
	}
	$url = str_replace('&'. $key .'='. $_GET[$key],'',$url);
	$url = str_replace('?'. $key .'='. $_GET[$key],'',$url);
	return $url;
}

/*
 * adds a value to the query string
 * @param $key string The key or name of the variable to update
 * @param $value string The value of the variable
 * @param $url string The url to manipulate, if left blank it will manipulate the current query string
 * @param $forceAmpersand boolean when true forces the get var to use an ampersand - required for the front end path 
*/
function addQStringVar($key, $value, $url='',$forceAmpersand=false)
{
if($url == '')
	{
	//$parts = explode('/', $_SERVER["SCRIPT_NAME"]);
	//$url = $parts[count($parts) - 1] . ($_SERVER["QUERY_STRING"] ? '?'. $_SERVER["QUERY_STRING"] : ''); 
	$sourcePath = str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER['PHP_SELF']);
	$url = ($sourcePath != '/' ? str_replace($sourcePath,'',$_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI']);
	//$this->debug(str_replace(str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER['PHP_SELF']),'',$_SERVER['REQUEST_URI']),true);
	//$this->dbArr($_SERVER);
	//$this->debug($url,true);
	}
$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&'); 
$url = substr($url, 0, -1); 

if ((strpos($url, '?') === false) and (strpos($url, '&') === false) and !$forceAmpersand and !$this->forceAmpersand)
	{
	return ($url . '?' . $key . '=' . $value); 
	} 
else 
	{ 
	return ($url . '&' . $key . '=' . $value); 
	} 
 
}

/*
 * old name for addQStringVar
*/
function add_querystring_var($key, $value, $url='') 
{
return $this->addQStringVar($key, $value, $url);
}

/*
 * outputs a json ( javascript object) string - very useful for ajax applications
*/
function outputJson($arr)
{
$innerOutputString = '';
//loop through the full array and grab the items
if(is_array($arr)){
	foreach($arr as $item){
		$itemOutput = '';
		//loop through the times grabbing the details
		foreach($item as $key=>$value){
			$itemOutput .='"'. $key .'":"'. $value .'",';
		}
		//trim the item output - removig the final and uneccassary comma
		$itemOutput = substr($itemOutput,0,-1);
		//add to the full inner output string
		$innerOutputString  .= '{'. $itemOutput .'},';
	}
	//remove the final uneccassary comma from the inner output string
	$innerOutputString = substr($innerOutputString,0,-1);
	//wrap the inner output string and return the full string
	$output = '['. $innerOutputString .']';
}
//return the output
return $output;
}

/**
 * stores a string as a cached file
*/
function storeCache($cacheName='cach',$str='',$min=true,$cacheFolder='cachebin/'){
	if(!isset($_SESSION['bdpccontrol'])){
		//run folder logic
		$cacheFolder = (mode == 'admin' ? '../'. $cacheFolder : $cacheFolder);
		//run filename logic - add the default .html extension to the file if no extension is set
		$cacheName = ((strpos($cacheName,'.') === false) ? $cacheName .'.html' : $cacheName);
		
		//check if the cache folder exists, if not create it
		if(!is_dir($cacheFolder)){
			mkdir($cacheFolder,'777');
		}
		//open or create the file
		$newHt = fopen($cacheFolder . $cacheName, "w+");
		if($newHt){
			//if set to minimise the cache file, remove all line breacks and spaces
			if($min){
				$str = str_replace(array("\r","\n"),'',$str);
				$str = preg_replace( '/\s+/', ' ', $str);
			}
			
			//delete present contents of the file
			fwrite($newHt,'');
			//write the contents to the file
			fwrite($newHt,$str);
			//close the file
			fclose($newHt);
		}
		else{
			$this->debug("unable to cache to file: ". $cacheFolder . $cacheName,true);
		}
	}
}

/**
 * gets the cache for a particular name
 *@param cacheName string name of the cached file (no extension required)
 *@param $folder string path to the cache folder relative to the website root
 *@param $age int maximum age of the file in seconds, when false (default) this parameter is not used
 *@param $modDate int should be a unix timestamp, if the fileis younger than the timestamp it will be used, default is false
*/
function getCache($cacheName='',$folder='cachebin/',$age=false,$modDate=false){
	if(!isset($_SESSION['bdpccontrol'])){
		//run folder logic
		$folder = (mode == 'admin' ? '../'. $folder : $folder);
		//run filename logic - add the default .html extension to the file if no extension is set
		$cacheName = (strpos($cacheName,'.') === false ? $cacheName .'.html' : $cacheName);
		//set the contents to false by default
		$contents = false;
		$fPath = $folder . $cacheName;
		if(file_exists($fPath)){
			//check that the file is not too old (if required to
			$runFile = true;
			$runFile = ($age ? (filemtime($fPath) > (time()-$age)) : $runFile);
			$runFile = ($modDate ? (filemtime($fPath) > $modDate) : $runFile);
			if($runFile){
				$handle = @fopen($fPath, "r");
				if($handle){
					$contents = fread($handle, filesize($fPath));
					fclose($handle);
				}
				else{
					$this->debug("File '". $fPath ."' not found.");
				}
			}
			else{
				//file is too old
			}
		}
	}
	//$this->debug( $contents,true);
	return $contents;
}

/**
 * converts an associative array to a query string
 *@param $arr array 2d associative array of values for the query string
 *@return querty string, not using html entities
*/
function arrQStr($arr=false){
	$output = '';
	if(is_array($arr)){
		foreach($arr as $key => $value){
			$output .= $key .'='. $value .'&';
		}
		$output = substr($output,0,-1);
	}
	return $output;
}

/**
 * converts a query string to an associative array
 * @param $qstr string query string based string of variables
 * @return array associative array of values
*/
function qStrArr($qstr=''){
	$outputArr = array();
	$arr = explode('?',$qstr);
	$qstr = $arr[count($arr)-1];
	$arr = explode('&',$qstr);
	foreach($arr as $param){
		$paramArr = explode('=',$param);
		if($paramArr[0]){
			$outputArr[$paramArr[0]] = $paramArr[1];
		}
	}
	return $outputArr;
}

/**
 * finds email addresses and urls in a string and replaces them with links
*/
function findLinks($text,$blankPage=true) {

	$email_pattern = '/(\S+@\S+\.\S+)/i';
	$url_pattern = "/((http|https|ftp|sftp):\/\/)[a-z0-9\-\._]+\/?[a-z0-9_\.\-\?\+\/~=&#;,]*[a-z0-9\/]{1}/si";
	$www_pattern = "/[^>](www)[a-z0-9\-\._]+\/?[a-z0-9_\.\-\?\+\/~=&#;,]*[a-z0-9\/]{1}/si";

	// First, check if the string contains an email address...
	if( preg_match( $email_pattern, $text, $email ) ) {
		$replacement = "<a href='mailto:$1'>$1</a> ";
		$text = preg_replace($email_pattern, $replacement, $text);
	}
	// Next, check if the string contains a URL beginning with http://, https://, ftp://, or sftp://
	if( preg_match( $url_pattern, $text, $url ) ) {
		$replacement = '<a '. ($blankPage ? ' target="_blank" ' : '') .' href="' . $url[0] . '">' . $url[0] . '</a>';
		$text = preg_replace($url_pattern, $replacement, $text);
	}
	// Last, check for a plain old www address (without a closing HTML tag before them)
	if( preg_match( $www_pattern, $text, $www ) ) {
		$replacement = ' <a '. ($blankPage ? ' target="_blank" ' : '') .' href="http://' . ltrim( $www[0] ). '">' . ltrim( $www[0] ) . '</a> ';
		$text = preg_replace($www_pattern, $replacement, $text);
	}

	return $text;

}

/**
 * returns the extension of a given filename
 *@param $filename string filename
 *@return string file extension in lower case
*/
function getExt($filename=''){
	$extData = explode('.',$filename);	
	$ext = trim(strtolower($extData[count($extData)-1]));
	
	return $ext;
}

/**
 * grabs a float or integer from a string and returns as a float
*/
function floatStr($str, $set=FALSE){
	return floatval($str);

}

/**
 * configures an object based on the arguments passed
 * by default only processes keys that have already been set
 *@param $cfg mixed object or array
 *@return void;
*/
function configPrep($cfg=false){
	foreach($cfg as $key => $val){
		if (array_key_exists($key, $this)){
			$this->$key=$val;
		}
	}
}

/**
 * logs a statement to a given file path
 *@param $filePath string when set logs the statement to a file at the given path, defaults to $this->logPath
 *@param $txt string text to log to the log file
*/
function fileLog($txt='',$filePath=''){
	//set the file path
	$file = ($filePath ? $filePath : $this->logPath);
	
	//add a line brealk to the text string
	$txt .= "\r\n";
	// Append if the fila already exists...
	if(file_exists($file)){
		$success = file_put_contents($file,  $txt, FILE_APPEND);
		// Note: use LOCK_EX if an exclusive lock is needed.
		// file_put_contents($file,  $txt, FILE_APPEND | LOCK_EX);
	}
	else{
		$success = file_put_contents($file, $txt);
	}
}


/**
 * logs a report and sends a debug out
*/
function logBug($txt=''){
	$this->logReport($txt);
	$this->debug($txt,true);
}

/**
 * logs and runs a report - mainly for scheduled tasks
*/
function logReport($txt=''){
	global $logReport;
	if(!$logReport){
		$logReport = array();
	}	
	$logReport[] = $txt;
	global $fileLog;
	if($fileLog){
		$this->fileLog($txt,$fileLog);
	}
}

/**
 * logs an array to the output
*/
function logArray($data){
	foreach($data as $key => $val){
		$this->logReport($key .' :: '. $val);
	}
}

/**
 * runs a report - mainly for scheduled tasks
*/
function runReport($plainText=true,$doHeader=true){
	global $logReport;
	$output = '';
	foreach($logReport as $report){
		$output .= $report . ($plainText ? "\n" : '<br/>');
	}
	if($doHeader){
		if($plainText){
			header('Content-Type: text/plain');
		}
		echo $output;
		exit;
	}
	return $output;
}

}
?>