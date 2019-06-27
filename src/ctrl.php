<?php
namespace yyk;
class Ctrl
{
	protected static $tplParam=array();	//用于模板输出的参数
	public function __construct(){
		//echo 'construct';
	}

	public function __call($name, $arguments) {
		//Error::
		E( $name . '方法不存在');
	}

	public function assign($key, $value) {//定义用于输出的参数
		self::$tplParam[$key] = $value;
	}

	public function display($tplFile='', $param=array(), $htmlCache=0) {	//输出模板
		header('Server: Microsoft-IIS/8.0');
    header('X-Powered-By: ASP.NET');
    // var_dump(debug_backtrace());
    $trace = debug_backtrace();
    $class = substr($trace[1]['class'], 0, -4);
    $method= $trace[1]['function'];
		//检查模板文件名
		// if (!isset($tplFile) || strlen($tplFile)==0){
		// 	$tplFile = YYK::$APP_path . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName . '.html';
		// }


    
    if (empty($tplFile)) {
      $tplFile = realpath('view' . DIRECTORY_SEPARATOR . $class . DIRECTORY_SEPARATOR . $method . '.php');
    } else {
      $tplFile = realpath($tplFile);
    }
		if(!file_exists($tplFile)){
			throw new \Exception($tplFile . '模板文件不存在');
    }
		if (!isset($param) || count($param)==0) {
			$param = self::$tplParam;
		}
		// $tplCache = YYK::$APP_path . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . YYK::$ctrlName . DIRECTORY_SEPARATOR . md5_file($tplFile) . '.php';
		// if (YYK::$debug || !file_exists($tplCache)) {
		// 	//调用模板引擎,生成模板缓存
		// 	YYKTpl::complie($tplFile, $param);
		// }

		// //检查是否开启了模板缓存
		// $staticCacheRoute = strstr($_SERVER['REQUEST_URI'], YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName);
		// $staticCacheType = false;
		// if (isset( YYK::$config['staticCache'][$staticCacheRoute] )) {
		// 	header("Date: " . date('D, d M Y H:i:s', time()) . ' GMT');
		// 	header("Last-Modified: " . date('D, d M Y H:i:s', time()) . ' GMT');
		// 	header("Cache-Control: max-age=" . YYK::$config['staticCache'][$staticCacheRoute]);
		// 	header("Expires: ".date('D, d M Y H:i:s', time() + YYK::$config['staticCache'][$staticCacheRoute]) . ' GMT');

		// 	$staticCacheType = isset(YYK::$config['staticCacheType']) ? YYK::$config['staticCacheType'] : 'FS';

		// 	switch(strtolower($staticCacheType)) {
		// 		case 'redis':
		// 			ob_start(array(&$this, 'staticRedis'));
		// 			break;
		// 		case 'memcache':
		// 			ob_start(array(&$this, 'staticMem'));
		// 			break;
		// 		default:	//默认文件系统缓存
		// 			ob_start(array(&$this, 'staticFS'));
		// 			break;
		// 	}
		// }

		/*
		if ($htmlCache){
			ob_start(array(&$this, 'saveHtml'));
		}
		*/
		//echo 'start';
		extract($param, EXTR_OVERWRITE);
		include_once($tplFile);

		// if ($staticCacheType){
		// 	$content =ob_get_contents();
		// 	ob_end_flush();
		// 	echo $content;
		// }
		/*
		$key = 'staticCache' . str_replace(DIRECTORY_SEPARATOR, '_', YYK::$APP_path);
		$field = strstr($_SERVER['REQUEST_URI'], YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName);
		$field = str_replace(DIRECTORY_SEPARATOR, '_', $field);
		die($key.$field);
		*/
		//清除参数?
	}

	private function staticRedis($content){
		$staticCacheRoute = strstr($_SERVER['REQUEST_URI'], YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName);
		$staticCacheKey = 'staticCache' . str_replace(DIRECTORY_SEPARATOR, '_', YYK::$APP_path) . str_replace(DIRECTORY_SEPARATOR, '_', $staticCacheRoute);
		$redis = CacheRedis::create();
		$redis->set($staticCacheKey, $content);
		$redis->expire($staticCacheKey, YYK::$config['staticCache'][$staticCacheRoute]) ;
	}

	private function staticMem($content){
		$staticCacheRoute = strstr($_SERVER['REQUEST_URI'], YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName);
		$staticCacheKey = 'staticCache' . str_replace(DIRECTORY_SEPARATOR, '_', YYK::$APP_path) . str_replace(DIRECTORY_SEPARATOR, '_', $staticCacheRoute);
		$memcache = CacheMem::create();
		$memcache->set($staticCacheKey, time().','.$content, YYK::$config['staticCache'][$staticCacheRoute]);
	}

	private function staticFS($content){
		$tplCache = YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName . '.html';
		if (!file_exists(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html')) {
			mkdir(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html');
		}
		if (!file_exists(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName)) {
			mkdir(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName);
		}
		file_put_contents($tplCache, $content);
	}
	
	public function saveHtml($content){
		$tplCache = YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName . DIRECTORY_SEPARATOR . YYK::$methodName . '.html';
		if (!file_exists(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html')) {
			mkdir(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html');
		}
		if (!file_exists(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName)) {
			mkdir(YYK::$APP_path . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . YYK::$ctrlName);
		}
		file_put_contents($tplCache, $content);
	}

	public function requestMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}

	public function isGet(){
		if ( strtoupper($this->requestMethod()) == 'GET') {
			return true;
		}
		else
			return false;
	}

	public function isPost(){
		if ( strtoupper($this->requestMethod()) == 'POST') {
			return true;
		}
		else
			return false;
	}

	public function isPut(){
		if ( strtoupper($this->requestMethod()) == 'PUT') {
			return true;
		}
		else
			return false;
	}

	public function isDelete(){
		if ( strtoupper($this->requestMethod()) == 'DELETE') {
			return true;
		}
		else
			return false;
	}

	// php 判断是否为 ajax 请求 
	public function isAjax(){
		if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
			return true;
		}else{ 
			return false;
		}
	}
}