<?php

namespace App\Controller\Stripe;

use Stripe\Stripe;
use App\Entity\Cart;
use Stripe\Checkout\Session;
use App\Services\CartServices;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeCheckoutController extends AbstractController
{
    /**
     * @Route("/create-checkout-session", name="create_checkout_session")
     */
    public function index(?Cart $cart, CartServices $cartServices): Response
    {
        /*if($_ENV['APP_ENV'] === 'dev'){
            $this->privateKey = $_ENV['key_test_stripe_secret'];
        } else {
            $this->privateKey = $_ENV['key_live_stripe_secret'];
        }
        $user = $this->getUser();
        if(!$cart){
          return $this->redirectToRoute('accueil');
        }*/

        $cart = $cartServices->getFullCart();
        //dd($cart['products'][0]);
        //$order = $orderServices->createOrder($cart);
        Stripe::setApiKey('sk_test_51J077OJKhLQ2081pItEOc0mNywBnmiTwo9z9ewiqkKbswOtY3sPS7fr3gEYFLtebDC7yHRDlEvWQ5aqhFK9lCF3400KjjNZCfs');
        $line_items = [];
        
        foreach (($cart['products']) as $dataProduct) {
            /*[
                'quantity' => 5,
                'product' => objet
            ]*/
            $product = $dataProduct['product'];
            $line_items[] = [[
                'price_data'=> [
                    'currency'=> 'eur',
                    'unit_amount' => $product->getPrice(),
                    'product_data' => [
                        'name' => $product->getNameProduct()
                    ],
                    
                ],
                'quantity' => $dataProduct['quantity'],
            ]];
        }   
        $checkout_session = Session::create([
            //'customer_email' => $user->getEmail(),
            "payment_method_types" => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-success',
            'cancel_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-cancel',
        ]);
        
        //$order->setStripeCheckoutSessionId($checkout_session->id);
        //$manager->flush();*/
        
        return $this->json(['id' => $checkout_session->id]);
        //echo json_encode(['id' => $checkout_session->id]);
        
    }
}
