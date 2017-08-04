<?php

namespace AgileFileUploadBundle\Model;

use Cocur\Slugify\Slugify;
use Gedmo\Uploadable\FilenameGenerator\FilenameGeneratorInterface;

/**
 * Class FilenameGenerator
 * @package AgileFileUploadBundle\Model
 */
class FilenameGenerator implements FilenameGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public static function generate($filename, $extension, $object = null)
    {
        $filename = (new Slugify())->slugify($filename);
        return $filename.$extension;
    }
}
