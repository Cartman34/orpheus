<?php
//! The HTML rendering class
/*!
	A basic class to render HTML using PHP scripts.
*/
class HTMLRendering extends Rendering {
	
	protected static $SHOWMODEL		= 'page_skeleton';
	
	public static $theme			= 'default';
	
	public static $cssPath			= 'css/';
	public static $modelsPath		= 'layouts/';
	
	public static $cssURLs			= array();// CSS files
	public static $jsURLs			= array();// Javascript files
	public static $metaprop			= array();// Meta-properties
	
	//! Renders the model.
	/*!
		\copydoc Rendering::render()
	*/
	public function render($model=null, $env=array()) {
		ob_start();
		$this->display($model, $env);
		return ob_get_clean();
	}

	//! Displays the model.
	/*!
	 \copydoc Rendering::display()
	*/
	public function display($model=null, $env=array()) {
		if( $model === NULL ) {
			throw new Exception("Invalid Rendering Model");
		}
		extract($env, EXTR_SKIP);
		
		include static::getModelPath($model);
	}
	
	public static function getModelPath($model) {
		return static::getModelsPath().$model.'.php';
	}
	
	public static function renderReport($report, $type, $stream) {
		$report	= nl2br($report);
		if( file_exists(static::getModelPath('report-'.$type)) ) {
			return static::doRender('report-'.$type, array('Report'=>$report, 'Type'=>$type, 'Stream'=>$stream));
		}
		if( file_exists(static::getModelPath('report')) ) {
			return static::doRender('report', array('Report'=>$report, 'Type'=>$type, 'Stream'=>$stream));
		}
		return '
		<div class="report report_'.$stream.' '.$type.'">'.nl2br($report).'</div>';
	}
	
	public static function addCSSFile($filename) {
		static::addCSSURL(static::getCSSURL().$filename);
	}
	public static function addCSSURL($url) {
		static::$cssURLs[]	= $url;
	}
	
	public static function addJSFile($filename) {
		static::addJSURL(JSURL.$filename);
	}
	public static function addJSURL($url) {
		static::$jsURLs[]	= $url;
	}
	
	public static function addMetaProperty($property, $content) {
		static::$metaprop[$property] = $content;
	}
	
	//! Gets the theme path.
	/*!
		\return The theme path.
		
		Gets the path to the current theme.
	*/
	public static function getThemePath() {
		return THEMESDIR.static::$theme.'/';
	}
	
	//! Gets the absolute theme path.
	/*!
		\return The theme path.
		
		Gets the absolute path to the current theme.
	*/
	public static function getAbsThemePath() {
		return pathOf(static::getThemePath());
	}
	
	//! Gets the models theme path.
	/*!
		\return The models theme path.
		
		Gets the path to the models.
	*/
	public static function getModelsPath() {
		return pathOf(static::getThemePath().static::$modelsPath);
	}

	//! Gets the css theme path.
	/*!
		\return The css theme path.
		
		Gets the path to the css files.
	*/
	public static function getCSSPath() {
		return pathOf(static::getThemePath().static::$cssPath);
	}

	//! Gets the theme path.
	/*!
		\return The theme path.
		
		Gets the URL to the current theme.
	*/
	public static function getThemeURL() {
		return THEMESURL.static::$theme.'/';
	}

	//! Gets the CSS files path.
	/*!
		\return The CSS path.
		
		Gets the URL to the CSS files.
	*/
	public static function getCSSURL() {
		return static::getThemeURL().static::$cssPath;
	}
}