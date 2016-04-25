<?php

namespace Mi\MongoDb\Migration\Tests\Version;

use Mi\MongoDb\Migration\Version\Version;
use Mi\MongoDb\Migration\Version\VersionCollection;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 *
 * @covers Mi\MongoDb\Migration\Version\VersionCollection
 */
class VersionCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VersionCollection
     */
    private $collection;

    /**
     * @test
     */
    public function filteredByVersion()
    {
        $version2 = $this->prophesize(Version::class);
        $version1 = $this->prophesize(Version::class);

        $this->collection->addVersion(2, $version2->reveal());
        $this->collection->addVersion(1, $version1->reveal());


        self::assertEquals([], iterator_to_array($this->collection->filteredByVersion(2)));
        self::assertEquals([2 => $version2->reveal()], iterator_to_array($this->collection->filteredByVersion(1)));
    }

    protected function setUp()
    {
        $this->collection = new VersionCollection();
    }
}
