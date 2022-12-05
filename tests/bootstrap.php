<?php declare(strict_types = 1);

use Symfony\Component\Dotenv\Dotenv;

if (file_exists($path = getcwd() . '/../config/bootstrap.php')) {
    require $path;
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(getcwd() . '/.env');
}
