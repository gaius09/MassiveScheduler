<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\Documents\ATask,
    MS\Documents\Battle;

/**
 * @ODM\Document
 */
class TaskTest extends ATask {
    
    /**  @ODM\String */ 
    protected $status;

    function __construct($duration) {
        parent::__construct($duration);
        $this->status = "Started";
    }

    public function executeTask() {
        $this->status = "Executed";
    }
}

?>