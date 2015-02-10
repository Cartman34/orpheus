<?php
/**
 * @brief The core functions
 * 
 * PHP File containing all system functions.
 */

/** Redirects the client to a destination by HTTP
 * @param $destination The destination to go. Default value is SCRIPT_NAME.
 * @sa permanentRedirectTo()

 * Redirects the client to a $destination using HTTP headers.
 * Stops the running script.
*/
function redirectTo($destination=null) {
	if( !isset($destination) ) {
		$destination = $_SERVER['SCRIPT_NAME'];
	}
	header('Location: '.$destination);
	die();
}

/** Redirects permanently the client to a destination by HTTP
 * @param $destination The destination to go. Default value is SCRIPT_NAME.
 * @sa redirectTo()

 * Redirects permanently the client to a $destination using the HTTP headers.
 * The only difference with redirectTo() is the status code sent to the client.
*/
function permanentRedirectTo($destination=null) {
	header('HTTP/1.1 301 Moved Permanently', true, 301);
// 	header('HTTP/1.1 301 Moved Permanently', false, 301);
	redirectTo($destination);
}

/** Redirects the client to a destination by HTML
 * @param $destination The destination to go.
 * @param $time The time in seconds to wait before refresh.
 * @param $die True to stop the script.

 * Redirects the client to a $destination using the HTML meta tag.
 * Does not stop the running script, it only displays.
*/
function htmlRedirectTo($destination, $time=3, $die=0) {
	echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"{$time} ; URL={$destination}\">";
	if( $die ) {
		exit();
	}
}

/** Displays a variable as HTML
 * @param $message The data to display. Default value is an empty string.
 * @param $html True to add html tags. Default value is True.
 * @warning Use it only for debugs.

 * Displays a variable as HTML.
 * If the constant TERMINAL is defined, parameter $html is forced to False.
*/
function text($message = '', $html = true) {
	if( defined("TERMINAL") ) {
		$html = false;
	}
	if( !is_scalar($message) ) {
		$message = print_r($message, 1);
		if( $html ) {
			$message = '<pre>'.$message.'</pre>';
		}
	}
	echo $message.(($html) ? '<br />' : '')."\n";
}

/** Do a binary test
 * @param $value The value to compare.
 * @param $reference The reference for the comparison.
 * @return True if $value is binary included in $reference.

 * Do a binary test, compare $value with $reference.
 * This function is very useful to do binary comparison for rights and inclusion in a value.
*/
function bintest($value, $reference) {
	return ( ($value & $reference) == $reference);
}

/** Sends a packaged response to the client.
 * @param $code string The response code.
 * @param $other array Other data to send to the client. Default value is an empty string.
 * @param $domain string The translation domain. Default value is 'global'.
 * @param $desc string The alternative description code. Default value is $code.

 * The response code is a status code, commonly a string.
 * User $Other to send arrays and objects to the client.
 * The packaged reponse is a json string that very useful for AJAX request.
 * This function stops the running script.
*/
function sendResponse($code, $other='', $domain='global', $desc=null) {
	header('Content-Type',	'application/json; charset=UTF-8');
	die( json_encode( array(
			'code'			=> $code,
			'description'	=> t($desc ? $desc : $code, $domain),
			'other'			=> $other
	) ) );
}

/** Runs a SSH2 command.
 * @param $command The command to execute.
 * @param $SSH2S Local settings for the connection.
 * @return The stream from ssh2_exec()

 * Runs a command on a SSH2 connection.
 * You can pass the connection settings array in argument but you can declare a global variable named $SSH2S too.
*/
function ssh2_run($command, $SSH2S=null) {
	if( !isset($SSH2S) ) {
		global $SSH2S;
	}
	$session = ssh2_connect($SSH2S['host']);
	if( $session === false ) {
		throw new Exception('SSH2_unableToConnect');
	}
	if( !ssh2_auth_password( $session , $SSH2S['users'] , $SSH2S['passwd'] ) ) {
		throw new Exception('SSH2_unableToIdentify');
	}
	$stream = ssh2_exec( $session, $command);
	if( $stream === false ) {
		throw new Exception('SSH2_execError');
	}
	return $stream;
}

/** Scans a directory cleanly.
 * @param $dir The directory to scan.
 * @param $sorting_order True to reverse results order. Default value is False.
 * @return An array of the files in this directory.

 * Scans a directory and returns a clean result.
*/
function cleanscandir($dir, $sorting_order=0) {
	try {
		$result = scandir($dir);
	} catch(Exception $e) {
		return array();
	}
	unset($result[0]);
	unset($result[1]);
	if( $sorting_order ) {
		rsort($result);
	}
	return $result;
}

function stringify($s) {
	if( is_object($s) && $s instanceof Exception ) {
		$s = formatException($s);
	} else {
		$s = "\n".print_r($s, 1);
	}
	return $s;
}

function toHtml($s) {
	if( $s===NULL ) {
		$s = '{NULL}';
	} else if( $s === false ) {
		$s = '{FALSE}';
	} else if( $s === true ) {
		$s = '{TRUE}';
	} else if( !is_scalar($s) ) {
		$s = '<pre>'.print_r($s, 1).'</pre>';
	}
	return $s;
}

function formatException($e) {
	return 'Exception \''.get_class($e).'\' with '.( $e->getMessage() ? " message '{$e->getMessage()}'" : 'no message')
		.' in '.$e->getFile().':'.$e->getLine()."\n<pre>".$e->getTraceAsString().'</pre>';
}

/** Logs a report in a file.
 * @param $report The report to log.
 * @param $file The log file path.
 * @param $action The action associated to the report. Default value is an empty string.
 * @param $message The message to display. Default is an empty string. See description for details.
 * @warning This function require a writable log file.

 * Logs an error in a file serializing data to JSON.
 * Each line of the file is a JSON string of the reports.
 * The log folder is the constant LOGSPATH or, if undefined, the current one.
 * Take care of this behavior:
 *	If message is NULL, it won't display any report
 *	Else if ERROR_LEVEL is DEV_LEVEL, displays report
 *	Else if message is empty, throw exception
 *	Else it displays the message.
*/
function log_report($report, $file, $action='', $message='') {
	if( !is_scalar($report) ) {
		$report	= 'NON-SCALAR::'.stringify($report);//."\n".print_r($report, 1);
	}
	$Error	= array('date' => date('c'), 'report' => $report, 'action' => $action);
	$logFilePath	= ((defined("LOGSPATH") && is_dir(LOGSPATH)) ? LOGSPATH : '').$file;
	try {
		file_put_contents($logFilePath, json_encode($Error)."\n", FILE_APPEND);
	} catch( Exception $e ) {
		$Error['report'] .= "<br />\n<b>And we met an error logging this report:</b><br />\n".stringify($e);
	}
	if( $message !== NULL ) {// Yeh != NULL, not !empty, null cause no report to user
		if( ERROR_LEVEL == DEV_LEVEL ) {
			$Error['message']	= $message;
			$Error['page']		= nl2br(htmlentities($GLOBALS['Page']));
			// Display a pretty formatted error report
			global $RENDERING;
			if( !class_exists($RENDERING) || !$RENDERING::doDisplay('error', $Error) ) {
				// If we fail in our display of this error, this is fatal.
				echo print_r($Error, 1);
			}
		} else if( empty($message) ) {
			throw new Exception('fatalErrorOccurred');
			
		} else {
			die($message);
		}
	}
}

