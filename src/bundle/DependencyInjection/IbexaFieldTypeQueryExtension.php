<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\FieldTypeQuery\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

final class IbexaFieldTypeQueryExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config/')
        );

        $loader->load('default_parameters.yaml');
        $loader->load('services.yaml');
        if (!$container->hasParameter('kernel.debug') || !$container->getParameter('kernel.debug')) {
            $loader->load('prod/services.yaml');
        }

        $this->addContentViewConfig($container);
    }

    public function prepend(ContainerBuilder $container)
    {
        $this->prependFieldTemplateConfig($container);
        $this->prependJMSTranslationConfig($container);
        $this->prependTwigConfig($container);
        $this->prependGraphQL($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function addContentViewConfig(ContainerBuilder $container): void
    {
        $contentViewDefaults = $container->getParameter('ibexa.site_access.config.default.content_view_defaults');
        $contentViewDefaults['content_query_field'] = [
            'default' => [
                'template' => '@IbexaFieldTypeQuery/content/contentquery.html.twig',
                'match' => [],
            ],
        ];
        $container->setParameter('ibexa.site_access.config.default.content_view_defaults', $contentViewDefaults);
    }

    protected function prependTwigConfig(ContainerBuilder $container): void
    {
        $views = Yaml::parseFile(__DIR__ . '/../Resources/config/default_parameters.yaml')['parameters'];
        $twigGlobals = [
            'ezContentQueryViews' => [
                'field' => $views['ibexa.field_type.query.content.view.field'],
                'item' => $views['ibexa.field_type.query.content.view.item'],
            ],
        ];
        $container->prependExtensionConfig('twig', ['globals' => $twigGlobals]);
    }

    private function prependJMSTranslationConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ibexa_fieldtype_query' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                ],
            ],
        ]);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function prependFieldTemplateConfig(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/field_templates.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ibexa', $config);
        $container->addResource(new FileResource($configFile));
    }

    private function prependGraphQL(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('overblog_graphql', [
            'definitions' => [
                'mappings' => [
                    'types' => [
                        [
                            'type' => 'yaml',
                            'dir' => __DIR__ . '/../Resources/config/graphql/types',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
