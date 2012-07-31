<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\Documents\ATask,
    MS\Documents\Battle;

/**
 * @ODM\Document
 */
class TaskAttack extends ATask {

    /**  @ODM\ReferenceOne(targetDocument="Player", inversedBy="taskAttack") */
    protected $attacker;

    /**  @ODM\ReferenceOne(targetDocument="Player") */
    protected $defender;

    function __construct($duration, $attacker, $defender) {
        parent::__construct($duration);
        $this->attacker = $attacker;
        $this->defender = $defender;
    }

    public function executeTask() {
        $battle = new Battle($this->attacker, $this->defender);
        $battle->fight();
    }

}

?>
