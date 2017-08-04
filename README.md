Agile File Upload Bundle
========================

Documentation
-------------

The source of the documentation is stored in the `Resources/doc/` folder :

[Read the Documentation for master](Resources/doc/index.md)

Installation
------------

Step 1: Download AgileFileUploadBundle using composer

Require the bundle with composer:
```bash
    $ composer require cregnier/fileupload-bundle "1.0.*"
```
Composer will install the bundle to your project's ``vendor/cregnier/fileupload-bundle`` directory.

Step 2: Add routing

```yaml
# app/config/routing.yml
agile_file_upload:
    resource: "@AgileFileUploadBundle/Resources/config/routing.yml"
    prefix: /
    
_liip_imagine:
    resource: "@LiipImagineBundle/Resources/config/routing.xml"    
```

Step 3: Add config

```yaml
# app/config/config.yml
stof_doctrine_extensions:
    orm:
        default:
            timestampable: true
            uploadable: true
            sluggable: true

liip_imagine:
    resolvers:
       default:
          web_path: ~

    filter_sets:
        cache: ~
        upload_large:
            quality: 75
            filters:
                thumbnail: { size: [120, 120], mode: outbound }

        upload_small:
            quality: 75
            filters:
                thumbnail: { size: [88, 88], mode: outbound }  
```

Step 4: Add javascript on assets.yml

```yaml
# app/config/assets.yml
assets:
    js:
        app:
            - // ...
            - // ...
            - ../../../../../vendor/cregnier/kernel-bundle/Resources/js/agile.form.js
            - ../../../../../vendor/cregnier/kernel-bundle/Resources/js/agile.request.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/jquery.ui.widget.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/jquery.iframe-transport.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/jquery.fileupload.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/load-image.all.min.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/jquery.fileupload-process.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/jquery-file-upload/jquery.fileupload-image.js
            - ../../../../../vendor/cregnier/fileupload-bundle/Resources/js/agile.fileupload.js
```

Step 5: Add sass on app.scss

```sass
// app/Resources/assets/sass/app.scss

//== AGILE BUNDLE
@import '../../../../vendor/cregnier/fileupload-bundle/Resources/sass/all';
```

Step 6: Enable the bundle

Enable the bundle in the kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new AgileFileUploadBundle\AgileFileUploadBundle(),
            // ...
        );
    }
