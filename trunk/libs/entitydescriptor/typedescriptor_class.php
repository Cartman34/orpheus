<?php

class TypeDescriptor {

	protected $name;
	protected $parent;
	protected $argsParser;
	protected $validator;
	protected $formatter;
	
	public function __construct($name, $parent, $argsParser, $validator=null, $formatter=null) {
		$this->name			= $name;
		$this->parent		= $parent;
		$this->argsParser	= $argsParser;
		$this->validator	= $validator;
		$this->formatter	= $formatter;
	}
	
	public function parseArgs($args) {
// 		text('Parse Args');
// 		text($fArgs);
		return call_user_func($this->argsParser, $args);
	}
	
	public function validate($args, &$value) {
		if( isset($this->parent) ) {
			$this->parent->validate($args, $value);
		} else
		if( !isset($this->validator) ) {
			throw new Exception('noValidator');
		}
		if( isset($this->validator) ) {
			call_user_func($this->validator, $args, &$value);
// 			text("Called validator, got value: $value");
		}
	}
	
	public function format($args, &$value) {
		if( isset($this->parent) ) {
			$this->parent->format($args, $value);
		}
		if( isset($this->formatter) ) {
// 			text("Call formatter with value: $value");
			call_user_func($this->formatter, $args, &$value);
		}
	}
}