/** Logs a debug.
 * @param $report The debug report to log.
 * @param $action The action associated to the report. Default value is an empty string.
 * @sa log_report()

 * Logs a debug.
 * The log file is the constant DEBUGFILENAME or, if undefined, '.debug'.
*/
function log_debug($report, $action='') {
	log_report($report, defined("DEBUGFILENAME") ? DEBUGFILENAME : '.debug', $action, null);
}

/** Logs a hack attemp.
 * @param $report The report to log.
 * @param $action The action associated to the report. Default value is an empty string.
 * @param $message If False, it won't display the report, else if a not empty string, it displays it, else it takes the report's value.
 * @sa log_report()

 * Logs a hack attemp.
 * The log file is the constant HACKFILENAME or, if undefined, '.hack'.
*/
function log_hack($report, $action='', $message=null) {
	global $USER;
	log_report($report.' 
[ IP: '.$_SERVER['REMOTE_ADDR'].'; User: '.(isset($USER) ? "$USER #".$USER->id() : 'N/A').'; agent: '.(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A').'; referer: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A').' ]', defined("HACKLOGFILENAME") ? HACKLOGFILENAME : '.hack', $action, $message);
}

/** Logs a system error.
 * @param $report The report to log.
 * @param $action The action associated to the report. Default value is an empty string.
 * @param $silent True to not display any report. Default value is false.
 * @sa log_report()
 * @deprecated

 * Logs a system error.
 * The log file is the constant SYSLOGFILENAME or, if undefined, '.log_error'.
*/
function sys_error($report, $action='', $silent=false) {
	log_report($report, defined("SYSLOGFILENAME") ? SYSLOGFILENAME : '.log_error', $action, $silent ? null : '');
}

/** Logs a system error.
 * @param string $report The report to log.
 * @param string $action The action associated to the report. Default value is an empty string.
 * @param boolean $fatal True if the error is fatal, it stops script. Default value is true.
 * @sa log_report()

 * Logs a system error.
 * The log file is the constant SYSLOGFILENAME or, if undefined, '.log_error'.
*/
function log_error($report, $action='', $fatal=true) {
	log_report($report, defined("SYSLOGFILENAME") ? SYSLOGFILENAME : '.log_error', $action,
		empty($fatal) && ERROR_LEVEL != DEV_LEVEL ? null :
			(is_string($fatal) ? $fatal : "A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.").
			(ERROR_LEVEL == DEV_LEVEL ? '<br /><pre>'.print_r(debug_backtrace(), 1).'</pre>' : ''));
}

/** Logs a sql error.
 * @param $report The report to log.
 * @param $action The action associated to the report. Default value is an empty string.
 * @sa log_report()

 * Logs a sql error.
 * The log file is the constant PDOLOGFILENAME or, if undefined, '.pdo_error'.
*/
function sql_error($report, $action='') {
	log_report($report, defined("PDOLOGFILENAME") ? PDOLOGFILENAME : '.pdo_error', $action, null);// NULL to do nothing
// 	log_report($report, defined("PDOLOGFILENAME") ? PDOLOGFILENAME : '.pdo_error', $action, null);//, t('errorOccurredWithDB'));
// 	throw new SQLException('errorOccurredWithDB');
}

/** Limits the length of a string
 * @param $string The string to limit length.
 * @param $max The maximum length of the string.
 * @param $strend A string to append to the shortened string.
 * @return The shortened string.

 * Limits the length of a string and append $strend.
 * This function do it cleanly, it tries to cut before a word.
*/
function str_limit($string, $max, $strend='...') {
	$max = (int) $max;
	if( $max <= 0 ) {
		return '';
	}
	if( strlen($string) <= $max ) {
		return $string;
	}
	$subStr = substr($string, 0, $max);
	if( !in_array($string[$max], array("\n", "\r", "\t", " ")) ) {
		$lSpaceInd = strrpos($subStr, ' ');
		if( $max-$lSpaceInd < 10 ) {
			$subStr = substr($string, 0, $lSpaceInd);
		}
	}
	return $subStr.$strend;
}

/** Escape a text
 * @param $str The string to escape.
 * @return The escaped string.

 * Escape the text $str from special characters.
*/
function escapeText($str) {
	return htmlentities(str_replace("\'", "'", $str), ENT_NOQUOTES, 'UTF-8', false); 	
}

/** Formats a string to be a html attribute value
 * @param $str The string to format.
 * @return The escaped string.

* Escape the text $str from special characters for HTML Attribute usage.
*/
function htmlFormATtr($str) {
	if( !is_scalar($str) ) {
		$str	= json_encode($str);
	}
	$flags	= ENT_QUOTES | ENT_IGNORE;
	if( defined('ENT_HTML5') ) {
		$flags |= ENT_HTML5;
	}
	return htmlentities($str, $flags, 'UTF-8', false); 	
}

/** Encodes to an internal URL
 * @param $u The URL to encode.
 * @return The encoded URL
 * 
 * Encodes to URL and secures some more special characters.
*/
function iURLEncode($u) {
	return str_replace(array(".", '%2F'), array(":46", ''), urlencode($u));
}

/** Decodes from an internal URL
 * @param $u The URL to decode.
 * @return The decoded URL
 * 
 * Decodes from URL.
*/
function iURLDecode($u) {
	return urldecode(str_replace(":46", ".", $u));
}

/** Parse Fields array to string
 * @param $fields The fields array.
 * @param $quote The quote to escape key.
 * @return A string as fields list.
 * 
 * It parses a field array to a fields list for queries.
*/
function parseFields(array $fields, $quote='"') {
	$list = '';
	foreach($fields as $key => $value) {
		$list .= (!empty($list) ? ', ' : '').$quote.$key.$quote.'='.$value;
	}
	return $list;
}

