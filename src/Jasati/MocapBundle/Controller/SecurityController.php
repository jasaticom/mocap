<?php

namespace Jasati\MocapBundle\Controller;

use Gregwar\Captcha\CaptchaBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        /** @var Session $session */
        $word = (string)rand(1000, 9999);
        $session = $request->getSession();
        $builder = new CaptchaBuilder($word);
        $session->set('captcha', $builder->getPhrase());

        /** @var AuthenticationUtils $authUtils */
        $authUtils = $this->get('security.authentication_utils');
        $error = $authUtils->getLastAuthenticationError();
        $lastUserName = $authUtils->getLastUsername();

        return $this->render('JasatiMocapBundle:user:login.html.twig', array(
            'last_username' => $lastUserName,
            'error' => $error,
            'captcha'=> $builder->build(150, 50)
        ));
    }

    /**
     * @Route("/logout", name="logout")
     * @param Request $request
     */
    public function logout(Request $request)
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->invalidate();
    }
}