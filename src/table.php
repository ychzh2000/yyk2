<?php
namespace yyk;
class Table
{
  protected $db;
  protected $table;
  public function __construct($tab)
  {
    if (!isset(Yyk::$config['db'][0])) {
      die('no database config');
    }

    $config = Yyk::$config['db'];
    switch ($config[0]['type']) {
      case 'pdo':
        $this->db = Pdo::create($config);
        break;
      case 'mysqli':
        break;
      default:
    }
    $this->db = $this->db->table($tab);
    $this->table = $tab;
  }

  public function add($data)
  {
    $r = $this->db->table($this->table)->insert($data);
    return $r;
  }

    public function del($where)
    {
      $r = $this->db->table($this->table)->where($where)->delete();
      return $r;
    }

    public function save($where, $data)
    {
      return $this->db->table($this->table)->where($where)->update($data);
    }

    public function getCount($where=1)
    {
      return $this->db->table($this->table)->where($where)->count();
    }

    public function getSum($field, $where=1)
    {
      return $this->db->table($this->table)->where($where)->sum($field);
    }

    public function getList($where=1, $order='', $page=1, $pageSize=0)
    {
      $pdo = $this->db->table($this->table)->where($where);
      if (!empty($order)) {
          $pdo = $pdo->order($order);
      }
      if ($pageSize>0) {
          $pdo = $pdo->limit(($page-1)*$pageSize . ', ' .$pageSize);
      }
      $arr = $pdo->select();
      // $pdo->last();
      return $arr;
    }

    public function query($sql)
    {
      return $this->db->query($sql);
    }

    public function last()
    {
      $this->db->last();
    }

    public function __call($name, $arguments)
    {
      if (substr($name, 0, 5) == 'getBy') {
        $where[substr($name, 5)] = $arguments[0];
        return $this->db->table($this->table)->where($where)->getLine();
      }
    }
}
