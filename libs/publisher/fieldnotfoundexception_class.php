<?php
//! The field not found exception class
/*!
	This exception is thrown when a field is not found in a set.
*/
class FieldNotFoundException extends Exception {
	
	private $fieldname;
	private $source;
	
	//! Constructor
	/*!
		\param $fieldname The name of the missing field.
	*/
	public function __construct($fieldname, $source=null) {
		parent::__construct('fieldNotFound['.(isset($source) ? $source.'-' : '').$fieldname.']', 1001);
		$this->fieldname	= (string) $fieldname;
		$this->source		= (string) $source;
	}
	
	//! Gets the field name
	/*!
		\return The field name.
	*/
	public function getFieldName() {
		return $this->fieldname;
	}
	
	//! Gets the source
	/*!
		\return The source of the exception.
	*/
	public function getSource() {
		return $this->source;
	}
}
