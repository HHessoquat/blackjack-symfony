<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cards")
 */
class Card {
     /**
    * @ORM\Id()
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="string")
     */
    private string $imagePath;
    
    /**
     * @ORM\Column(type="integer")
     */
    private int $value;

                //setters
    public function setId($cardId):void {
        $this->id = $cardId;
    }
    public function setName(string $cardName):void {
        $this->name = $cardName;
    }
    public function setImagePath(string $path):void {
        $this->imagePath = $path;
    }
    public function setValue(int $cardValue):void {
        $this->value = $cardValue;
    }
                //getters
    public function getId():int {
        return $this->id;
    }
    public function getName():string {
        return $this->name;
    }
    public function getPath():string {
        return $this->imagePath;
    }
    public function getValue():int {
        return $this->value;
    }
}