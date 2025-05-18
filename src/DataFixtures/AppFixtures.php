<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $defaultProducts = Product::createProducts();

        foreach ($defaultProducts as $product) {
            $manager->persist($product);
        }

        $manager->flush();
    }
}
