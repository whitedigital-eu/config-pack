<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test\Traits;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use function json_decode;

trait GetCollection
{
    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCollection(): array
    {
        $response = self::$client->request(Request::METHOD_GET, self::$iri);

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['@id' => self::$iri]);

        return json_decode($response->getContent(), true)['hydra:member'] ?? [];
    }
}
