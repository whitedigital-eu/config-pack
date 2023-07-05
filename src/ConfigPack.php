<?php declare(strict_types = 1);

namespace WhiteDigital\Config;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function is_array;
use function ltrim;

class ConfigPack extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
            ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('login_email')->defaultNull()->end()
                    ->scalarNode('locale')->defaultValue('lv_LV')->end()
                    ->integerNode('seed')->defaultValue(2022)->end()
                ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach (self::makeOneDimension(['whitedigital.test' => $config]) as $key => $value) {
            $builder->setParameter($key, $value);
        }

        $container->import('../config/services.php');
    }

    private static function makeOneDimension(array $array, string $base = '', string $separator = '.', bool $onlyLast = false, int $depth = 0, int $maxDepth = PHP_INT_MAX, array $result = []): array
    {
        if ($depth <= $maxDepth) {
            foreach ($array as $key => $value) {
                $key = ltrim(string: $base . '.' . $key, characters: '.');

                if (self::isAssociative(array: $value)) {
                    $result = self::makeOneDimension(array: $value, base: $key, separator: $separator, onlyLast: $onlyLast, depth: $depth + 1, maxDepth: $maxDepth, result: $result);

                    if ($onlyLast) {
                        continue;
                    }
                }

                $result[$key] = $value;
            }
        }

        return $result;
    }

    private static function isAssociative(mixed $array): bool
    {
        if (!is_array(value: $array) || [] === $array) {
            return false;
        }

        return !array_is_list(array: $array);
    }
}