/** Gets value from an Array Path
 * @param $array The array to get the value from.
 * @param $apath The path used to browse the array.
 * @param $default The default value returned if array is valid but key is not found.
 * @param $pathRequired True if the path is required. Default value is False.
 * @return The value from $apath in $array.
 * @sa build_apath()
 *
 * Gets value from an Array Path using / as separator.
 * Returns null if parameters are invalids, $default if the path is not found else the value.
 * If $default is not null and returned value is null, you can infer your parameters are invalids.
*/
function apath_get($array, $apath, $default=null, $pathRequired=false) {
	if( empty($array) || !is_array($array) || $apath === NULL ) {
		return null;
	}
	list($key, $suffix)	= explodeList('/', $apath, 2);
	// If element does not exist in array
	if( !isset($array[$key]) ) {
		// If has a child, the child could not be found
		// Else container exists, but element not found.
		return ($pathRequired && $suffix !== NULL) ? null : $default;
// 		return ($pathRequired && isset($rpaths[1])) ? null : $default;
	}
	return $suffix !== NULL ? apath_get($array[$key], $suffix) : $array[$key];
// 	return isset($rpaths[1]) ? apath_get($array[$rpaths[0]], $rpaths[1]) : $array[$rpaths[0]];
}

function apath_setp(&$array, $apath, $value) {
	if( $array === NULL ) {
		$array	= array();
	}
// 	debug("Set array $apath to value $value", $array);
// 	if( empty($array) || !is_array($array) || $apath === NULL ) {
// 		return null;
// 	}
	
	list($key, $suffix)	= explodeList('/', $apath, 2);//('/', $apath, 2);
	// The path is ends here
	if( $suffix === NULL ) {
		$array[$key]	= $value;
		return;
	}
	// The path continues
	if( !isset($array[$key]) ) {
		$array[$key]	= array();
	}
	apath_setp($array[$key], $suffix, $value);
}

/** Build all path to browse array
 * @param $array The array to get the value from.
 * @param $prefix The prefix to get the value, this is for an internal use only.
 * @return An array of apath to get all values.
 * @sa apath_get()
 *
 * Builds an array associating all values with their apath of the given one using / as separator.
 * e.g Array('path'=>array('to'=>array('value'=>'value'))) => Array('path/to/value'=>'value')
*/
function build_apath($array, $prefix='') {
	if( empty($array) || !is_array($array) ) {
		return array();
	}
	$r = array();
	foreach($array as $key => $value) {
		if( is_array($value) ) {
			$r += build_apath($value, $prefix.$key.'/');
		} else {
			$r[$prefix.$key] = $value; 
		}
	}
	return $r;
}

/** Imports the required class(es).
 * @param $pkgPath The package path.
 * @warning You should only use lowercase for package names.
 * 
 * Includes a class from a package in the libs directory, or calls the package loader.
 * e.g: "package.myclass", "package.other.*", "package"
 * 
 * Packages should include a _loader.php or loader.php file (it is detected in that order).
 * Class files should be named classname_class.php
*/
function using($pkgPath) {
	$pkgPath = LIBSDIR.str_replace('.', '/',strtolower($pkgPath));
	// Including all contents of a package
	if( substr($pkgPath, -2) == '.*' ) {
		$dir = pathOf(substr($pkgPath, 0, -2));
		$files = scandir($dir);
		foreach($files as $file) {
			if( preg_match("#^[^\.].*_class.php$#", $file) ) {
				require_once $dir.'/'.$file;
			}
		}
		return;
	}
	// Including loader of a package
	if( existsPathOf($pkgPath, $path) && is_dir($path) ) {
		if( file_exists($path.'/_loader.php') ) {
			require_once $path.'/_loader.php';
		} else {
			require_once $path.'/loader.php';
		}
		return;
	}
	// Including a class
	require_once existsPathOf($pkgPath.'_class.php', $path) ? $path : pathOf($pkgPath.'.php');
}

/** Adds a class to the autoload.
 * @param $className The class name.
 * @param $classPath The class path.
 * 
 * Adds the class to the autoload list, associated with its file.
 * The semi relative path syntax has priority over the full relative path syntax.
 * e.g: ("MyClass", "mylib/myClass") => libs/mylib/myClass_class.php
 * or ("MyClass2", "mylib/myClass2.php") => libs/mylib/myClass.php
*/
function addAutoload($className, $classPath) {
	global $AUTOLOADS;
	$className = strtolower($className);
	if( !empty($AUTOLOADS[$className]) ) {
		return false;
	}
	if( existsPathOf(LIBSDIR.$classPath.'_class.php') ) {
		$AUTOLOADS[$className] = $classPath.'_class.php';
		
	} else if( existsPathOf(LIBSDIR.$classPath) ) {
		$AUTOLOADS[$className] = $classPath;
		
	} else {
		throw new Exception("Class file of \"{$className}\" not found.");
	}
	return true;
}

/** Starts a new report stream
 * @param $stream The new report stream name
 * @sa endReportStream()

 * A new report stream starts, all new reports will be added to this stream.
*/
function startReportStream($stream) {
	global $REPORT_STREAM;
	$REPORT_STREAM = $stream;
}

/** Ends the current stream
 * @sa startReportStream()
 * Ends the current stream by setting current stream to the global one, so you can not end global stream.
*/
function endReportStream() {
	startReportStream('global');
}
endReportStream();

/** Transfers the stream reports to another
 * @param $from Transfers $from this stream. Default value is null (current stream).
 * @param $to Transfers $to this stream. Default value is global.
 * 
 * Transfers the stream reports to another
*/
function transferReportStream($from=null, $to='global') {
	if( is_null($from) ) {
		$from = $GLOBALS['REPORT_STREAM'];
	}
	if( $from==$to ) { return false; }
	global $REPORTS;
	if( !empty($REPORTS[$from]) ) {
		if( !isset($REPORTS[$to]) ) {
			$REPORTS[$to] = array();
		}
		$REPORTS[$to] = isset($REPORTS[$to]) ? array_merge_recursive($REPORTS[$to], $REPORTS[$from]) : $REPORTS[$from];
		unset($REPORTS[$from]);
	}
	return true;
}

/** Adds a report
 * @param $report string The report (Commonly a string or an UserException).
 * @param $type string The type of the message.
 * @param $domain string The domain to use to automatically translate the message. Default value is 'global'.
 * @return boolean False if rejected.
 * @sa reportSuccess(), reportError()

 * Adds the report $message to the list of reports for this $type.
 * The type of the message is commonly 'success' or 'error'.
*/
function addReport($report, $type, $domain='global', $code=null) {
	global $REPORTS, $REPORT_STREAM, $REJREPORTS, $DISABLE_REPORT;
	if( !empty($DISABLE_REPORT) ) { return false; }
	$report = "$report";
	if( !$code ) {
		$code	= $report;
	}
	if( isset($REJREPORTS[$report]) && (empty($REJREPORTS[$report]['t']) || in_array($type, $REJREPORTS[$report]['t'])) ) {
		return false;
	}
	if( !isset($REPORTS[$REPORT_STREAM]) ) {
		$REPORTS[$REPORT_STREAM] = array();
	}
	if( !isset($REPORTS[$REPORT_STREAM][$type]) ) {
		$REPORTS[$REPORT_STREAM][$type] = array();
	}
	$report	= t($report, $domain);// Added recently, require tests
	$REPORTS[$REPORT_STREAM][$type][] = array('c'=>$code, 'r'=>$report, 'd'=>$domain);
// 	$REPORTS[$REPORT_STREAM][$type][] = array('c'=>$report, 'r'=>t($report, $domain), 'd'=>$domain);
	return true;
}

