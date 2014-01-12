<?php
/*!
 * \file index.php
 * \brief The Orpheus Core
 * \author Florent Hazard
 * \copyright The MIT License, see LICENSE.txt
 * 
 * PHP File for the website core.
 */
echo __FILE__.' : '.__LINE__;

if( isset($SRCPATHS) ) {
	$t = $SRCPATHS; unset($SRCPATHS);
}
require_once 'loader.php';

$f = dirname(dirname($_SERVER['SCRIPT_FILENAME'])).'/instance.php';
if( file_exists($f) ) {
	require_once $f;
}
unset($f);

// These constants take care about paths through symbolic links.
defifn('ORPHEUSPATH',		dirpath($_SERVER['SCRIPT_FILENAME']));	// The Orpheus sources
defifn('APPLICATIONPATH',	ORPHEUSPATH);							// The application sources
defifn('INSTANCEPATH',		APPLICATIONPATH);						// The instance sources

addSrcPath(ORPHEUSPATH);
addSrcPath(APPLICATIONPATH);
addSrcPath(INSTANCEPATH);
if( isset($t) ) {
	foreach($t as $path) {
		addSrcPath($path);
	}
	unset($t);
}

defifn('CONSTANTSPATH', pathOf('configs/constants.php'));

// Edit the constant file according to the system context (OS, directory tree ...).
require_once CONSTANTSPATH;

error_reporting(ERROR_LEVEL);//Edit ERROR_LEVEL in previous file.

// Errors Actions
define("ERROR_THROW_EXCEPTION", 0);
define("ERROR_DISPLAY_RAW", 1);
define("ERROR_IGNORE", 2);
set_error_handler(
//! Error Handler
/*!
	System function to handle PHP errors and convert it into exceptions.
*/
function($errno, $errstr, $errfile, $errline ) {
	if( empty($GLOBALS['NO_EXCEPTION']) && (empty($GLOBALS['ERROR_ACTION']) || $GLOBALS['ERROR_ACTION']==ERROR_THROW_EXCEPTION) ) {//ERROR_THROW_EXCEPTION
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	} else if( $GLOBALS['ERROR_ACTION'] == ERROR_IGNORE ) {//ERROR_IGNORE
		return;
	} else {//ERROR_DISPLAY_RAW
		$backtrace = '';
		foreach( debug_backtrace() as $trace ) {
			if( !isset($trace['file']) ) {
				$trace['file'] = $trace['line'] = 'N/A';
			}
			$backtrace .= '
'.$trace['file'].' ('.$trace['line'].'): '.$trace['function'].'('.print_r($trace['args'], 1).')<br />';
		}
		if( !function_exists('sys_error') ) {
			die($errstr."<br />\n{$backtrace}");
		}
		sys_error($errstr."<br />\n{$backtrace}");
		die("A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.");
	}
});

register_shutdown_function(
//! Shutdown Handler
/*!
	System function to handle PHP shutdown and catch uncaught errors.
*/
function() {
	if( $error = error_get_last() ) {
		switch($error['type']){
			case E_ERROR:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR: {
				$Page = ob_get_contents();
				ob_end_clean();
				$message = $error['message'].' in '.$error['file'].' ('.$error['line'].')<br />
PAGE:<br /><div style="clear: both;">'.$Page.'</div>';
				
				if( !function_exists('sys_error') ) {
					die($message);
				}
				sys_error($message);
				die("A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez re-essayer plus tard.");
				break;
			}
		}
	}
});

