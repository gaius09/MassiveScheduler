<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\ITask,
    MS\MongoDataManager;

/**
 * @ODM\Document (collection="task")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({
  "attack"="TaskAttack",
  "createTroop"="TaskCreateTroop"
  })
 */
abstract class ATask implements ITask {

    /** @ODM\Id */
    protected $id;

    /** @ODM\Float */
    protected $duration;

    /** @ODM\Float */
    protected $executionTime;

    function __construct($duration) {
        if ($duration > 0) {
            $this->duration = $duration;
            $this->executionTime = microtime(true) + $this->duration;
        } else {
            throw new \Exception('Instante de ejecución no válido en ATask');
        }
    }

    public abstract function executeTask();

    public function getId() {
        return $this->id;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function getRemainingTime() {
        return $this->executionTime - microtime(true);
    }

    public function getExecutionTime() {
        return $this->executionTime;
    }

}

?>