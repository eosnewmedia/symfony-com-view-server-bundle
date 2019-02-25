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
            ->setArgument('$schema', $mergedConfig['schema'])
            ->setPublic(true);
    }
}
