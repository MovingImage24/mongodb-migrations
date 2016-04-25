<?php

namespace Mi\MongoDb\Migration\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Mi\MongoDb\Migration\DependencyInjection\Compiler\VersionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;


/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 *
 * @covers Mi\MongoDb\Migration\DependencyInjection\Compiler\VersionPass
 */
class VersionPassTest extends AbstractCompilerPassTestCase
{

    /**
     * @test
     */
    public function shouldBuildDriverWithPuliFileLocator()
    {
        $version1 = new Definition();
        $version1->addTag('mi.mongo_db.version', ['version_number' => '1']);
        $version2 = new Definition();
        $version2->addTag('mi.mongo_db.version', ['version_number' => '2']);

        $versionCollection = new Definition();

        $this->setDefinition('mi.mongo_db.migration.version.collection', $versionCollection);
        $this->setDefinition('version_1', $version1);
        $this->setDefinition('version_2', $version2);
        $this->compile();

        $methods = $versionCollection->getMethodCalls();

        self::assertCount(2, $methods);

        self::assertEquals('addVersion', $methods[0][0]);
        self::assertEquals('addVersion', $methods[1][0]);

        self::assertEquals('1', $methods[0][1][0]);
        self::assertEquals('2', $methods[1][1][0]);

        self::assertEquals('version_1', (string) $methods[0][1][1]);
        self::assertEquals('version_2', (string) $methods[1][1][1]);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new VersionPass());
    }
}
