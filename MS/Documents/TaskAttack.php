<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\Documents\ATask;

/**
 * @ODM\Document
 */
class TaskAttack extends ATask {

    /** @ODM\String */
    private $attacker;

    function __construct($executionTime, $attacker) {
        parent::__construct($executionTime);
        $this->attacker = $attacker;
    }

    public function executeTask() {
        echo '<br> Ejecución de TaskAttack <br>';
    }

}

?>
