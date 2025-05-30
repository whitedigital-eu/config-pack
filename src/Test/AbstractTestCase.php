<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WhiteDigital\Config\Faker;
use WhiteDigital\Config\Traits;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;

use function array_slice;
use function copy;
use function dirname;
use function func_get_args;
use function getcwd;
use function hash;
use function in_array;
use function json_decode;
use function sort;
use function sprintf;
use function sys_get_temp_dir;
use function unlink;

use const SORT_NATURAL;

abstract class AbstractTestCase extends ApiTestCase
{
    use Traits\Common;
    use Traits\FakerTrait;

    protected static ContainerInterface $container;
    protected static HttpClientInterface $client;
    protected static string $iri;
    protected static ?string $email;
    protected static ?string $password;
    protected static ?stdClass $file = null;
    protected static bool $authenticate = true;
    private static bool $init = false;

    public static function setUpBeforeClass(): void
    {
        if (!self::$init) {
            (new Filesystem())->remove([sys_get_temp_dir() . '/' . hash(algo: 'xxh128', data: getcwd()) . '/var/cache']);
            self::$init = true;
        }
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    protected function setUp(): void
    {
        self::$client = static::createClient();
        self::$email = (self::$container = self::getContainer())->getParameter('whitedigital.test.login_email');
        self::$password = (self::$container = self::getContainer())->getParameter('whitedigital.test.login_password');

        if (self::$authenticate) {
            self::authenticate();
        }

        self::setFaker(self::$container->get(Faker::class));
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected static function authenticate(?string $email = null, ?string $password = null): void
    {
        self::$client->request(Request::METHOD_POST, '/api/users/login', ['json' => [
            'email' => $email ?? self::$email,
            'password' => $password ?? self::$password,
        ]]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected static function logout(): void
    {
        self::$client->request(Request::METHOD_GET, '/api/users/logout');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    protected static function get(?string $iri = null, ?int $key = null, ?int $max = null, bool $asIri = true, bool $isSingle = false, bool $assert = false): mixed
    {
        $iri ??= static::$iri;
        $response = json_decode(self::$client->request(Request::METHOD_GET, $iri)->getContent());

        self::assertResponseIsSuccessful();
        if ($assert) {
            self::assertJsonContains(['@id' => $iri]);
        }

        if ($isSingle) {
            return $response;
        }

        $result = $response->{'hydra:member'} ?? [];

        try {
            if (-1 === $key) {
                return $result[self::randomArrayKey($result)];
            }
        } catch (Exception) {
            $key = 0;
        }

        if (null !== $key) {
            return $result[$key] ?? null;
        }

        if (null !== $max) {
            $result = array_slice($result, 0, $max);
        }

        if (!$asIri) {
            return $result;
        }

        $iris = [];
        foreach ($result as $item) {
            $iris[] = $item->{'@id'};
        }
        sort($iris, SORT_NATURAL);

        return $iris;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     *
     * @deprecated Use get() instead
     */
    protected static function getResource(?string $iri = null, ?int $key = null, ?int $max = null, bool $asIri = true, bool $isSingle = false, bool $assert = false): mixed
    {
        return self::get(...func_get_args());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected static function uploadFile(?string $title = null, bool $skip = false)
    {
        if (null !== self::$file && !$skip) {
            return self::$file;
        }

        $templateFileName = dirname(__DIR__, 2) . '/assets/template_upload.gif';
        $fileName = dirname(__DIR__, 2) . '/assets/upload.gif';
        @unlink($fileName);
        copy($templateFileName, $fileName);

        $extra = [
            'files' => [
                'file' => new UploadedFile($fileName, 'upload.gif'),
            ],
        ];

        if (null !== $title) {
            $extra['parameters']['title'] = $title;
            $extra['parameters']['altText'] = $title;
        }

        $response = self::$client->request(Request::METHOD_POST, '/api/storage_items', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => $extra,
        ]);

        self::assertResponseIsSuccessful();

        return self::$file = json_decode($response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected static function assertData(array $data, array $excludes = []): void
    {
        $excludes = [] === $excludes ? ['position', 'newPassword', 'newPasswordRepeat'] : $excludes;

        foreach ($data as $key => $item) {
            if ($item instanceof BackedEnum) {
                $item = $item->value;
            }

            if (null === $item || in_array($key, $excludes, true)) {
                continue;
            }

            self::assertJsonContains([$key => $item]);
        }
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    protected static function post(array $data, ?string $iri = null, int $code = Response::HTTP_CREATED, bool $assert = true, bool $return = true, array $excludes = []): ?stdClass
    {
        $response = self::$client->request(Request::METHOD_POST, $iri ?? static::$iri, ['json' => $data]);

        self::assertResponseStatusCodeSame($code);

        if ($assert) {
            self::assertData($data, $excludes);
        }

        return $return ? json_decode($response->getContent()) : null;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    protected static function patch(mixed $id, array $data, ?string $iri = null, int $code = Response::HTTP_OK, bool $assert = true, bool $return = true, array $excludes = []): ?stdClass
    {
        $response = self::$client->request(Request::METHOD_PATCH, sprintf('%s/%s', $iri ?? static::$iri, $id), [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame($code);

        if ($assert) {
            self::assertData($data, $excludes);
        }

        return $return ? json_decode($response->getContent()) : null;
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected static function delete(mixed $id, ?string $iri = null, int $code = Response::HTTP_NO_CONTENT): void
    {
        self::$client->request(Request::METHOD_DELETE, sprintf('%s/%s', $iri ?? static::$iri, $id));

        self::assertResponseStatusCodeSame($code);
    }

    protected static function datetimeFormat(UTCDateTimeImmutable|DateTimeImmutable|null $dt = null): string
    {
        return ($dt ?? new UTCDateTimeImmutable())->format(DateTimeInterface::RFC3339);
    }

    protected static function dateFormat(UTCDateTimeImmutable|DateTimeImmutable|null $dt = null): string
    {
        return self::datetimeFormat(($dt ?? new UTCDateTimeImmutable())->setTime(0, 0));
    }
}
