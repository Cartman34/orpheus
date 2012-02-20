<?php
abstract class SQLMapper {
	
	protected static $IDFIELD;
	
	//Defaults for selecting
	protected static $selectDefaults = array();
	//Defaults for updating
	protected static $updateDefaults = array();
	
	//List of outputs for getting list
	const ARR_OBJECTS	= 1;
	const ARR_ASSOC		= 2;
	const STATEMENT		= 3;
	const SQLQUERY		= 4;
	
	public abstract static function select(array $options=array()) {}
}

includeDir();