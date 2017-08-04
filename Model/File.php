<?php

namespace AgileFileUploadBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

class File implements FileInterface
{
    use Timestampable;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * Uploaded file info from $_FILES
     * Not mapped with Doctrine
     *
     * @var array
     */
    protected $file;

    /**
     * @var string The folder where to store the files
     */
    private $context;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $originalFilename;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string
     */
    protected $mimeType;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var float
     */
    protected $size;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $isPlaceholder;

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * @return float
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param array $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return array|UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        return 'uploads/'.($this->context ? $this->context.'/' : '').date('Y/m/d').'/';
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setContext($context)
    {
        if (!preg_match('#^[a-z0-9_\-]+$#i', $context)) {
            throw new \InvalidArgumentException('Invalid context format.');
        }
        $this->context = $context;
    }

    protected function getContext()
    {
        return $this->context;
    }

    public function callbackMethod(array $file)
    {
        $this->originalFilename = $file['origFileName'];
        $this->extension        = substr($file['fileExtension'], 1);
    }

    public function isPlaceholder()
    {
        return $this->isPlaceholder;
    }

    public function setPlaceholder($isPlaceholder)
    {
        $this->isPlaceholder = $isPlaceholder;
    }

    /**
     * @return string
     */
    function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Used to return path to the ImageValidator
     *
     * @see \Symfony\Component\Validator\Constraints\ImageValidator
     * @return string
     */
    public function __toString()
    {
        if ($this->file instanceof UploadedFile) {
            return $this->file->getRealPath();
        }

        return $this->path ?: '';
    }
}