/** Reports a success
 * @param $report string The message to report.
 * @param $domain string The domain fo the message. Not used for translation. Default value is global.
 * @sa addReport()

 * Adds the report $message to the list of reports for this type 'success'.
*/
function reportSuccess($report, $domain='global') {
	return addReport($report, 'success', $domain);
}

/** Reports a warning
 * @param $report string The message to report.
 * @param $domain string The domain fo the message. Not used for translation. Default value is global.
 * @sa addReport()

 * Adds the report $message to the list of reports for this type 'warning'.
 * Warning come in some special cases, we meet it when we do automatic checks before loading contents and there is something to report to the user.
*/
function reportWarning($report, $domain='global') {
	return addReport($report, 'warning', $domain);
}

/** Reports an error
 * @param $report string The report.
 * @param $domain string The domain fo the message. Default value is the domain of Exception in cas of UserException else 'global'.
 * @sa addReport()

 * Adds the report $message to the list of reports for this type 'error'.
*/
function reportError($report, $domain=null) {
	$code	= null;
	if( $report instanceof UserException ) {
		if( class_exists('InvalidFieldException') && $report instanceof InvalidFieldException ) {
			// InvalidFieldException translates the message when using __toString method, so we need to get the original code
			// Should be improved by object inheritance
			$code	= $report->getMessage();
		}
		if( $domain === NULL ) {
			$domain = $report->getDomain();
		}
	}
// 	$message = ($message instanceof Exception) ? $message->getMessage() : "$message";
	return addReport($report, 'error', $domain === NULL ? 'global' : $domain, $code);
}

/** Checks if there is error reports
 * @return boolean True if there is any error report.
*/
function hasErrorReports() {
	global $REPORTS;
	if( empty($REPORTS) ) { return false; }
	foreach($REPORTS as $stream => $types) {
		if( !empty($types['error']) ) {
			return true;
		}
	}
	return false;
}

/** Rejects reports
 * @param $report The report message to reject, could be an array.
 * @param $type Filter reject by type, could be an array. Default value is null, not filtering.
 * @sa addReport()
 * 
 * Register this report to be rejected in the future, addReport() will check it.
 * All previous values for this report will be replaced.
*/
function rejectReport($report, $type=null) {
	global $REJREPORTS;
	if( !isset($REJREPORTS) ) { $REJREPORTS = array(); }
	if( !is_array($report) ) {
		$report = array($report);
	}
	$d = array();
	if( isset($type) ) {
		$d['t'] = is_array($type) ? $type : array($type);
	}
	foreach( $report as $r ) {
		$d['r'] = $r;
		$REJREPORTS["$r"] = $d;
	}
}

/** Gets some/all reports as HTML
 * @param $stream The stream to get the reports. Default value is "global".
 * @param $type Filter results by report type. Default value is null.
 * @param $delete True to delete entries from the list. Default value is true.
 * @sa getReportsHTML()

 * Gets all reports from the list of $domain optionnally filtered by type.
*/
function getReports($stream='global', $type=null, $delete=1) {
	global $REPORTS;
	if( empty($REPORTS[$stream]) ) { return array(); }
	// Type specified
	if( !empty($type) ) {
		if( empty($REPORTS[$stream][$type]) ) { return array(); }
		$r = $REPORTS[$stream][$type];
		if( $delete ) {
			unset($REPORTS[$stream][$type]);
		}
		return array($type=>$r);
	}
	// All types
	$r = $REPORTS[$stream];
	if( $delete ) {
		$REPORTS[$stream] = array();
	}
	return $r;
}

/** Gets some/all reports as HTML
 * @param	$stream string The stream to get the reports. Default value is 'global'.
 * @param	$rejected array An array of rejected messages. Default value is an empty array.
 * @param	$delete boolean True to delete entries from the list. Default value is true.
 * @return	The renderer HTML.
 * @sa displayReportsHTML()
 * @sa getHTMLReport()

 * Gets all reports from the list of $domain and generates the HTML source to display.
*/
function getReportsHTML($stream='global', $rejected=array(), $delete=true) {
	$reports = getReports($stream, null, $delete);
	if( empty($reports) ) { return ''; }
	$reportHTML = '';
	foreach( $reports as $type => &$rl ) {
		foreach( $rl as $report) {
			$msg = "{$report['r']}";
			if( !in_array($msg, $rejected) ) {
				$reportHTML .= getHTMLReport($stream, $msg, $report['d'], $type);
			}
		}
	}
	return $reportHTML;
}

/** Gets one report as HTML
 * @param $stream	The stream of the report.
 * @param $report	The message to report.
 * @param $domain	The domain of the report.
 * @param $type		The type of the report.

 * Returns a valid HTML report.
 * This function is only a HTML generator.
*/
function getHTMLReport($stream, $report, $domain, $type) {
	if( class_exists('HTMLRendering', true) ) {
		return HTMLRendering::renderReport($report, $domain, $type, $stream);
	} 
	return '
		<div class="report report_'.$stream.' '.$type.' '.$domain.'">'.nl2br($report).'</div>';
}

/** Displays reports as HTML
 * @param $stream The stream to display. Default value is 'global'.
 * @param $rejected An array of rejected messages. Can be the first parameter.
 * @param $delete True to delete entries from the list.
 * @sa getReportsHTML()

 * Displays all reports from the list of $domain and displays generated HTML source.
*/
function displayReportsHTML($stream='global', $rejected=array(), $delete=1) {
	if( is_array($stream) && empty($rejected) ) {
		$rejected	= $stream;
		$stream		= 'global';
	}
	echo '
	<div class="reports '.$stream.'">
	'.getReportsHTML($stream, $rejected, $delete).'
	</div>';
}

/** Gets POST data
 * @param $path The path to retrieve. The default value is null (retrieves all data).
 * @return Data using the path or all data from POST array.
 * @sa isPOST()
 * @sa extractFrom()

 * Gets data from a POST request using the $path.
 * With no parameter or parameter null, all data are returned.
*/
function POST($path=null) {
	return extractFrom($path, $_POST);
}

