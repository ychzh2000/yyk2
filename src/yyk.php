<?php
namespace yyk;
class Yyk
{
  /*配置信息*/
  public static $config;
  public static $debug;
  public static function start($appPath='.', $configPath='./config.php', $debug=true)
  {
    self::$debug = $debug;
    
    //加载用户配置
    $configPath = empty($configPath) ? "config.php" : $configPath;
		if (file_exists(realpath($configPath))) {
			self::$config = include realpath($configPath);
		} else {
      // 配置文件加载失败
      die('load config fail');
    }

    //设置时区
    self::$config['timeZone'] ? date_default_timezone_set(self::$config['timeZone']) : date_default_timezone_set('Asia/Shanghai');
    
    // spl_autoload_register('yyk\Yyk::autoload');
  }

  public static function autoload($class)
  {
    $class = strtolower($class);
    list($ns, $cn) = explode('\\', $class);
    if ($ns === 'yyk') {
      require_once(__DIR__ . DIRECTORY_SEPARATOR. $cn . '.php');
    }
  }
}

spl_autoload_register('yyk\Yyk::autoload');