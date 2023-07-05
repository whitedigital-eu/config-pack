<?php declare(strict_types = 1);

namespace WhiteDigital\Config;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class Faker
{
    private static Generator $factory;

    public function __construct(?string $locale = 'lv_LV', ?int $seed = 2022, ?ParameterBagInterface $bag = null)
    {
        if (null !== $bag) {
            $locale = $bag->get('whitedigital.test.locale');
            $seed = $bag->get('whitedigital.test.seed');
        }

        self::$factory = Factory::create($locale);
        self::$factory->seed($seed);
    }

    public static function f(): Generator
    {
        return self::$factory;
    }
}
