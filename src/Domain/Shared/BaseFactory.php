<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

abstract class BaseFactory
{
    public function fromArray(array $array): BaseFactory
    {
        $reflectedFactoryClass = new ReflectionClass($this);
        $factoryMethods = $reflectedFactoryClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $factoryMethodMap = [];

        foreach ($factoryMethods as $method) {
            $methodName = $method->getName();

            if (str_starts_with($methodName, 'set')) {
                $property = lcfirst(substr($methodName, 3));
                $factoryMethodMap[$property] = $method;
            }
        }

        foreach ($array as $key => $value) {
            $camelCaseKey = lcfirst(str_replace('_', '', ucwords($key, '_')));

            if (array_key_exists($camelCaseKey, $factoryMethodMap)) {
                $parameter = $factoryMethodMap[$camelCaseKey]->getParameters()[0];
                $type = $parameter->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $typeName = $type->getName();

                    if (class_exists($typeName)) {
                        $value = new $typeName($value);
                    }
                }

                $this->{$factoryMethodMap[$camelCaseKey]->getName()}($value);
            }
        }

        return $this;
    }


    /**
     * @param DateTimeInterface|string|null $dataClique
     * @return DateTimeInterface
     * @throws DateMalformedStringException
     */
    protected function handleData(DateTimeInterface | string | null $dataClique = null): DateTimeInterface
    {
        if ($dataClique instanceof DateTimeInterface) {
            return $dataClique;
        }

        if (is_string($dataClique)) {
            return new DateTimeImmutable($dataClique);
        }

        return new DateTimeImmutable();
    }
}