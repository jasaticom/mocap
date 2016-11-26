<?php

namespace Jasati\MocapBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class IndexController extends Controller
{
    /**
     * @Route("/",name="index")
     */
    public function indexAction()
    {
        return $this->render('JasatiMocapBundle::index.html.twig');
    }

    /**
     * @Route("/profile",name="profile")
     */
    public function profile()
    {
        return $this->render('JasatiMocapBundle::profile.html.twig');
    }

    /**
     * @Route("/contact",name="contact")
     */
    public function contact()
    {
        return $this->render('JasatiMocapBundle::contact.html.twig');
    }

    /**
     * @Route("/locale/{locale}", name="change_locale", requirements={"locale": "\w+"})
     *
     * @param Request $request
     * @param $locale
     * @return Response
     */
    public function changeLocale(Request $request, $locale)
    {
        $request->getSession()->set('_locale', $locale);
        $uri = $request->getUri();
        $referer = $request->headers->get('referer');
        if($uri == $referer) {
            $this->redirect('/');
        }
        return $this->redirect($referer);
    }
}