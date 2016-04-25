<?php

namespace Mi\MongoDb\Migration\Command;

use Mi\MongoDb\Migration\Version\Version;
use Puli\Repository\Api\ResourceRepository;
use Puli\Repository\Resource\FileResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\GenericTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\FileGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Reflection\MethodReflection;

/**
 * @author Alexander Miehe <alexander.miehe@movingimage.com>
 */
class GenerateVersionCommand extends Command
{
    private $classPath;
    private $filesystem;
    private $xmlPath;
    private $resourceRepository;
    private $namespaceName;

    /**
     * @param string             $classPath
     * @param Filesystem         $filesystem
     * @param ResourceRepository $resourceRepository
     * @param string             $namespaceName
     * @param string             $xmlPath
     */
    public function __construct(
        $classPath,
        Filesystem $filesystem,
        ResourceRepository $resourceRepository,
        $namespaceName,
        $xmlPath = null
    ) {
        $this->classPath = $classPath;
        $this->filesystem = $filesystem;
        $this->xmlPath = $xmlPath;
        $this->namespaceName = $namespaceName;
        $this->resourceRepository = $resourceRepository;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('mi:mongo-db:migration:generate')
            ->setDescription('Generate a empty version class to write database migration code.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $versionNumber = (new \DateTime())->format('YmdHis');
        $className = sprintf('Version%s', $versionNumber);

        $class = new ClassGenerator($className, $this->namespaceName);
        $class->addUse(Version::class);
        $class->setImplementedInterfaces(['Version']);
        $class->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('codeCoverageIgnore')]));

        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection(Version::class, 'migrate'));
        $methodGenerator->setFlags(MethodGenerator::FLAG_PUBLIC);
        $methodGenerator->setBody('//migration code');
        $class->addMethodFromGenerator($methodGenerator);

        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection(Version::class, 'rollback'));
        $methodGenerator->setFlags(MethodGenerator::FLAG_PUBLIC);
        $methodGenerator->setBody('//rollback code');
        $class->addMethodFromGenerator($methodGenerator);

        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection(Version::class, 'verifyMigration'));
        $methodGenerator->setFlags(MethodGenerator::FLAG_PUBLIC);
        $methodGenerator->setBody('return false;');
        $class->addMethodFromGenerator($methodGenerator);

        $methodGenerator = MethodGenerator::fromReflection(new MethodReflection(Version::class, 'errorMessage'));
        $methodGenerator->setFlags(MethodGenerator::FLAG_PUBLIC);
        $methodGenerator->setBody('return \'Something went wrong during migration.\';');
        $class->addMethodFromGenerator($methodGenerator);

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($class);

        $path = $this->classPath . '/' . $class->getName() . '.php';

        $this->filesystem->dumpFile($path, $fileGenerator->generate());
        $this->extendServiceXML($versionNumber, $this->namespaceName, $className);

        $output->writeln(sprintf('%s was generated and service definition was extended.', $path));

        return 0;
    }

    /**
     * @param string $versionNumber
     * @param string $namespaceName
     * @param string $className
     */
    private function extendServiceXML($versionNumber, $namespaceName, $className)
    {
        if ($this->xmlPath === null) {
            return;
        }
        /** @var FileResource $resource */
        $resource = $this->resourceRepository->get($this->xmlPath);

        $xml = simplexml_load_file($resource->getFilesystemPath());

        /** @var \SimpleXMLElement $serviceElement */
        $serviceElement = $xml->services->addChild('service');

        $serviceElement->addAttribute('id', sprintf('mi.mongo_db.migration.version.%s', $versionNumber));
        $serviceElement->addAttribute('lazy', 'true');
        $serviceElement->addAttribute('autowire', 'true');
        $serviceElement->addAttribute('class', $namespaceName . '\\' . $className);
        $tagElement = $serviceElement->addChild('tag');
        $tagElement->addAttribute('name', 'mi.mongo_db.version');
        $tagElement->addAttribute('version_number', $versionNumber);

        $xml->saveXML($resource->getFilesystemPath());
    }
}
