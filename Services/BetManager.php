<?php
namespace App\Services;

use App\Entity\Player;


class BetManager {
    private bool $betSequence = false;
    private float $pot = 0;
    private float $splitPot = 0;
    private float  $betValue = 0;
    private string $message = "";
    private float $insurance = 0;

                //setters
    public function setBetSequence(bool $switch):void {
        $this->betSequence = $switch;
    }
    public function setPot(float $bet, Player $player):bool{
            //checks if the player has enough money to cover his bet
        if ($bet > $player->getMoney()){
            $this->message = "Vous ne pouvez pas pariez cette somme";
            $done = false;
        }
            //checks if the bet is at least 2
        elseif ($bet >=0 && $bet < 2) {
            $this->message = "la mise minimum est de 2";
            $done = false;
        }
        
            //set the pot
        elseif ($player->getEnableBet() == true) {
            if ($bet < 0) {
                $bet = $player->getMoney();
            }
                //saves Bet value, to be used in case of split or double
            $bet = round($bet, 2);
            $this->betValue = $bet;
            $this->pot = $this->pot + $bet;
            
            $player->takeMoney($bet);
            $player->setEnableBet();
            $done = true;
                //end the bet sequence
            $this->betSequence = false;
        }
        else {
            $done = false;
        }
        return $done;
    }

    public function setSplitPot(Player $player):bool {
        if ($player->getEnableBet() == true) {
            $this->splitPot = $this->betValue;
            $player->takeMoney($this->betValue);
            $player->setEnableBet();
            $done = true;
        }
        else {
            $done = false;
        }
        return $done;
    }

    public function setInsurance(Player $player):bool {
        if ($player->getEnableBet() == true) {
            $insurance = $this->betValue / 2;
            $insurance = round($insurance, 2);
            $this->insurance = $insurance;
            $player->takeMoney($insurance);
            $player->setEnableBet();
            $done = true;
        }
        else {$done = false;}
        return $done;
    }

                //getters
    public function getBetSequence():bool {
        return $this->betSequence;
    }
    public function getPot():float {
        return $this->pot;
    }
    public function getSplitPot():float {
        return $this->splitPot;
    }
    public function getBetValue():float {
        return $this->betValue;
    }
    public function getMessage():string {
        return $this->message;
    }

    public function getInsurance():float {
        return $this->insurance;
    }

                //set the pot if the player double
    public function doubleBet(Player $player, bool $splitted, bool $endSplitMove):bool {
        if ($splitted == true && $endSplitMove == false){
            $newSplitPot = $this->splitPot * 2;
            $done = $this->setSplitPot($player, $newSplitPot);
            return $done;
        }
        else {
            $done = $this->setPot($this->betValue, $player);
            return $done;
        }
    }

    public function payInsurance(Player $player):void{
        $insurance = $this->getInsurance();
        $insurance = $insurance * 2;
        $player->setMoney($insurance);
    }

                //resetters
    public function resetPot():void {
        $this->pot = 0;
    }
    public function resetSelf() {
        $this->insurance = 0;
        $this->resetPot();
        $this->message = "";
        $this->betValue = 0;
        $this->splitPot = 0;
    }
    public function betResolver(Player $player, bool $split, array $Results):void {
        $bankPays = 0;

        $wins = $Results[0];
        $losts = $Results[1];
        $draws = $Results[2];

        if ($split == false){
            if ($wins == 1) {
                if($player->getHasBlackJack() == false){
                    $bankPays = $this->getPot() * 2;
                }
                else {
                    $bankPays = $this->getPot() * 2.5;
                }

            }
            if ($losts == 1) {
                $bankPays = 0;
            }
            if ($draws == 1) {
                $bankPays = $this->getPot();
            }
        }
        elseif ($split == true) {
            $mainPot = $this->getPot();
            $splitPot = $this->getSplitPot();
                //if both of player's hands win
            if ($wins == 2) {
                        //if both are blackjack, both bets are multiplied by 2.5 (Bet and split bet are same value)
                if ($player->getHasBlackJack() == true && $player->getSplitIsBlackjack() == true){
                    $mainPot = $mainPot * 2.5;
                    $splitPot = $splitPot * 2.5;
                }
                        //if at least one is black jack
                elseif ($player->getHasBlackJack() == true || $player->getSplitIsBlackjack() == true){
                        //one bet is multiplied by 2.5 the other by Two
                    if ($player->getHasBlackJack() == true) {
                        $mainPot = $mainPot * 2.5;
                        $splitPot = $splitPot * 2;
                    }
                    else {
                        $mainPot = $mainPot * 2;
                        $splitPot = $splitPot * 2.5;
                    }
                }
                        //if no blackjack
                else {
                        //each bet is doubled
                    $mainPot = $mainPot * 2;
                    $splitPot = $splitPot * 2;
                }
            }
                        //if player win 1 and lose one
            if ($wins == 1 && $losts == 1) {
                            // if the winning hand is a blackjack, one bet is lost the other is multiplied by 2.5
                if($player->getHasBlackJack() == true || $player->getSplitIsBlackjack() == true) {
                    if ($player->getHasBlackJack() == true) {
                        $mainPot = $mainPot * 2.5;
                        $splitPot = $splitPot * 0;
                    }
                    else {
                        $mainPot = $mainPot * 0;
                        $splitPot = $splitPot * 2.5;
                    }
                }
                else{
                            //if no black jack
                    if($player->getMainHandWins() == true){
                        $mainPot = $mainPot * 2;
                        $splitPot = 0;
                    }
                    else {
                        $mainPot = 0;
                        $splitPot = $splitPot * 2;
                    }
                }
            }
                            //if one hand wins and the other draws the bank card
            if ($wins == 1 && $draws == 1) {
                        // if the win is a black jack the player the bet is 2.5 and the draws is given back
                if($player->getHasBlackJack() == true || $player->getSplitIsBlackjack() == true) {
                    if($player->getHasBlackJack() == true || $player->getSplitIsBlackjack() == true) {
                        if ($player->getHasBlackJack() == true) {
                            $mainPot = $mainPot * 2.5;
                        }
                        else {
                            $splitPot = $splitPot * 2.5;
                        }
                    }
                    else {
                    //if not, one bet is doubled, the other is given back
                        if($player->getMainHandWins() == true){
                            $mainPot = $mainPot * 2;
                        }
                        else {
                            $splitPot = $splitPot * 2;
                        }
                    }
                }
            }
                        //if one hand draws and the oth loses, the player gets back hamf of the pot
            if ($draws == 1 && $losts == 1) {
                if ($player->getMainhandDraws() == true) {
                    $splitPot = 0;
                }
                else {
                    $mainPot = 0;
                }
            }
                        //if both hands lose, the whole pot is lost
            if ($losts == 2) {
                $mainPot = 0;
                $splitPot = 0;
            }
            $bankPays = $mainPot + $splitPot;
        }
                    //pays the player et reset the pot
        $player->setMoney($bankPays);
        $this->resetPot();
    }
}