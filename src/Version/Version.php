<?php

namespace Mi\MongoDb\Migration\Version;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
interface Version
{
    /**
     * @return void
     */
    public function migrate();

    /**
     * @return void
     */
    public function rollback();

    /**
     * @return boolean
     */
    public function verifyMigration();

    /**
     * @return string
     */
    public function errorMessage();
}
