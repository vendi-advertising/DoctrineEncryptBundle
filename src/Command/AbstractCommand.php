<?php

namespace Ambta\DoctrineEncryptBundle\Command;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Ambta\DoctrineEncryptBundle\Subscribers\DoctrineEncryptSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Command\Command;

/**
 * Base command containing usefull base methods.
 *
 * @author Michael Feinbier <michael@feinbier.net>
 **/
abstract class AbstractCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface|EntityManager $entityManager;

    /**
     * @var DoctrineEncryptSubscriber
     */
    protected DoctrineEncryptSubscriber $subscriber;

    /**
     * AbstractCommand constructor.
     *
     * @param EntityManager $entityManager
     * @param DoctrineEncryptSubscriber $subscriber
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DoctrineEncryptSubscriber $subscriber,
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->subscriber    = $subscriber;
    }

    /**
     * Get a result iterator over the whole table of an entity.
     */
    protected function getEntityIterator(string $entityName): iterable
    {
        $query = $this->entityManager->createQuery(sprintf('SELECT o FROM %s o', $entityName));

        return $query->toIterable();
    }

    /**
     * Get the number of rows in an entity-table
     */
    protected function getTableCount(string $entityName): int
    {
        $query = $this->entityManager->createQuery(sprintf('SELECT COUNT(o) FROM %s o', $entityName));

        return (int)$query->getSingleScalarResult();
    }

    /**
     * Return an array of entity-metadata for all entities
     * that have at least one encrypted property.
     */
    protected function getEncryptionableEntityMetaData(): array
    {
        $validMetaData = [];
        $metaDataArray = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metaDataArray as $entityMetaData) {
            if ($entityMetaData instanceof ClassMetadataInfo and $entityMetaData->isMappedSuperclass) {
                continue;
            }

            $properties = $this->getEncryptionableProperties($entityMetaData);
            if (count($properties) === 0) {
                continue;
            }

            $validMetaData[] = $entityMetaData;
        }

        return $validMetaData;
    }

    private function getAttributeForPropertyByName(ReflectionProperty $refProperty, string $attributeName): ?object
    {
        $attributes = $refProperty->getAttributes($attributeName, \ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) > 0) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    private function hasAttributeForPropertyByName(ReflectionProperty $refProperty, string $attributeName): bool
    {
        return count($refProperty->getAttributes($attributeName, \ReflectionAttribute::IS_INSTANCEOF)) > 0;
    }

    /**
     * @throws \ReflectionException
     */
    protected function getEncryptionableProperties($entityMetaData): array
    {
        //Create reflectionClass for each meta data object
        $reflectionClass = new ReflectionClass($entityMetaData->name);
        $propertyArray   = $reflectionClass->getProperties();
        $properties      = [];

        foreach ($propertyArray as $property) {
            if($property = $this->getAttributeForPropertyByName($property, Encrypted::class)) {
                $properties[] = $property;
            }
        }

        return $properties;
    }
}
