<?php

namespace Mi\MongoDb\Migration\Tests\Command;

use Mi\MongoDb\Migration\Command\MigrateVersionCommand;
use Mi\MongoDb\Migration\Version\Version;
use Mi\MongoDb\Migration\Version\VersionCollection;
use MongoDB\BSON\UTCDatetime;
use MongoDB\Collection;
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
        $version = $this->prophesize(Version::class);
        
        $this->versionCollection->filteredByVersion(2)->willReturn([3 => $version->reveal()]);

        $version->migrate()->shouldBeCalled();
        $version->verifyMigration()->willReturn(false);
        $version->errorMessage()->willReturn('test error');

        $this->migrationCollection->findOne([], ['projection' => ['to' => 1], 'sort' => ['createdAt' => -1]])->willReturn(['to' => 2]);

        self::expectException('\RuntimeException');
        self::expectExceptionMessage('test error');

        $this->commandTester->execute([]);
    }

    /**
     * @test
     */
    public function migrateVersion()
    {
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
            \PHPUnit_Framework_TestCase::assertInstanceOf(UTCDatetime::class, $insert['createdAt']);

            return true;
        };

        $this->migrationCollection->insertOne(Argument::that($insertCheck))->shouldBeCalled();
        $this->migrationCollection->findOne([], ['projection' => ['to' => 1], 'sort' => ['createdAt' => -1]])->willReturn(['to' => 2]);


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
        $this->migrationCollection->findOne([], ['projection' => ['to' => 1], 'sort' => ['createdAt' => -1]])->willReturn(['_id' => 'wrong', 'no_to' => '1']);

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

        $version = $this->prophesize(Version::class);

        $this->versionCollection->filteredByVersion(0)->willReturn([3 => $version->reveal()]);

        $version->migrate()->shouldBeCalled();
        $version->verifyMigration()->willReturn(true);

        $insertCheck = function (array $insert) {

            \PHPUnit_Framework_TestCase::assertCount(3, $insert);
            \PHPUnit_Framework_TestCase::assertEquals(0, $insert['from']);
            \PHPUnit_Framework_TestCase::assertEquals(3, $insert['to']);
            \PHPUnit_Framework_TestCase::assertInstanceOf(UTCDatetime::class, $insert['createdAt']);

            return true;
        };

        $this->migrationCollection->insertOne(Argument::that($insertCheck))->shouldBeCalled();
        $this->migrationCollection->findOne([], ['projection' => ['to' => 1], 'sort' => ['createdAt' => -1]])->willReturn(null);

        self::assertEquals(0, $this->commandTester->execute([]));

        self::assertContains(
            'Migration until version 3 done.',
            $this->commandTester->getDisplay()
        );
    }

    protected function setUp()
    {
        $this->versionCollection = $this->prophesize(VersionCollection::class);
        $this->migrationCollection = $this->prophesize(Collection::class);
        $this->commandTester = new CommandTester(
            new MigrateVersionCommand($this->versionCollection->reveal(), $this->migrationCollection->reveal())
        );
    }
}