/** Checks an existing post key
 * @param $path string The path to the array. The default value is null (search in POST).
 * @param $value int The output value of the item to delete.
 * @return True if there is an item to delete

 * This function is used to key the key value from an array sent by post
 * E.g You use POST to delete an item from a list, it's name is delete[ID], where ID is the ID of this item
 * If you call hasPOSTKey("delete", $itemID), the function will return true if a delete item is defined and $itemID will contain the ID of the item to delete.
*/
function hasPOSTKey($path=null, &$value=null) {
	$v = POST($path);
	if( !$v || !is_array($v) ) { return false; }
	$value	= key($v);
	return true;
}

/** Gets GET data
 * @param $path The path to retrieve. The default value is null (retrieves all data).
 * @return Data using the path or all data from GET array.
 * @sa isGET()
 * @sa extractFrom()

 * Gets data from a GET request using the $path.
 * With no parameter or parameter null, all data are returned.
*/
function GET($path=null) {
	return extractFrom($path, $_GET);
}

/** Checks the POST status
 * @param $apath The apath to test.
 * @return True if the request is a POST one. Compares also the $key if not null.
 * @sa POST()
 * 
 * Check the POST status to retrieve data from a form.
 * You can specify the name of your submit button as first parameter.
 * We advise to use the name of your submit button, but you can also use another important field of your form.
*/
function isPOST($apath=null) {
	// !empty because $_POST is always set in case of web access, but is an empty array
	return !empty($_POST) && ($apath===NULL || POST($apath)!==NULL);
}

/** Checks the GET status
 * @param $apath The apath to test.
 * @return True if the request is a GET one. Compares also the $key if not null.
 * @sa GET()
 * 
 * Check the GET status to retrieve data from a form.
 * You can specify the name of your submit button as first parameter.
 * We advise to use the name of your submit button, but you can also use another important field of your form.
*/
function isGET($apath=null) {
	// !empty because $_GET is always set in case of web access, but is an empty array
	return !empty($_GET) && ($apath===NULL || GET($apath)!==NULL);
}

/** Extracts data from array using apath
 * @param $apath The apath to retrieve. null retrieves all data.
 * @param $array The array of data to browse.
 * @return Data using the apath or all data from the given array.

 * Gets data from an array using the $apath.
 * If $apath is null, all data are returned.
*/
function extractFrom($apath, $array) {
	return $apath===NULL ? $array : apath_get($array, $apath);
// 	return is_null($path) ? $array : ( (!is_null($v = apath_get($array, $path))) ? $v : false) ;
}

/** Gets the HTML value
* @param $name The name of the field
* @param $data The array of data where to look for. Default value is $formData (if exist) or $_POST
* @param $default The default value if $name is not defined in $data
* @return A HTML source with the "value" attribute.
*
* Gets the HTML value attribut from an array of data if this $name exists.
*/
function htmlValue($name, $data=null, $default='') {
	fillFormData($data);
	$v = apath_get($data, $name, $default);
	return !empty($v) ? " value=\"{$v}\"" : '';
}

/** Generates the HTML source for a SELECT
* @param $name The name of the field.
* @param $values The values to build the dropdown menu.
* @param $data The array of data where to look for. Default value is $formData (if exist) or $_POST
* @param $selected The selected value from the data. Default value is null (no selection).
* @param $prefix The prefix to use for the text name of values. Default value is an empty string.
* @param $domain The domain to apply the Key. Default value is 'global'.
* @param $tagAttr Additional attributes for the SELECT tag.
* @return A HTML source for the built SELECT tag.
* @sa htmlOptions
* @warning This function is under conflict with name attribute and last form data values, prefer htmlOptions()
*
* Generates the HTML source for a SELECT from the $data.
*/
function htmlSelect($name, $values, $data=null, $selected=null, $prefix='', $domain='global', $tagAttr='') {
	fillFormData($data);
	$namePath = explode('/', $name);
	$name = $namePath[count($namePath)-1];
	$htmlName = '';
	foreach( $namePath as $index => $path ) {
		$htmlName .= ( $index ) ? "[{$path}]" : $path;
	}
	$tagAttr .= ' name="'.$htmlName.'"';
	$v = apath_get($data, $name);
	if( !empty($v) ) {//is_null($selected) && 
		$selected = $v;
	}
	$opts = '';
	foreach( $values as $dataKey => $dataValue ) {
		$addAttr = '';
		if( is_array($dataValue) ) {
			list($dataValue, $addAttr) = array_pad($dataValue, 2, null);
		}
		$key = is_int($dataKey) ? $dataValue : $dataKey;// If this is an associative array, we use the key, else the value.
		$opts .= '
	<option value="'.$dataValue.'" '.( ($dataValue == $selected) ? 'selected="selected"' : '').' '.$addAttr.'>'.t($prefix.$key, $domain).'</option>';
	}
	return "
	<select {$tagAttr}>{$opts}
	</select>";
}

/** Generates the HTML source for options of a SELECT
* @param $fieldPath string The name path to the field.
* @param $values string[] The values to build the dropdown menu.
* @param $default string|integer The default selected value. Default value is null (no selection).
* @param $matches integer Define the associativity between array and option values. Default value is OPT_VALUE2LABEL (as null).
* @param $prefix string The prefix to use for the text name of values. Default value is an empty string.
* @param $domain string The domain to apply the Key. Default value is 'global'.
* @return A HTML source for the built SELECT tag.
* @sa htmlOption()
*
* Generates the HTML source for a SELECT from the $data.
* For associative arrays, we commonly use the value=>label model (OPT_VALUE2LABEL) but sometimes for associative arrays we could prefer the label=>value model (OPT_LABEL2VALUE).
* You can use your own combination with defined constants OPT_VALUE_IS_VALUE, OPT_VALUE_IS_KEY, OPT_LABEL_IS_VALUE and OPT_LABEL_IS_KEY.
* Common combinations are OPT_LABEL2VALUE, OPT_VALUE2LABEL and OPT_VALUE.
* The label is prefixed with $prefix and translated using t(). This function allows bidimensional arrays in $values, used as option group.
*/
function htmlOptions($fieldPath, $values, $default=null, $matches=null, $prefix='', $domain='global') {
	if( $matches===NULL ) { $matches = OPT_VALUE2LABEL; }
	// Value of selected/default option
	fillInputValue($selValue, $fieldPath, OPT_PERMANENTOBJECT && is_object($default) ? $default->id() : $default);
	$opts	= '';
	foreach( $values as $dataKey => $elValue ) {
		if( $elValue===null ) { continue; }
		if( is_array($elValue) ) {
			$opts .= '<optgroup label="'.t($prefix.$dataKey, $domain).'">'.htmlOptions($fieldPath, $elValue, $default, $matches, $prefix, $domain).'</optgroup>';
			continue;
		}
		$addAttr	= '';
		if( is_array($elValue) ) {
			list($elValue, $addAttr) = array_pad($elValue, 2, null);
		}
		if( bintest($matches, OPT_PERMANENTOBJECT) ) {
			$optLabel	= "$elValue";
			$optValue	= $elValue->id();
		} else {
			$optLabel	= bintest($matches, OPT_LABEL_IS_KEY) ? $dataKey : $elValue;
			$optValue	= bintest($matches, OPT_VALUE_IS_KEY) ? $dataKey : $elValue;
		}
		$opts .= htmlOption($optValue, t($prefix.$optLabel, $domain), "$selValue"==="$optValue", $addAttr);
	}
	return $opts;
}
function _htmlOptions($fieldPath, $values, $default=null, $matches=null, $prefix='', $domain='global') {
	echo htmlOptions($fieldPath, $values, $default, $matches, $prefix, $domain);
}
define('OPT_VALUE_IS_VALUE'	 , 0);
define('OPT_VALUE_IS_KEY'	 , 1);
define('OPT_LABEL_IS_VALUE'	 , 0);
define('OPT_LABEL_IS_KEY'	 , 2);
define('OPT_PERMANENTOBJECT' , 4);
define('OPT_LABEL2VALUE'	 , OPT_VALUE_IS_VALUE | OPT_LABEL_IS_KEY);
define('OPT_VALUE2LABEL'	 , OPT_VALUE_IS_KEY | OPT_LABEL_IS_VALUE);
define('OPT_VALUE'			 , OPT_VALUE_IS_VALUE | OPT_LABEL_IS_VALUE);

