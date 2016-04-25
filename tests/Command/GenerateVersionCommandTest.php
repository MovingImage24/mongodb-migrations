<?php

namespace Mi\MongoDb\Migration\Tests\Command;

use Mi\MongoDb\Migration\Command\GenerateVersionCommand;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Puli\Repository\FilesystemRepository;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 *
 * @covers Mi\MongoDb\Migration\Command\GenerateVersionCommand
 */
class GenerateVersionCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var vfsStreamDirectory
     */
    private $fileSystem;

    /**
     * @test
     */
    public function generateVersion()
    {
        self::assertEquals(0, $this->commandTester->execute([]));

        /** @var vfsStreamDirectory $versionDir */
        $versionDir = $this->fileSystem->getChild('Version');

        self::assertRegExp(
            '/class Version\d{14} implements Version/',
            file_get_contents($versionDir->getChildren()[0]->url())
        );

        self::assertRegExp(
            '/<service id=\"mi\\.mongo_db\\.migration\\.version\\.\\d{14}\" lazy=\"true\" autowire=\"true\" class=\"Test\\\\Name\\\\\Space\\\\Version\\d{14}\"><tag name=\"mi\\.mongo_db\\.version\" version_number=\"\\d{14}\"\\/><\\/service>/',
            file_get_contents('vfs://root/fixture.xml')
        );
    }

    protected function setUp()
    {
        $xmlContent = <<<'EOF'
<?xml version="1.0"?>
<root>
  <services>

</services>
</root>

EOF;


        $this->fileSystem = vfsStream::setup('root', null, ['Version' => [], 'fixture.xml' => $xmlContent]);
        $repo = new FilesystemRepository('vfs://root');

        $this->commandTester = new CommandTester(
            new GenerateVersionCommand('vfs://root/Version', new Filesystem(), $repo, 'Test\Name\Space', '/fixture.xml')
        );
    }
}
