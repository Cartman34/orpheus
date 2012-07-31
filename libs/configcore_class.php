<?php
//! The config core class
/*!
	This class is the core for config classes inherited from custom configuration.
*/
abstract class ConfigCore {
	
	//! Contains the main configuration, reachable from everywhere.
	protected static $main;
	
	//! Contains the configuration for this Config Object. Must be inherited from ConfigCore.
	protected $config = array();
	
	//! The magic function to get config.
	/*!
		\param $key The key to get the value.
		\return A config value.
		
		Returns the configuration item with key $key.
		Except for:
		- 'all' : It returns an array containing all configuration items.
	*/
	public function __get($key) {
		if( $key == 'all' ) {
			return $this->config;
		}
		return (isset($this->config[$key])) ? $this->config[$key] : NULL;
	}
	
	//! Adds configuration to this object.
	/*!
		\param $conf The configuration array to add to the current object.
		
		Adds the configuration array $conf to this configuration.
	*/
	public function add($conf) {
		$this->config += $conf;
	}
	
	//!	Loads new configuration source.
	/*!
		\param $source An identifier to get the source.
		
		Loads a configuration from a source identified with $source.
	*/
	public abstract function load($source);
	
	//!	Builds new configuration source.
	/*!
		\param $source An identifier to build the source.
		\param $minor Specify if this is a minor configuration.
		
		Builds a configuration from $source using load() method.
		If it is not a minor configuration, that new configuration is added to the main configuration.
	*/
	public static function build($source, $minor=false) {
		if( !$minor ) {
			if( !isset(static::$main) ) {
				$newConf = new static();
				static::$main = $newConf;
				$GLOBALS['CONFIG'] = &$main;
			}
			static::$main->load($source);
			return static::$main;
		} else {
			$newConf = new static();
			$newConf->load($source);
			return $newConf;
		}
	}
	
	//! Gets configuration from the main configuration object.
	/*!
		\param $key The key to get the value.
		\return A config value.
		
		Calls __get() method from main configuration object.
	*/
	public static function get($key) {
		if( !isset(static::$main) ) {
			throw new Exception('No Main Config');
		}
		return static::$main->$key;
	}
}
