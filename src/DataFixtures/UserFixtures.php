<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i=1;$i<=10;$i++){
            $user = new User();
            $user->setName("Toto_$i")
                    ->setEmail("Toto$i@toto.com")
                    ->setPassword("toto$i")
                    ->setCreationDate(new\DateTime())
                    ->setIsAdmin("0");
            $manager->persist($user);

        }
        for($i=1;$i<=3;$i++){
            $admin = new User();
            $admin->setName("Martin_$i")
                    ->setEmail("martin$i@martin.com")
                    ->setPassword("martin$i")
                    ->setCreationDate(new\DateTime())
                    ->setIsAdmin("1");
            $manager->persist($admin);

        }
        $manager->flush();
    }
}
