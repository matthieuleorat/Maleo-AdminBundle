<?php

namespace Maleo\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MaleoAdminBundle:Default:index.html.twig');
    }
}
