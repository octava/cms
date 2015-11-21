<?php

namespace Octava\Bundle\MenuBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('OctavaMenuBundle:Default:index.html.twig', array('name' => $name));
    }
}
