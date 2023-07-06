<?php declare(strict_types = 1);

namespace WhiteDigital\Config;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class Faker
{
    private static Generator $factory;

    public function __construct(ParameterBagInterface $bag)
    {
        self::$factory = Factory::create($bag->get('whitedigital.test.locale'));
        self::$factory->seed($bag->get('whitedigital.test.seed'));
    }

    public static function f(): Generator
    {
        return self::$factory;
    }
}
