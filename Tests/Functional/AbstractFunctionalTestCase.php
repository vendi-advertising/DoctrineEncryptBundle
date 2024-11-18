<?php


namespace Ambta\DoctrineEncryptBundle\Tests\Functional;


use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Ambta\DoctrineEncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\TestCase;

abstract class AbstractFunctionalTestCase extends TestCase
{
    /** @var DoctrineEncryptSubscriber */
    protected $subscriber;
    /** @var EncryptorInterface */
    protected $encryptor;
    /** @var false|string */
    protected $dbFile;
    /** @var EntityManager */
    protected $entityManager;
    /** @var DebugStack */
    protected $sqlLoggerStack;

    abstract protected function getEncryptor(): EncryptorInterface;

    public function setUp(): void
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode                 = true;
        $proxyDir                  = null;
        $cache                     = null;
        $useSimpleAnnotationReader = false;

        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . "/fixtures/Entity"],
            $isDevMode,
            $proxyDir,
            $cache,
        );

        // database configuration parameters
        $this->dbFile = tempnam(sys_get_temp_dir(), 'amb_db');
        $conn         = [
            'driver' => 'pdo_sqlite',
            'path'   => $this->dbFile,
        ];

        // obtaining the entity manager
        $this->entityManager = EntityManager::create($conn, $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $classes    = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);

        $this->sqlLoggerStack = new DebugStack();
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->sqlLoggerStack);

        $this->encryptor  = $this->getEncryptor();
        $this->subscriber = new DoctrineEncryptSubscriber($this->encryptor);
        $this->entityManager->getEventManager()->addEventSubscriber($this->subscriber);

        error_reporting(E_ALL);
    }

    public function tearDown(): void
    {
        $this->entityManager->getConnection()->close();
        unlink($this->dbFile);
    }

    protected function getLatestInsertQuery(): ?array
    {
        $insertQueries = array_values(array_filter($this->sqlLoggerStack->queries, static function ($queryData) {
            return stripos($queryData['sql'], 'INSERT ') === 0;
        }));

        return current(array_reverse($insertQueries)) ?: null;
    }

    protected function getLatestUpdateQuery(): ?array
    {
        $insertQueries = array_values(array_filter($this->sqlLoggerStack->queries, static function ($queryData) {
            return stripos($queryData['sql'], 'UPDATE ') === 0;
        }));

        return current(array_reverse($insertQueries)) ?: null;
    }

    /**
     * Using the SQL Logger Stack this method retrieves the current query count executed in this test.
     */
    protected function getCurrentQueryCount(): int
    {
        return count($this->sqlLoggerStack->queries);
    }

    /**
     * Asserts that a string starts with a given prefix.
     *
     * @param string $stringn
     * @param string $string
     * @param string $message
     */
    public function assertStringDoesNotContain($needle, $string, $ignoreCase = false, $message = ''): void
    {
        if ( ! \is_string($needle)) {
            throw new \InvalidArgumentException(sprintf('Argument %d passed to %s must be a string, %s given', 1, __METHOD__, \gettype($needle)));
        }

        if ( ! \is_string($string)) {
            throw new \InvalidArgumentException(sprintf('Argument %d passed to %s must be a string, %s given', 2, __METHOD__, \gettype($string)));
        }

        if ( ! \is_bool($ignoreCase)) {
            throw new \InvalidArgumentException(sprintf('Argument %d passed to %s must be a bool, %s given', 3, __METHOD__, \gettype($ignoreCase)));
        }

        $constraint = new LogicalNot(
            new StringContains(
                $needle,
                $ignoreCase,
            ),
        );

        static::assertThat($string, $constraint, $message);
    }
}
