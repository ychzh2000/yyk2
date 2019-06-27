<?php
$phar = new Phar('yyk.phar', 0, 'yyk.phar');
$phar->buildFromDirectory(dirname(__FILE__) . '/yyk2');
$phar->setStub($phar->createDefaultStub('yyk.php', 'yyk.php'));
$phar->compressFiles(Phar::GZ);
