<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Entity\User;
use App\Entity\Listing;
use App\Entity\Movie;

class MovieController extends AbstractController
{  
    /**
     * @Route("/lists", methods={"GET"}, name="get_lists")
     */
    public function showListsBelongingToUser()
    {
        $friends = $this->findAllFriends();

        $user_id = $this->getUser()->getId();

        // Get User object by $user_id
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        if (!$user) {
            throw $this->createNotFoundException(
                'No user found for id ' . $user_id
            );
        }
        // Get all Listing objects (containing Movies objects) belonging to $user
        if ($user)
            $listings = $user->getListings();

        return $this->render('listfilms.html.twig', [
            'user' => $user,
            'friends' => $friends,
            'listings' => $listings,
        ]);
    }

    /**
     * @Route("/lists", methods={"POST"}, name="post_lists")
     */
    public function createListingAndAddToUser()
    {
        $user_id = $this->getUser()->getId();

        // You can fetch the EntityManager via $this->getDoctrine()
        $entityManager = $this->getDoctrine()->getManager();

        $listing = new Listing();
        $listing->setTitle($_POST["new_list_name"]);
        $listing->setOwnerId($user_id);

        // Get User object by $user_id
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        // Add new Listing to $user
        $user->addListing($listing);

        // Tell Doctrine you want to (eventually) save the listing (no queries yet)
        $entityManager->persist($listing);
        $entityManager->persist($user);

        // Actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return $this->redirectToRoute('get_lists');
    }

    /**
     * @Route("/removefromlist/{listing_id}/{movie_id}", methods={"GET"}, name="removefromlist")
     */
    public function removeFromList($listing_id, $movie_id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)
            ->find($listing_id);

        $movie = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->find($movie_id);

        // The given movie is removed from the list
        $listing->removeMovie($movie);

        $entityManager->persist($listing);
        $entityManager->flush();
        
        return $this->redirectToRoute('get_lists');
    }

    /**
     * @Route("/movie/{movie_id}", methods={"GET"}, name="view_movie")
     */
    public function viewInformationsAboutMovie($movie_id)
    {
        // Get Movie object by $movie_id and request external API to get full data about movie
        $movie = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->find($movie_id);

        $imdb_id = $movie->getimdb_id();

        // Init curl
        $curl = curl_init();
        $url ="http://www.omdbapi.com/?apikey=d21e2164&i=$imdb_id&plot=full";
        
        // retourner la valeur récupérée au lieu de l'afficher
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        
        // variable de retour / curl method
        $response = curl_exec($curl);
        curl_close($curl);
        
        //pour renvoyer la data avec mise en forme: 
        $movie = json_decode($response, true);
        
        return $this->render('movie_description.html.twig', [
            'user' => "*** INFORMATION DE SESSION ***",
            'movie' => $movie,
            
        ]);
    }

    /**
     * @Route("/remove/{listing_id}", methods={"GET"}, name="delete_lists")
     */ 
    public function removeListing($listing_id)
    {
        // Delete list from DB if the current user is the owner of the list
        $entityManager = $this->getDoctrine()->getManager();

        // Get Listing object by $listing_id
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)
            ->find($listing_id);

        // Get User object by $user_id
        $user_id = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        if ($user_id === $listing->getowner_id()) {
            $entityManager->remove($listing);
        } else {
            $user->removeListing($listing);
        }
        
        $entityManager->flush();

        return $this->redirectToRoute('get_lists');
    }

    /**
     * @Route("/share/{listing_id}/{user_id}", methods={"GET"}, name="share_list")
     */  
    public function addListToUser($listing_id, $user_id)
    {
        $entityManager = $this->getDoctrine()->getManager();       

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)
            ->find($listing_id);

        $user->addListing($listing);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('get_lists');
    }

    public function findAllFriends()
    {
        // Find and return all user's friends in DB

        $user_id = $this->getUser()->getId();
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        $friends = $user->getUsers();
        return $friends;
    }
}
