<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\DependencyInjection\Compiler;

use Eos\ComView\Server\Health\HealthProviderChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class HealthProviderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(HealthProviderChain::class)) {
            return;
        }

        $healthProviderChain = $container->getDefinition(HealthProviderChain::class);

        $healthProviders = $container->findTaggedServiceIds('com_view.health_provider');
        foreach ($healthProviders as $id => $tags) {
            $healthProviderChain->addMethodCall('add', [new Reference($id)]);
        }
    }
}
