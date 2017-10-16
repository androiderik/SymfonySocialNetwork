<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PublicationController extends Controller{
	public function indexAction(Request $request){ //get request object
		
            return $this->render('AppBundle:Publication:home.html.twig');
	}
}