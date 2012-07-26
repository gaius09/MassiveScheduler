<?php

namespace MS\Documents;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM,
    MS\TaskManager,
    MS\MongoDataManager;

/**
 * @ODM\Document
 */
class Player {

    const GOLD_PER_MINUTE = 10;
    const TROOP_PRICE = 2;
    const ATTACK_TIME = 30;

    /** @ODM\Id */
    protected $id;

    /** @ODM\Int */
    protected $army;

    /** @ODM\Int */
    protected $gold;

    /** @ODM\Int */
    protected $goldTime;

    /**
     * @ODM\ReferenceOne(targetDocument="TaskCreateTroop")
     */
    protected $troopQueue;

    /**
     * @ODM\ReferenceOne(targetDocument="TaskAttack", cascade={"all"})
     */
    protected $taskAttack;

    function __construct($army, $gold) {
        $this->army = $army;
        $this->gold = $gold;
    }

    public function getId() {
        return $this->id;
    }

    public function getArmy() {
        return $this->army;
    }

    public function getGold() {
        return $this->gold;
    }

    public function getTaskAttack() {
        return $this->taskAttack;
    }

    public function isAttacking() {
        return isset($this->taskAttack);
    }

    public function getGoldTime() {
        return $this->goldTime;
    }

    public function setArmy($army) {
        $this->army = $army;
    }

    public function setGold($gold) {
        $this->gold = $gold;
    }

    public function setTaskAttack($taskAttack) {
        $this->taskAttack = $taskAttack;
    }

    public function attack(Player $player) {
        if (isset($this->taskAttack)) {
            throw new \Exception('Ya se está atacando a un jugador...');
        } else {
            $dm = MongoDataManager::getDocumentManager();
            $this->taskAttack = new TaskAttack(Player::ATTACK_TIME, $this, $player);
            $dm->persist($this->taskAttack);
            $dm->flush();
            $taskManager = new TaskManager();
            $taskManager->add($this->taskAttack);
        }
    }

    public function buildTroops($number) {
        
    }

    public function cancelBuildTroops() {
        
    }

    public function cancelAttackPlayer() {
        if ($this->isAttacking()) {
            $taskManager = new TaskManager();
            $taskManager->remove($this->taskAttack);
            
            $dm = MongoDataManager::getDocumentManager();
            $dm->remove($this->taskAttack);
            $this->taskAttack = NULL;
            $dm->flush();
        }
    }
}

?>
