<?php

namespace AgileFileUploadBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use AgileFileUploadBundle\EventListener\UploadableListener;
use AgileFileUploadBundle\Model\FileInterface;
use AgileFileUploadBundle\Validator\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType as BaseFileType;

/**
 * Class FileType
 *
 * @package AgileFileUploadBundle\Form\Type
 */
class FileType extends AbstractType
{
    protected $class;

    /**
     * @var UploadableListener
     */
    protected $uploadableListener;

    /**
     * @var EntityManager
     */
    protected $om;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $defaultFilterName;

    /**
     * @var string
     */
    protected $defaultOriginFilterName;

    /**
     * FileType constructor.
     *
     * @param string              $class
     * @param ManagerRegistry     $registry
     * @param UploadableListener  $uploadableListener
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     * @param string              $defaultFilterName
     * @param string              $defaultOriginFilterName
     */
    public function __construct(
        $class,
        ManagerRegistry $registry,
        UploadableListener $uploadableListener,
        RouterInterface $router,
        TranslatorInterface $translator,
        $defaultFilterName,
        $defaultOriginFilterName
    ) {
        $this->class                   = $class;
        $this->om                      = $registry->getManagerForClass($class);
        $this->uploadableListener      = $uploadableListener;
        $this->router                  = $router;
        $this->translator              = $translator;
        $this->defaultFilterName       = $defaultFilterName;
        $this->defaultOriginFilterName = $defaultOriginFilterName;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $fileAttr = [];
        if (null !== $options['accept']) {
            $fileAttr['accept'] = implode(',', $options['accept']);
        }

        $fileInput = $builder->create('file', BaseFileType::class, [
            'mapped'      => false,
            'multiple'    => $options['multiple'],
            'attr'        => $fileAttr,
            'constraints' => [
                new File([
                    'mimeTypes' => $options['accept'],
                    'multiple'  => $options['multiple'],
                ]),
            ],
        ]);
        $idInput   = $builder->create('id', HiddenType::class, [
            'mapped' => false,
        ]);

        /** @var CsrfTokenManagerInterface $csrfTokenManager */
        $csrfTokenManager = $options['csrf_token_manager'];
        $token            = $csrfTokenManager->getToken('file')->getValue();

        $fileInput->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($token) {
            $config   = $event->getForm()->getConfig();
            $multiple = $config->getOption('multiple');
            $data     = $event->getData();

            $handleFile = function ($data) use (&$options, $token) {
                if ($data instanceof UploadedFile) {
                    /** @var FileInterface $file */
                    $file = new $this->class;
                    $file->setFile($data);
                    $file->setToken($token);

                    return $file;
                } else {
                    return null;
                }
            };

            if ($multiple && $data) {
                $d = [];
                foreach ($data as $file) {
                    $f = $handleFile($file);
                    if (null !== $f) {
                        $d[] = $f;
                    }
                }
            } else {
                $d = $handleFile($data);
            }

            $event->setData($d);
        });

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use (&$options) {
            $idInput = $event->getForm()->get('id');
            if ($options['multiple']) {
                /** @var FileInterface[] $files */
                if ($files = $event->getForm()->getNormData()) {
                    $ids = [];
                    foreach ($files as $file) {
                        $ids[] = $file->getId();
                    }
                    $idInput->setData(implode(',', $ids));
                }
            } else {
                $file = $event->getForm()->getNormData();
                if ($file instanceof FileInterface) {
                    $idInput->setData($file->getId());
                }
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($token) {
            $config   = $event->getForm()->getConfig();
            $multiple = $config->getOption('multiple');

            $originData = $event->getData();
            $fileInput  = $event->getForm()->get('file');
            $idInput    = $event->getForm()->get('id');

            $data = $idInput->getData();
            if ($multiple) {
                $files = [];
                if ($data) {
                    /** @var FileInterface[] $result */
                    $result = $this->om->getRepository($this->class)
                        ->createQueryBuilder('f')
                        ->where('f.id IN (:id)')
                        ->setParameter('id', explode(',', $data))
                        ->getQuery()
                        ->getResult();

                    foreach ($result as $file) {
                        if ($token !== $file->getToken() && !$originData->contains($file)) {
                            throw new AccessDeniedHttpException('Invalid file token');
                        }
                        $files[] = $file;
                    }
                }

                $event->setData($files);
            } else {
                if ($fileInput->getData() instanceof FileInterface) {
                    $event->setData($fileInput->getData());
                } elseif ($data) {
                    $file = $this->om->find($this->class, $data);
                    if ($file instanceof FileInterface) {
                        if ($originData !== $file && $token !== $file->getToken()) {
                            throw new AccessDeniedHttpException('Invalid file token');
                        }
                        $event->setData($file);
                    }
                } else {
                    $event->setData(null);
                }
            }
        });

        $builder->add($fileInput);
        $builder->add($idInput);
    }

