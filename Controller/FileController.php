<?php

namespace AgileFileUploadBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use AgileKernelBundle\Controller\Traits\UtilsTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class FileController
 *
 * @package AgileFileUploadBundle\Controller
 */
class FileController extends Controller
{
    use UtilsTrait;

    /**
     * @return JsonResponse
     */
    public function uploadAction()
    {
        $fileManager = $this->get('agile_fileupload.file_upload_manager');

        return $fileManager->handleForm();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $id    = $request->get('id');
        $class = $this->getParameter('agile_fileupload.model.file.class');

        /** @var ObjectManager $om */
        $om = $this->getDoctrine()->getManager();

        $file = $om->getRepository($class)->find($id);
        if (!$file) {
            throw $this->createNotFoundException('File not found');
        }

        $om->remove($file);
        $om->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([]);
        }

        $this->addFlash('success', 'flashes.delete.success', true, [], 'AgileFileUploadBundle');

        return $this->redirect($request->headers->get('referer'));
    }
}
