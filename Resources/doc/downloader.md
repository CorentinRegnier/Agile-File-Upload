Agile File Upload Bundle
========================

### Downloader

With downloader you can download an external web file and save it in your entity.
```php
use AgileFileUploadBundle\Model\FileInterface;

$fileDownloader = $container->get('agile_fileupload.file_downloader');
/** @var FileInterface $file */
$file = $fileDownloader->download('https://corentinregnier.fr/assets/images/profile.png');

$myEntity->setFile($file);
$em->persist($myEntity);
$em->flush();
```
