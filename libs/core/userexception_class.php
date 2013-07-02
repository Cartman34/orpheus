<?php
//! The user exception class
/*!
	This exception is thrown when an occured caused by the user.
*/
class UserException extends Exception {
	
	private $domain;
	
	public function __construct($message=null, $domain=null) {
		parent::__construct($message);
		$this->domain = $domain;
	}
	
	public function getDomain() {
		return $this->domain;
	}
	
	public function getText() {
		return $this->getMessage();
	}
	
	public function __toString() {
		try {
			return $this->getText();
		} catch(Exception $e) {
			if( ERROR_LEVEL == DEV_LEVEL ) {
				die('A fatal error occurred in UserException::__toString() :<br />'.$e->getMessage());
			}
			die('A fatal error occurred, please report it to an admin.<br />Une erreur fatale est survenue, veuillez contacter un administrateur.<br />');
// 			reportError($e);
		}
		return '';
	}
}
