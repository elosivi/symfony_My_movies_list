<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\User;

class FriendsController extends AbstractController
{
    /**
     * @Route("/friends", methods={"GET"}, name="get_friends")
     */
    public function displayUsers()
    {
        // Display all user's friends and display all users in application
        $friends = $this->findAllFriends();

        $users = $this->findAllUsers();

        return $this->render('friends/index.html.twig', [
            'friends' => $friends,
            'users' => $users,
        ]);
    }

    /**
     * @Route("/friends", methods={"POST"}, name="search_friends")
     */
    
    public function searchForFriends()
    {
        // Display all user's friends and search bar result
        $friends = $this->findAllFriends();

        $searchedUsers = $this->findSearchedUsers();

        return $this->render('friends/index.html.twig', [
            'friends' => $friends,
            'users' => $searchedUsers,
        ]);
    }

    public function findSearchedUsers()
    {
        // Search for users according to search bar entry
        $user_id = $this->getUser()->getId();
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery("SELECT u
                                            FROM App\Entity\User u
                                            WHERE u.name LIKE :searched_value
                                            AND u.id != :myself");
        $query->setParameter('searched_value', '%'.$_POST['searched_friend'].'%');
        $query->setParameter('myself', $user_id);

        $users = $query->getResult();
        return $users;
    }

    public function findAllFriends()
    {
        // Get all user's friends
        $user_id = $this->getUser()->getId();
        dump($user_id);
        $entityManager = $this->getDoctrine()->getManager();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        $friends = $user->getUsers();
        return $friends;   
    }

    public function findAllUsers()
    {
        // Get all users in DB except the current user (myself)
        $user_id = $this->getUser()->getId();
        $entityManager = $this->getDoctrine()->getManager();
        $query = $entityManager->createQuery("SELECT u
                                            FROM App\Entity\User u
                                            WHERE u.id != :myself");
        $query->setParameter('myself', $user_id);

        $users = $query->getResult();
        return $users;
    }

    /**
     * @Route("/addfriend/{friend_id}", methods={"GET"}, name="add_friend")
     */
    public function addFriendToUser($friend_id) {
        $entityManager = $this->getDoctrine()->getManager();

        $user_id = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        $friend = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($friend_id);

        // Double insertion : $user gets a $friend and $friend gets a $user
        $user->addUser($friend);
        $friend->addUser($user);

        $entityManager->persist($user);
        $entityManager->persist($friend);

        $entityManager->flush();

        return $this->redirectToRoute('get_friends');
    }

    /**
     * @Route("/removefriend/{friend_id}", methods={"GET"}, name="remove_friend")
     */
    public function removeFriend($friend_id)
    {
        // Break friendship between two users
        $entityManager = $this->getDoctrine()->getManager();

        $user_id = $this->getUser()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($user_id);

        $friend = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($friend_id);

        // Double deletion : two users are not friends any more so they can not share lists anymore
        $user->removeUser($friend);
        $friend->removeUser($user);

        $entityManager->persist($user);
        $entityManager->persist($friend);
        $entityManager->flush();        
        
        return $this->redirectToRoute('get_friends');
    }
}
