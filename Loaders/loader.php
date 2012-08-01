<?php
require_once (__DIR__.'/../lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', __DIR__.'/../lib/vendor/');
$loader->registerNamespace('Doctrine\ODM\MongoDB', __DIR__.'/../lib/vendor/doctrine-mongodb-odm/lib');
$loader->registerNamespace('Doctrine\Common', __DIR__.'/../lib/vendor/doctrine-common/lib');
$loader->registerNamespace('Doctrine\MongoDB', __DIR__.'/../lib/vendor/doctrine-mongodb/lib');
$loader->registerNamespace('MS', __DIR__.'/../');
$loader->registerNamespace('Daemon', __DIR__.'/../');
$loader->register();
?>
