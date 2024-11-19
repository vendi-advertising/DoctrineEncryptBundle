<?php

namespace Ambta\DoctrineEncryptBundle\EventListener;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Embedded;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

#[AsDoctrineListener(event: Events::postLoad, connection: 'default')]
#[AsDoctrineListener(event: Events::postUpdate, connection: 'default')]
#[AsDoctrineListener(event: Events::preFlush, connection: 'default')]
#[AsDoctrineListener(event: Events::preUpdate, connection: 'default')]
#[AsDoctrineListener(event: Events::onFlush, connection: 'default')]
#[AsDoctrineListener(event: Events::postFlush, connection: 'default')]
class EntityEncryptDecryptListener
{
    /**
     * Appended to end of encrypted value
     */
    public const ENCRYPTION_MARKER = '<ENC>';

    public const METHOD_DECRYPT = 'decrypt';
    public const METHOD_ENCRYPT = 'encrypt';

    /**
     * Used for restoring the encryptor after changing it
     */
    private ?EncryptorInterface $restoreEncryptor;

    public function __construct(
        private ?EncryptorInterface $encryptor,
    ) {
        $this->restoreEncryptor = $this->encryptor;
    }

    /**
     * Count number of decrypted values in this service
     */
    public int $decryptCounter = 0;

    /**
     * Count number of encrypted values in this service
     */
    public int $encryptCounter = 0;

    private array $cachedDecryptions = [];

    /**
     * Get the current encryptor
     */
    public function getEncryptor(): ?EncryptorInterface
    {
        return $this->encryptor;
    }

    /**
     * Restore encryptor to the one set in the constructor.
     */
    public function restoreEncryptor(): void
    {
        $this->encryptor = $this->restoreEncryptor;
    }

    /**
     * Change the encryptor
     */
    public function setEncryptor(?EncryptorInterface $encryptor = null): void
    {
        $this->encryptor = $encryptor;
    }

    private function isEmbeddedProperty(ReflectionProperty $refProperty): bool
    {
        return count($refProperty->getAttributes(Embedded::class)) > 0;
    }

    private function getAttributeForPropertyByName(ReflectionProperty $refProperty, string $attributeName): ?object
    {
        $attributes = $refProperty->getAttributes($attributeName, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) > 0) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    private function hasAttributeForPropertyByName(ReflectionProperty $refProperty, string $attributeName): bool
    {
        return count($refProperty->getAttributes($attributeName, ReflectionAttribute::IS_INSTANCEOF)) > 0;
    }

    /**
     * Process (encrypt/decrypt) entities fields
     *
     * @throws RuntimeException|ReflectionException
     */
    public function processFields(object $entity, bool $isEncryptOperation = true): ?object
    {
        if ( ! empty($this->encryptor)) {
            // Check which operation to be used
            $encryptorMethod = $isEncryptOperation ? self::METHOD_ENCRYPT : self::METHOD_DECRYPT;

            $realClass = $entity::class;

            // Get ReflectionClass of our entity
            $properties = $this->getClassProperties($realClass);

            // Foreach property in the reflection class
            foreach ($properties as $refProperty) {
                if ($this->isEmbeddedProperty($refProperty, $isEncryptOperation)) {
                    $this->handleEmbeddedAnnotation($entity, $refProperty, $isEncryptOperation);
                    continue;
                }

                /**
                 * If property is a normal value and contains the Encrypt tag, let's encrypt/decrypt that property
                 */
                if ( ! $this->hasAttributeForPropertyByName($refProperty, Encrypted::class)) {
                    continue;
                }

                match ($encryptorMethod) {
                    self::METHOD_DECRYPT => $this->doDecrypt($entity, $refProperty),
                    self::METHOD_ENCRYPT => $this->doEncrypt($entity, $refProperty),
                };
            }

            return $entity;
        }

        return $entity;
    }

    private function doDecrypt(object $entity, $refProperty): void
    {
        $pac   = PropertyAccess::createPropertyAccessor();
        $value = $pac->getValue($entity, $refProperty->getName());

        if (empty($value)) {
            return;
        }

        if ( ! str_ends_with($value, self::ENCRYPTION_MARKER)) {
            return;
        }

        $this->decryptCounter++;
        $currentPropValue = $this->encryptor->decrypt(substr($value, 0, -5));
        $pac->setValue($entity, $refProperty->getName(), $currentPropValue);
        $this->cachedDecryptions[get_class($entity)][spl_object_id($entity)][$refProperty->getName()][$currentPropValue] = $value;
    }

