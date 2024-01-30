<?php
namespace App\Entity;

use App\Entity\Card;
use Doctrine\Persistence\ManagerRegistry;


class Deck {
                //an array contains all the card in a randomn order
    private array $deck;
                //to manipulate the cursor of the array "deck"
    private int $currentCard = 0;


                //setters
    public function setDeck(ManagerRegistry $doctrine):void {
        $this->deck = $doctrine->getRepository(Card::class)->findAll();
        shuffle($this->deck);

    }
    public function moveCursor():void {
        if($this->currentCard < 51){
            $this->currentCard++;
        }
        else {
            $this->currentCard = 0;
        }
    }
                //return one card from the deck
    public function getCard():Card {
        return $this->deck[$this->currentCard];
    }


    public function __construct(ManagerRegistry $doctrine)
    {
        $this->setDeck($doctrine);
    }
}