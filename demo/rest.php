<?php
// require('vendor/autoload.php');
require('../src/yyk.php');
// require('phar://yyk.phar');
yyk\Restful::start('.', './config.php', true);
