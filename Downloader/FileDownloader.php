<?php

namespace AgileFileUploadBundle\Downloader;

use Buzz\Client\Curl;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Cocur\Slugify\Slugify;
use AgileFileUploadBundle\Model\FileInterface;
use Gedmo\Uploadable\MimeType\MimeTypesExtensionsMap;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileDownloader
 *
 * @package AgileFileUploadBundle\Downloader
 */
class FileDownloader
{
    /**
     * @var Curl
     */
    private $client;

    /**
     * @var UploadableManager
     */
    private $uploadableManager;

    /**
     * @var string
     */
    private $fileClass;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * FileDownloader constructor.
     *
     * @param Curl              $client
     * @param UploadableManager $uploadableManager
     * @param string            $fileClass
     * @param string|null       $tempDir
     */
    public function __construct(Curl $client, UploadableManager $uploadableManager, $fileClass, $tempDir = null)
    {
        $this->client            = $client;
        $this->uploadableManager = $uploadableManager;
        $this->fileClass         = $fileClass;
        $this->tempDir           = $tempDir ?: sys_get_temp_dir();
    }

    /**
     * @param string $uri
     *
     * @return FileInterface
     */
    public function download($uri)
    {
        $request  = new Request('GET', $uri);
        $response = new Response();
        $this->client->setTimeout(50);
        $this->client->send($request, $response, [CURLOPT_FOLLOWLOCATION => true]);

        if(false === $response->isSuccessful()) {
            return null;
        }

        $mimeType = $response->getHeader('Content-Type');

        if (isset(MimeTypesExtensionsMap::$map[$mimeType])) {
            $extension = MimeTypesExtensionsMap::$map[$mimeType];
        } else {
            return null;
        }

        $pathInfo = pathinfo($uri);
        $filename = (new Slugify())->slugify($pathInfo['filename']);

        $tmpFile = $this->tempDir.'/'.$filename.'.'.$extension;
        file_put_contents($tmpFile, $response->getContent());
        $fileInfo = new UploadedFile($tmpFile, basename($tmpFile), $mimeType, filesize($tmpFile), null, true);

        /** @var FileInterface $file */
        $file = new $this->fileClass;
        $this->uploadableManager->markEntityToUpload($file, $fileInfo);

        return $file;
    }
}