function htmlOption($elValue, $label=null, $selected=false, $addAttr='') {
	if( !$label ) { $label = $elValue; }
	return '<option '.valueField($elValue).($selected ? ' selected="selected"' : '').' '.$addAttr.'>'.$label.'</option>';
}

/** Generates a selected attribute
* @param $fieldPath The field path to use to define name.
* @param $default The default value.
* @param $addAttr additional attributes.
* @return A HTML source for the built selected attribute.
* @sa htmlSelect()
* @sa htmlOptions()
*
* Generates a HTML source as selected attribute for a SELECT.
* This function is useful for very customized select which could not use htmlSelect().
*/
// function htmlOptionValue($field, $value, $data=null, $attr='selected') {
// 	if( is_null($data) ) {
// 		$data = isset($GLOBALS['formData']) ? $GLOBALS['formData'] : POST();
// 	}
// 	return (isset($data[$field]) && $value == $data[$field]) ? 'selected="selected"' : '';
// }

global $FORM_EDITABLE;
$FORM_EDITABLE	= true;
function htmlDisabledAttr() {
	global $FORM_EDITABLE;
	return $FORM_EDITABLE ? '' : ' disabled';
}
function valueField($v) {
	return 'value="'.addcslashes($v, '"').'"';
}
function htmlFileUpload($fieldPath, $addAttr='') {
	return '<input type="file" name="'.apath_html($fieldPath).'" '.$addAttr.htmlDisabledAttr().'/>';
}

function htmlPassword($fieldPath, $addAttr='') {
	return '<input type="password" name="'.apath_html($fieldPath).'" '.$addAttr.htmlDisabledAttr().'/>';
}

function _htmlText($fieldPath, $default='', $addAttr='', $formatter=null) {
	echo htmlText($fieldPath, $default, $addAttr, $formatter);
}
function htmlText($fieldPath, $default='', $addAttr='', $formatter=null, $type='text') {
	fillInputValue($value, $fieldPath, $default);
	return '<input type="'.$type.'" name="'.apath_html($fieldPath).'" '.valueField(isset($value) ? isset($formatter) ? call_user_func($formatter, $value) : $value : '').' '.$addAttr.htmlDisabledAttr().'/>';
}

function htmlTextArea($fieldPath, $default='', $addAttr='') {
	fillInputValue($value, $fieldPath, $default);
	return '<textarea name="'.apath_html($fieldPath).'" '.$addAttr.htmlDisabledAttr().'>'.$value.'</textarea>';
}

function htmlHidden($fieldPath, $default='', $addAttr='') {
	fillInputValue($value, $fieldPath, $default);
	return '<input type="hidden" name="'.apath_html($fieldPath).'" '.(isset($value) ? valueField($value).' ' : '').$addAttr.htmlDisabledAttr().'/>';
}

function htmlRadio($fieldPath, $elValue, $default=false, $addAttr='') {
	$selected = fillInputValue($value, $fieldPath) ? $value==$elValue : $default;
	return '<input type="radio" name="'.apath_html($fieldPath).'" '.valueField($elValue).' '.($selected ? 'checked="checked"' : '').' '.$addAttr.htmlDisabledAttr().'/>';
}

function htmlCheckBox($fieldPath, $default=false, $addAttr='') {
	// Checkbox : Null => Undefined, False => Unchecked, 'on' => Checked
	// 			If Value found,	we consider this one, else we use default
	fillInputValue($selected, $fieldPath, $default, true);
	return '<input type="checkbox" name="'.apath_html($fieldPath).'" '.($selected ? 'checked="checked"' : '').' '.$addAttr.htmlDisabledAttr().'/>';
}

function apath_html($apath) {
	$apath = explode('/', $apath);
	$htmlName = '';
	foreach( $apath as $index => $path ) {
		$htmlName .= ( $index ) ? '['.$path.']' : $path;
	}
	return $htmlName;
}

/** Gets input form data
 * @return POST() or global $formData if set.
 *
 * Gets input form data from POST.
 * Developers can specify an array of data to use by filling global $formData.
 * This function is designed to be used internally to have compliant way to get input form data.
 */
function getFormData() {
	return isset($GLOBALS['formData']) ? $GLOBALS['formData'] : POST();
}

/** Fills the given data from input form
 * @param $data The data to fill, as pointer.
 * @return The resulting $data.
 * @sa getFormData()
 *
 * Fills the given pointer data array with input form data if null.
 * This function is designed to only offset the case where $data is null.
 */
function fillFormData(&$data) {
	return $data = is_null($data) ? getFormData() : $data;
}

/** Fills the given value from input form
 * @param $value The value to fill, as pointer.
 * @param $fieldPath The apath to the input form value.
 * @param $default The default value if not found. Default value is null (apath_get()'s default).
 * @param $pathRequired True if the path is required. Default value is False (apath_get()'s default).
 * @return True if got value is not null (found).
 * @sa getFormData()
 * @sa apath_get()
 *
 * Fills the given pointer value with input form data or uses default.
 */
