<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setName($faker->name);
            $user->setEmail($faker->unique()->safeEmail);
            $user->setFirstName($faker->firstName);
            $password = password_hash('password', PASSWORD_DEFAULT);
            $user->setPassword($password);
            $roles = ['ROLE_USER'];
            $user->setRoles([$faker->randomElement($roles)]);


            $manager->persist($user);
        }
        $manager->flush();
    }
}
