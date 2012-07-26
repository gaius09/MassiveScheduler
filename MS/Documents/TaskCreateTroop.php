<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\Documents\ATask;

/**
 * @ODM\Document
 */
class TaskCreateTroop extends ATask{
    
    /** @ODM\Int */
    private $units;

    function __construct($executionTime, $units) {
        parent::__construct($executionTime);
        $this->units = $units;
    }

    public function executeTask() {
         echo '<br> Ejecución de TaskCreateTroop <br>';
    }
}

?>
