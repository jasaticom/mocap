<?php

namespace Jasati\MocapBundle\Model\Event;

use Doctrine\ORM\EntityManager;
use Jasati\MocapBundle\Model\User\AbstractUser;
use Jasati\MocapBundle\Model\User\UserRepository;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class LoginListener implements AuthenticationSuccessHandlerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        $captcha = $request->request->get('captcha');

        $thisCaptcha = $request->getSession()->get('captcha', null);

        if(strcmp($captcha, $thisCaptcha) != 0) {
            throw new BadCredentialsException('Wrong captcha');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine')->getManager();
        /** @var UserRepository $repository */
        $repository = $em->getRepository(AbstractUser::getClassName());
        /** @var AbstractUser $user */
        $user = $token->getUser();
        $user->addLoginEvent();
        $repository->update($user);

        /** @var Router $router */
        $router = $this->container->get('router');
        return new RedirectResponse($router->generate('index'));
    }
}