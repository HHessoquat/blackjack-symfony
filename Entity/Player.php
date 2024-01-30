<?php 
namespace App\Entity;


class Player extends Stackholder {

     
    private string $name;
    private float $money = 100;
            //variable of splitted hand
    private array $splitHand = [];
    private int $splitScore = 0;
    private bool $splitAceWorth11 =false;
    private bool $splitIsBlackjack = false;
    private bool $mainHandWins = false;
    private bool $mainHandDraws = false;
    private bool $enableBet = true;

                //setters
                   /**
     * @Assert\Length(
     *     min = 4,
     *     max = 30,
     *     minMessage = "Ce nom est trop court",
     *     maxMessage = "Ce nom est trop long"
     * )
     */
    public function setName(string $name):void {
          $this->name = $name;
    }
    public function setMoney(float $money):void {
        if($money <= 0){
             $this->money = $this->money ;
        }
        else {
            $money = round($money, 2);
            $this->money = $this->money + $money;
        }
    }
    public function takeMoney(float $value):bool {
        if ($this->getEnableBet() == true) {
            $this->money = $this->money - $value;
            $done = true;
            return $done;
        }
        else {
            $done = false;
            return $done;
        }
    }

    public function setSplitHand(Card $card):void {
        $this->splitHand[] = $card;
    }

    public function setSplitScore(int $newValue):void {
        //add the new value to the previous score
        if ($newValue == 1 && $this->splitScore <= 10){
            $this->splitAceWorth11 = true;
            $this->splitScore = $this->splitScore + 11;
        } elseif($newValue >= 10){
            $this->splitScore = $this->splitScore + 10;
        } else {
        $this->splitScore = $this->splitScore + $newValue;
    }

            //if an Ace worth 11 make the score over 21, it is reduced to a value of 1
        if($this->splitAceWorth11 == true && $this->splitScore > 21) {
            $this->splitScore = $this->splitScore - 10;
            $this->splitAceWorth11 = false;
        }
    }
    public function setMainHandWins(bool $result):void {
        $this->mainHandWins = $result;
    }
    public function setMainHandDraws(bool $result):void {
        $this->mainHandDraws = $result;
    }
    public function setEnableBet():void {
        if ($this->getMoney() <= 0) {
            $this->enableBet = false;
        }
        else {
            $this->enableBet = true;
        }
    }

                    //getters
    public function getName():string {
        return $this->name;
    }
    public function getMoney():float {
        return $this->money;
    }
    public function getCard(int $key):Card {
        return $this->cards[$key];
    }
    public function getSplitScore():int {
        return $this->splitScore;
    }
    public function getSplitHand():array {
        return $this->splitHand;
    }
    public function getSplitIsBlackjack():bool {
        return $this->splitIsBlackjack;
    }
    public function getMainHandWins():bool {
        return $this->mainHandWins;
    }
    public function getMainhandDraws():bool {
        return $this->mainHandDraws;
    }
    public function getEnableBet():bool {
        return $this->enableBet;
    }

                        //resetters
    public function resetSplits():void {
        $this->splitScore = 0;
        $this->splitHand = array();
        $this->splitAceWorth11 = false;
        $this->splitIsBlackjack = false;
    }
    public function resetWins():void {
        $this->mainHandWins = false;
        $this->mainHandDraws = false;
    }

                    //constructor
    public function __construct(string $name){
        $this->setName($name);
    }
}