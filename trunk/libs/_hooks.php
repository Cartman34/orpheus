<?php
/*!
	\brief The hooks' default callbacks
	
	PHP File containing default registering of hooks' callbacks.
 */

//! Callback for Hook 'runModule'
Hook::register('runModule', function ($Module) {
	//If user try to override url rewriting and the requested page is not root.
	if( empty($_SERVER['REDIRECT_rewritten']) && $_SERVER['REQUEST_URI'] != '/' && $Module != 'remote' ) {
		if( $Module == DEFAULTMOD ) {
			$redirLink = './';
		} else {
			$redirLink = $Module.'.html';
		}
		permanentRedirectTo($redirLink);
	}
	// If the module is the default but with wrong link.
	if( $Module == DEFAULTMOD && empty($Action) && $_SERVER['REQUEST_URI'] != '/' ) {
		permanentRedirectTo(DEFAULTLINK);
	}
});

//! Callback for Hook 'checkModule'
Hook::register('checkModule', function () {
	if( User::is_login() ) {
		//global $USER;// Do not work in this context.
		$GLOBALS['USER'] = &$_SESSION['USER'];
	}
	$GLOBALS['ACCESS'] = Config::build('access', true);
	$GLOBALS['RIGHTS'] = Config::build('rights', true);
});

// Publisher library

//! Callback for Hook 'runModule'
Hook::register('runModule', function () {
	if( !User::canAccess($GLOBALS['Module']) ) {
		redirectTo((( defined('ACCESSDENIEDMOD') ) ? ACCESSDENIEDMOD : DEFAULTMOD).'.html');
	}
});