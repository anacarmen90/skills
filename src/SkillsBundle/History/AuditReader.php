<?php

namespace SkillsBundle\History;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query;
use SimpleThings\EntityAudit\AuditConfiguration;
use Doctrine\ORM\Mapping\QuoteStrategy;
use SimpleThings\EntityAudit\Collection\AuditedCollection;
use SkillsBundle\Entity\UserSkillLevel;

/**
 * Class AuditReader
 *
 * @package SkillsBundle\History
 */
class SkillsAuditReader
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AuditConfiguration
     */
    protected $config;

    /**
     * @var ClassMetadataInfo
     */
    protected $classMetadata;

    /**
     * @var AbstractPlatform
     */
    protected $platform;

    /**
     * @var QuoteStrategy
     */
    protected $quoteStrategy;

    /**
     * Entity cache to prevent circular references
     * @var array
     */
    protected $entityCache;

    /**
     * SkillsAuditReader constructor.
     *
     * @param \SimpleThings\EntityAudit\AuditConfiguration $config
     * @param EntityManagerInterface $em
     */
    public function __construct(AuditConfiguration $config, EntityManagerInterface $em)
    {
        $this->config = $config;
        $this->classMetadata = $em->getClassMetadata('SkillsBundle:UserSkillLevel');
        $this->em = $em;
        $this->platform = $em->getConnection()->getDatabasePlatform();
        $this->quoteStrategy = $em->getConfiguration()->getQuoteStrategy();
    }

    /**
     * This method is copied and modified from AuditReader's findRevisions()
     *
     * @param int $userId
     *
     * @return array
     */
    public function findUserRevisionHistory(int $userId)
    {
        $tableName = $this->config->getTableName($this->classMetadata);

        $whereId = [];
//        foreach ($this->classMetadata->identifier AS $idField) {
//            if (isset($this->classMetadata->fieldMappings[$idField])) {
//                $columnName = $this->classMetadata->fieldMappings[$idField]['columnName'];
//            } else {
//                if (isset($this->classMetadata->associationMappings[$idField])) {
//                    $columnName = $this->classMetadata->associationMappings[$idField]['joinColumns'][0];
//                } else {
//                    continue;
//                }
//            }
//
//            $whereId[] = "{$columnName} = ?";
//        }
        $whereId[] = "usl.user_id = ?";

        $whereSQL = implode(' AND ', $whereId);
        $columnList = [$this->config->getRevisionFieldName()];
        $columnMap = [];

        foreach ($this->classMetadata->fieldNames as $columnName => $field) {
            $type = Type::getType($this->classMetadata->fieldMappings[$field]['type']);
            $columnList[] = $type->convertToPHPValueSQL(
                $this->quoteStrategy->getColumnName(
                  $field,
                  $this->classMetadata,
                  $this->platform
                ),
                $this->platform
              ).' AS '.$this->platform->quoteSingleIdentifier($field);
            $columnMap[$field] = $this->platform->getSQLResultCasing(
              $columnName
            );
        }

        foreach ($this->classMetadata->associationMappings AS $assoc) {
            if (($assoc['type'] & ClassMetadata::TO_ONE) == 0 || !$assoc['isOwningSide']) {
                continue;
            }

            foreach ($assoc['targetToSourceKeyColumns'] as $sourceCol) {
                $columnList[] = $sourceCol;
                $columnMap[$sourceCol] = $this->platform->getSQLResultCasing(
                  $sourceCol
                );
            }
        }

//        $values = array_values($id);

        $query = "SELECT ".implode(
            ', ',
            $columnList
          )." FROM ".$tableName." e WHERE ".$whereSQL." ORDER BY e.".$this->config->getRevisionFieldName(
          )." DESC";
        $stmt = $this->em->getConnection()->executeQuery($query, $values);

        $result = [];
        while ($row = $stmt->fetch(Query::HYDRATE_ARRAY)) {
            $rev = $row[$this->config->getRevisionFieldName()];
            unset($row[$this->config->getRevisionFieldName()]);
            $result[] = $row;
//            $result[] = $this->createEntity(
//              $this->classMetadata->name,
//              $columnMap,
//              $row,
//              $rev
//            );
        }

        return $result;
    }

    /**
     * Simplified and stolen code from UnitOfWork::createEntity.
     *
     * @param string $className
     * @param array $columnMap
     * @param array $data
     * @param $revision
     * @throws DeletedException
     * @throws NoRevisionFoundException
     * @throws NotAuditedException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     * @return object
     */
    private function createEntity($className, array $columnMap, array $data, $revision)
    {
        /** @var ClassMetadataInfo|ClassMetadata $class */
        $class = $this->em->getClassMetadata($className);

        //lookup revisioned entity cache
        $keyParts = array();

        foreach($class->getIdentifierFieldNames() as $name) {
            $keyParts[] = $data[$name];
        }

        $key = implode(':', $keyParts);

        if (isset($this->entityCache[$className]) &&
          isset($this->entityCache[$className][$key]) &&
          isset($this->entityCache[$className][$key][$revision])
        ) {
            return $this->entityCache[$className][$key][$revision];
        }

        if (!$class->isInheritanceTypeNone()) {
            if (!isset($data[$class->discriminatorColumn['name']])) {
                throw new \RuntimeException('Expecting discriminator value in data set.');
            }
            $discriminator = $data[$class->discriminatorColumn['name']];
            if (!isset($class->discriminatorMap[$discriminator])) {
                throw new \RuntimeException("No mapping found for [{$discriminator}].");
            }

            if ($class->discriminatorValue) {
                $entity = $this->em->getClassMetadata($class->discriminatorMap[$discriminator])->newInstance();
            } else {
                //a complex case when ToOne binding is against AbstractEntity having no discriminator
                $pk = array();

                foreach ($class->identifier as $field) {
                    $pk[$class->getColumnName($field)] = $data[$field];
                }

                return $this->find($class->discriminatorMap[$discriminator], $pk, $revision);
            }
        } else {
            $entity = $class->newInstance();
        }

        //cache the entity to prevent circular references
        $this->entityCache[$className][$key][$revision] = $entity;

        foreach ($data as $field => $value) {
            if (isset($class->fieldMappings[$field])) {
                $type = Type::getType($class->fieldMappings[$field]['type']);
                $value = $type->convertToPHPValue($value, $this->platform);
                $class->reflFields[$field]->setValue($entity, $value);
            }
        }

        foreach ($class->associationMappings as $field => $assoc) {
            // Check if the association is not among the fetch-joined associations already.
            if (isset($hints['fetched'][$className][$field])) {
                continue;
            }

            /** @var ClassMetadataInfo|ClassMetadata $targetClass */
            $targetClass = $this->em->getClassMetadata($assoc['targetEntity']);

            if ($assoc['type'] & ClassMetadata::TO_ONE) {
                //print_r($targetClass->discriminatorMap);
                if ($this->metadataFactory->isAudited($assoc['targetEntity'])) {
                    if ($this->loadAuditedEntities) {
                        // Primary Key. Used for audit tables queries.
                        $pk = array();
                        // Primary Field. Used when fallback to Doctrine finder.
                        $pf = array();

                        if ($assoc['isOwningSide']) {
                            foreach ($assoc['targetToSourceKeyColumns'] as $foreign => $local) {
                                $pk[$foreign] = $pf[$foreign] = $data[$columnMap[$local]];
                            }
                        } else {
                            /** @var ClassMetadataInfo|ClassMetadata $otherEntityMeta */
                            $otherEntityAssoc = $this->em->getClassMetadata($assoc['targetEntity'])->associationMappings[$assoc['mappedBy']];

                            foreach ($otherEntityAssoc['targetToSourceKeyColumns'] as $local => $foreign) {
                                $pk[$foreign] = $pf[$otherEntityAssoc['fieldName']] = $data[$class->getFieldName($local)];
                            }
                        }

                        $pk = array_filter($pk, function ($value) {
                            return !is_null($value);
                        });

                        if (!$pk) {
                            $class->reflFields[$field]->setValue($entity, null);
                        } else {
                            try {
                                $value = $this->find($targetClass->name, $pk, $revision, array('threatDeletionsAsExceptions' => true));
                            } catch (DeletedException $e) {
                                $value = null;
                            } catch (NoRevisionFoundException $e) {
                                // The entity does not have any revision yet. So let's get the actual state of it.
                                $value = $this->em->getRepository($targetClass->name)->findOneBy($pf);
                            }

                            $class->reflFields[$field]->setValue($entity, $value);
                        }
                    } else {
                        $class->reflFields[$field]->setValue($entity, null);
                    }
                } else {
                    if ($this->loadNativeEntities) {
                        if ($assoc['isOwningSide']) {
                            $associatedId = array();
                            foreach ($assoc['targetToSourceKeyColumns'] as $targetColumn => $srcColumn) {
                                $joinColumnValue = isset($data[$columnMap[$srcColumn]]) ? $data[$columnMap[$srcColumn]] : null;
                                if ($joinColumnValue !== null) {
                                    $associatedId[$targetClass->fieldNames[$targetColumn]] = $joinColumnValue;
                                }
                            }
                            if (!$associatedId) {
                                // Foreign key is NULL
                                $class->reflFields[$field]->setValue($entity, null);
                            } else {
                                $associatedEntity = $this->em->getReference($targetClass->name, $associatedId);
                                $class->reflFields[$field]->setValue($entity, $associatedEntity);
                            }
                        } else {
                            // Inverse side of x-to-one can never be lazy
                            $class->reflFields[$field]->setValue($entity, $this->getEntityPersister($assoc['targetEntity'])
                              ->loadOneToOneEntity($assoc, $entity));
                        }
                    } else {
                        $class->reflFields[$field]->setValue($entity, null);
                    }
                }
            } elseif ($assoc['type'] & ClassMetadata::ONE_TO_MANY) {
                if ($this->metadataFactory->isAudited($assoc['targetEntity'])) {
                    if ($this->loadAuditedCollections) {
                        $foreignKeys = array();
                        foreach ($targetClass->associationMappings[$assoc['mappedBy']]['sourceToTargetKeyColumns'] as $local => $foreign) {
                            $field = $class->getFieldForColumn($foreign);
                            $foreignKeys[$local] = $class->reflFields[$field]->getValue($entity);
                        }

                        $collection = new AuditedCollection($this, $targetClass->name, $targetClass, $assoc, $foreignKeys, $revision);

                        $class->reflFields[$assoc['fieldName']]->setValue($entity, $collection);
                    } else {
                        $class->reflFields[$assoc['fieldName']]->setValue($entity, new ArrayCollection());
                    }
                } else {
                    if ($this->loadNativeCollections) {
                        $collection = new PersistentCollection($this->em, $targetClass, new ArrayCollection());

                        $this->getEntityPersister($assoc['targetEntity'])
                          ->loadOneToManyCollection($assoc, $entity, $collection);

                        $class->reflFields[$assoc['fieldName']]->setValue($entity, $collection);
                    } else {
                        $class->reflFields[$assoc['fieldName']]->setValue($entity, new ArrayCollection());
                    }
                }
            } else {
                // Inject collection
                $reflField = $class->reflFields[$field];
                $reflField->setValue($entity, new ArrayCollection);
            }
        }

        return $entity;
    }
}
