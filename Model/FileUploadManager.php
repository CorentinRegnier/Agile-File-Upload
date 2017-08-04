<?php

namespace AgileFileUploadBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;
use AgileFileUploadBundle\Form\Type\FileType;
use AgileFileUploadBundle\Manager\ImageManager;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class FileUploadManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    protected $formFactory;

    protected $imageManager;

    protected $assetsPackage;

    protected $translator;

    protected $requestStack;

    function __construct(
        ObjectManager $om,
        FormFactoryInterface $formFactory,
        ImageManager $imageManager,
        Packages $assetsPackage,
        TranslatorInterface $translator,
        RequestStack $requestStack
    ) {
        $this->om            = $om;
        $this->formFactory   = $formFactory;
        $this->imageManager  = $imageManager;
        $this->assetsPackage = $assetsPackage;
        $this->translator    = $translator;
        $this->requestStack  = $requestStack;
    }

    /**
     * @param array $fileOptions
     * @return Form
     */
    public function getForm(array $fileOptions = [])
    {
        $formBuilder = $this->formFactory->createNamedBuilder('file', FormType::class, null);
        $formBuilder->add('file', FileType::class, $fileOptions);

        return $formBuilder->getForm();
    }

    /**
     * @param \Closure|null $callback
     * @param array         $fileOptions
     * @return JsonResponse
     */
    public function handleForm(\Closure $callback = null, array $fileOptions = [])
    {
        $response = new JsonResponse();

        // < IE10 doesn't support JSON responsse
        if (!in_array('application/json', $this->requestStack->getCurrentRequest()->getAcceptableContentTypes())) {
            $response->headers->set('Content-type', 'text/plain');
        }

        $form = $this->getForm($fileOptions);
        $form->handleRequest($this->requestStack->getCurrentRequest());
        if ($form->isValid()) {
            /** @var FileInterface $data */
            $data = $form->get('file')->getData();
            if ($callback) {
                call_user_func($callback, $data, $this->om);
            }
            $this->om->persist($data);
            $this->om->flush();

            $file = $this->getFileResponse($data);

            $content = [
                'file' => $file,
            ];
        } else {
            $errors = [];
            $this->getErrors($form, $errors);

            $response->setStatusCode(400);

            $content = [
                'errors' => $errors,
            ];
        }

        $response->setData($content);

        return $response;
    }

    public function getFileResponse(FileInterface $file)
    {
        if (strpos($file->getMimeType(), 'image/') === 0) {
            if ($originFilterName = $this->requestStack->getCurrentRequest()->get('origin_filter_name')) {
                $fileUrl = $this->imageManager->getImagePath($file, $originFilterName);
            } else {
                $fileUrl = $this->assetsPackage->getUrl($file->getPath());
            }
            if ($filterName = $this->requestStack->getCurrentRequest()->get('filter_name')) {
                $thumbnailUrl = $this->imageManager->getImagePath($file, $filterName);
            }
        } else {
            $fileUrl = $this->assetsPackage->getUrl($file->getPath());
        }

        $file = [
            'id'  => $file->getId(),
            'url' => $fileUrl
        ];
        if (isset($thumbnailUrl)) {
            $file['thumbnail_url'] = $thumbnailUrl;
        }

        return $file;
    }

    private function getErrors(FormInterface $form, array &$errors)
    {
        foreach ($form->getErrors() as $error) {
            $errors[] = $this->translator->trans($error->getMessage(), $error->getMessageParameters(), 'AgileFileUploadBundle');
        }
        foreach ($form as $child) {
            $this->getErrors($child, $errors);
        }
    }
}
