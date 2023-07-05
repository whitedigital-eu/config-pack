<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test\Traits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

trait GetItemNotFound
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetItemNotFound(): void
    {
        self::$client->request(Request::METHOD_GET, sprintf('%s/99999', self::$iri));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
