Agile File Upload Bundle
========================

### File Form Type

```php
# /src/AppBundle/Form/Type/CarType.php

use AgileFileUploadBundle\Form\Type\FileType;

public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('backgroundImage', FileType::class, [
                'browse_label'              => 'form.browse_label', // Browse button label will be translate
                'browse_translation_domain' => 'AgileFileUploadBundle', // Browse button label translation domain
                'file_restriction'          => null, // File restriction displayed will be translate (ex: 'file.max.size') 
                'file_label'                => null, // File label displayed will be translate (ex: 'file.label') 
                'display_preview_name'      => true, // Show filename
                'placeholder_path'          => null, // Image path for placeholder (ex: '/img/avatar.png') 
                'filter_name'               => 'upload_small', // LiipImagine filter for thumbnail
                'origin_filter_name'        => 'upload_large', // LiipImagine filter for big preview
                'upload_route_name'         => 'agile_file_upload_file_upload', // Route name for ajax call
                'remove_file_label'         => 'form.remove_label', // Remove label will be translate
                'unknown_error_message'     => 'form.unknown_error_message', // Error message will be translate
                'pending_uploads_label'     => 'form.pending_uploads_label', // Pending upload message will be translate
                'multiple'                  => false, // Multiple or single files
                'accept'                    => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ], // Define allowed MIME Types
                'icons_classes'             => [
                    'image/.+'                                                                            => 'fa fa-file-picture-o',
                    'application/g?zip'                                                                   => 'fa fa-file-archive-o',
                    'video/.+'                                                                            => 'fa fa-file-video-o',
                    'application/(msword|vnd\.openxmlformats-officedocument\.wordprocessingml\.document)' => 'fa fa-file-word-o',
                    'application/(excel|vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet)'        => 'fa fa-file-excel-o',
                    'audio/.+'                                                                            => 'fa fa-file-sound-o',
                    'text/(php|javascript|html|x-shockwave-flash)'                                        => 'fa fa-file-code-o',
                    'text/plain'                                                                          => 'fa fa-file-text-o',
                    'application/pdf'                                                                     => 'fa fa-file-pdf-o',
                    'application/powerpoint'                                                              => 'fa fa-file-powerpoint-o',
                    '.+'                                                                                  => 'fa fa-file-o',
                ], // Define icon for MIME Types
            ])
```
