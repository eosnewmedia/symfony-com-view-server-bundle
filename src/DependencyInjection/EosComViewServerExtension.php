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

        $container->autowire(ComViewController::class)
            ->setArgument('$schema', $this->normalizedSchema($mergedConfig))
            ->setPublic(true);
    }

    /**
     * @param array $mergedConfig
     * @return mixed
     */
    private function normalizedSchema(array $mergedConfig)
    {
        $schema = $mergedConfig['schema'] ?? ['views' => [], 'commands' => [], 'schemas' => []];

        foreach ($schema['views'] as $key => $definition) {
            if (\array_key_exists('parameters', $definition) && \count($definition['parameters']) === 0) {
                unset($schema['views'][$key]['parameters']);
            }
            if (\array_key_exists('pagination', $definition) && \count($definition['pagination']) === 0) {
                unset($schema['views'][$key]['pagination']);
            }

            if (\array_key_exists('parameters', $definition)) {
                foreach ($definition['parameters'] as $name => $parameter) {
                    if (array_key_exists('values', $parameter) && $parameter['type'] !== 'enum') {
                        unset($schema['views'][$key]['parameters'][$name]['values']);
                    }
                }
            }
        }

        foreach ($schema['commands'] as $key => $definition) {
            $parametersExists = \array_key_exists('parameters', $definition);
            if ($parametersExists && \array_key_exists('properties', $definition['parameters'])) {
                foreach ($definition['parameters']['properties'] as $name => $property) {
                    if (array_key_exists('properties', $property) && $property['type'] !== 'object') {
                        unset($schema['commands'][$key]['parameters']['properties'][$name]['properties']);
                    }
                    if (array_key_exists('values', $property) && $property['type'] !== 'enum') {
                        unset($schema['commands'][$key]['parameters']['properties'][$name]['values']);
                    }
                }
            }
        }

        foreach ($schema['schemas'] as $key => $definition) {
            if (\array_key_exists('properties', $definition)) {
                foreach ($definition['properties'] as $name => $property) {
                    if (array_key_exists('properties', $property) && $property['type'] !== 'object') {
                        unset($schema['schemas'] [$key]['properties'][$name]['properties']);
                    }
                    if (array_key_exists('values', $property) && $property['type'] !== 'enum') {
                        unset($schema['schemas'] [$key]['properties'][$name]['values']);
                    }
                }
            }
        }

        return $schema;
    }
}
