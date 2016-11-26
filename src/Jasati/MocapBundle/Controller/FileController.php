<?php

namespace Jasati\MocapBundle\Controller;

use Jasati\MocapBundle\Model\Event\Event;
use Jasati\MocapBundle\Model\File\File;
use Jasati\MocapBundle\Model\File\Provinsi;
use Jasati\MocapBundle\Model\File\FileRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class FileController extends Controller
{
    /**
     * @Route("/file", name="file")
     * @Method ("GET")
     * @param Request $request
     * @return RedirectResponse
     */
    public function index(Request $request)
    {
        $session = $request->getSession();
        $session->remove('title');
        $session->remove('category');

        return $this->redirectToRoute('file_browse');
    }

    /**
     * @Route("/file/browse/{page}", name="file_browse",
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
            $session->set('title',  $request->request->get('title', ''));
            $session->set('category', $request->request->get('category', ''));
        }

        $title      = $session->get('title', '');
        $category   = $session->get('category', '');

        $criteria = array(
            'title'     => $title,
            'category'  => $category,
            'page'      => $page,
            'limit'     => $limit,
            'mimes'     => array_merge(File::BINARY, FILE::COMPRESSED)
        );

        $em = $this->getDoctrine()->getManager();
        /** @var FileRepository $fileRepository */
        $fileRepository = $em->getRepository(File::getClassName());

        $categories     = $fileRepository->findCategories();
        $files          = $fileRepository->findFiles(File::getClassName(), $criteria);

        $response = array();
        $response['categories']  = $categories;
        $response['files']       = $files;
        $response['recordCount'] = $files->count();
        $response['maxPage']     = ceil($files->count() / $limit);
        $response['currentPage'] = $files->count() > 0 ? $page : 0;
        $response['route']       = $request->attributes->get('_route');

        return $this->render('JasatiMocapBundle:file:index.html.twig', $response);
    }

    /**
     * @Route("/file/upload",name="file_upload")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request)
    {
        /** @var Session $session */
        $session = $request->getSession();
        /** @var Logger $logger */
        $logger = $this->get('logger');

        $em = $this->getDoctrine()->getManager();
        /** @var FileRepository $fileRepository */
        $fileRepository = $em->getRepository(Provinsi::getClassName());
        $categories     = $fileRepository->findCategories();

        if($request->getMethod() == Request::METHOD_POST)
        {
            $title      = $request->request->get('title', null);
            $categoryId = $request->request->get('category', 0);
            $role       = $request->request->get('roles', null);
            $session->set('category', $categoryId);

            /** @var UploadedFile $file1 */
            $file1 = $request->files->get('file1', null);
            /** @var UploadedFile $file2 */
            $file2  = $request->files->get('file2', null);

            try {
                if(!$file1) {
                    throw new \Exception('err_upload_failed');
                }

                /** @var Provinsi $category */
                $category = $fileRepository->find($categoryId);

                $file = new File($title, $file1);
                $file->setCategory($category);
                if($role) {
                    $file->addRole($role);
                }
                
                if($file2) {
                    $file->addChild(new File(null, $file2));
                }

                $file->move($this->getParameter('mocap_storage'), $this->getUser());
                $fileRepository->persist($file);

                $session->getFlashBag()->add('success', 'lbl_upload_success');
            } catch (\Exception $e) {
                $logger->addError((string) $e);
                $session->getFlashBag()->add('error', $e->getMessage());
            }
        }

        $response = array();
        $response['categories']  = $categories;
        return $this->render('JasatiMocapBundle:file:upload.html.twig', $response);
    }

    /**
     * @Route("/file/download/{id}", name="file_download", requirements={"id": "\d+"})
     * @Method ("GET")
     * @param Request $request
     * @param $id
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function download(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var FileRepository $fileRepository */
        $fileRepository = $em->getRepository(File::getClassName());

        try {
            /** @var File $file */
            $file = $fileRepository->find($id);

            if(! $file->isPublic()) {
                if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
                    throw new \Exception('Please register');
                }
            }

            $response = new BinaryFileResponse ($file->getRealPath());
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalFilename());

            /*$file->addEvent(new Event(Event::TYPE_DOWNLOAD, $this->getUser(), $file));
            $fileRepository->update($file);*/

            return $response;
        } catch (\Exception $e) {
            /** @var Logger $logger */
            $logger = $this->get('logger');
            $logger->addError((string) $e);
            throw $e;
        }
    }

    /**
     * @Route("/file/preview/{id}", name="file_preview", requirements={"id": "\d+"})
     * @Method ("GET")
     * @param Request $request
     * @param $id
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function preview(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var FileRepository $fileRepository */
        $fileRepository = $em->getRepository(File::getClassName());
        /** @var File $file */
        $file = $fileRepository->find($id);

        $criteria = array('mimes' => File::VIDEO);
        $children = $file->getChildren($criteria);

        if(! $children) {
            throw new \Exception('File not found');
        }

        $file = $children[0];

        $response = new BinaryFileResponse ($file->getRealPath());
        $response->headers->set('Content-Type', $file->getMime());
        $response->headers->set('Content-Length', $file->getSize());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getOriginalFilename()
        );


        /*$file->addEvent(new Event(Event::TYPE_PREVIEW, $this->getUser(), $file));
        $fileRepository->update($file);*/

        return $response;
    }

    /**
     * @Route("/file/remove/{id}", name="file_remove", requirements={"id": "\d+"})
     * @Method ("GET")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function remove(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var FileRepository $fileRepository */
        $fileRepository = $em->getRepository(File::getClassName());

        /** @var File $file */
        $file = $fileRepository->find($id);
        if($file) {
            $file->remove($this->getUser());
            $fileRepository->remove($file);
        }
        return $this->redirectToRoute('file');
    }
}