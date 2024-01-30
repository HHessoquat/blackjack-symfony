<?php

namespace App\Services;

use App\Entity\Game;
use App\Entity\Stackholder;
use App\Entity\Player;
use App\Entity\Bank;
use Doctrine\Persistence\ManagerRegistry;

class GameManager {
    public Game $game;
    private bool $endPlayersMove = false;
    private bool $endBanksMove = false;
    private string $winnerAnnounce = "";
    private bool $splittable = false;
    private bool $splitted = false;
    private bool $insurable = false;
    private bool $enableDouble = false;

            //setters
    public function setEndPlayersMove(bool $allowed):void {
        $this->endPlayersMove = $allowed;
    }
    public function setEndBanksMove(bool $allowed):void {
        $this->endBanksMove = $allowed;
    }
    public function setSplitted(bool $split):void {
        $this->splitted = $split;
    }
    public function setSplittable(bool $allowed):void {
        $this->splittable = $allowed;
    }
    public function setinsurable(bool $allowed):void {
        $this->insurable = $allowed;
    }
    public function setEnableDouble($allowed):void {
        $this->enableDouble = $allowed;
    }

            //getters
    public function getEndPlayersMove():bool {
        return $this->endPlayersMove;
    }
    public function getEndBanksMove():bool {
        return $this->endBanksMove;
    }

    public function getWinnerAnnounce():string {
        return $this->winnerAnnounce;
    }
    public function getSplittable():bool {
        return $this->splittable;
    }
    public function getSplitted():bool {
        return $this->splitted;
    }
    public function getInsurable():bool {
        return $this->insurable;
    }
    public function getEnableDouble():bool {
        return $this->enableDouble;
    }

            //game creator
    public function setNewGame(ManagerRegistry $doctrine, string $name){
                //Creation of all objects
        $this->game = new game($doctrine, $name);
    }
    public function setGame(Game $game):void {
        $this->game = $game;
    }
    public function getGame():Game {
        return $this->game;
    }

        //round Creator
    public function setRound():void {
                //one card given to the bank
        $card = $this->game->deck->getCard();
        $this->game->bank->setCard($card);
        $this->game->bank->setScore($card->getValue() );
        $this->game->deck->moveCursor();
        $this->setEnableDouble(true);
        $this->game->bank->setBlackjackCanny($card);
        if ($this->game->bank->getBlackjackCanny() == true) {
            $this->insurable = true;
        }

                //two cards given to the player
        for ($i=1; $i<=2; $i++){
            $card = $this->game->deck->getCard();
            $this->game->player->setCard($card);
            $this->game->deck->moveCursor();
            $this->game->player->setScore($card->getValue());
        }
        $blackjack = $this->blackJack($this->game->player);

                //if there is a blackjack, the players stop playing
        if ($blackjack == true) {
            $this->endPlayersMove = true;
            $this->setEnableDouble(false);
            $this->game->player->setHasBlackJack(true);
                //checks if the bank cannot get a blackjack, the rounds ends
            if ($this->game->bank->getBlackjackCanny() == false){
                $this->endBanksMove = true;
            } 

        }
                //detects if the player's hand is splittable
        elseif($this->game->player->getCard(0)->getValue() == $this->game->player->getCard(1)->getValue()) {
            $this->splittable = true;
        }   
    }
                    //gives a card to the player or the bank (stackholder is a generic name for both)
    public function addCard(Stackholder $stackholder, BetManager $betManager):void {
        $card = $this->game->deck->getCard();
        $stackholder->setCard($card);
        $this->game->deck->moveCursor();
        $stackholder->setScore($card->getValue());

                //check if the new score is higher than 21
        $this->over21Check($stackholder, $betManager);
        if ($stackholder == $this->game->bank){
            $this->endRound();
        }
    }

                    //returns 'true' if a stackholder's score is over 21
    private function over21Check(Stackholder $stackholder, BetManager $betManager):void {

        if ($stackholder->getScore() > 21) {
            
            $this->endPlayersMove = true;
            if ($stackholder == $this->game->player && $this->splitted == false && $betManager->getInsurance() == 0){
                $this->endBanksMove = true;
            }
            if ($stackholder == $this->game->player && $this->splitted == true && $this->game->player->getSplitScore() > 21) {
                $this->endBanksMove = true;
            }
        }
    }

