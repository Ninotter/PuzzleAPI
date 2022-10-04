<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use App\Entity\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class AppFixtures extends Fixture
{

    /**
     * Faker Generator
     * @var Generator
     */
    private Generator $faker;

    public function __construct(){
        $this->faker = Factory::create('CC3');
    }
    public function load(ObjectManager $manager): void
    {
        $lesTypes = array();
        for ($i=0; $i < 5; $i++) { 
            $type = new Type();
            $type->setNom($this->faker->word());
            $lesTypes[] = $type;
            $manager->persist($type);
        }
        for ($i=0; $i < 20; $i++) { 
            $product = new Produit();
            $product->setNom($this->faker->word());
            $product->setNiveauDifficulte(mt_rand(0,5));
            $product->setType($lesTypes[array_rand($lesTypes)]);
            $product->setTempsCompletion($product->getNiveauDifficulte() * mt_rand(2,30));
            $product->setNbPiece(mt_rand(1,40));
            $prix = mt_rand(30,1000) / 10;
            $product->setPrix($prix);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
