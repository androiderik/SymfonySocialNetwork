<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use BackendBundle\Entity\Following;
use BackendBundle\Entity\User; //import class User (Entity)


class FollowingController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function followAction(Request $request){
        echo "Follow Action";
        die();
    }
}
