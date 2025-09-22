<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use App\Domain\Shared\Exceptions\IdAlreadyAssignedException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

abstract class AbstractDomainEntity implements DomainEntityInterface
{
    public function __construct(private ?int $id)
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @throws IdAlreadyAssignedException
     */
    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            $entityClass = get_class($this);
            throw new IdAlreadyAssignedException("Esta entidade ($entityClass) já possui um ID atribuído.");
        }

        $this->id = $id;
    }

    public function toArray(): array
    {
        $reflectionClass = new ReflectionClass($this);
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $array = [];

        foreach ($properties as $property) {
            $name = lcfirst(str_replace('_', '', ucwords($property->getName(), '_')));
            $type = $property->getType();
            $getter = 'get' . ucfirst($name);

            if (method_exists($this, $getter)) {
                $value = $property->isPublic() ? $this->{$getter}() : $property->getValue($this);

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $typeName = $type->getName();

                    if (class_exists($typeName)) {
                        $value = new $typeName($value);
                    }
                }

                $array[strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($name)))] = $value;
            }
        }

        return $array;
    }
}