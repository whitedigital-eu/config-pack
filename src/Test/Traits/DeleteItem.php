<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test\Traits;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use function sprintf;

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
        self::$client->request(Request::METHOD_DELETE, sprintf('%s/%d', self::$iri, $id));
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
