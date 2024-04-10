<?php

namespace App\DataFixtures;

use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
class LocationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
//        $faker = Faker\Factory::create('fr_FR');
//
//        for ($i = 0; $i < 10; $i++) {
//            $location = new Location();
//            $location->setName($faker->name);
//            $location->setCity($faker->city);
//            $location->setPostcode($faker->postcode);
//            $location->setStreet($faker->streetAddress);
//            $location->setLatitude($faker->latitude);
//            $location->setLongitude($faker->longitude);
//            $manager->persist($location);
//        }
//        $manager->flush();
    }
}
