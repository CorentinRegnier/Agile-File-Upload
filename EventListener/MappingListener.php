<?php

namespace AgileFileUploadBundle\EventListener;

use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use AgileKernelBundle\EventListener\AbstractMappingListener;

/**
 * Class MappingListener
 *
 * @package AgileFileUploadBundle\Doctrine
 */
class MappingListener extends AbstractMappingListener
{
    protected $fileClass;
    protected $fileTable;
    protected $fileRepository;

    /**
     * MappingListener constructor.
     *
     * @param string $fileClass
     * @param string $fileTable
     * @param string $fileRepository
     */
    public function __construct($fileClass, $fileTable, $fileRepository)
    {
        $this->fileClass      = $fileClass;
        $this->fileTable      = $fileTable;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadataInfo $metadata */
        $metadata  = $eventArgs->getClassMetadata();
        $className = $metadata->getName();
        if ($className === $this->fileClass) {
            $this->setConcrete($metadata, $this->fileTable);
            $metadata->setCustomRepositoryClass($this->fileRepository);
        }
    }
}
