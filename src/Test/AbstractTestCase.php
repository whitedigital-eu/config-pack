<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Test;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Constants\Enum\Definition;
use BackedEnum;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
use WhiteDigital\Config\Traits\FakerTrait;

use function copy;
use function dirname;
use function json_decode;
use function sprintf;
use function unlink;

abstract class AbstractTestCase extends ApiTestCase
{
    use FakerTrait;

    protected static ContainerInterface $container;
    protected static HttpClientInterface $client;
    protected static string $iri;
    protected static ?string $email;
    protected static ?stdClass $file = null;
    protected static bool $authenticate = true;

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function setUp(): void
    {
        self::$client = static::createClient();
        self::$email = (self::$container = self::getContainer())->getParameter('whitedigital.test.login_email');

        if (self::$authenticate) {
            self::authenticate();
        }

        self::setFaker(new Faker(bag: self::$container->get(ParameterBagInterface::class)));
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected static function authenticate(?string $email = null, string $password = 'secret'): void
    {
        self::$client->request(Request::METHOD_POST, '/api/users/login', ['json' => [
            'email' => $email ?? self::$email,
            'password' => $password,
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
     */
    protected function getResource(string $iri, ?int $key = null): array|object|null
    {
        $result = json_decode(self::$client->request(Request::METHOD_GET, $iri)->getContent())->{'hydra:member'};

        if (null !== $key) {
            return $result[$key];
        }

        return $result;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function uploadFile(?string $title = null, bool $skip = false)
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
        }

        $response = self::$client->request(Request::METHOD_POST, '/api/storage_items', [
            'headers' => [
                'Content-Type' => Definition::TYPE_MULTIPART_FORM->value,
            ],
            'extra' => $extra,
        ]);

        return self::$file = json_decode($response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function assertData(array $data): void
    {
        foreach ($data as $key => $item) {
            if ($item instanceof BackedEnum) {
                $item = $item->value;
            }

            if ('position' === $key || null === $item) {
                continue;
            }

            self::assertJsonContains([$key => $item]);
        }
    }

    protected function post(array $data, ?string $iri = null, int $code = Response::HTTP_CREATED): stdClass
    {
        $response = self::$client->request(Request::METHOD_POST, $iri ?? static::$iri, ['json' => $data]);

        self::assertResponseStatusCodeSame($code);
        self::assertData($data);

        return json_decode($response->getContent());
    }

    protected function patch(mixed $id, array $data, ?string $iri = null, int $code = Response::HTTP_OK): stdClass
    {
        $response = self::$client->request(Request::METHOD_PATCH, sprintf('%s/%s', $iri ?? static::$iri, $id), [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseStatusCodeSame($code);
        self::assertData($data);

        return json_decode($response->getContent());
    }
}
