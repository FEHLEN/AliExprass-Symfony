<?php
namespace App\Services;

use App\Entity\Cart;
use App\Entity\Orders;
use App\Entity\CartDetails;
use App\Entity\OrderDetails;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderServices{
    private $manager;
   

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        
    }

    public function createOrder($cart)
    {
        $order = new Orders();
        $order->setReference($cart->getReference())
            ->setFullname($cart->getFullName())
            ->setTransportName($cart->getTransportName()())
            ->setTransportPrice($cart->getPrice()/100)
            ->setLivraisonAdresse($cart->getLivraisonAdresse())
            ->setMoreInformations($cart->getMoreInformations())
            ->setCreatedAt($cart->getCreatedAt())
            ->setUser($cart->getUser())
            ->setQuantity($cart->getQuantity())
            ->setSubTotalHT($cart->getSubTotalHT()/100)
            ->setTaxe($cart->getTaxe()/100)
            ->setSubTotalTTC($cart->getSubTotalTTC()/100);
        $this->manager->persist($order);
        
        $products = $cart->getCartDetails()->getValues();
        foreach ($products as $cart_product) {
            $orderDetails = new OrderDetails();
            $orderDetails->setOrders($order)
                         ->setProductName($cart_product->getProductName())
                         ->setProductPrice($cart_product->getProductPrice())
                         ->setProductQuantity($cart_product->getProductQuantity())
                         ->setSubTotalHt($cart_product->getSubTotalHt())
                         ->setTaxe($cart_product->getTaxe())
                         ->setSubTotalTtc($cart_product->getSubTotalTtc());
                         
            $this->manager->persist($orderDetails);
        }

        $this->manager->flush();

        return $order;
    }

    public function getLineItems($cart){
        $cartDetails = $cart->getCartDetails()->getValues();

        $line_items = [];
        foreach ($cartDetails as $details) {
            $product = $this->repoProduct->findOneByName($details->getProductName());
            $line_items[] = [
                'price_data' => [
                  'currency' => 'eur',
                  'unit_amount' => $product->getProductPrice(),
                  'product_data' => [
                    'name' => $product->getProductName(),
                    'images' => [$_ENV['YOUR_DOMAIN'].'public/assets/uploads/products/'.$product->getImage()],
                  ],
                ],
                'quantity' =>  $details->getProductQuantity(),
            ];
        }
        //transport
        $line_items[] = [
            'price_data' => [
              'currency' => 'eur',
              'unit_amount' => $cart->getPrice(),
              'product_data' => [
                'name' => 'Transport ( '.$cart->getNameTransport().' )',
                'images' => [$_ENV['YOUR_DOMAIN'].'public/assets/images/Transport-Truck-icon.png'],
              ],
            ],
            'quantity' =>  1,
        ];
        // Taxe
        $line_items[] = [
            'price_data' => [
              'currency' => 'eur',
              'unit_amount' => $cart->getTaxe(),
              'product_data' => [
                'name' => 'TVA (20%)',
                'images' => [$_ENV['YOUR_DOMAIN'].'public/assets/images/taxe-vectoriel.jpg'],
              ],
            ],
            'quantity' =>  1,
        ];
        return $line_items;
    }

    public function saveCart($data, $user)
    {
        /*[
            'products' => [],//tous les produits du panier
            'data' => [],//sous-total, taxe, totalTTC
            'checkout' => [
                'address' => objet,
                'transport' => objet,
                'informations' => sdfsfn
            ]
        ]*/
        $cart = new Cart();//remplissage de la table cart
        $reference = $this->generateUuid();
        $address = $data['checkout']['address'];
        $transport = $data['checkout']['transport'];
        $informations = $data['checkout']['informations'];

        $cart->setReference($reference)
             ->setTransportName($transport->getNameTransport())
             ->setTransportPrice($transport->getPrice()/100)
             ->setFullname($address->getFullName())
             ->setLivraisonAdresse($address)
             ->setMoreInformations($informations)
             ->setQuantity($data['data']['quantity_cart'])//voir dans CartServices.php
             ->setSubTotalHT($data['data']['subTotalHT'])
             ->setTaxe($data['data']['Taxe'])
             ->setSubTotalTTC($data['data']['subTotalTTC']+$transport->getPrice()/100)
             ->setUser($user)
             ->setCreatedAt(new \DateTime());
        $this->manager->persist($cart);
        //creation de l'objet cart details
        $cart_details_array = [];
        //dans session deux clés voir dans CartServices : "quantity" => $quantity, "product" => $product
        foreach ($data['products'] as $products) {
            $cartDetails = new CartDetails(); //remplissage de la table cart-details
            $subtotal = $products['quantity'] * $products['product']->getPrice()/100;
            $cartDetails->setCarts($cart)
            ->setProductName($products['product']->getNameProduct())
            ->setProductPrice($products['product']->getPrice()/100)
            ->setProductQuantity($products['quantity'])
            ->setSubtotalHt($subtotal)
            ->setTaxe(round($subtotal*0.2), 2)
            ->setSubTotalTtc($subtotal*1.2);
            
        $this->manager->persist($cartDetails);
        $cart_details_array[] = $cartDetails;
        }
        $this->manager->flush();

        return $reference;
    }
    

    public function generateUuid()
    {
        // Initialise le générateur de nombres aléatoires Mersenne Twister
        mt_srand((double)microtime()*100000);

        //strtoupper : Renvoie une chaîne en majuscules
        //uniqid : Génère un identifiant unique
        $charid = strtoupper(md5(uniqid(rand(), true)));

        //Générer une chaîne d'un octet à partir d'un nombre
        $hyphen = chr(45);

        //substr : Retourne un segment de chaîne
        $uuid = ""
        .substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid, 12, 4).$hyphen
        .substr($charid, 16, 4).$hyphen
        .substr($charid, 20, 12);
        
        return $uuid;
    }
}