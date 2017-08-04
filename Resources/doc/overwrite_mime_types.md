Agile File Upload Bundle
========================

### Overwrite MIME Types

We need edit both files because we have PHP and Javascript check.
```php
# /src/AppBundle/Form/Type/ArticleType.php

use AgileFileUploadBundle\Form\Type\FileType;

public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attachment', FileType::class, [
                'upload_route_name'         => 'my_custom_upload_route',
                'accept'                    => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'text/plain',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/zip',
                    'application/x-zip-compressed',
                    'audio/wav',
                    'audio/x-wav',
                    'audio/mpeg3',
                    'audio/mpeg',
                    'audio/x-mpeg-3',
                    'audio/x-ms-wma',
                    'video/mpeg',
                    'video/x-mpeg',
                    'video/x-ms-wmv',
                    'video/x-ms-asf',
                    'application/x-troff-msvideo',
                    'video/avi',
                    'video/msvideo',
                    'video/x-msvideo'
                ],
            ])
```

```php
# /src/AppBundle/Controller/ArticleController.php

    /**
     * @Route("/custom-upload", name="my_custom_upload_route")
     */
    public function uploadAction()
    {
        $fileManager = $this->get('agile_fileupload.file_upload_manager');
        return $fileManager->handleForm(null, [
            'accept' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'text/plain',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/zip',
                'application/x-zip-compressed',
                'audio/wav',
                'audio/x-wav',
                'audio/mpeg3',
                'audio/mpeg',
                'audio/x-mpeg-3',
                'audio/x-ms-wma',
                'video/mpeg',
                'video/x-mpeg',
                'video/x-ms-wmv',
                'video/x-ms-asf',
                'application/x-troff-msvideo',
                'video/avi',
                'video/msvideo',
                'video/x-msvideo'
            ],
        ]);
    }
```