function fillInputValue(&$value, $fieldPath, $default=null, $pathRequired=false) {
	$value = apath_get(getFormData(), $fieldPath, $default, $pathRequired);
	if( $value === NULL ) {
		$value = $default;
	}
	return $value !== NULL;
}

/** Converts special characters to non-special ones
 * @param $string The string to convert.
 * @return The string wih no special characters.
 *
 * Replaces all special characters in $string by the non-special version of theses.
 */
function convertSpecialChars($string) {
	// Replaces all letter special characters.
	// See http://stackoverflow.com/a/6837302/2610855
	// The answer is improved
	$string = str_replace(
		array(
			'À','à','Á','á','Â','â','Ã','ã','Ä','ä','Æ','æ','Å','å',
			'ḃ','Ḃ',
			'ć','Ć','ĉ','Ĉ','č','Č','ċ','Ċ','ç','Ç',
			'ď','Ď','ḋ','Ḋ','đ','Đ','ð','Ð',
			'é','É','è','È','ĕ','Ĕ','ê','Ê','ě','Ě','ë','Ë','ė','Ė','ę','Ę','ē','Ē',
			'ḟ','Ḟ','ƒ','Ƒ',
			'ğ','Ğ','ĝ','Ĝ','ġ','Ġ','ģ','Ģ',
			'ĥ','Ĥ','ħ','Ħ',
			'í','Í','ì','Ì','î','Î','ï','Ï','ĩ','Ĩ','į','Į','ī','Ī','ĵ',
			'Ĵ',
			'ķ','Ķ',
			'ĺ','Ĺ','ľ','Ľ','ļ','Ļ','ł','Ł',
			'ṁ','Ṁ',
			'ń','Ń','ň','Ň','ñ','Ñ','ņ','Ņ',
			'ó','Ó','ò','Ò','ô','Ô','ő','Ő','õ','Õ','ø','Ø','ō','Ō','ơ','Ơ','ö','Ö',
			'ṗ','Ṗ',
			'ŕ','Ŕ','ř','Ř','ŗ','Ŗ',
			'ś','Ś','ŝ','Ŝ','š','Š','ṡ','Ṡ','ş','Ş','ș','Ș','ß',
			'ť','Ť','ṫ','Ṫ','ţ','Ţ','ț','Ț','ŧ','Ŧ',
			'ú','Ú','ù','Ù','ŭ','Ŭ','û','Û','ů','Ů','ű','Ű','ũ','Ũ','ų','Ų','ū','Ū','ư','Ư','ü','Ü',
			'ẃ','Ẃ','ẁ','Ẁ','ŵ','Ŵ','ẅ','Ẅ',
			'ý','Ý','ỳ','Ỳ','ŷ','Ŷ','ÿ','Ÿ',
			'ź','Ź','ž','Ž','ż','Ż',
			'þ','Þ','µ','а','А','б','Б','в','В','г','Г','д','Д','е','Е','ё','Ё','ж','Ж','з','З','и','И','й','Й','к','К','л','Л','м','М','н','Н','о','О','п','П','р','Р','с','С','т','Т','у','У','ф','Ф','х','Х','ц','Ц','ч','Ч','ш','Ш','щ','Щ','ъ' => '', 'Ъ' => '', 'ы','Ы','ь' => '', 'Ь' => '', 'э','Э','ю','Ю','я','Я',
			' ','&'
		),
		array(
			'A','a','A','a','A','a','A','a','Ae','ae','AE','ae','A','a',
			'b','B',
			'c','C','c','C','c','C','c','C','c','C',
			'd','D','d','D','d','D','dh','Dh',
			'e','E','e','E','e','E','e','E','e','E','e','E','e','E','e','E','e','E',
			'f','F','f','F',
			'g','G','g','G','g','G','g','G',
			'h','H','h','H',
			'i','I','i','I','i','I','i','I','i','I','i','I','i','I','j',
			'J',
			'k','K',
			'l','L','l','L','l','L','l','L',
			'm','M',
			'n','N','n','N','n','N','n','N',
			'o','O','o','O','o','O','o','O','o','O','oe','OE','o','O','o','O','oe','OE',
			'p','P',
			'r','R','r','R','r','R',
			's','S','s','S','s','S','s','S','s','S','s','S','SS',
			't','T','t','T','t','T','t','T','t','T',
			'u','U','u','U','u','U','u','U','u','U','u','U','u','U','u','U','u','U','u','U','ue','UE',
			'w','W','w','W','w','W','w','W',
			'y','Y','y','Y','y','Y','y','Y',
			'z','Z','z','Z','z','Z',
			'th','Th','u','a','a','b','b','v','v','g','g','d','d','e','E','e','E','zh','zh','z','z','i','i','j','j','k','k','l','l','m','m','n','n','o','o','p','p','r','r','s','s','t','t','u','u','f','f','h','h','c','c','ch','ch','sh','sh','sch','sch','','','y','y','','','e','e','ju','ju','ja','ja',
			'_','and'
		), $string);
		//'','','','','','',''), $string);
	// Now replaces all other special character by nothing.
	$string = preg_replace('#[^a-z0-9\-\_\.]#i', '', $string);
	return $string;
}

/** Converts the string into a slug
 * @param $string The string to convert.
 * @param $case The case style to use, values: null (default), LOWERCAMELCASE or UPPERCAMELCASE.
 * @return The slug version.
 *
 * Converts string to lower case and converts all special characters. 
*/
function toSlug($string, $case=null) {
	$string = str_replace(' ', '', ucwords(str_replace('&', 'and', strtolower($string))));
	if( isset($case) ) {
		if( bintest($case, CAMELCASE) ) {
			if( $case == LOWERCAMELCASE ) {
				$string = lcfirst($string);
			}
		}
	}
	return convertSpecialChars($string);
}

/** Converts the string into a slug
 * @param $string The string to convert.
 * @param $case The case style to use, values: null (default), LOWERCAMELCASE or UPPERCAMELCASE.
 * @return The slug version.
 *
 * Converts string to lower case and converts all special characters. 
*/
function slug($string, $case=null) {
	$string = preg_replace('#[^a-z0-9\-_]#i', '', ucwords(str_replace('&', 'and',strtolower($string))));
	if( isset($case) ) {
		if( bintest($case, CAMELCASE) ) {
			if( $case == LOWERCAMELCASE ) {
				$string = lcfirst($string);
			}
		}
	}
	return convertSpecialChars($string);
}
defifn('CAMELCASE',			1<<0);
defifn('LOWERCAMELCASE',	CAMELCASE);
defifn('UPPERCAMELCASE',	CAMELCASE | 1<<1);

// /** Converts the boolean into a string
// function bool2str($v) {
// 	return ($v ? 'True' : 'False');
// }

