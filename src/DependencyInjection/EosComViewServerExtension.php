<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\DependencyInjection;

use Eos\Bundle\ComView\Server\Controller\ComViewController;
use Eos\ComView\Server\Command\CommandProcessorInterface;
use Eos\ComView\Server\Command\CommandProcessorRegistry;
use Eos\ComView\Server\ComViewServer;
use Eos\ComView\Server\Health\CommandHealthProviderInterface;
use Eos\ComView\Server\Health\HealthProviderChain;
use Eos\ComView\Server\Health\ViewHealthProviderInterface;
use Eos\ComView\Server\View\ViewInterface;
use Eos\ComView\Server\View\ViewRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class EosComViewServerExtension extends ConfigurableExtension
{
    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->autowire(CommandProcessorRegistry::class)
            ->setPublic(false);
        $container->setAlias(CommandProcessorInterface::class, CommandProcessorRegistry::class)
            ->setPublic(false);

        $container->autowire(ViewRegistry::class)
            ->setPublic(false);
        $container->setAlias(ViewInterface::class, ViewRegistry::class)
            ->setPublic(false);

        $container->autowire(HealthProviderChain::class)
            ->setPublic(false);
        $container->setAlias(CommandHealthProviderInterface::class, HealthProviderChain::class)
            ->setPublic(false);
        $container->setAlias(ViewHealthProviderInterface::class, HealthProviderChain::class)
            ->setPublic(false);

        $container->autowire(ComViewServer::class)
            ->setPublic(false);

        $container->setParameter('eos_com_view_server.schema', $this->normalizedSchema($mergedConfig));
        $container->setParameter('eos_com_view_server.allow_origin', $mergedConfig['allow_origin']);

        $container->autowire(ComViewController::class)
            ->setArgument('$schema', '%eos_com_view_server.schema%')
            ->setArgument('$allowOrigin', '%eos_com_view_server.allow_origin%')
            ->setPublic(true);
    }

    /**
     * @param array $mergedConfig
     * @return array
     */
    private function normalizedSchema(array $mergedConfig): array
    {
        $schema = $mergedConfig['schema'] ?? ['views' => [], 'commands' => [], 'schemas' => []];

        foreach ($schema['views'] as &$definition) {
            if (array_key_exists('parameters', $definition) && count($definition['parameters']) === 0) {
                unset($definition['parameters']);
            }
            if (array_key_exists('pagination', $definition) && count($definition['pagination']) === 0) {
                unset($definition['pagination']);
            }

            if (array_key_exists('parameters', $definition)) {
                foreach ($definition['parameters'] as &$parameter) {
                    if (array_key_exists('values', $parameter) && $parameter['type'] !== 'enum') {
                        unset($parameter['values']);
                    }
                }
                unset($parameter);
            }
        }
        unset($definition);

        foreach ($schema['commands'] as &$definition) {
            $parametersExists = array_key_exists('parameters', $definition);
            if ($parametersExists && array_key_exists('properties', $definition['parameters'])) {
                $definition['parameters']['properties'] = $this->normalizedProperties($definition['parameters']['properties']);
            }
        }
        unset($definition);

        foreach ($schema['schemas'] as &$definition) {
            if (array_key_exists('properties', $definition)) {
                $definition['properties'] = $this->normalizedProperties($definition['properties']);
            }
        }
        unset($definition);

        return $schema;
    }

    /**
     * @param array $properties
     * @return array
     */
    private function normalizedProperties(array $properties): array
    {
        foreach ($properties as &$property) {
            if (array_key_exists('properties', $property)) {
                if ($property['type'] !== 'object') {
                    unset($property['properties']);
                } else {
                    $property['properties'] = $this->normalizedProperties($property['properties']);
                }
            }

            if (array_key_exists('values', $property) && $property['type'] !== 'enum') {
                unset($property['values']);
            }

            if (array_key_exists('source', $property) && $property['type'] !== 'schema') {
                unset($property['source']);
            }
        }
        unset($property);

        return $properties;
    }
}
