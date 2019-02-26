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

        $schema = $root->arrayNode('schema')->children();
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
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $viewDefinition->scalarNode('description')->isRequired()->cannotBeEmpty();
        $this->addParameterDefinitions($viewDefinition, 'parameters');
        // "parameters" will be removed by extension if empty
        $this->addParameterDefinitions($viewDefinition, 'pagination');
        // "pagination" will be removed by extension if empty

        $orderDefinition = $viewDefinition->arrayNode('orderBy')->children();
        $orderDefinition->booleanNode('required');
        $orderDefinition->arrayNode('possibilities')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->scalarPrototype();

        $this->addResponseDefinition($viewDefinition->arrayNode('data')->children());

        $viewDefinition->integerNode('minResults');
        $viewDefinition->integerNode('maxResults');
    }

    /**
     * @param NodeBuilder $parentDefinition
     * @param string $property
     */
    private function addParameterDefinitions(NodeBuilder $parentDefinition, string $property): void
    {
        $parameterDefinition = $parentDefinition->arrayNode($property)
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $parameterDefinition->scalarNode('description')->isRequired()->cannotBeEmpty();
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
        $parameterDefinition->booleanNode('required');
        $parameterDefinition->booleanNode('multiple');
        $parameterDefinition->booleanNode('request');
        $parameterDefinition->booleanNode('response');
        $parameterDefinition->arrayNode('values')
            // "values" will be removed by extension if type is not "enum"
            ->useAttributeAsKey('name')
            ->scalarPrototype();
    }

    /**
     * @param NodeBuilder $schema
     */
    private function addCommandDefinitions(NodeBuilder $schema): void
    {
        $commandDefinition = $schema->arrayNode('commands')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $commandDefinition->scalarNode('description')->isRequired()->cannotBeEmpty();
        $this->addSchemaDefinition($commandDefinition->arrayNode('parameters')->children());
        $this->addResponseDefinition($commandDefinition->arrayNode('result')->children());
    }

    /**
     * @param NodeBuilder $schema
     */
    private function addSchemaDefinitions(NodeBuilder $schema): void
    {
        $schemaDefinition = $schema->arrayNode('schemas')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children();

        $this->addSchemaDefinition($schemaDefinition);
    }

    /**
     * @param NodeBuilder $responseDefinition
     */
    private function addResponseDefinition(NodeBuilder $responseDefinition): void
    {
        $responseDefinition->scalarNode('success');
        $responseDefinition->scalarNode('error');
    }

    /**
     * @param NodeBuilder $schemaDefinition
     */
    private function addSchemaDefinition(NodeBuilder $schemaDefinition): void
    {
        $schemaDefinition->scalarNode('description')->isRequired()->cannotBeEmpty();

        $this->addPropertyDefinition(
            $schemaDefinition->arrayNode('properties')
                ->isRequired()
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                ->children()
        );
    }

    private function addPropertyDefinition(NodeBuilder $propertyDefinition, int $level = 0): void
    {
        $propertyDefinition->scalarNode('description')->isRequired()->cannotBeEmpty();
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
        $propertyDefinition->booleanNode('nullable');
        $propertyDefinition->booleanNode('multiple');


        // "properties" will be removed by extension if type is not "object"
        if ($level < 15) {
            $level++;
            $this->addPropertyDefinition(
                $propertyDefinition->arrayNode('properties')->useAttributeAsKey('name')->arrayPrototype()->children(),
                $level
            );
        } else {
            $propertyDefinition->arrayNode('properties')
                ->useAttributeAsKey('name')
                ->variablePrototype();
        }

        $propertyDefinition->arrayNode('values')
            // "values" will be removed by extension if type is not "enum"
            ->useAttributeAsKey('name')
            ->scalarPrototype();
    }
}
