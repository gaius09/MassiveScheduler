<?php
require_once (__DIR__.'/lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', './lib/vendor/');
$loader->registerNamespace('Doctrine\ODM\MongoDB', './lib/vendor/doctrine-mongodb-odm/lib');
$loader->registerNamespace('Doctrine\Common', './lib/vendor/doctrine-common/lib');
$loader->registerNamespace('Doctrine\MongoDB', './lib/vendor/doctrine-mongodb/lib');
$loader->registerNamespace('MS', './');
$loader->register();
?>
