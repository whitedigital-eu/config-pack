<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test\Traits;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

trait DeleteItem
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Depends('testGetItem')]
    public function testDeleteItem(int $id): void
    {
        self::delete($id);
    }
}
