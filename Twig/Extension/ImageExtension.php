<?php

namespace AgileFileUploadBundle\Twig\Extension;

use AgileFileUploadBundle\Model\FileInterface;
use AgileFileUploadBundle\Manager\ImageManager;

class ImageExtension extends \Twig_Extension
{
    /**
     * @var ImageManager
     */
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'image';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('image', [$this, 'image']),
        ];
    }

    public function image($object, $field, $filter = null)
    {
        if ($object instanceof FileInterface) {
            return $this->imageManager->getImagePath($object, $filter);
        }

        return $this->imageManager->imagePath($object, $field, $filter);
    }
}
