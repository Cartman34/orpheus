<?php

class AdminUserListController extends AdminController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {

		/* @var $USER SiteUser */
		global $USER;
// 		global $USER_CLASS;
		$userDomain	= SiteUser::getDomain();
		
// 		$formData = array();
		if( $request->hasData('createUser') ) {
		
			try {
// 				$formData = POST('createData');
				$newUser = SiteUser::create($request->getArrayData('createUser'));
				reportSuccess('createUser', $userDomain);
// 				$formData = array();
		
			} catch(UserException $e) {
				reportError($e, $userDomain);
			}
		}
		
		$USER_CAN_USER_EDIT	= $USER->canUserEdit();
		
		$users = SiteUser::get(array(
				'where'		=> $USER->canSeeDevelopers() ? '' : 'accesslevel<='.Config::get('user_roles/administrator'),
				'orderby'	=> 'fullname ASC',
				'output'	=> SQLAdapter::ARR_OBJECTS
		));
		
		return HTMLHTTPResponse::render('app/admin_userlist', array(
			'USER_CAN_USER_EDIT'	=> $USER_CAN_USER_EDIT,
			'users'	=> $users
		));
	}

}
