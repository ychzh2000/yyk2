<?php
return array(
    'pathinfoSeparator' => '/', //url路径及参数分隔符
    'timeZone'          => 'Asia/Shanghai', //定义时区
    'switchRoute'       => true, //是否开启路由模式  pathinfo伪静态
    //'urlFix'            =>    '.html',
    'routeRule'         => array( //路由规则
    ),
    'db'               => array(
        '0'      => array(
            'type'     => 'pdo',
            'pdo'      => 'mysql:dbname=test;host=127.0.0.1',
            'username' => 'root',
            'password' => 'root',
        ),
        'common' => array(
            'charset' => 'utf8mb4',
        ),

    ),
    'redis'        => array(
        array(
            'host' => '192.168.0.248',
            'port' => 6379,
            // 'username'    => 'root',
            // 'password'    => '1234',
        ),
    ),

);
