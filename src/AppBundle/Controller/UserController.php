<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use BackendBundle\Entity\User; //import class User (Entity)
use AppBundle\Form\RegisterType; //import class RegisterType (create Form)
use AppBundle\Form\UserType; //import class UserType (create User Biographic Form)

class UserController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function loginAction(Request $request) { //get request object
        if (is_object($this->getUser())) { //There is a object(array user data), means is a user logged if not then is null
            return $this->redirect('home');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle:User:login.html.twig', array(
                    'last_username' => $lastUsername,
                    'error' => $error
        ));
    }

    public function registerAction(Request $request) { //get request object
        if (is_object($this->getUser())) { //There is a object(array user data), means is a user logged if not then is null
            return $this->redirect('home');
        }

        $user = new User(); //User entity
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request); //direct from form to entity
        if ($form->IsSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager(); //EntityManager
                //$user_repo = $em->getRepository("BackendBundle:User");
                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                        ->setParameter('email', $form->get('email')->getData())
                        ->setParameter('nick', $form->get('nick')->getData());

                $user_isset = $query->getResult();
                if (count($user_isset) == 0) {
                    $factory = $this->get("security.encoder_factory");
                    $encoder = $factory->getEncoder($user);
                    $password = $encoder->encodePassword($form->get('password')->getData(), $user->getSalt());

                    $user->setPassword($password);
                    $user->setRole("ROLE USER");
                    $user->setImage(null);

                    $em->persist($user); //persisted objects in doctrine
                    $flush = $em->flush(); //save in d.b
                    if ($flush == null) {
                        $status = "Te has registrado correctamente";
                        $this->session->getFlashBag()->add("status", $status);
                        return $this->redirect("login");
                    } else {
                        $status = "No te has registrado correctamente";
                    }
                } else {
                    $status = "El usuario ya existe";
                }
            } else {
                $status = "No te has registrado correctamente";
            }
            $this->session->getFlashBag()->add("status", $status);
        }

        return $this->render('AppBundle:User:register.html.twig', array(
                    "form" => $form->createView()
        ));
    }

    public function nickTestAction(Request $request) {
        $nick = $request->get("nick"); //request name
        $em = $this->getDoctrine()->getManager(); //EntityManager All entities
        $user_repo = $em->getRepository("BackendBundle:User"); //get User entity
        $user_isset = $user_repo->findOneBy(array("nick" => $nick)); //One d.b register == request name

        $result = "used";

        if (count($user_isset) >= 1 && is_object($user_isset)) {
            $result = "used";
        } else {
            $result = "unused";
        }
        return new Response($result);
    }

    public function editUserAction(Request $request) {

        $user = $this->getUser(); //Instance User Logged (UPDATE DATA)
        $user_image = $user->getImage();
        $form = $this->createForm(UserType::class, $user); // put the current data user

        $form->handleRequest($request); //direct from form to entity
        if ($form->IsSubmitted()) {
            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager(); //EntityManager

                $query = $em->createQuery('SELECT u FROM BackendBundle:User u WHERE u.email = :email OR u.nick = :nick')
                        ->setParameter('email', $form->get('email')->getData())
                        ->setParameter('nick', $form->get('nick')->getData());

                $user_isset = $query->getResult();
                if ( count($user_isset) == 0 || ($user->getEmail() == $user_isset[0]->getEmail() && $user->getNick() == $user_isset[0]->getNick())) {
                    //upload file
                    $file = $form["image"]->getData();

                    if (!empty($file) && $file != null) {
                        $ext = $file->guessExtension();
                        if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif') {
                            $file_name = $user->getId() . time() . '.' . $ext;
                            $file->move("uploads/users", $file_name);

                            $user->setImage($file_name); //default image
                        }
                    } else {
                        $user->setImage($user_image); //current image in d.b if isset == null
                    }

                    $em->persist($user); //persisted objects in doctrine
                    $flush = $em->flush(); //save in d.b

                    if ($flush == null) {
                        $status = "Has modificado tus datos correctamente ";
                    } else {
                        $status = "No has modificado tus datos correctamente";
                    }
                } else {
                    $status = "El usuario ya existe ";
                }
            } else {
                $status = "No has modificado tus datos correctamente";
            }
            $this->session->getFlashBag()->add("status", $status);
            return $this->redirect('my-data');
        }


        return $this->render('AppBundle:User:edit_user.html.twig', array(
                    "formBio" => $form->createView()
        ));
    }

    public function usersAction(Request $request) { //people
        $em = $this->getDoctrine()->getManager(); //EntityManager
        $dql = "SELECT u FROM BackendBundle:User u ORDER BY u.id ASC";
        $query = $em->createQuery($dql);

        $paginator = $this->get("knp_paginator");
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 5); //$_GET 
        
        return $this->render('AppBundle:User:users.html.twig', array(
                    'pagination' => $pagination
        ));
    }
    
    public function searchAction(Request $request) { //search people
        $em = $this->getDoctrine()->getManager(); //EntityManager
        
        $search = $request->query->get("search", null);
        if ($search == null) {
            return $this->redirect($this->generateURL('home/publications')); 
        }
        
        $dql = "SELECT u FROM BackendBundle:User u " 
                . "WHERE u.name LIKE :search OR u.surname LIKE :search "
                . "OR u.nick LIKE :search ORDER BY u.id ASC";
        $query = $em->createQuery($dql)->setParameter('search', "%$search%"); //NEEDS PARAMETERS

        $paginator = $this->get("knp_paginator");
        $pagination = $paginator->paginate(
                $query, $request->query->getInt('page', 1), 5); //$_GET 
        
        return $this->render('AppBundle:User:users.html.twig', array(
                    'pagination' => $pagination
        ));
    }

}
