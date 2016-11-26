<?php

namespace Jasati\MocapBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Gregwar\Captcha\CaptchaBuilder;
use Jasati\MocapBundle\Model\Event\Event;
use Jasati\MocapBundle\Model\User\AbstractUser;
use Jasati\MocapBundle\Model\User\User;
use Jasati\MocapBundle\Model\User\UserRepository;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class UserController extends Controller
{
    /**
     * @Route("/user",name="user")
     * @Method ("GET")
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        $session = $request->getSession();
        $session->remove('userName');
        $session->remove('userEmail');

        return $this->redirectToRoute('user_search');
    }

    /**
     * @Route("/user/register", name="user_register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        /** @var Session $session */
        $session = $request->getSession();
        /** @var Logger $logger */
        $logger = $this->get('logger');

        if($request->getMethod() == Request::METHOD_POST)
        {
            $email  = $request->request->get('email', null);
            $name   = $request->request->get('name', null);
            $clearPassword = $request->request->get('password', null);

            try {
                if(!$email OR !$clearPassword) {
                    throw new \Exception('err_registration_failed_1');
                }
                if(!$name) {
                    throw new \Exception('err_registration_failed_2');
                }

                $captcha     = $request->request->get('captcha');
                $thisCaptcha = $session->get('captcha', null);

                if(strcmp($captcha, $thisCaptcha) != 0) {
                    throw new \Exception('err_registration_failed_3');
                }

                $user = new User($email, $name);
                $encoder = $this->container->get('security.password_encoder');
                $encoded = $encoder->encodePassword($user, $clearPassword);
                $user->setPassword($encoded);

                $em = $this->getDoctrine()->getManager();
                /** @var UserRepository $repository */
                $repository = $em->getRepository(User::getClassName());
                $repository->persist($user);

                $session->getFlashBag()->add('success', 'lbl_register_success');

                return $this->redirectToRoute('login');
            } catch (\Exception $e) {
                $logger->addError((string) $e);
                $session->getFlashBag()->add('error', $e->getMessage());
            }
        }

        $word = (string)rand(1000, 9999);
        $builder = new CaptchaBuilder($word);
        $session->set('captcha', $builder->getPhrase());

        return $this->render('JasatiMocapBundle:user:registration.html.twig', array(
            'captcha'=> $builder->build(150, 50)
        ));
    }

    /**
     * @Route("/user/search/{page}", name="user_search",
     *      requirements={"page": "\d+"}, defaults = {"page"=1}))
     *
     * @param Request $request
     * @param $page
     * @param $limit
     * @return Response
     */
    public function search(Request $request, $page = 1, $limit = 10)
    {
        $session = $request->getSession();

        if($request->getMethod() == Request::METHOD_POST)
        {
            $session->set('userName',  $request->request->get('name', ''));
            $session->set('userEmail', $request->request->get('email', ''));
        }

        $userName  = $session->get('userName', '');
        $userEmail = $session->get('userEmail', '');

        $criteria = array(
            'userName'  => $userName,
            'userEmail' => $userEmail,
            'page'      => $page,
            'limit'     => $limit,
        );

        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::getClassName());
        /** @var Paginator $users */
        $users = $userRepository->findUsers($criteria);

        $response = array();
        $response['users']       = $users;
        $response['recordCount'] = $users->count();
        $response['maxPage']     = ceil($users->count() / $limit);
        $response['currentPage'] = $users->count() > 0 ? $page : 0;
        $response['route']       = $request->attributes->get('_route');

        return $this->render('JasatiMocapBundle:user:index.html.twig', $response);
    }

    /**
     * @Route("/user/edit/{id}", name="user_edit", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function edit(Request $request, $id)
    {
        if($this->getUser()->getId() != $id) {
            if (! $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                throw new \Exception("You can only edit your account");
            }
        }

        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(AbstractUser::getClassName());
        /** @var AbstractUser $user */
        $user = $userRepository->find($id);

        if(!$user) {
            throw new \Exception("User not found");
        }

        /** @var Session $session */
        $session = $request->getSession();
        /** @var Logger $logger */
        $logger = $this->get('logger');

        $response = array();
        $response['user']   = $user;

        if($request->getMethod() == Request::METHOD_POST)
        {
            $email = $request->request->get('email', null);
            $name  = $request->request->get('name', null);
            $oldPassword  = $request->request->get('oldpassword', null);
            $newPassword  = $request->request->get('newpassword', null);

            try {
                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                    $user->setEmail($email);
                }
                $user->setName($name);

                if($newPassword) {
                    if(password_verify($oldPassword, $user->getPassword()) == false) {
                        throw new \Exception("Old and new password not match");
                    }
                    $encoder = $this->container->get('security.password_encoder');
                    $user->changePassword($encoder->encodePassword($user, $newPassword));
                }
                $user->addEditProfileEvent();
                $userRepository->update($user);
                $session->getFlashBag()->add('success', 'Edit successful');
            } catch (\Exception $e) {
                $logger->addError((string) $e);
                $session->getFlashBag()->add('error', $e->getMessage());
            }

            //return $this->redirectToRoute('user_profile', array ('id' => $user->getId()));
            return $this->render('JasatiMocapBundle:user:profile.html.twig', $response);
        }

        return $this->render('JasatiMocapBundle:user:edit.html.twig', $response);
    }

    /**
     * @Route("/user/profile/{id}", name="user_profile", requirements={"id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     * @param Request $request
     * @param $id
     * @return Response
     * @throws \Exception
     */
    public function profile(Request $request, $id)
    {
        if($this->getUser()->getId() != $id) {
            if (! $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                throw new \Exception("You can only view your account");
            }
        }

        $em = $this->getDoctrine()->getManager();
        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(AbstractUser::getClassName());
        /** @var AbstractUser $user */
        $user = $userRepository->find($id);

        if(!$user) {
            throw new \Exception("User not found");
        }

        $response = array();
        $response['user']   = $user;
        return $this->render('JasatiMocapBundle:user:profile.html.twig', $response);
    }
}