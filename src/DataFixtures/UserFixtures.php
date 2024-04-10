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
        // Admin
        $admin = new User();
        $admin->setName('admin');
        $admin->setEmail('admin@admin.fr');
        $admin->setFirstName('admin');
        $password = password_hash('admin', PASSWORD_DEFAULT);
        $admin->setPassword($password);
        $roles = ['ROLE_ADMIN'];
        $admin->setRoles($roles);
        $admin->setPhoneNumber('0666666666');
        $state = 1;
        $admin->setState($state);
        $manager->persist($admin);

        // User Lambda
        $faker = Faker\Factory::create();

        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setName($faker->lastName());
            $user->setEmail($faker->unique()->safeEmail);
            $user->setFirstName($faker->firstName);
            $password = password_hash('password', PASSWORD_DEFAULT);
            $user->setPassword($password);
            $roles = ['ROLE_USER'];
            $user->setRoles([$faker->randomElement($roles)]);
            $user->setPhoneNumber($faker->phoneNumber);
            $state = 1;
            $user->setState($state);


            $manager->persist($user);
        }
        $manager->flush();
    }

}
