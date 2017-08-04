<?php

namespace AgileFileUploadBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use AgileFileUploadBundle\Model\FileInterface;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class UploadableListener
 *
 * @package AgileFileUploadBundle\EventListener
 */
class UploadableListener implements EventSubscriber
{
    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * UploadableListener constructor.
     *
     * @param UploadableManager $uploadableManager
     */
    public function __construct(UploadableManager $uploadableManager)
    {
        $this->uploadableManager = $uploadableManager;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        if ($object instanceof FileInterface && $object->getFile()) {
            $this->triggerUpdate($object, $object->getFile());
        }
    }

    /**
     * @param FileInterface $object
     * @param UploadedFile  $file
     */
    public function triggerUpdate(FileInterface $object, UploadedFile $file)
    {
        $this->uploadableManager->markEntityToUpload($object, $file);
    }
}
