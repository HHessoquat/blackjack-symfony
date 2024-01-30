<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\NameType;


class FormController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function new(Request $request,)
    {
                //to be deletede after tests
                $session = $request->getSession();
        $session->clear();
                //end of to be deleted
        $form = $this->createForm(NameType::class);

        $form->handleRequest($request);
        

        return $this->render('nameForm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}