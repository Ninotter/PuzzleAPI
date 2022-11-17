<?php

namespace App\DataFixtures;

use App\Entity\LignePanier;
use Faker\Factory;
use App\Entity\Type;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Produit;
use App\Entity\Panier;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    /**
     * Faker Generator
     * @var Generator
     */
    private Generator $faker;

    /**
     * Classe hasheant le password
     *
     * @var UserPasswordHasherInterface
     */
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher){
        $this->faker = Factory::create('CC3');
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {

        $lesTypes = array();
        for ($i=0; $i < 5; $i++) { 
            $type = new Type();
            $type->setNom($this->faker->word());
            $type->setStatus(true);
            $lesTypes[] = $type;
            $manager->persist($type);
        }
        $lesProduits = array();
        for ($i=0; $i < 20; $i++) { 
            $product = new Produit();
            $product->setNom($this->faker->word());
            $product->setNiveauDifficulte(mt_rand(0,5));
            $product->setType($lesTypes[array_rand($lesTypes)]);
            $product->setTempsCompletion($product->getNiveauDifficulte() * mt_rand(2,30));
            $product->setNbPiece(mt_rand(1,40));
            $prix = mt_rand(30,1000) / 10;
            $product->setPrix($prix);
            $product->setDateCreation($this->faker->dateTime());
            $product->setPaysOrigine($this->faker->countryISOAlpha3());
            $product->setStatus(true);
            $lesProduits[] = $product;
            $manager->persist($product);
        }

        //Authentified Users
        for ($i=0; $i < 10; $i++) { 
            $userUser = new User();
            $password = $this->faker->password(2,6);
            $userUser->setUsername($this->faker->username() .'@'.$password);
            $userUser->setRoles(["USER"]);
            $userUser->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
            $manager->persist($userUser);
            $panier = new Panier();
            $panier->setUser($userUser);
            $lignePanier= new LignePanier();
            $lignePanier->setPanier($panier);
            $lignePanier->setProduit($lesProduits[rand(0,19)]);
            $lignePanier->setQuantity(rand(1,25));
            $manager->persist($lignePanier);
            $panier->getLignesPanier()->add($lignePanier);
            $manager->persist($panier);
        }

        $userAdmin = new User();
        $userAdmin->setUsername("admin");
        $userAdmin->setRoles(["ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        

        $manager->flush();
    }
}
