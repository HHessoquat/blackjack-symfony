<?php 
namespace App\Services;
use App\Entity\player;
use App\Entity\Card;

class SplitManager {

private bool $endSplitMove = false;

    public function separateHands(Player $player):void {
        if(count($player->getCards()) == 2){
            $card1 = $player->getCard(0);
            $card2 = $player->getCard(1);
            $player->resetSelf();
            $player->setCard($card1);
            $player->setSplitHand($card2);
            $player->resetScore();
            $player->setScore($card1->getValue());
            $player->setSplitScore($card2->getValue());
        }
    }
                //setters
    public function setEndSplitMove(bool $bool):void {
        $this->endSplitMove = $bool;
    }

                //getters
    public function getEndSplitMove():bool {
        return $this->endSplitMove;
    }
    
    public function addCard(Player $player,Card $newCard):void {
        $player->setSplitHand($newCard);
        $player->setSplitScore($newCard->getValue());

        if ($player->getSplitScore() > 21) {
            $this->endSplitMove = true;
        }
    }

                //Resetter
    public function resetSelf():void {
        $this->endSplitMove = false;
    }
}


