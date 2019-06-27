<?php
namespace yyk;
class Restful extends Yyk
{
  public static function start($appPath='.', $configPath='./config.php', $debug=true)
  {
    parent::start($appPath, $configPath, $debug);
    if (!$debug) {
      error_reporting(0);
    } else {
			set_error_handler('yyk\Restful::error', E_ALL);
			set_exception_handler('yyk\Restful::exception');
		}
    
    //路由分析
		$resourceName = self::parseRoute();

		if ($resourceName) {
			$httpMethod = ucfirst(strtolower( $_SERVER['REQUEST_METHOD'] ));
			// echo self::$httpMethod;
			header('access-control-allow-credentials:true');
			header('access-control-allow-methods:GET,HEAD,PUT,PATCH,POST,DELETE,OPTIONS');
			header('access-control-allow-origin: *');
			header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, X-Auth-Token");
			header('access-control-allow-Authorization: *');
			if (strtoupper($httpMethod) == 'OPTIONS') {
				exit();
			}
			$resourceClass = $resourceName . $httpMethod;
			
			if (file_exists($appPath . DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . $resourceName . DIRECTORY_SEPARATOR . $resourceClass . '.php')){
				unset($_REQUEST);
				$_REQUEST['GET'] = $_GET;
				if ($httpMethod != 'Get') {
					$reqMethod = '_' . strtoupper($httpMethod);
					$tmp = file_get_contents('php://input');
					if ($array = json_decode($tmp, true)) {
						$reqData = array();
						foreach ($array as $key => $value) {
							$reqData[$key] = $value;
						}
						$$reqMethod = $reqData;
					}
					else{
						parse_str($tmp, $$reqMethod);
					}
					$_REQUEST[substr($reqMethod,1)] = $$reqMethod;
				}
				else{
					$reqMethod = '_GET';
					$$reqMethod = $_GET;
        }
        require_once($appPath . DIRECTORY_SEPARATOR . 'rest' . DIRECTORY_SEPARATOR . $resourceName . DIRECTORY_SEPARATOR . $resourceClass . '.php');
				// echo $resourceClass;
				$instance = new $resourceClass();
				$instance->exec($$reqMethod, $_GET);
			}
			else{
				die('没找到对应类, 返回 404') ;
				header($_SERVER['SERVER_PROTOCOL'] . ' 404 NOT FOUND');
				exit(0);
			}
		}
		else{
			die('没有解析到资源地址');
			// self::export(['httpStatus' => 404]);
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 NOT FOUND');
			exit(0);
		}
  }

  //路由分析
	public static function parseRoute(){
		if (isset($_GET['_s']) && strlen($_GET['_s'])>0){
			$_SERVER['PATH_INFO'] = $_GET['_s'];
			// $_GET = [];
			unset($_GET['_s']);
		}

		// echo $_SERVER['PATH_INFO'];exit;
		if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])>1) {
			$pathInfo = $_SERVER['PATH_INFO'];
			$pathInfo = trim($pathInfo, '/');
			$arr = explode(Yyk::$config['pathinfoSeparator'], trim($pathInfo, '/'));

			if ((!isset($arr[0])) || strlen($arr[0])==0) {
				return false;
			}

			for ($i=1; ; $i+=2) {
				if(!isset($arr[$i+1])) break;
				$_GET[$arr[$i]] = $arr[$i+1];
			}
			return $arr[0];
		}
		else{
			// 输出错误提示或帮助信息
			return false;
		}
	}

  public static function error($fehlercode, $fehlertext, $fehlerdatei, $fehlerzeile){
		if (self::$debug) {
			echo "<b>Custom error:</b> [$fehlercode] $fehlertext";
			echo " Error on line $fehlerzeile in $fehlerdatei<br />";
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
