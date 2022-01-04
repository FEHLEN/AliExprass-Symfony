<?php

namespace App\Controller\Panier;

use App\Services\CartServices;
use App\Services\OrderServices;
use App\Form\CheckoutType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{
    private $cartServices;
    private $session;

    public function __construct(CartServices $cartServices, SessionInterface $session)
    {
        $this->cartServices = $cartServices;
        $this->session = $session;
    }
    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();    
        $cart = $this->cartServices->getFullCart();

        if(!isset($cart['products'])){
            return $this->redirectToRoute("accueil");
        }
        if(!$user->getAddresses()->getValues()){
            $this->addFlash('checkout_message', 'Merci de renseigner une adresse de livraison avant de continuer !');
            return $this->redirectToRoute("address_new");
        }
        $form = $this->createForm(CheckoutType::class, null, ['user'=>$user]);
        //$form->handleRequest($request);
        //traitement du formulaire

        return $this->render('checkout/index.html.twig', [
            'cart'=>$cart,
            'checkout'=>$form->createView()
        ]);
    }

    /**
     * @route("/checkout/confirm" , name="checkoutConfirm")
     */
    public function confirm(Request $request, OrderServices $orderServices):Response{
        $user = $this->getUser();
        $cart = $this->cartServices->getFullCart(); //deux fois utiliser donc faire un contructeur
        if(!isset($cart['products'])){
            return $this->redirectToRoute("accueil");
        }
        if(!$user->getAddresses()->getValues()){
            $this->addFlash('checkout_message', 'Merci de renseigner une adresse de livraison avant de continuer !');
            return $this->redirectToRoute("address_new");
        }
        //dd($cart['products'][0]);
        $form = $this->createForm(CheckoutType::class, null, ['user'=>$user]);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid() ){

            $data = $form->getData();
            $address = $data['address'];
            $transport = $data['transport'];
            $informations = $data['informations'];
            //dd($data);
            //save panier
            $cart['checkout'] = $data;
            $reference = $orderServices->saveCart($cart,$user);
            //dd($reference);
            return $this->render('checkout/confirm.html.twig', [
                'cart'=>$cart,
                'address' =>$address,
                'transport' =>$transport,
                'informations' =>$informations,
                'checkout'=>$form->createView()
            ]);
        } else {
            return $this->redirectToRoute("checkout/index.html.twig");
        }
       
        
    }
}
