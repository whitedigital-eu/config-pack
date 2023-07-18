<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Traits;

use BackedEnum;
use Random\Randomizer;

trait Common
{
    protected static function getEnum(array $cases): BackedEnum
    {
        return $cases[self::randomArrayKey($cases)];
    }

    protected static function randomArrayKey(array $array): mixed
    {
        return self::randomArrayKeys($array, 1)[0];
    }

    protected static function randomArrayKeys(array $array, ?int $count = null): array
    {
        $count ??= count($array) - 1;

        return (new Randomizer())->pickArrayKeys($array, $count);
    }
}
