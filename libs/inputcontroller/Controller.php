<?php


abstract class Controller {

	/* @var $request InputRequest */
	protected $request;
	
	public function __toString() {
		return get_called_class();
	}

	/**
	 *
	 * @param InputRequest $request
	 * @return OutputResponse
	 */
	public function process(InputRequest $request) {
		// run, preRun and postRun take parameter depending on Controller, request may be of a child class of InputRequest
		$this->request	= $request;
		
		ob_start();
		$this->preRun($request);
		try {
			$result	= $this->run($request);
		} catch( UserException $e ) {
			$result	= $this->processUserException($e);
		}
		$this->postRun($request, $result);
// 		$output = ob_get_clean();
// 		debug('Got controller output => '.strlen($output));
// 		$result->setControllerOutput($output);
		$result->setControllerOutput(ob_get_clean());
		
		return $result;
	}
	
	public function processUserException(UserException $e) {
		throw $e;// Throw to request
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getRoute() {
		return $this->request->getRoute();
	}
	
	public function getRouteName() {
		return $this->request->getRouteName();
	}
	
	public function render($response, $layout, $values=array()) {
		$values['Controller']	= $this;
		$values['Request']		= $this->getRequest();
		$values['Route']		= $this->getRoute();
		$response->collectFrom($layout, $values);
		return $response;
	}
	
	
}
