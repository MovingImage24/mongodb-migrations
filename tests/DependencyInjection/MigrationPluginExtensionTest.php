<?php

namespace Mi\MongoDb\Migration\Tests\DependencyInjection;

use Matthias\BundlePlugins\ExtensionWithPlugins;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Mi\MongoDb\Migration\DependencyInjection\MigrationPlugin;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 *
 * @covers Mi\MongoDb\Migration\DependencyInjection\MigrationPlugin
 */
class MigrationPluginExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function loadServices()
    {
        $this->load(['migration' => ['xml_path' => '/mi/mongo/migration/services/version.xml', 'path' => '%app.root_dir%/src/Migration/Version', 'namespace' => 'Mi\Test', 'migration_collection' => 'service_id']]);

        $this->assertContainerBuilderHasAlias('mi.mongo_db.migration.migration.collection', 'service_id');
        $this->assertContainerBuilderHasParameter('xml_path', '/mi/mongo/migration/services/version.xml');
        $this->assertContainerBuilderHasParameter('migration_path', '%app.root_dir%/src/Migration/Version');
        $this->assertContainerBuilderHasParameter('namespace', 'Mi\Test');
    }

    /**
     * @inheritDoc
     */
    protected function getContainerExtensions()
    {
        return [new ExtensionWithPlugins('test', [new MigrationPlugin()])];
    }
}
