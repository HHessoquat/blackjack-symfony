<?php
namespace App\Entity;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Player;
use App\Entity\Deck;
use App\Entity\Bank;

class Game {
    public Player $player;
    public Bank $bank;
    public Deck $deck;

                //Setters
    public function setPlayer($name):void {
        $this->player = new Player($name);
    }

    public function setBank():void {
        $this->bank = new Bank();
    }

    public function setDeck(ManagerRegistry $doctrine):void {
        $this->deck = new Deck( $doctrine);
    }


                //Getters
    public function getPlayer():Player {
        return $this->player;
    }
    public function getBank():Bank {
        return $this->bank;
    }
    public function getDeck():Deck {
        return $this->deck;
    }

                //
    public function __construct(ManagerRegistry $doctrine, string $name) {
        $this->setPlayer($name);
        $this->setBank();
        $this->setDeck($doctrine);
    }
        
}