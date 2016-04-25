<?php

namespace Mi\MongoDb\Migration\Version;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
class VersionCollection
{
    private $versions = [];

    /**
     * @param int     $versionId
     * @param Version $version
     */
    public function addVersion($versionId, Version $version)
    {
        $this->versions[$versionId] = $version;
        ksort($this->versions);
    }

    /**
     * @param int $versionId
     *
     * @return \Generator|Version[]
     */
    public function filteredByVersion($versionId)
    {
        foreach ($this->versions as $id => $version) {
            if ($id > $versionId) {
                yield $id => $version;
            }
        }
    }
}