    private function doEncrypt(object $entity, $refProperty): void
    {
        $pac   = PropertyAccess::createPropertyAccessor();
        $value = $pac->getValue($entity, $refProperty->getName());

        if (empty($value)) {
            return;
        }

        if (isset($this->cachedDecryptions[get_class($entity)][spl_object_id($entity)][$refProperty->getName()][$value])) {
            $pac->setValue($entity, $refProperty->getName(), $this->cachedDecryptions[get_class($entity)][spl_object_id($entity)][$refProperty->getName()][$value]);
        } elseif ( ! str_ends_with($value, self::ENCRYPTION_MARKER)) {
            $this->encryptCounter++;
            $currentPropValue = $this->encryptor->encrypt($value) . self::ENCRYPTION_MARKER;
            $pac->setValue($entity, $refProperty->getName(), $currentPropValue);
        }
    }

    private function handleEmbeddedAnnotation($entity, ReflectionProperty $embeddedProperty, bool $isEncryptOperation = true): void
    {
        $propName = $embeddedProperty->getName();

        $pac = PropertyAccess::createPropertyAccessor();

        $embeddedEntity = $pac->getValue($entity, $propName);

        if ($embeddedEntity && is_object($embeddedEntity)) {
            $this->processFields($embeddedEntity, $isEncryptOperation);
        }
    }

    /**
     * Recursive function to get an associative array of class properties
     * including inherited ones from extended classes
     *
     * @throws ReflectionException
     */
    private function getClassProperties(string $className): array
    {
        $reflectionClass = new ReflectionClass($className);
        $properties      = $reflectionClass->getProperties();
        $propertiesArray = [];

        foreach ($properties as $property) {
            $propertyName                   = $property->getName();
            $propertiesArray[$propertyName] = $property;
        }

        if ($parentClass = $reflectionClass->getParentClass()) {
            $parentPropertiesArray = $this->getClassProperties($parentClass->getName());
            if (count($parentPropertiesArray) > 0) {
                $propertiesArray = array_merge($parentPropertiesArray, $propertiesArray);
            }
        }

        return $propertiesArray;
    }

    /**
     * Encrypt entities that are inserted into the database
     */
    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $unitOfWork = $onFlushEventArgs->getObjectManager()->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            $encryptCounterBefore = $this->encryptCounter;
            $this->processFields($entity);
            if ($this->encryptCounter > $encryptCounterBefore) {
                $classMetadata = $onFlushEventArgs->getObjectManager()->getClassMetadata(get_class($entity));
                $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity);
            }
        }
    }

    /**
     * Decrypt entities after having been inserted into the database
     */
    public function postFlush(PostFlushEventArgs $postFlushEventArgs): void
    {
        $unitOfWork = $postFlushEventArgs->getObjectManager()->getUnitOfWork();
        foreach ($unitOfWork->getIdentityMap() as $entityMap) {
            foreach ($entityMap as $entity) {
                $this->processFields($entity, false);
            }
        }
    }

    /**
     * Encrypt entities property's values on preUpdate, so they will be stored encrypted
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->processFields($entity);
    }

    /**
     * Encrypt entities that are inserted into the database
     */
    public function preFlush(PreFlushEventArgs $preFlushEventArgs): void
    {
        $unitOfWOrk = $preFlushEventArgs->getObjectManager()->getUnitOfWork();
        foreach ($unitOfWOrk->getIdentityMap() as $entityName => $entityArray) {
            if (isset($this->cachedDecryptions[$entityName])) {
                foreach ($entityArray as $entityId => $instance) {
                    $this->processFields($instance);
                }
            }
        }
        $this->cachedDecryptions = [];
    }

    /**
     * Decrypt entities property's values when post updated.
     *
     * So for example after form submit the preUpdate encrypted the entity
     * We have to decrypt them before showing them again.
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->processFields($entity, false);
    }

    /**
     * Decrypt entities property's values when loaded into the entity manger
     */
    public function postLoad($args): void
    {
        $entity = $args->getObject();
        $this->processFields($entity, false);
    }
}