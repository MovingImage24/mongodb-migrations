<?php

namespace Mi\MongoDb\Migration\DependencyInjection;

use Matthias\BundlePlugins\SimpleBundlePlugin;
use Mi\MongoDb\Migration\DependencyInjection\Compiler\VersionPass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
class MigrationPlugin extends SimpleBundlePlugin
{
    /**
     * @inheritDoc
     */
    public function name()
    {
        return 'migration';
    }

    /**
     * @inheritDoc
     */
    public function addConfiguration(ArrayNodeDefinition $pluginNode)
    {
        $pluginNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('path')->isRequired()->end()
                ->scalarNode('namespace')->isRequired()->end()
                ->scalarNode('migration_collection')->isRequired()->end()
                ->scalarNode('xml_path')->defaultNull()->end()
            ->end()
        ;
    }

    /**
     * @inheritDoc
     *
     * @codeCoverageIgnore
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new VersionPass());
    }

    /**
     * @inheritDoc
     */
    public function load(array $pluginConfiguration, ContainerBuilder $container)
    {
        $container->setAlias('mi.mongo_db.migration.migration.collection', $pluginConfiguration['migration_collection']);

        $container->setParameter('migration_path', $pluginConfiguration['path']);
        $container->setParameter('xml_path', $pluginConfiguration['xml_path']);
        $container->setParameter('namespace', $pluginConfiguration['namespace']);
    }
}