set_exception_handler(
//! Exception Handler
/*!
	System function to handle all exceptions and stop script execution.
 */
function($e) {
	global $coreAction;
	if( !function_exists('sys_error') ) {
		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	die('A fatal error occurred, retry later.<br />\nUne erreur fatale est survenue, veuillez réessayer plus tard.');
});

spl_autoload_register(
// Class autoload function
/*
	\param $className The classname not loaded yet.
	\sa The \ref libraries documentation
	
	Includes the file according to the classname in lowercase and suffixed by '_class.php'.\n
	The script stops if the class file is not found.\n
*/
function($className) {
	try {
		global $AUTOLOADS, $AUTOLOADSFROMCONF;
		// In the first __autoload() call, we try to load the autoload config from file.
		if( !isset($AUTOLOADSFROMCONF) && class_exists('Config') ) {
			try {
				$alConf = Config::build('autoloads', true);
				$AUTOLOADS = array_merge($AUTOLOADS, $alConf->all);
				$AUTOLOADSFROMCONF = true;
			} catch( Exception $e ) {
				// Might be not found (default)
			}
		}
		// PHP's class' names are not case sensitive.
		$bFile = strtolower($className);
		
		// If the class file path is known in the AUTOLOADS array
		if( !empty($AUTOLOADS[$bFile]) ) {
			if( existsPathOf(LIBSDIR.$AUTOLOADS[$bFile], $path) ) {
				// if the path is a directory, we search the class file into this directory.
				if( is_dir($path) ) {
					if( existsPathOf($path.$bFile.'_class.php') ) {
						require_once pathOf($path.$bFile.'_class.php');
						return;
					}
				// if the path is a file, we include the class file.
				} else {
					require_once $path;
					return;
				}
			}
			throw new Exception("Bad use of Autoloads. Please use addAutoload().");
			
		// NOT USED, PREFER ADDAUTOLOAD()
// 		// If the class file is directly in the libs directory
// 		} else if( is_readable(pathOf(LIBSDIR.$bFile.'_class.php') ) {
// 			require_once pathOf(LIBSDIR.$bFile.'_class.php';
			
			
// 		// If the class file is in a eponymous sub directory in the libs directory
// 		} else if( is_readable(pathOf(LIBSDIR.$bFile.'/'.$bFile.'_class.php') ) {
// 			require_once pathOf(LIBSDIR.$bFile.'/'.$bFile.'_class.php';
			
		// If the class name is like Package_ClassName, we search the class file "classname" in the "package" directory in libs/.
		} else {
			$classExp = explode('_', $bFile, 2);
			if( count($classExp) > 1 && existsPathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php') ) {
				require_once pathOf(LIBSDIR.$classExp[0].'/'.$classExp[1].'_class.php');
				return;
			}
			// NOT FOUND
			//Some libs could add their own autoload function.
			//throw new Exception("Unable to load lib \"{$className}\"");
		}
	} catch( Exception $e ) {
		@sys_error("$e", 'loading_class_'.$className);
		die('A fatal error occured loading libraries.');
	}
}, true, true );// End of spl_autoload_register()

$AUTOLOADS = array();
$Module = $Page = '';// Useful for initializing errors.

$coreAction = 'initializing_core';

try {
echo __FILE__.' : '.__LINE__;
	defifn('CORELIB',		'core');
	defifn('CONFIGLIB',		'config');
	
	includePath(LIBSDIR.CORELIB.'/');// Load engine Core
	
	includePath(LIBSDIR.CONFIGLIB.'/');// Load configuration library (Must provide Config class).
	
	includePath(CONFDIR);// Require to be loaded before libraries to get hooks.
	
	Config::build('engine');// Some libs should require to get some configuration.
	
	includePath(LIBSDIR);// Require some hooks.
	
	// Here starts Hooks and Session too.
	Hook::trigger('startSession');

	if( !defined('TERMINAL') ) {
		$NO_EXCEPTION = 1;
	
		//PHP is unable to manage exception thrown during session_start()
		session_start();
		if( !isset($_SESSION['ORPHEUS']) ) {
			$_SESSION['ORPHEUS'] = array('LAST_REGENERATEID' => 0);
		}
		if( version_compare(PHP_VERSION, '4.3.3', '>=') ) {
			// Only version >= 4.3.3 can regenerate session id without losing data
			//http://php.net/manual/fr/function.session-regenerate-id.php
			if( TIME-$_SESSION['ORPHEUS']['LAST_REGENERATEID'] > 600 ) {
				$_SESSION['ORPHEUS']['LAST_REGENERATEID'] = TIME;
				session_regenerate_id();
			}
		}
	
		$NO_EXCEPTION = 0;
	}
	
	// Checks and Gets global inputs.
	$Action = ( !empty($_GET['action']) && is_name($_GET['action'], 50, 1) ) ? $_GET['action'] : null;
	$Format = ( !empty($_GET['format']) && is_name($_GET['format'], 50, 2) ) ? strtolower($_GET['format']) : 'html';
	
	Hook::trigger('checkModule');
	
	$Module = GET('module');
	
	if( empty($Module) ) {
// 		$Module = ($Format == 'json') ? 'remote' : DEFAULTMOD;
		$Module = DEFAULTMOD;
	}
	
	if( empty($Module) || !is_name($Module) ) {
		throw new UserException('invalidModuleName');
	}
	if( !existsPathOf(MODDIR.$Module.'.php') ) {
// 		die('inexistantModule : '.$Module);
		throw new UserException('inexistantModule');
	}
	
	$allowedFormats = Config::get('module_formats');
	$allowedFormats = isset($allowedFormats[$Module]) ? $allowedFormats[$Module] : 'html';
	if( $allowedFormats != '*' && $allowedFormats != $Format && (!is_array($allowedFormats) || !in_array($Format, $allowedFormats)) ) {
		throw new UserException('unavailableFormat');
	}
	unset($allowedFormats);
	
	// Future feature ?
	//$Module = Hook::trigger('routeModule', $Module, $Format, $Action);

	echo __FILE__.' : '.__LINE__;
	$coreAction = 'running_'.$Module;
	$Module = Hook::trigger('runModule', false, $Module);
	define('OBLEVEL_INIT', ob_get_level());
	ob_start();
echo __FILE__.' : '.__LINE__;
	require_once pathOf(MODDIR.$Module.'.php');
	$Page = ob_get_contents();
	ob_end_clean();
echo __FILE__.' : '.__LINE__;
	
} catch(UserException $e) {
	reportError($e);
	$Page = getReportsHTML();
	
} catch(Exception $e) {
	if( defined('OBLEVEL_INIT') && ob_get_level() > OBLEVEL_INIT ) {
		$Page = ob_get_contents();
		ob_end_clean();
	}
	if( !function_exists('sys_error') ) {
		die($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()));
	}
	ob_start();
	sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	$Page = ob_get_contents();
	ob_end_clean();
}
echo __FILE__.' : '.__LINE__;

try {
	$coreAction = 'displaying_'.$Module;
	if( class_exists('Hook') ) {
		Hook::trigger('showRendering', true);
	}
echo __FILE__.' : '.__LINE__;
log_debug(__FILE__.' : '.__LINE__);
	if( class_exists('Rendering') ) {
		Rendering::doShow();//Generic final display.
echo __FILE__.' : '.__LINE__;
	} else {
		echo $Page;
echo __FILE__.' : '.__LINE__;
	}
	
} catch(Exception $e) {
	@sys_error($e->getMessage()."<br />\n".nl2br($e->getTraceAsString()), $coreAction);
	die('A fatal display error occured.');
}