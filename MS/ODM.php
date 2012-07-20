<?php

namespace MS;

use Doctrine\Common\ClassLoader,
    Doctrine\Common\Annotations\AnnotationReader,
    Doctrine\ODM\MongoDB\DocumentManager,
    Doctrine\MongoDB\Connection,
    Doctrine\ODM\MongoDB\Configuration,
    Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver,
    MS\ODM;

class ODM {

    private static $dm;

    public function inicializate() {
//        require '../lib/vendor/doctrine-mongodb-odm/lib/vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

//        // ODM Classes
//        $classLoader = new ClassLoader('Doctrine\ODM\MongoDB', '../lib/vendor/doctrine-mongodb-odm/lib');
//        $classLoader->register();
//
//        // Common Classes
//        $classLoader = new ClassLoader('Doctrine\Common', '../lib/vendor/doctrine-mongodb-odm/lib/vendor/doctrine-common/lib');
//        $classLoader->register();
//
//        // MongoDB Classes
//        $classLoader = new ClassLoader('Doctrine\MongoDB', '../lib/vendor/doctrine-mongodb-odm/lib/vendor/doctrine-mongodb/lib');
//        $classLoader->register();
//
//        // Document classes
//        $classLoader = new ClassLoader('lib\Model\PersistEntity', '../');
//        $classLoader->register();

        AnnotationDriver::registerAnnotationClasses();

        $config = new Configuration();
        $config->setProxyDir('./MS/cache');
        $config->setProxyNamespace('Proxies');

        $config->setHydratorDir('./MS/cache');
        $config->setHydratorNamespace('Hydrators');

        $reader = new AnnotationReader();
        $config->setMetadataDriverImpl(new AnnotationDriver($reader, './MS/Documents'));
        $config->setDefaultDB('massiveScheduler');

        self::$dm = DocumentManager::create(new Connection(), $config); //se le pasa el servidor usuario y contraseña en el objeto connection
    }

    public static function getDocumentManager() {
        if (!isset(self::$dm)) {
            $odm = new ODM();
            $odm->inicializate();
        }
        return self::$dm;
    }

}

?>