    /**
     * Add the file_path option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'error_bubbling'        => false,
            'browse_label'          => 'form.browse_label',
            'translation_domain'    => 'AgileFileUploadBundle',
            'file_restriction'      => null,
            'file_label'            => null,
            'display_preview_name'  => true,
            'placeholder_path'      => null,
            'data_class'            => function (Options $options) {
                if ($options['multiple']) {
                    return null;
                } else {
                    return $this->class;
                }
            },
            'filter_name'           => $this->defaultFilterName,
            'origin_filter_name'    => $this->defaultOriginFilterName,
            'upload_route_name'     => 'agile_file_upload_file_upload',
            'url'                   => function (Options $options) {
                return $this->router->generate($options['upload_route_name']);
            },
            'remove_file_label'     => 'form.remove_label',
            'unknown_error_message' => 'form.unknown_error_message',
            'pending_uploads_label' => 'form.pending_uploads_label',
            'render_order'          => 'append',
            'multiple'              => false,
            'required'              => false,
            'accept'                => [
                'image/jpeg',
                'image/png',
                'image/gif',
            ],
            'icons_classes'         => [
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
            ],
        ]);

        $resolver->setAllowedTypes('icons_classes', 'array');
        $resolver->setAllowedValues('render_order', ['prepend', 'append']);
    }

    /**
     * Pass the file URL to the view
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['multiple']             = $options['multiple'];
        $view->vars['placeholder']          = $options['placeholder_path'];
        $view->vars['remove_file_label']    = $options['remove_file_label'];
        $view->vars['filter_name']          = $options['filter_name'];
        $view->vars['file_restriction']     = $options['file_restriction'];
        $view->vars['file_label']           = $options['file_label'];
        $view->vars['display_preview']      = true;
        $view->vars['browse_label']         = $options['browse_label'];
        $view->vars['translation_domain']   = $options['translation_domain'];
        $view->vars['display_preview_name'] = $options['display_preview_name'];
        if ($options['multiple']) {
            $view->vars['attr']['multiple'] = 'multiple';
        }

        $rootForm = $form->getRoot();
        $token    = (string)$rootForm->getConfig()->getOption('csrf_token_manager')->getToken('file');

        $jsOptions = [
            'ajax'                  => true,
            'url'                   => $options['url'],
            'token'                 => $token,
            'remove_file_label'     => $this->translate($options['remove_file_label'], $options['translation_domain']),
            'unknown_error_message' => $this->translate($options['unknown_error_message'], $options['translation_domain']),
            'icon_classes'          => $options['icons_classes'],
            'pending_uploads_label' => $this->translate($options['pending_uploads_label'], $options['translation_domain']),
            'render_order'          => $options['render_order'],
        ];
        if ($options['filter_name']) {
            $jsOptions['filter_name'] = $options['filter_name'];
        }
        if ($options['origin_filter_name']) {
            $jsOptions['origin_filter_name'] = $options['origin_filter_name'];
        }

        $jsOptions['multiple'] = $options['multiple'];

        $view->vars['js_options'] = json_encode($jsOptions);

        $data = $form->getNormData();

        $filesInfo = [];
        if (null !== $data) {
            $iconClasses = $options['icons_classes'];
            $files       = $options['multiple'] ? $data : [$data];
            foreach ($files as $file) {
                if ($file instanceof FileInterface) {
                    $accessor    = PropertyAccess::createPropertyAccessor();
                    $mimeType    = $accessor->getValue($file, 'mimeType');
                    $filesInfo[] = [
                        'id'        => $file->getId(),
                        'url'       => $accessor->getValue($file, 'path'),
                        'name'      => $accessor->getValue($file, 'originalFilename'),
                        'mime_type' => $mimeType,
                        'icon'      => $this->getFileIcon($iconClasses, $mimeType),
                        'object'    => $file,
                    ];
                }
            }
        }
        $view->vars['files'] = $filesInfo;
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'agile_file';
    }

    /**
     * @param       $id
     * @param array $parameters
     *
     * @return string
     */
    private function translate($id, $translationDomain, array $parameters = [])
    {
        return $this->translator->trans($id, $parameters, $translationDomain);
    }

    /**
     * @param array $iconsClasses
     * @param       $mimeType
     *
     * @return mixed
     */
    private function getFileIcon(array $iconsClasses, $mimeType)
    {
        if (isset($iconsClasses[$mimeType])) {
            return $iconsClasses[$mimeType];
        }

        foreach ($iconsClasses as $mask => $iconsClass) {
            if (preg_match('#^'.$iconsClass.'$#', $mimeType)) {
                return $iconsClass;
            }
        }
    }
}
