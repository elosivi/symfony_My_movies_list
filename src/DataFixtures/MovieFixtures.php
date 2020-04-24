<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Movie;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {   $random=0260713;
        for($i=1;$i<=30;$i++){
            $movie = new Movie();
            $movie->setImdbId("tt".$random);
            $random++;        
            $manager->persist($movie);

        }
        
        $manager->flush();
    }

}
