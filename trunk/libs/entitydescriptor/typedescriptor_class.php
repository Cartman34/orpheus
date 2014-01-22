<?php

class TypeDescriptor {

	protected $name;
	protected $parent;
	protected $argsParser;
	protected $validator;
	protected $formatter;
	protected $writable;
	protected $nullable;
	
	public function __construct($name, $parent, $argsParser, $validator=null, $formatter=null, $writable=null, $nullable=null) {
		$this->name			= $name;
		$this->parent		= $parent;
		$this->argsParser	= $argsParser;
		$this->validator	= $validator;
		$this->writable		= $writable;
		$this->nullable		= $nullable;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function isWritable() {
		if( isset($this->writable) ) {
			return $this->writable;
		}
		if( isset($this->parent) ) {
			return $this->writable = $this->parent->isWritable();
		}
		return null;
	}
	
	public function isNullable() {
		if( isset($this->nullable) ) {
			return $this->nullable;
		}
		if( isset($this->parent) ) {
			return $this->nullable = $this->parent->isNullable();
		}
		return null;
	}
	
	public function isType($type) {
		return $this->name==$type;
	}
	
	public function knowType($type) {
		return $this->isType($type) || (isset($this->parent) && $this->parent->knowType($type));
	}
	
	public function parseArgs($args) {
		return isset($this->argsParser) ? call_user_func($this->argsParser, $args) : new stdClass();
	}
	
	public function validate($args, &$value, $inputData) {
		if( isset($this->parent) ) {
			$this->parent->validate($args, $value, $inputData);
		} else
		if( !isset($this->validator) ) {
			throw new Exception('noValidator');
		}
		if( isset($this->validator) ) {
			call_user_func_array($this->validator, array($args, &$value, $inputData));
		}
	}
	
	public function format($args, &$value) {
		if( isset($this->parent) ) {
			$this->parent->format($args, $value);
		}
		if( isset($this->formatter) ) {
			call_user_func_array($this->formatter, array($args, &$value));
		}
	}
}

