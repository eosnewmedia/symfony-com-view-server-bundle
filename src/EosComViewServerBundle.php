<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server;

use Eos\Bundle\ComView\Server\DependencyInjection\Compiler\CommandProcessorPass;
use Eos\Bundle\ComView\Server\DependencyInjection\Compiler\HealthProviderPass;
use Eos\Bundle\ComView\Server\DependencyInjection\Compiler\ViewPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class EosComViewServerBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CommandProcessorPass());
        $container->addCompilerPass(new HealthProviderPass());
        $container->addCompilerPass(new ViewPass());
    }
}
