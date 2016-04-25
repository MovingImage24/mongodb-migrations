<?php

namespace Mi\MongoDb\Migration\Tests\Command;

use Mi\MongoDb\Migration\Command\MigrateVersionCommand;
use Mi\MongoDb\Migration\Version\Version;
use Mi\MongoDb\Migration\Version\VersionCollection;
use MongoCollection;
use MongoCursor;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 *
 * @covers Mi\MongoDb\Migration\Command\MigrateVersionCommand
 */
class MigrateVersionCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    private $versionCollection;
    private $migrationCollection;

    /**
     * @test
     */
    public function throwExceptionIfMigrateVersionFails()
    {

        $cursor = $this->prophesize(MongoCursor::class);
        $version = $this->prophesize(Version::class);
        
        $cursor->sort(['createdAt' => -1])->willReturn($cursor->reveal());
        $cursor->limit(1)->willReturn($cursor->reveal());
        $cursor->getNext()->willReturn(['to' => 2]);

        $this->versionCollection->filteredByVersion(2)->willReturn([3 => $version->reveal()]);

        $version->migrate()->shouldBeCalled();
        $version->verifyMigration()->willReturn(false);
        $version->errorMessage()->willReturn('test error');

        $this->migrationCollection->find([], ['to' => true])->willReturn($cursor->reveal());

        self::expectException('\RuntimeException');
        self::expectExceptionMessage('test error');

        $this->commandTester->execute([]);
    }

    /**
     * @test
     */
    public function migrateVersion()
    {
        $cursor = $this->prophesize(MongoCursor::class);
        $version = $this->prophesize(Version::class);

        $cursor->sort(['createdAt' => -1])->willReturn($cursor->reveal());
        $cursor->limit(1)->willReturn($cursor->reveal());
        $cursor->getNext()->willReturn(['to' => 2]);

        $this->versionCollection->filteredByVersion(2)->willReturn([3 => $version->reveal()]);

        $version->migrate()->shouldBeCalled();
        $version->verifyMigration()->willReturn(true);

        $insertCheck = function (array $insert) {

            \PHPUnit_Framework_TestCase::assertCount(3, $insert);
            \PHPUnit_Framework_TestCase::assertEquals(2, $insert['from']);
            \PHPUnit_Framework_TestCase::assertEquals(3, $insert['to']);
            \PHPUnit_Framework_TestCase::assertInstanceOf('\MongoDate', $insert['createdAt']);

            return true;
        };

        $this->migrationCollection->insert(Argument::that($insertCheck))->shouldBeCalled();
        $this->migrationCollection->find([], ['to' => true])->willReturn($cursor->reveal());

        self::assertEquals(0, $this->commandTester->execute([]));

        self::assertContains(
            'Migration until version 3 done.',
            $this->commandTester->getDisplay()
        );
    }

    /**
     * @test
     */
    public function tryToMigrateWithInvalidVersion()
    {
        $cursor = $this->prophesize(MongoCursor::class);

        $cursor->sort(['createdAt' => -1])->willReturn($cursor->reveal());
        $cursor->limit(1)->willReturn($cursor->reveal());
        $cursor->getNext()->willReturn(['_id' => 'wrong', 'no_to' => '1']);

        $this->migrationCollection->find([], ['to' => true])->willReturn($cursor->reveal());

        self::assertEquals(1, $this->commandTester->execute([]));

        self::assertContains(
            'Migration with id "wrong" was found, but no current version.',
            $this->commandTester->getDisplay()
        );
    }


    /**
     * @test
     */
    public function migrateFirstVersion()
    {

        $cursor = $this->prophesize(MongoCursor::class);
        $version = $this->prophesize(Version::class);

        $cursor->sort(['createdAt' => -1])->willReturn($cursor->reveal());
        $cursor->limit(1)->willReturn($cursor->reveal());
        $cursor->getNext()->willReturn(null);

        $this->versionCollection->filteredByVersion(0)->willReturn([3 => $version->reveal()]);

        $version->migrate()->shouldBeCalled();
        $version->verifyMigration()->willReturn(true);

        $insertCheck = function (array $insert) {

            \PHPUnit_Framework_TestCase::assertCount(3, $insert);
            \PHPUnit_Framework_TestCase::assertEquals(0, $insert['from']);
            \PHPUnit_Framework_TestCase::assertEquals(3, $insert['to']);
            \PHPUnit_Framework_TestCase::assertInstanceOf('\MongoDate', $insert['createdAt']);

            return true;
        };

        $this->migrationCollection->insert(Argument::that($insertCheck))->shouldBeCalled();
        $this->migrationCollection->find([], ['to' => true])->willReturn($cursor->reveal());

        self::assertEquals(0, $this->commandTester->execute([]));

        self::assertContains(
            'Migration until version 3 done.',
            $this->commandTester->getDisplay()
        );
    }

    protected function setUp()
    {
        $this->versionCollection = $this->prophesize(VersionCollection::class);
        $this->migrationCollection = $this->prophesize(MongoCollection::class);
        $this->commandTester = new CommandTester(
            new MigrateVersionCommand($this->versionCollection->reveal(), $this->migrationCollection->reveal())
        );
    }
}
