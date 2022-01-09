<?php

namespace App\Controller\Admin;

use App\Entity\Cart;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CartCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Cart::class;
    }

    public function configureCrud(Crud $crud): Crud{
        return $crud->setDefaultSort(['id'=>'DESC']);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('reference'),
            TextField::new('fullname'),
            TextField::new('transportName'),
            MoneyField::new('transportPrice')->setCurrency('EUR'),
            TextareaField::new('livraisonAdresse'),
            IntegerField::new('quantity', 'Quantité totale'),
            MoneyField::new('subTotalHT', 'Sous total HT')->setCurrency('EUR'),
            MoneyField::new('taxe', 'Montant taxe')->setCurrency('EUR'),
            MoneyField::new('subTotalTTC', 'Total TTC inclus transport')->setCurrency('EUR'),
            
        ];
    }
    
}
