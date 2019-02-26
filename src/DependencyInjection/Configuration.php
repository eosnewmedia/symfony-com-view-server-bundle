<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('eos_com_view_server')->children();

        $schema = $root->arrayNode('schema')->addDefaultsIfNotSet()->children();
        $this->addViewDefinitions($schema);
        $this->addCommandDefinitions($schema);
        $this->addSchemaDefinitions($schema);

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $schema
     */
    private function addViewDefinitions(NodeBuilder $schema): void
    {
        $viewDefinition = $schema->arrayNode('views')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children();

        $viewDefinition->scalarNode('description')->isRequired();
        $this->addParameterDefinitions($viewDefinition, 'parameters');
        $this->addParameterDefinitions($viewDefinition, 'pagination');

        $orderDefinition = $viewDefinition->arrayNode('orderBy')->defaultNull()->children();
        $orderDefinition->booleanNode('required')->defaultFalse();
        $orderDefinition->arrayNode('possibilities')
            ->isRequired()
            ->cannotBeEmpty()
            ->useAttributeAsKey('name')
            ->scalarPrototype();

        $this->addResponseDefinition($viewDefinition->arrayNode('data')->addDefaultsIfNotSet()->children());
    }

    /**
     * @param NodeBuilder $parentDefinition
     * @param string $property
     */
    private function addParameterDefinitions(NodeBuilder $parentDefinition, string $property): void
    {
        $parameterDefinition = $parentDefinition->arrayNode($property)
            ->defaultNull()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children();

        $parameterDefinition->scalarNode('description')->isRequired();
        $parameterDefinition->enumNode('type')
            ->isRequired()
            ->values(
                [
                    'string',
                    'int',
                    'float',
                    'bool',
                    'enum'
                ]
            );
        $parameterDefinition->booleanNode('required')->defaultFalse();
        $parameterDefinition->booleanNode('multiple')->defaultFalse();
        $parameterDefinition->booleanNode('request')->defaultTrue();
        $parameterDefinition->booleanNode('response')->defaultTrue();
        $parameterDefinition->arrayNode('values')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->scalarPrototype();
    }

    /**
     * @param NodeBuilder $schema
     */
    private function addCommandDefinitions(NodeBuilder $schema): void
    {
        $commandDefinition = $schema->arrayNode('commands')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children();

        $commandDefinition->scalarNode('description')->isRequired();
        $this->addSchemaDefinition($commandDefinition->arrayNode('parameters')->defaultNull()->children());
        $this->addResponseDefinition($commandDefinition->arrayNode('result')->defaultNull()->children());
    }

    /**
     * @param NodeBuilder $schema
     */
    private function addSchemaDefinitions(NodeBuilder $schema): void
    {
        $schemaDefinition = $schema->arrayNode('schemas')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children();

        $this->addSchemaDefinition($schemaDefinition);
    }

    /**
     * @param NodeBuilder $responseDefinition
     */
    private function addResponseDefinition(NodeBuilder $responseDefinition): void
    {
        $responseDefinition->scalarNode('success')->defaultNull();
        $responseDefinition->scalarNode('error')->defaultNull();
    }

    /**
     * @param NodeBuilder $schemaDefinition
     */
    private function addSchemaDefinition(NodeBuilder $schemaDefinition): void
    {
        $schemaDefinition->scalarNode('description')->isRequired();
        $propertyDefinition = $schemaDefinition->arrayNode('properties')
            ->isRequired()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->addDefaultsIfNotSet()
            ->children();

        $propertyDefinition->scalarNode('description')->isRequired();
        $propertyDefinition->enumNode('type')
            ->isRequired()
            ->values(
                [
                    'string',
                    'int',
                    'float',
                    'bool',
                    'object',
                    'typedValue',
                    'nested',
                    'enum',
                    'geoJson'
                ]
            );
        $propertyDefinition->booleanNode('nullable')->defaultFalse();
        $propertyDefinition->booleanNode('multiple')->defaultFalse();

        $propertyDefinition->arrayNode('properties')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->variablePrototype();

        $propertyDefinition->arrayNode('values')
            ->defaultValue([])
            ->useAttributeAsKey('name')
            ->scalarPrototype();
    }
}
