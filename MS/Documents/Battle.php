<?php

namespace MS\Documents;

use MS\Documents\Player;

class Battle {

    private $attacker;
    private $defender;
    
    function __construct(&$attacker, &$defender) {
        $this->attacker = $attacker;
        $this->defender = $defender;
    }
    
    public function getAttacker() {
        return $this->attacker;
    }

    public function setAttacker(Player $attacker) {
        $this->attacker = $attacker;
    }

    public function getDefender() {
        return $this->defender;
    }

    public function setDefender(Player $defender) {
        $this->defender = $defender;
    }
    
    public function fight() {
        if ($this->attacker->getArmy() > $this->defender->getArmy()) {
            $this->attacker->setArmy($this->attacker->getArmy() - $this->defender->getArmy());
            $this->defender->setArmy(0);
        } else {
            $this->defender->setArmy($this->defender->getArmy() - $this->attacker->getArmy());
            $this->attacker->setArmy(0);
        }
    }
}

?>
