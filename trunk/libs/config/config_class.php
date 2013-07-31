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
	
		If an identifier, loads a configuration from a .ini file in CONFPATH.
		Else $source is a full path to the ini configuration file.
	*/
	public function load($source) {		
		// Full path given
		if( is_readable($source) ) {
			$confPath = $source;
			
		// File in configs folder
		} else if( is_readable(CONFPATH.$source.'.'.self::EXT) ) {
			$confPath = CONFPATH.$source.'.'.self::EXT;
			
		/// File not found
		} else {
			return array();
		}
		$parsed = parse_ini_file($confPath, true);
		$this->add($parsed);
		return $parsed;
	}
	
}
