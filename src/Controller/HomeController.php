<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="home_")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @return string
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig');
    }
}