/** Gets the string of a boolean
 * @param $b The boolean.
* @return The boolean's string.
*/
function b($b) {
	return $b ? 'TRUE' : 'FALSE';
}

/** Splits a string by string in limited values
 * @param $delimiter	The boundary string.
 * @param $string		The input string.
 * @param $limit		The limit of values exploded.
 * @param $default		The default value to use if missing.
 * @return An array of a defined number of values.
 * @sa explode()
 * 
 * Splits a string by string in a limited number of values.
 * The main difference with explode() is this function complete missing values with $default.
 * If you want $limit optional, use explode()
 */
function explodeList($delimiter, $string, $limit, $default=null) {
	return array_pad(explode($delimiter, $string, $limit), abs($limit), $default);
}

function hashString($str) {
	//http://www.php.net/manual/en/faq.passwords.php
	$salt = defined('USER_SALT') ? USER_SALT : '1$@g&';
	return hash('sha512', $salt.$str.'7');
}

/** Gets the date as string
 * @param $time The UNIX timestamp.
 * @return The date using 'dateFormat' translation key
 * 
 * Date format is storing a date, not a specific moment, we don't care about timezone
*/
function d($time=TIME) {
	return !empty($time) ? strftime(t('dateFormat'), is_numeric($time) ? $time : strtotime($time)) : null;
}

/** Gets the date time as string
 * @param $time The UNIX timestamp.
 * @return The date using 'timeFormat' translation key
 * 
 * Datetime format is storing a specific moment, we care about timezone
*/
function dt($time=TIME) {
	return !empty($time) ? strftime(t('timeFormat'), is_numeric($time) ? $time : strtotime($time.' GMT')) : null;
}

/** Gets the date as string in SQL format
 * @param $time The UNIX timestamp.
 * @return The date using sql format
 * 
 * Date format is storing a date, not a specific moment, we don't care about timezone
*/
function sqlDate($time=TIME) {
// 	return gmstrftime('%Y-%m-%d', $time);
	return strftime('%Y-%m-%d', $time);
}

/** Gets the date time as string in SQL format
 * @param $time The UNIX timestamp.
 * @return The date using sql format
 * 
 * Datetime format is storing a specific moment, we care about timezone
*/
function sqlDatetime($time=TIME) {
	return gmstrftime('%Y-%m-%d %H:%M:%S', $time);
}

/** Gets the client public IP
 * @return The ip of the client
*/
function clientIP() {
	return $_SERVER['REMOTE_ADDR'];
}

/** Gets the id of the current user
 * @return The user's id
*/
function userID() {
	global $USER;
	return !empty($USER) ? $USER->id() : 0;
}

/** Generates a new password
 * @param $length The length of the generated password. Default value is 10.
 * @param $chars The characters to use to generate password. Default value is 'abcdefghijklmnopqrstuvwxyz0123456789'
 * @return The generated password.
 * 
 * Letters are randomly uppercased
*/
function generatePassword($length=10, $chars='abcdefghijklmnopqrstuvwxyz0123456789') {
	$max = strlen($chars)-1;
	$r = '';
	for( $i=0; $i<$length; $i++ ) {
		$c = $chars[mt_rand(0, $max)];
		$r .= mt_rand(0, 1) ? strtoupper($c) : $c;
	}
	return $r;
}

/** Returns the day timestamp using the given integer
 * @param $time The time to get the day time. Default value is current timestamp.
 * 
 * Returns the timestamp of the current day of $time according to the midnight hour.
*/
function dayTime($time=null) {
	if( $time === NULL ) { $time = time(); }
	return $time - $time%86400 - date('Z');
}

/** Returns the timestamp of the $day of the month using the given integer
 * @param $day The day of the month to get the timestamp. Default value is 1, the first day of the month.
 * @param $time The time to get the month timestamp. Default value is current timestamp.
 * @sa dayTime()
 *
 * Returns the timestamp of the $day of current month of $time according to the midnight hour.
*/
function monthTime($day=1, $time=null) {
	if( is_null($time) ) { $time = time(); }
	return dayTime($time - (date('j', $time)-$day)*86400);
}

/** Standardizes the phone number to FR country format
 * @param $number The input phone number.
 * @param $delimiter The delimiter for series of digits. Default value is current timestamp. Default value is '.'.
 * @param $limit The number of digit in a serie separated by delimiter. Optional, the default value is 2.
 * @sa dayTime()
 *
 * Returns a standard phone number for FR country format.
 */
function standardizePhoneNumber_FR($number, $delimiter='.', $limit=2) {
	// If there is not delimiter we try to put one
	$number = str_replace(array('.', ' ', '-'), '', $number);
	$length	= strlen($number);
	if( $length < 10  ) { return ''; }
	$n = '';
	for( $i=strlen($number)-$limit; $i>3 || ($number[0]!='+' && $i>($limit-1)); $i-=$limit ) {
		$n = $delimiter.substr($number, $i, $limit).$n;
	}
	return substr($number, 0, $i+2).$n;
}

function formatDuration_Shortest($duration) {
	$formats	= array('days'=>86400, 'hours'=>3600, 'minutes'=>60);
	foreach( $formats as $unit => $time ) {
		$r	= $duration/$time;
		if( $r >= 1 ) { break; }
	}
	return intval($r).t($unit.'_short');
}

function count_intersect_keys($array1, $array2) {
	return count(array_intersect_key($array1, $array2));
}

function getMimeType($filePath) {
	if( function_exists('finfo_open') ) {
// 		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);
	}
	return mime_content_type($filePath);
}

function checkDir($filePath) {
	return is_dir($filePath) || mkdir($filePath, 0772, true);
}

function array_insert(&$array, $position, $value) {
	array_splice($array, $position, 0, $value);
}

function array_index($array, $key) {
	return array_search($key, array_keys($array));
// 	return array_search($key, array_keys(array_values($array)));
}

function array_last($array) {
	// Copy of array, the pointer is not moved
	return end($array);
}

function array_get($array, $index, $default=false) {
	$array	= array_values($array);
// 	debug('array_get('.$index.') values', $array);
	return isset($array[$index]) ? $array[$index] : $default;
}

function array_apply($array, $callback, $userdata=null, &$success=null) {
	$success	= array_walk($array, $callback, $userdata);
	return $array;
}

function array_peer($array, $peerGlue=': ') {
	return array_apply($array, function(&$v, $k) use($peerGlue) { $v = $k.$peerGlue.$v; });
}

function str_ucfirst($str) {
	return ucfirst(strtolower($str));
}

function str_ucwords($str) {
	return ucwords(strtolower($str));
}

function reverse_values(&$val1, &$val2) {
	$tmp	= $val1;
	$val1	= $val2;
	$val2	= $tmp;
}
