<?php
namespace App\Entity;

use App\Entity\Card;

class Bank extends Stackholder {
    private bool $blackjackCanny = false;

    public function setBlackjackCanny(Card $firstCard):void {
        if($firstCard->getValue() == 1 || $firstCard->getValue() >= 10){
            $this->blackjackCanny = true;
        }
    }

    public function getBlackjackCanny():bool {
        return $this->blackjackCanny;
    }

    public function resetBlackjackCanny():void {
        $this->blackjackCanny = false;
    }
}