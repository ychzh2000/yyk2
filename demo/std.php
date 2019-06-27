<?php
// require('vendor/autoload.php');
require('../src/yyk.php');
// require('phar://yyk.phar');
yyk\Std::start('.', './config.php', true);

$param = yyk\Data::get($_GET['_s'], yyk\Data::Str, '');
echo $param;
$db1 = new yyk\Table('test');
$data = $db1->getList(array('id'=>2));
var_dump($data);