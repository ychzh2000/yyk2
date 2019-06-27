<?php
namespace yyk;
class Redis{
	private  $redis;
	private  $config;
	private static $instance;

	public static function create(){
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}

	private function __construct(){
		if (!isset(Yyk::$config['redis'])) {
			return false;
		}
		$this->config = Yyk::$config['redis'];
		self::server(0);
	}

	public function server($n=0){
		if (isset($this->config[$n]) && is_array($this->config[$n])) {
			
		}
		else{
			return false;
		}
		if (is_object($this->redis)) {
			$this->redis->quit();
		}

		$this->redis = new \Redis();
		if ($this->redis->connect($this->config[$n]['host'], $this->config[$n]['port']) == false) {
			throw new Exception($this->redis->getLastError());
		}
		if (isset($this->config[$n]['username']) && isset($this->config[$n]['password']) && strlen($this->config[$n]['username'])>0 ) {
			if ($this->redis->auth($this->config[$n]['username'] . ":" . $this->config[$n]['password']) == false) {
				throw new Exception($this->redis->getLastError());
			}
		}
		return self::$instance;
	}

	public function __call($name, $arguments) {
		try{
			if (is_object($this->redis) && $this->redis->ping() == '+PONG') {
				return call_user_func_array(array($this->redis, $name), $arguments); 
			}
			else{
				return false;
			}
		}
		catch(Exception $e){
			return false;
		}
	}
}