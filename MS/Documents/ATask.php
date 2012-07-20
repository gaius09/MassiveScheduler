<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\ITask;

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
    protected $executionDate;

    function __construct($executionDate) {
        if ($executionDate > 0) {
            $this->executionDate = $executionDate;
        } else {
            throw new \Exception('Instante de ejecución no válido en ATask');
        }
    }

    public abstract function executeTask();

    public function getId() {
        return $this->id;
    }

    public function getTime() {
        return $this->executionDate;
    }
}

?>