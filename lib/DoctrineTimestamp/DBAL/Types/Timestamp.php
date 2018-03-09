<?php

namespace DoctrineTimestamp\DBAL\Types;

use DateTimeInterface;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Timestamp type for the Doctrine 2 ORM
 */
class Timestamp extends Type
{
    /**
     * Type name
     *
     * @var string
     */
    const TIMESTAMP = 'timestamp';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::TIMESTAMP;
    }

    /**
     * @inheritDoc
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        throw $this->buildTypeException($value, ['null', 'DateTime']);
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!is_int($value)) {
            throw $this->buildTypeException($value, ['integer']);
        }
        
        return (new DateTimeImmutable())->setTimestamp($value);
    }

    /**
     * @inheritDoc
     */
    public function getBindingType()
    {
        return \PDO::PARAM_INT;
    }
    
    /**
     * @inheritDoc
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * ConversionException factory, using when needed & available the conversionFailedInvalidType
     * static factory.
     *
     * @param mixed      $value
     * @param array|null $allowedTypes Allowed types, if applyable.
     *
     * @return ConversionException
     */
    protected function buildTypeException($value, $allowedTypes = null)
    {
        if (
            is_array($allowedTypes)
            && is_callable('ConversionException::conversionFailedInvalidType')
        ) {
            return ConversionException::conversionFailedInvalidType(
                $value,
                $this->getName(),
                $allowedTypes
            );
        }

        return ConversionException::conversionFailed($value, $this->getName());
    }
}
