<?php
//! The config class
/*!
	This class is the main way to get configuration.
*/
class Config extends ConfigCore {
	
	const EXT = 'ini';

	//!	Loads configuration from new source.
	/*!
		\param $source An identifier or a path to get the source.
		\return The loaded configuration array.
	
		If an identifier, loads a configuration from a .ini file in CONFDIR.
		Else $source is a full path to the ini configuration file.
	*/
	public function load($source) {		
		// Full path given
		if( is_readable($source) ) {
			$confPath = $source;
			
		// File in configs folder
		} else {
			try {
				$confPath = static::getFilePath($source);
			} catch( Exception $e ) {
				// File not found
				return array();
			}
		}
		$parsed = parse_ini_file($confPath, true);
		$this->add($parsed);
		return $parsed;
	}

	//!	Gets the file path
	/*!
		\param $source An identifier to get the source.
		\return The configuration file path according to Orpheus file are organized.
	
		Gets the configuration file path in CONFDIR.
	*/
	public static function getFilePath($source) {
		return pathOf(CONFDIR.$source.'.'.self::EXT);
	}
}