<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\AccueilSliderRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(ProductRepository $repoProduct, AccueilSliderRepository $repoSlider): Response
    {
        $products = $repoProduct->findAll();
        $accueilSlider = $repoSlider->findBy(['isDisplayed'=>true]);
        //dd($accueilSlider);
        $productBest = $repoProduct->findByIsBest(1);
        $productNew = $repoProduct->findByIsNew(1);
        $productFeatured = $repoProduct->findByIsFeatured(1);
        $productSpecialOffer = $repoProduct->findByIsSpecialOffer(1);

        //dd($products);
        //dd([$productBest, $productNew, $productFeatured, $productSpecialOffer]);
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
            'products' => $products,
            'productBest' => $productBest,
            'productNew' => $productNew,
            'productFeatured' => $productFeatured,
            'productSpecialOffer' => $productSpecialOffer,
            'accueilSlider'=>$accueilSlider
        ]);
    }

    /**
     * @Route("/product/{slug}", name="product_details")
     */
    public function show(?Product $product): Response{
        
        if(!$product){
            return $this->redirectToRoute("accueil");
        }

        return $this->render("accueil/single_product.html.twig",[
            'product' => $product
        ]);
    }

    /**
     * @Route("/shop", name="shop")
     */
    public function shop(ProductRepository $repoProduct, Request $request): Response
    {
        $products = $repoProduct->findAll();

        $search = new SearchProduct();
        $form = $this->createForm(SearchProductType::class, $search);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           $products = $repoProduct->findWithSearch($search);    
        }

        return $this->render('accueil/shop.html.twig', [
            'products' => $products,
            'search' => $form->createView()
        ]);
    }
}
