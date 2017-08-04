<?php

namespace AgileFileUploadBundle\Validator;

use Symfony\Component\Validator\Constraints\File as BaseFile;

class File extends BaseFile
{
    public $maxSizeMessage = 'smt_fileupload.file.too_large';
    public $mimeTypesMessage = 'smt_fileupload.file.mime_type';

    public $uploadIniSizeErrorMessage = 'smt_fileupload.file.too_large';

    public $multiple = false;
}
