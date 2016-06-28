<?php


use Orpheus\InputController\HTTPController\HTTPController;
use Orpheus\InputController\HTTPController\HTTPRequest;
use Orpheus\InputController\HTTPController\HTMLHTTPResponse;

class DownloadController extends HTTPController {
	
	/**
	 * @param HTTPRequest $request The input HTTP request
	 * @return HTTPResponse The output HTTP response
	 * @see HTTPController::run()
	 */
	public function run(HTTPRequest $request) {
		
		$downloadURL = GlobalConfig::instance()->get($request->hasParameter('releases') ? 'releases_url' : 'download_url');
		debug('$downloadURL => '.$downloadURL);
		die();

		return HTMLHTTPResponse::render('app/home');
// 		return new RedirectHTTPResponse($downloadURL ? $downloadURL : 'home');
	}

	
}
