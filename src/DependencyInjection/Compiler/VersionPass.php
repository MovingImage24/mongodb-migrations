<?php

namespace Mi\MongoDb\Migration\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
class VersionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('mi.mongo_db.migration.version.collection')) {
            return;
        }

        $versionCollection = $container->getDefinition('mi.mongo_db.migration.version.collection');
        $taggedServices = $container->findTaggedServiceIds('mi.mongo_db.version');

        foreach ($taggedServices as $id => $tags) {
            $versionCollection->addMethodCall('addVersion', [$tags[0]['version_number'], new Reference($id)]);
        }
    }
}
