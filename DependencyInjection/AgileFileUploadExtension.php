<?php

namespace AgileFileUploadBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class AgileFileUploadExtension
 *
 * @package AgileFileUploadBundle\DependencyInjection
 */
class AgileFileUploadExtension extends Extension
{
    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $loader        = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('agile_fileupload', $config);
        $this->mapModel($container, $config, 'file');
        $this->loadImage($container, $loader);
    }

    /**
     * @param ContainerBuilder $container
     * @param YamlFileLoader   $loader
     */
    public function loadImage(ContainerBuilder $container, YamlFileLoader $loader)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['LiipImagineBundle'])) {
            throw new InvalidConfigurationException('LiipImagineBundle must be enabled in order to use image component.');
        }
        $loader->load('image.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $model
     * @param                  $field
     */
    private function mapModel(ContainerBuilder $container, array $config, $field)
    {
        $container->setParameter('agile_fileupload.model.'.$field.'.class', $config['model'][$field.'_class']);
        $container->setParameter('agile_fileupload.model.'.$field.'.table', $config['model'][$field.'_table']);
        $container->setParameter('agile_fileupload.repository.'.$field, $config['repository'][$field]);
    }
}
