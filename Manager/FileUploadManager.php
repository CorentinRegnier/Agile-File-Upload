<?php

namespace AgileFileUploadBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use AgileFileUploadBundle\Model\FileInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class FileUploadManager
 *
 * @package AgileFileUploadBundle\Manager
 */
class FileUploadManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var PackageInterface
     */
    protected $assetsHelper;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Request
     */
    protected $request;

    /**
     * FileUploadManager constructor.
     *
     * @param ObjectManager        $om
     * @param FormFactoryInterface $formFactory
     * @param ImageManager         $imageManager
     * @param PackageInterface     $assetsHelper
     * @param TranslatorInterface  $translator
     * @param Request              $request
     */
    public function __construct(
        ObjectManager $om,
        FormFactoryInterface $formFactory,
        ImageManager $imageManager,
        PackageInterface $assetsHelper,
        TranslatorInterface $translator,
        Request $request
    ) {
        $this->om           = $om;
        $this->formFactory  = $formFactory;
        $this->imageManager = $imageManager;
        $this->assetsHelper = $assetsHelper;
        $this->translator   = $translator;
        $this->request      = $request;
    }

    /**
     * @param array $fileOptions
     *
     * @return Form
     */
    public function getForm(array $fileOptions = [])
    {
        $formBuilder = $this->formFactory->createNamedBuilder('file', 'form', null, [
            'intention' => 'file',
        ]);
        $formBuilder->add('file', 'agile_file', $fileOptions);

        return $formBuilder->getForm();
    }

    /**
     * @param \Closure|null $callback
     * @param array         $fileOptions
     *
     * @return JsonResponse
     */
    public function handleForm(\Closure $callback = null, array $fileOptions = [])
    {
        $response = new JsonResponse();

        // < IE10 doesn't support JSON responsse
        if (!in_array('application/json', $this->request->getAcceptableContentTypes())) {
            $response->headers->set('Content-type', 'text/plain');
        }

        $form = $this->getForm($fileOptions);
        $form->handleRequest($this->request);
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

    /**
     * @param FileInterface $file
     *
     * @return array|FileInterface
     */
    public function getFileResponse(FileInterface $file)
    {
        if (strpos($file->getMimeType(), 'image/') === 0) {
            if ($originFilterName = $this->request->get('origin_filter_name')) {
                $fileUrl = $this->imageManager->getImagePath($file, $originFilterName);
            } else {
                $fileUrl = $this->assetsHelper->getUrl($file->getPath());
            }
            if ($filterName = $this->request->get('filter_name')) {
                $thumbnailUrl = $this->imageManager->getImagePath($file, $filterName);
            }
        } else {
            $fileUrl = $this->assetsHelper->getUrl($file->getPath());
        }

        $file = [
            'id'  => $file->getId(),
            'url' => $fileUrl,
        ];
        if (isset($thumbnailUrl)) {
            $file['thumbnail_url'] = $thumbnailUrl;
        }

        return $file;
    }

    /**
     * @param FormInterface $form
     * @param array         $errors
     */
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
