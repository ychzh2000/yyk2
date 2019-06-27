<?php
namespace yyk;
class Std extends Yyk
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
