<?php declare(strict_types = 1);

namespace WhiteDigital\Config;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

use function getcwd;
use function hash;
use function sys_get_temp_dir;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return getcwd();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' . hash(algo: 'xxh128', data: getcwd()) . '/var/cache';
    }
}
