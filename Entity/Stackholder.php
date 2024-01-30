<?php

namespace App\Entity;

use App\Entity\Card;

abstract class Stackholder {
    protected array $cards = [];
    protected int $score = 0;
    protected bool $aceWorth11 = false;
    protected bool $hasBlackjack = false;

            //Setters
    public function setCard(Card $newCard):void {
        $this->cards[] = $newCard;
    }
    public function setScore(int $newValue):void {
            //add the new value to the previous score
        if ($newValue == 1 && $this->score <= 10){
                $this->aceWorth11 = true;
            $this->score = $this->score + 11;

        } elseif($newValue >= 10){
            $this->score = $this->score + 10;
        } else {
            
            $this->score = $this->score + $newValue;
        }
                //if an Ace worth 11 make the score over 21, it is reduced to a value of 1
        if($this->aceWorth11 == true && $this->score > 21) {
            $this->score = $this->score - 10;
            $this->aceWorth11 = false;
        }
    }

    public function setHasBlackJack(bool $confirmation):void {
        if ($confirmation == true) {
            $this->hasBlackjack = true;
        }
    }

            //Getters
    public function getCards():array {
        return $this->cards;
    }
    public function getScore():int {
        return $this->score;
    }
    public function getAceWorth11():bool {
        return $this->aceWorth11;
    }
    public function getHasBlackJack():bool {
        return $this->hasBlackjack;
    }
            //resetters
    public function resetScore():void {
        $this->score = 0;
    }
            //reset
    public function resetSelf():void {
        $this->cards = array();
        $this->resetScore();
        $this->aceWorth11 = false;
        $this->hasBlackjack = false;
    }
}