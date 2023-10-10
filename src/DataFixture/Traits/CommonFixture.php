<?php declare(strict_types = 1);

namespace WhiteDigital\Config\DataFixture\Traits;

use BackedEnum;
use InvalidArgumentException;
use WhiteDigital\Config\DataFixture\BaseClassifierFixture;

use function sprintf;

trait CommonFixture
{
    /**
     * @return BackedEnum
     */
    protected function getClassifier(BackedEnum $type, ?int $key = null): object
    {
        $ref = ($values = BaseClassifierFixture::$references[BaseClassifierFixture::class][$type->name])[$key ??= self::randomArrayKey($values)] ?? null;
        if (null === $ref) {
            throw new InvalidArgumentException(sprintf('Key %d not found in %s', $key, $type->name));
        }

        return $this->getReference($ref);
    }

    protected function getClassifierReferences(BackedEnum $type): array
    {
        return BaseClassifierFixture::$references[BaseClassifierFixture::class][$type->name] ?? [];
    }
}
