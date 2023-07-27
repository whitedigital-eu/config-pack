<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test\Traits;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use function sprintf;

trait GetItem
{
    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Depends('testPatchItem')]
    public function testGetItem(int $id): int
    {
        return self::getResource(sprintf('%s/%d', self::$iri, $id), isSingle: true, assert: true)->id;
    }
}
