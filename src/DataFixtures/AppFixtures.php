<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Type;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Produit;
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

        //Authentified Users
        for ($i=0; $i < 10; $i++) { 
            $userUser = new User();
            $password = $this->faker->password(2,6);
            $userUser->setUsername($this->faker->username() .'@'.$password);
            $userUser->setRoles(["USER"]);
            $userUser->setPassword($this->userPasswordHasher->hashPassword($userUser, $password));
            $manager->persist($userUser);
        }

        $userAdmin = new User();
        $userAdmin->setUsername("admin");
        $userAdmin->setRoles(["ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

        $lesTypes = array();
        for ($i=0; $i < 5; $i++) { 
            $type = new Type();
            $type->setNom($this->faker->word());
            $type->setStatus(true);
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
            $product->setStatus(true);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
