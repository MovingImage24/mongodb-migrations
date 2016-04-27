<?php

namespace Mi\MongoDb\Migration\Command;

use Mi\MongoDb\Migration\Version\VersionCollection;
use MongoDB\BSON\UTCDatetime;
use MongoDB\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
class MigrateVersionCommand extends Command
{
    private $versionCollection;
    private $migrationsCollection;

    /**
     * @param VersionCollection $versionCollection
     * @param Collection        $migrationsCollection
     */
    public function __construct(VersionCollection $versionCollection, Collection $migrationsCollection)
    {
        $this->versionCollection = $versionCollection;
        $this->migrationsCollection = $migrationsCollection;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('mi:mongo-db:migration:migrate')
            ->setDescription('migrate from the last version to the actual version.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->migrationsCollection->findOne([], ['projection' => ['to' => 1], 'sort' => ['createdAt' => -1]]);

        $currentVersion = 0;

        if (is_array($data)) {
            if (array_key_exists('to', $data) === false) {
                $output->writeln(
                    sprintf('<error>Migration with id "%s" was found, but no current version.</error>', $data['_id'])
                );

                return 1;
            }

            $currentVersion = $data['to'];
        }

        if ($currentVersion === 0) {
            $this->migrationsCollection->createIndex(['createdAt' => -1]);
        }

        foreach ($this->versionCollection->filteredByVersion($currentVersion) as $version => $migration) {
            $migration->migrate();
            if (!$migration->verifyMigration()) {
                $migration->rollback();
                throw new \RuntimeException($migration->errorMessage());
            }
            $versionInfo = ['from'      => $currentVersion,
                            'to'        => $version,
                            'createdAt' => new UTCDatetime(time() * 1000),
            ];
            $this->migrationsCollection->insertOne($versionInfo);

            $currentVersion = $version;
        }

        $output->writeln(sprintf('<info>Migration until version %s done.</info>', $currentVersion));


        return 0;
    }
}
