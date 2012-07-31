<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\Documents\ATask;

/**
 * @ODM\Document
 */
class TaskCreateTroop extends ATask {

    /** @ODM\Int */
    protected $units;

    /**  @ODM\ReferenceOne(targetDocument="Player", inversedBy="taskCreateTroopCollection") */
    protected $player;

    function __construct($duration, $player, $units) {
        parent::__construct($duration);
        $this->player = $player;
        $this->units = $units;
    }

    public function getUnits() {
        return $this->units;
    }

    public function getPlayer() {
        return $this->player;
    }

    public function executeTask() {
        $this->player->setArmy($this->player->getArmy() + $this->units);
    }

}

?>
