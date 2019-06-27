<?php
namespace yyk;
class Database
{
  protected static $instance;
	protected $conn = array();//数据库连接句柄

	protected $config;		//数据库配置
	protected $currentSrv=0;	//当前(默认)服务器(配置编号)
	protected $currentDb;	//当前(默认)数据库名

	protected $currentTab;	//当前访问数据表

	protected $currentOper;	//当前操作 select/update/delete/insert
	protected $currentWhere;	//当前操作条件
	protected $currentSet;		//update操作时要set的部分
	protected $currentOrder;	//order子句
	protected $currentGroup;	//group子句
	protected $currentHaving;	//having子句
	protected $currentField;	//所需字段
	protected $currentLimit;
	protected $lastSql;
	protected $lastData;

}