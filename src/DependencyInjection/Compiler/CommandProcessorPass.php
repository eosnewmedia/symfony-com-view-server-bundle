<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\DependencyInjection\Compiler;

use Eos\ComView\Server\Command\CommandProcessorRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class CommandProcessorPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(CommandProcessorRegistry::class)) {
            return;
        }

        $commandProcessorRegistry = $container->getDefinition(CommandProcessorRegistry::class);

        $commandProcessors = $container->findTaggedServiceIds('com_view.command_processor');
        foreach ($commandProcessors as $id => $tags) {
            $commandProcessorRegistry->addMethodCall('add', [new Reference($id)]);
        }
    }
}
