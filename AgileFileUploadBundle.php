<?php

namespace AgileFileUploadBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AgileFileUploadBundle
 *
 * @package AgileFileUploadBundle
 */
class AgileFileUploadBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $symfonyVersion = class_exists('Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass');
        $mappings = [
            realpath(__DIR__ . '/Resources/config/doctrine/model') => __NAMESPACE__ . '\Model',
        ];

        if ($symfonyVersion && class_exists('Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass')) {
            $container->addCompilerPass(DoctrineOrmMappingsPass::createYamlMappingDriver(
                $mappings,
                ['agile_file_upload.model_manager_name'],
                false,
                ['AgileFileUploadBundle' => 'AgileFileUploadBundle\Model']
            ));
        }
    }
}
