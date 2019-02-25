<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\DependencyInjection\Compiler;

use Eos\ComView\Server\View\ViewRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ViewPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ViewRegistry::class)) {
            return;
        }

        $viewRegistry = $container->getDefinition(ViewRegistry::class);

        $views = $container->findTaggedServiceIds('com_view.view');
        foreach ($views as $id => $tags) {
            $viewRegistry->addMethodCall('add', [new Reference($id)]);
        }
    }
}
