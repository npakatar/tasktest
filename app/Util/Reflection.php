<?php

namespace App\Util;

use Illuminate\Database\Eloquent\Model;

abstract class Reflection
{
    public static function isModelWithTrait($model, $trait, $recursive = true)
    {
        $class = self::getClass($model);

        if (!(new $class) instanceof Model) {
            return false;
        }

        return self::hasTrait($model, $trait, $recursive);
    }

    public static function hasTrait($param, $trait, $recursive = true)
    {
        $directTraits = collect(self::getReflectionInstance($param)->getTraits())->map->getName();

        $hasDirectTrait = $directTraits->contains($trait);

        if ($hasDirectTrait || !$recursive) {
            return $hasDirectTrait;
        }

        foreach ($parentClasses = self::getInheritanceChain($param) as $parentClass) {
            if (collect(self::getReflectionInstance($parentClass)->getTraits())->map->getName()->contains($trait)) {
                return true;
            }
        };

        return false;
    }

    public static function getInheritanceChain($param)
    {
        $parentClasses = [];

        $reflection = self::getReflectionInstance($param);

        while ($reflection = $reflection->getParentClass()) {
            $parentClasses[] = $reflection->getName();
        }

        return $parentClasses;
    }

    public static function getReflectionInstance($param)
    {
        return new \ReflectionClass(self::getClass($param));
    }

    public static function getClass($param)
    {
        if (is_string($param) && class_exists($param)) {
            return $param;
        }

        if (is_object($param)) {
            return get_class($param);
        }

        throw new \RuntimeException('INVALID_REFLECTION_PARAMETER');
    }
}
