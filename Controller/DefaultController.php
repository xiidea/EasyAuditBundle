<?php

namespace Xiidea\EasyAuditBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($page)
    {
        return $this->render('XiideaEasyAuditBundle:Default:index.html.twig', ['page' => $page]);
    }
}
