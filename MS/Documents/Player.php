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
    const TIME_PER_TROOP = 5;
    const MAX_CREATE_TROOP_QUEUE = 5;
    const MAX_UNITS_CREATE_TROOP = 100;
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
     * @ODM\ReferenceOne(targetDocument="TaskAttack", mappedBy="attacker")
     */
    protected $taskAttack;

    /**
     * @ODM\ReferenceMany(targetDocument="TaskCreateTroop", mappedBy="player", cascade="all")
     */
    protected $taskCreateTroopCollection;

    function __construct($army, $gold) {
        $this->army = $army;
        $this->gold = $gold;
        $this->taskCreateTroopCollection = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getTaskCreateTroopCollection() {
        return $this->taskCreateTroopCollection;
    }

    public function setTaskCreateTroopCollection($taskCreateTroopCollection) {
        $this->taskCreateTroopCollection = $taskCreateTroopCollection;
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
        if (count($this->taskCreateTroopCollection) < Player::MAX_CREATE_TROOP_QUEUE) {
            if ($number > Player::MAX_UNITS_CREATE_TROOP) {
                throw new \Exception("No puedes crear más de Player::MAX_UNITS_CREATE_TROOP a la vez");
            } else {
                $dm = MongoDataManager::getDocumentManager();
                $task = new TaskCreateTroop(Player::TIME_PER_TROOP * $number, $this, $number);
                $this->taskCreateTroopCollection->add($task);
                $dm->persist($this);
                $dm->flush();
                $taskManager = new TaskManager();
                $taskManager->add($task);
            }
        } else {
            throw new \Exception('Cola de creación de tropas llena...');
        }
    }

    public function cancelBuildTroops($taskId) {
        $dm = MongoDataManager::getDocumentManager();
        $task = $dm->find('MS\Documents\TaskCreateTroop', $taskId);
        if (!isset($task)) {
            throw new \Exception('No se encontró la tarea a cancelar');
        } else {
            $taskManager = new TaskManager();
            $taskManager->remove($task);
            $dm->remove($task);
            $dm->flush();
        }
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
