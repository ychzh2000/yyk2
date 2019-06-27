<?php
namespace yyk;
class Pdo extends Database
{
  public static function create($config)
  {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c($config);
		}
		return self::$instance;
  }
  
  private function __construct($config)
  {
		$this->config = $config;
	}

	public function connect($no) {
		$no = is_numeric($no) ? $no : 0;

		try{
			$this->conn[$no] = new \PDO($this->config[$no]['pdo'], $this->config[$no]['username'], $this->config[$no]['password']);
			$this->charset($this->config['common']['charset']);
			return self::$instance;
		}
		catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
  }

  
	private function clearParam(){
		$this->currentTab	= null;
		$this->currentOper	= null;	 	//当前操作 select/update/delete/insert
		$this->currentWhere	= null;	//当前操作条件
		$this->currentSet	= null;		//update操作时要set的部分
		$this->currentOrder	= null;	//order子句
		$this->currentGroup	= null;	//group子句
		$this->currentHaving	= null;	//having子句
		$this->currentField	= null;	//所需字段
		$this->currentLimit = null;
	}

	public function server($no){
		$no = is_numeric($no) ? $no : 0;
		$this->currentSrv = $no;
		return self::$instance;
	}

	public function setDb($dbname){
		$this->conn[$this->currentSrv]->exec('use '. $dbname);//  ??  inject
		$this->currentDb = $dbname;
		return self::$instance;
	}

	public function charset($charset){
		$this->conn[$this->currentSrv]->exec('set names '. $charset);//  ??  inject
		return self::$instance;
	}

	public function beginTransaction(){
		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		return $this->conn[$this->currentSrv]->beginTransaction();
	}

	public function commit(){
		return $this->conn[$this->currentSrv]->commit();
	}

	public function rollBack(){
		return $this->conn[$this->currentSrv]->rollBack();
	}

	public function table($tabName){
		$this->currentTab = $tabName;
		return self::$instance;
	}

	public function field($fieldName){
		if (is_array($fieldName)) {
			$this->currentField = implode(',', $fieldName);
		}
		else
			$this->currentField = $fieldName;
		return self::$instance;
	}

	public function where($where){
		$this->currentWhere = $where;
		return self::$instance;
	}

	public function order($order){
		$this->currentOrder = $order;
		return self::$instance;
	}

	public function group($group){
		$this->currentGroup = $group;
		return self::$instance;
	}

	public function limit($limit){
		$this->currentLimit = $limit;
		return self::$instance;
	}
	//构造select语句
	private function constructSelect(){
		$sql = 'select ';
		if (strlen($this->currentField)) {
			$sql .= $this->currentField;
		}
		else{
			$sql .= '*';
		}

		$sql .= ' from ' . $this->currentTab ;

		if ((is_array($this->currentWhere) && count($this->currentWhere)) || strlen($this->currentWhere) ) {
			$sql.= ' where ';
			if (is_array($this->currentWhere)){
				foreach ($this->currentWhere as $key => $value) {
					$sql .= '`'.$key . '`' . '=:' . $key . ' and ';
				}
				$sql = substr($sql, 0, strlen($sql)-5);
			}
			else
				$sql .= $this->currentWhere;
		}

		//group
		if (strlen($this->currentGroup)) {
			$sql .= ' group by ' . $this->currentGroup;
		}

		//having
		if (strlen($this->currentHaving)) {
			$sql .= ' having ' . $this->currentHaving;
		}

		//order
		if (strlen($this->currentOrder)) {
			$sql .= ' order by ' . $this->currentOrder;
		}

		//limit
		if (strlen($this->currentLimit)) {
			$sql .= ' limit ' . $this->currentLimit;
		}

		//var_dump($sql);
		$this->lastSql = $sql;

		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		$sth = $this->conn[$this->currentSrv]->prepare($sql);	//, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
		//var_dump($this->currentWhere);
		$this->lastData = $this->currentWhere;
		if (is_array($this->currentWhere)) {
			$sth->execute($this->currentWhere);
		}
		else{
			$sth->execute();
		}
		return $sth;
	}

	public function select(){
		$sth = $this->constructSelect();
		$re = $sth->fetchAll(\PDO::FETCH_ASSOC);
		$sth->closeCursor();

		//清空当前字段
		$this->clearParam();
		return $re;
	}

	//取一行，返回一维数组
	public function getLine(){
		$sth = $this->constructSelect();
		$re = $sth->fetch(\PDO::FETCH_ASSOC);
		$sth->closeCursor();

		//清空当前字段
		$this->clearParam();
		return $re;
	}

	//取一个字段，返回简单变量
	public function getField($fieldName){
		$sth = $this->constructSelect();
		$re = $sth->fetch(\PDO::FETCH_ASSOC);
		$sth->closeCursor();

		//清空当前字段
		$this->clearParam();
		return $re[$fieldName];
	}
	public function getFiled($fieldName){
		return $this->getField($fieldName);
	}

	//
	public function count(){
		$this->currentField = 'count(*)';
		$sth = $this->constructSelect();
		$re = $sth->fetch(PDO::FETCH_NUM);
		$sth->closeCursor();

		//清空当前字段
		$this->clearParam();
		return $re[0];
	}

	public function sum($field){
		$this->currentField = "sum({$field})";
		$sth = $this->constructSelect();
		$re = $sth->fetch(PDO::FETCH_NUM);
		$sth->closeCursor();

		//清空当前字段
		$this->clearParam();
		return $re[0];
	}

	//insert
	public function insert($data){
		$sql = 'insert into `' . $this->currentTab . '` ( ';
		if (is_array($data)){
			foreach ($data as $key => $value) {
				$sql .= '`' . $key . '`' . ' ,';
			}
			$sql = rtrim($sql, ',');
			$sql .= ') values(';
			foreach ($data as $key => &$value) {
				$sql .= ':' . $key . ' ,';
				$value = $this->filter($value);
			}
			$sql = rtrim($sql, ',');
			$sql .= ')';
		}
		$this->lastSql = $sql;
		$this->lastData = $data;

		try{
			if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
				$this->connect($this->currentSrv);
			}
			$sth = $this->conn[$this->currentSrv]->prepare($sql);
			$sth->execute($data);
			$rows = $sth->rowCount ();
			$sth->errorInfo();
		}
		catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
		}
		$lastId = $this->conn[$this->currentSrv]->lastInsertId();
		$result = $lastId?$lastId:$rows;
		$this->clearParam();
		return $result;
	}


	public function update($data){
		$sql = 'update ' . $this->currentTab . ' set ';

		if (is_array($data)){
			foreach ($data as $key => $value) {
				$sql .= '`' . $key . '`=:' . $key . ' ,';
				$data[$key] = $this->filter($value);
			}
			$sql = rtrim($sql, ',');
			$sql .= ' ';
		}

		if ((is_array($this->currentWhere) && count($this->currentWhere)) || strlen($this->currentWhere) ) {
			$sql.= 'where ';
			if (is_array($this->currentWhere)){
				foreach ($this->currentWhere as $key => $value) {
					$sql .= $key . '=:w_' . $key . ' and ';
					$data['w_'.$key] = $value;
				}
				$sql = substr($sql, 0, strlen($sql)-5);
			}
			else
				$sql .= $this->currentWhere;
		}
		//var_dump($data);
		$this->lastSql = $sql;
		$this->lastData = $data;
		//echo $sql;
		//var_dump($data);

		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		$sth = $this->conn[$this->currentSrv]->prepare($sql);
		$sth->execute($data);
		$this->clearParam();
		return $sth->rowCount();
		//exit;
	}

	public function delete(){
		$sql = 'delete from ' . $this->currentTab ;

		if ((is_array($this->currentWhere) && count($this->currentWhere)) || strlen($this->currentWhere) ) {
			$sql.= ' where ';
			if (is_array($this->currentWhere)){
				foreach ($this->currentWhere as $key => $value) {
					$sql .= $key . '=:w_' . $key . ' and ';
					$data['w_'.$key] = $value;
				}
				$sql = substr($sql, 0, strlen($sql)-5);
			}
			else{
				$sql .= $this->currentWhere;
				$data = array();
			}
		}

		//echo $sql;
		//var_dump($data);

		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		$sth = $this->conn[$this->currentSrv]->prepare($sql);
		$sth->execute($data);
		$this->clearParam();
		return $sth->rowCount();
	}

	/*
	*直接执行sql语句
	*
	*return select返回结果集，insert成功返回最后插入id
	*		其他情况返回受影响条数
	*/
	public function query($sql,$data=array()){
		$this->lastSql = $sql;
		$this->lastData = $data;
		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		if(is_array($data)){
			$sth = $this->conn[$this->currentSrv]->prepare($sql);
			$sth->execute($data);
		}else{
			$sth = $this->conn[$this->currentSrv]->query($sql);
		}
		if(strtolower(substr($sql,0,6))=='select'){
			$re = $sth->fetchAll(\PDO::FETCH_ASSOC);
			/*
			while($result=$sth->fetch(PDO::FETCH_ASSOC)){
				$re[]=$result;
			}
			if(stripos($sql,'count')&&!stripos($sql,'order by')&&!stripos($sql,'group by')&&!stripos($sql,'limit ')){
				$re = $re[0];
			}
			*/
		}else{
			$re = $sth->rowCount();
			if(strtolower(substr($sql,0,6))=='insert'){
				$lastId='';
				$lastId = $this->conn[$this->currentSrv]->lastInsertId();
				$re = $lastId?$lastId:$re;
			}
		}
		$sth->closeCursor();
		return $re;
	}


	public function setInc($field, $num=1){
		$sql = 'update `' . $this->currentTab . '` set `' . $field . '`=`'. $field .'`+'.$num;

		if ((is_array($this->currentWhere) && count($this->currentWhere)) || strlen($this->currentWhere) ) {
			$sql.= ' where ';
			if (is_array($this->currentWhere)){
				foreach ($this->currentWhere as $key => $value) {
					$sql .= $key . '=:w_' . $key . ' and ';
					$data['w_'.$key] = $value;
				}
				$sql = substr($sql, 0, strlen($sql)-5);
			}
			else
				$sql .= $this->currentWhere;
		}
		//var_dump($data);
		$this->lastSql = $sql;
		$this->lastData = $data;
		//echo $sql;
		//var_dump($data);

		if (!isset($this->conn[$this->currentSrv]) || !($this->conn[$this->currentSrv])) {
			$this->connect($this->currentSrv);
		}
		$sth = $this->conn[$this->currentSrv]->prepare($sql);
		$this->clearParam();
		$sth->execute($data);
	}

	//count
	//max/min/distinct

	public function last(){
    echo $this->lastSql;
    echo "<br>\n";
    echo json_encode($this->lastData);
    echo "<br>\n";
    $str = $this->lastSql;
    $data = (array)$this->lastData;
    foreach ( $data as $key => $value) {
      $str = str_replace(':'.$key, $value, $str);
    }
    echo 'SQL: ' . $str;
	}

}
