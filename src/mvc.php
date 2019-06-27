<?php
namespace yyk;
class Mvc extends Yyk
{
  public static function start($appPath='.', $configPath='./config.php', $debug=true)
  {
    parent::start($appPath, $configPath, $debug);
    if (!$debug) {
      error_reporting(0);
    } else {
			set_error_handler('yyk\std::error', E_ALL);
			set_exception_handler('yyk\std::exception');
		}
    
    //启动session
    session_start();
    
    //路由分析
		$requestRoute = self::parseRoute();
		$ctrlName = $requestRoute[0];
    $methodName = $requestRoute[1];
    
    $ctrlName = $requestRoute[0] . 'Ctrl';

    //检查路由是否存在
    $ctrlPath = realpath($appPath . DIRECTORY_SEPARATOR . 'ctrl' . DIRECTORY_SEPARATOR . $ctrlName . '.php');
		if (file_exists($ctrlPath)) {
      require_once($ctrlPath);
			$C = new $ctrlName();
			if (method_exists($C, $methodName)){
				call_user_func(array($C, $methodName));
			}
			else{
				throw new \Exception('no method');
			}
		}
		else{
			//控制器不存在
			throw new \Exception('no controller');
		}
  }

  //路由分析
	public static function parseRoute(){
		if (isset($_GET['_s']) && strlen($_GET['_s'])>0){
			$_SERVER['PATH_INFO'] = $_GET['_s'];
		}

		if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])>1){
			$pathInfo = $_SERVER['PATH_INFO'];
			if (isset(Yyk::$config['urlFix'])) {
				if (substr($pathInfo, 0- strlen(Yyk::$config['urlFix'])) == Yyk::$config['urlFix']) {
					$pathInfo = substr($pathInfo, 0, 0- strlen(Yyk::$config['urlFix']));
				}
			}
			$pathInfo = trim($pathInfo, '/');

			$arr = explode(self::$config['pathinfoSeparator'], trim($pathInfo, '/'));

			if (self::$config['switchRoute'] && count($arr)>0){
				//检查是否在路由定义中

				//精确匹配
				$tmp = preg_grep("/^{$arr[0]}$/", array_keys(self::$config['routeRule']));
				//模糊匹配
				$tmp2 = preg_grep("/^{$arr[0]}:/", array_keys(self::$config['routeRule']));

				if (count($arr)==1){	//路由规则无参数
					if (count($tmp)>0) {//精确匹配
						return self::$config['routeRule'][$tmp[0]];
					}
					if (count($tmp2)>0) {
						foreach ($tmp2 as $key => $value) {
							$param = explode(':', $value);
							echo count($param) .','. count($arr) .';';
							if (count($param) == count($arr)) {
								return self::$config['routeRule'][$value];
							}
						}

						foreach ($tmp2 as $key => $value) {
							return self::$config['routeRule'][$value];
						}
					}
				}

				if (count($arr) > 1){	//带参数
					if (count($tmp2)>0) {
						foreach ($tmp2 as $key => $value) {
							$param = explode(':', $value);
							if (count($param) == count($arr)) {
								for ($i=1; ; $i++) {
									if(!isset($arr[$i])) break;
									$_GET[$param[$i]] = $arr[$i];
								}                                
								return self::$config['routeRule'][$value];
							}
						}
						foreach ($tmp2 as $key => $value) {
							for ($i=1; ; $i++) {
								if(!isset($arr[$i])) break;
								$_GET[$param[$i]] = $arr[$i];
							}
							return self::$config['routeRule'][$value];
						}
					}

					if (count($tmp)>0) {//精确匹配
						return self::$config['routeRule'][$tmp[0]];
					}
				}
			}
			//pathinfo非路由
			if ((!isset($arr[0])) || strlen($arr[0])==0) {
				$arr[0] = 'Index';
			}
			if ((!isset($arr[1])) || strlen($arr[1])==0) {
				$arr[1] = 'index';
			}
			for ($i=2; ; $i+=2) {
				if(!isset($arr[$i])) break;
				$_GET[$arr[$i]] = $arr[$i+1];
			}
			return array($arr[0], $arr[1]);
		}
		else{	//非pathinfo模式
			if (isset($_GET['c']))
				$c = $_GET['c'];
			else
				$c = 'Index';
			if (isset($_GET['m']))
				$m = $_GET['m'];
			else
				$m = 'index';
			return array($c, $m);
		}
	}

  public static function error($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile){
		if (self::$debug) {
			echo "<b>Custom error:</b> [$fehlercode] $fehlertext<br>\n";
			echo "Error on line $fehlerzeile in $fehlerdatei<br />";
		}
		return true;
	}

	public static function exception($exception){
		if (self::$debug) {
			echo "Uncaught exception: " , $exception->getMessage(), "<br>";
		}
		return true;
	}
}
