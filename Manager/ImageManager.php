<?php

namespace AgileFileUploadBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use AgileFileUploadBundle\Model\FileInterface;
use AgileFileUploadBundle\Model\ImageInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;

class ImageManager
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    private $cache = [];

    function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function imagePath($object, $field, $filter)
    {
        $key = spl_object_hash($object) . '.' . $field . '.' . $filter;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $image = $object->{'get' . ucfirst($field)}();

        $path = null;
        if ($image instanceof FileInterface) {
            $path = $image->getPath();
        }

        if (null === $path) {
            return $this->cache[$key] = null;
        }

        $this->cache[$key] = $this->cacheManager->getBrowserPath($path, $filter);

        return $this->cache[$key];
    }

    public function getImagePath(FileInterface $image, $filter)
    {
        $key = '__' . spl_object_hash($image) . '.' . '.' . $filter;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $path = $image->getPath();

        $this->cache[$key] = $this->cacheManager->getBrowserPath($path, $filter);

        return $this->cache[$key];
    }
}
