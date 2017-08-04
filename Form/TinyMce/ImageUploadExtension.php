<?php

namespace AgileFileUploadBundle\Form\TinyMce;

use AgileKernelBundle\Form\TinyMce\AbstractTinyMceExtension;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class ImageUploadExtension
 *
 * @package AgileFileUploadBundle\Form\TinyMce
 */
class ImageUploadExtension extends AbstractTinyMceExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * ImageUploadExtension constructor.
     *
     * @param RouterInterface           $router
     * @param CsrfTokenManagerInterface $csrfTokenManager
     */
    public function __construct(RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->router           = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @return array
     */
    public function getPlugins()
    {
        return [
            'agile_image_upload' => 'bundles/agilefileupload/tinymce/plugins/agile_file_upload/plugin.min.js',
        ];
    }

    /**
     * @return array
     */
    public function getConfigurations()
    {
        return [
            'image_upload_path' => $this->router->generate('agile_file_upload_file_upload'),
            'session_token'     => $this->csrfTokenManager->getToken('file')->getValue(),
        ];
    }

    /**
     * Extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'agile_image_upload';
    }
}
