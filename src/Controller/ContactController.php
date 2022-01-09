<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contact")
 */
class ContactController extends AbstractController
{
    
    /**
     * @Route("/new", name="contact_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getdoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            //envoi d'un message par mail  
            $contact = new Contact();
            $form = $this->createForm(ContactType::class, $contact);
            $this->addFlash('contact_success', 'Votre message a bien été envoyé. Un administrateur vous répondra trés rapidement !');          
        }
        if($form->isSubmitted() && !$form->isValid()){
            $this->addFlash('contact_cancel', 'Votre message ne peut pas être envoyé. Veuillez recommencer !');          
        }
        return $this->render('contact/new.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    
}
