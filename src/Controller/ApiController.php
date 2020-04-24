<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


use App\Entity\Movie;
use App\Entity\Listing;
use App\Entity\User;

//This class links the data of the omdapi API with the moovies app, by the API link url to show and add movie in views or in internal bdd 


class ApiController extends AbstractController {


    // 1  >> give_films function return a list of films according to the content of search.
// 1.1  >>  listen if POST is defined, if a seacrh was send or not. If not : we have to initialize the search, if "s" is empty, the API url return an error
// 1.2  >>  initialization of API-url-request, add an option if user mention a search with space between words.
// 1.3  >>  use curl method to converse with with api and return result : $resp
// 1.4  >>  API return a json format, so use json_decode to recover data and use it. The json file have several levels we just need the ['search,] one
// 1.5  >>  Return array of results in the view /search

    /**
     * @Route("/search",  name="search")
     */

      
    public function give_films(){ 
        $mess="";
    
        //1.1//
        if (!(isset($_POST['searchByTitle']))){
            $byTitle="all";
        }else{
            $byTitle=$_POST['searchByTitle'];
        }
        if (!function_exists("curl_init")){
            die("Désolé cURL n'est pas installé !");
        }

        // 1.2 //
        $url = "http://www.omdbapi.com/?apikey=3602bf92&s=".$byTitle."&type=movie"; 
        $url = str_replace(" ", "%20", $url); 

        //1.3//
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        $resp = curl_exec($curl);
        curl_close($curl);
      
        // 1.4 //
        $json = json_decode($resp,true);
        if (isset($json['Search'])){
            $allmovies=($json['Search']);
        }else{
            $allmovies="";
            $mess= "Please search for a movie";
        }

        //other action needed : to show lists by user in the same view
        $listings = $this->showListsBelongingToUser();    
      
        // 1.5//
        return $this->render('searchfilms.html.twig', [
            'movies' =>$allmovies,
            'post' =>$_POST,
            'mess' =>$mess,
            'lists' => $listings,
         ]);
    }

    
    
    public function addMovieToDB(string $imdb_id, string $title, $year, string $poster)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $movie = new Movie();
        $movie->setImdbId($imdb_id);
        $movie->setTitle($title);
        $movie->setYear($year);
        $movie->setPoster($poster);

        $entityManager->persist($movie);
        $entityManager->flush();

        return $movie;
    }

    public function addMovieToListing($movie_id, $listing_id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Get Movie object by $movie_id
        $movie = $this->getDoctrine()
            ->getRepository(Movie::class)
            ->find($movie_id);

        // Get Listing object by $movie_id
        $listing = $this->getDoctrine()
            ->getRepository(Listing::class)
            ->find($listing_id);

        $listing->addMovie($movie);

        $entityManager->persist($listing);

        $entityManager->flush();
    }
    
    /**
     * @Route("/movie_api/{imdb_id}", methods={"GET"}, name="view_movie_api")
     */
    public function viewInformationsAboutMovie($imdb_id)
    {
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

        $lists = $this->showListsBelongingToUser();

        return $this->render('movie_description.html.twig', [
            'user' => "*** INFORMATION DE SESSION ***",
            'movie' => $movie,
            'user_lists' => $lists,
        ]);
    }
    
    /**
     * @Route("/search_post", methods={"POST"}, name="search_post")
     * 
     */

    
    public function addMovieToDbAndToListing()
    {
        $title = $_POST['title'];
        $imdb_id = $_POST['imdb_id'];
        $year = $_POST['year'];
        $poster = $_POST['poster'];

        $movie = $this->addMovieToDB($imdb_id, $title, $year, $poster);

        $list = $_POST['lists'];
        
        $this->addMovieToListing($movie->getId(), $list);

        return $this->redirectToRoute('get_lists');
    }


    public function showListsBelongingToUser() {
        
        $user_id = $this->getUser()->getId();
        
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);
        
        $listings = $user->getListings();
            return $listings;
   
        }

    /**
     * @Route("/movie_api/search_post", methods={"POST"}, name="search_post_movie")
     */
    
    public function addMovieToDbAndToListingTwo()
    {
        $title = $_POST['title'];
        $imdb_id = $_POST['imdb_id'];
        $year = $_POST['year'];
        $poster = $_POST['poster'];

        $movie = $this->addMovieToDB($imdb_id, $title, $year, $poster);

        $list = $_POST['lists'];
        
        $this->addMovieToListing($movie->getId(), $list);

        return $this->redirectToRoute('get_lists');
    }


    public function showListsBelongingToUserTwo() {
        
        $user_id = $this->getUser()->getId();
        
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);
        
        $listings = $user->getListings();
            return $listings;
   
        }
    

    }
