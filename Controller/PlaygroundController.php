<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Services\GameManager;
use App\Services\SplitManager;
use App\Services\BetManager;
use App\Form\BetType;



class PlaygroundController extends AbstractController {
    /**
     * @Route("/playground")
     */

    public function playground(ManagerRegistry $doctrine, Request $request, GameManager $gameManager, SplitManager $splitManager, BetManager $betManager) {
        
                    //declare requiered variables
        $session = $request->getSession(); 
        $action= "";
        $roundOver = false;
        $bet = $request->request->all('bet');
        $message = "";
        $form = null;
        $action = $request->query->get('action');
        $paymentIsValid = true;
        $gameOver = false;

                //If no game is on
        if ($session->get('game') === null || isset($request->request->all('name')['nom'])){
                //retrieve the player's name
            $name = $request->request->all('name')['nom'];
                //The service Game Manager create the Game(the bet part is yet to come)
            $gameManager->setNewGame($doctrine, $name);

                //set the first Bet sequence
            $form = $this->createForm(BetType::class);
            $form->handleRequest($request);
            $form = $form->createView();
        }
                //If game is on
        else {
                        //retrieves managers' data
            $gameManager = unserialize($session->get('game'));                
            $splitManager = unserialize($session->get('split'));
            $betManager = unserialize($session->get('bet'));
            
                        //Action to be done

            if ($request->request->all('bet') != null && $gameManager->game->player->getCards() == null) {
                    //The service manager start a round, and look if the player gets a blackjack
                if ($bet['bet'] != null) {
                    $bet = $bet['bet'];
                }
                elseif(isset($bet['otherBet'])) {
                    $bet =$bet['otherBet'];
                    $bet = floatval($bet);
                }
                
                $paymentIsValid = $betManager->setPot($bet, $gameManager->game->player);
                if ($betManager->getPot() != 0) {
                    $gameManager->setRound();
                }
                else {
                    $message = $betManager->getMessage();
                }
            }
                            //the player takes the insurance
            elseif ($action == 'insurance') {
                if($gameManager->getInsurable() == true && $gameManager->game->player->getEnableBet() == true) {
                    $paymentIsValid = $betManager->setInsurance($gameManager->game->player);
                    $gameManager->setInsurable(false);
                }
            }
                            //the player asks for a new card
            elseif ($action == 'card') {
                if($gameManager->getEndPlayersMove() == false){
                    $gameManager->setInsurable(false);
                    $gameManager->setSplittable(false);
                    $gameManager->setEnableDouble(false);
                    if ($gameManager->getSplitted() == true && $splitManager->getEndSplitMove() == false) {
                        $splitManager->addCard($gameManager->game->player, $gameManager->game->deck->getCard());
                        $gameManager->game->deck->moveCursor();
                    }
                    else {
                        $gameManager->addCard($gameManager->game->player, $betManager);
                    } 
                }
            }
                            //the player stops asking for cards
            elseif ($action == 'done'){
                if ($gameManager->getSplitted() == true && $splitManager->getEndSplitMove() == false){
                    $splitManager->setEndSplitMove(true);
                    $gameManager->addCard($gameManager->game->player, $betManager);
                    $gameManager->setEnableDouble(true);
                    $gameManager->setInsurable(false);
                }
                else {
                    if ($gameManager->getEndBanksMove() == false){
                        $gameManager->setEnableDouble(false);
                        $gameManager->setEndPlayersMove(true);
                        $gameManager->addCard($gameManager->game->bank, $betManager);
                            //checks if the bank gets a blackjack
                        $blackjack = $gameManager->blackJack($gameManager->game->bank);
                        if ($blackjack == true){
                            $gameManager->game->bank->setHasBlackJack(true);
                            $betManager->payInsurance($gameManager->game->player);
                            $gameManager->setInsurable(false);
                        }
                    }
                }
            }
                            //The bank gets a new card
            elseif ($action == 'next'){
                if($gameManager->getEndPlayersMove() == true && $gameManager->getEndBanksMove() == false){
                    $gameManager->addCard($gameManager->game->bank, $betManager);
                            
                    $blackjack = $gameManager->blackJack($gameManager->game->bank);
                        if ($blackjack == true){
                            $gameManager->game->bank->setHasBlackJack(true);
                            $betManager->payInsurance($gameManager->game->player);
                            $gameManager->setInsurable(false);
                        }
                }
            }
                            //the players doubles its bet
            elseif ($action == 'double') {
                if ($gameManager->getEnableDouble() == true){
                    if ($gameManager->getSplitted() == true && $splitManager->getEndSplitMove() == false && $gameManager->game->player->getEnableBet() == true) {
                        $paymentIsValid = $betManager->doubleBet($gameManager->game->player, $gameManager->getSplitted(), $splitManager->getEndsplitMove());
                        $splitManager->addCard($gameManager->game->player, $gameManager->game->deck->getCard());
                        $gameManager->game->deck->moveCursor();
                        $splitManager->setEndSplitMove(true);
                        $gameManager->addCard($gameManager->game->player, $betManager);
                    }
                    else {
                        if ($gameManager->game->player->getEnableBet() == true){
                            $paymentIsValid = $betManager->doubleBet($gameManager->game->player, $gameManager->getSplitted(), $splitManager->getEndSplitMove());
                            $gameManager->addCard($gameManager->game->player, $betManager);
                            $gameManager->setEndPlayersMove(true);
                            $gameManager->setEnableDouble(false);
                            $gameManager->setInsurable(false);
                        }
                    }
                }
            }
                            //the player split its hands
            elseif ($action == "split"){
                if ($gameManager->getSplittable() == true){
                    if ($gameManager->game->player->getEnableBet() == true) {
                        $gameManager->setInsurable(false);
                        $gameManager->setSplitted(true);
                        $gameManager->setSplittable(false);
                        $gameManager->setEnableDouble(true);
                        $splitManager->separateHands($gameManager->game->player);
                        $paymentIsValid = $betManager->setSplitPot($gameManager->game->player);
                        $splitManager->addCard($gameManager->game->player, $gameManager->game->deck->getCard());
                        $gameManager->game->deck->moveCursor();
                    }
                }
            }              //the player split its hands
                            //prepares the new Round
            elseif ($action == 'newRound'){
                if($gameManager->getEndBanksMove() == true || $gameManager->game->player->getCards() == null){
                    $gameManager->roundReset($splitManager, $betManager);
                    $form = $this->createForm(BetType::class);
                    $form->handleRequest($request);
                    $form = $form->createView();
                    $betManager->setBetSequence(false);
                }
            }
            if ($gameManager->game->player->getCards() == null) {
                $form = $this->createForm(BetType::class);
                    $form->handleRequest($request);
                    $form = $form->createView();
            }
        }

        if ($gameManager->getEndBanksMove() == true && $gameManager->getEndPlayersMove() == true) {
            $roundOver = true;
            $result = $gameManager->setWinner($gameManager->game->player, $gameManager->game->bank, $gameManager->getSplitted());
            $message = $gameManager->getWinnerAnnounce();
            $betManager->betResolver($gameManager->game->player, $gameManager->getSplitted(), $result);
            $gameOver = $gameManager->endGameCheck();
            $gameManager->game->player->setEnableBet();
        }

        $session->set('game', serialize($gameManager));
        $session->set('split', serialize($splitManager));
        $session->set('bet', serialize($betManager));
        
        return $this->render('playground.html.twig', ([
            'bankCards' => $gameManager->game->bank->getCards(),
            'playerCards' => $gameManager->game->player->getCards(),
            'playerName' =>$gameManager->game->player->getName(),
            'action' => $action,
            'bankScore' => $gameManager->game->bank->getScore(),
            'playerScore' => $gameManager->game->player->getScore(),
            'playerSplitScore' => $gameManager->game->player->getSplitScore(),
            'endPlayersMove' => $gameManager->getEndPlayersMove(),
            'endBanksMove' => $gameManager->getEndbanksMove(),
            'roundOver' => $roundOver,
            'message' => $message,
            'splittable' => $gameManager->getSplittable(),
            'splittedHand' => $gameManager->getSplitted(),
            'secondHand' => $gameManager->game->player->getSplitHand(),
            'endSplit' => $splitManager->getEndSplitMove(),
            'playerMoney' => $gameManager->game->player->getMoney(),
            'pot' => $betManager->getPot(),
            'form' => $form,
            'insurable' => $gameManager->getInsurable(),
            'insurance' => $betManager->getInsurance(),
            'enableDouble' => $gameManager->getEnableDouble(),
            'splitPot' => $betManager->getSplitPot(),
            'paymentIsValid' => $paymentIsValid,
            'enableBet' => $gameManager->game->player->getEnableBet(),
            'gameOver' => $gameOver,
        ]));
    }
}