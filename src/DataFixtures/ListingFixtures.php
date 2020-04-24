<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Listing;

class ListingFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {   $random=0260713;
        for($i=1;$i<=10;$i++){
            $list = new Listing();
            $list->setTitle("Mylist_#".$i);
                    
            $manager->persist($list);

        }
        
        $manager->flush();
    }

}