                    //ends the Bank's move if its score is at least 17
    private function endRound():void {
        if ($this->game->bank->getScore() >= 17) {
            $this->endBanksMove = true;
        }
    }

    public function endGameCheck():bool {
        if ($this->game->player->getMoney() <= 0) {
            $gameOver = true;
        }
        else {
            $gameOver = false;
        }
        return $gameOver;
    }

                    //detects Blackjacks
    public function blackJack(Stackholder $stackholder):bool {
        $blackjack = false;
        if ($stackholder->getScore() == 21 && count($stackholder->getCards()) == 2) {
            $blackjack = true;
        }
    return $blackjack;
    }


                    //declares Winner
    public function setWinner(Player $player, Bank $bank, bool $split) {
        $playerScore = $player->getScore();
        $bankScore = $bank->getScore();
                    //counts the wins, lost and draws and the blackjacks, only useful if the player splitted its hand
        $wins = 0;
        $lost = 0;
        $draws = 0;

                    //compares player's and bank's score return 1 if player wins, 2 if bank wins, 3 if draw 
        if($player->getHasBlackJack() == true && $bank->getHasBlackJack() == false){
            $this->winnerAnnounce = "Vous avez gagner ce round, votre mise est multipliée par 2,5";
            $wins++;
            $player->setMainHandWins(true);
        }
        elseif ($player->getHasBlackJack() == false && $bank->getHasBlackJack() == true) {
            $this->winnerAnnounce = "Vous avez Perdu cette Manche et votre mise";
            $lost++;
        }
        elseif ($player->getHasBlackJack() == true && $bank->getHasBlackJack() == true) {
            $this->winnerAnnounce = "Il n'y a pas de vainqueur dans cette manche, vous récupérer votre mise";
            $draws++;
            $player->setMainHandDraws(true);
        }
        elseif ($playerScore > 21) {
            $this->winnerAnnounce = "Vous avez pris une carte de trop et perdu votre mise";
            $lost++;
        }
        elseif ($bankScore > 21){
            $this->winnerAnnounce = "La banque saute, vous doublez votre mise";
            $wins++;
            $player->setMainHandWins(true);
        }
        elseif ($playerScore > $bankScore ){

            $this->winnerAnnounce = "Vous gagner ce round et doublez votre mise";
            $wins++;
            $player->setMainHandWins(true);
        }elseif ($playerScore < $bankScore){
            $this->winnerAnnounce = "Vous perdez ce round et votre mise";
            $lost++;
        }
        else { 
            $this->winnerAnnounce = "Cette manche se conclue sur une draw";
            $draws++;
            $player->setMainHandDraws(true);
        }
        if($split == true) {
            $playerScore = $player->getSplitScore();
            if($player->getSplitIsBlackJack() == true && $bank->getHasBlackJack() == false){
                $wins++;
            }
            elseif ($player->getSplitIsBlackJack() == false && $bank->getHasBlackJack() == true) {
                $lost++;
            }
            elseif ($player->getSplitIsBlackJack() == true && $bank->getHasBlackJack() == true) {
                $draws++;
            }
            elseif ($playerScore > 21) {
                $lost++;
            }
            elseif ($bankScore > 21){
                $wins++;
            }
            elseif ($playerScore > $bankScore ){
                $wins++;
            }elseif ($playerScore < $bankScore){
                $lost++;
            }
            else { 
                $draws++;
            }
            $this->winnerAnnounce = "Vous avez battu " . $wins . " fois la banque, perdu " . $lost . " et fait " . $draws . " égalité";
        }
        return [$wins, $lost, $draws];
    }
    public function roundReset(splitManager $splitManager, BetManager $betManager):void {
        
        $this->game->bank->resetSelf();
        $this->game->player->resetSelf();
        $betManager->resetSelf();
        $splitManager->resetSelf();
        $this->game->player->resetSplits();
        $this->game->player->resetWins();
        $this->game->bank->resetBlackjackCanny();
        $this->setEndBanksMove(false);
        $this->setEndPlayersMove(false);
        $this->winnerAnnounce = "";
        $this->game->deck->moveCursor();
        $this->setSplitted(false);
        $this->setSplittable(false);
        $this->setinsurable(false);
    }
}