<?php declare(strict_types = 1);

namespace WhiteDigital\Config\DataFixture;

use BackedEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Random\Randomizer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use WhiteDigital\Config\Faker;
use WhiteDigital\Config\Traits\FakerTrait;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\SiteTree\DataFixture\SiteTreeFixture;

abstract class AbstractFixture extends Fixture implements DependentFixtureInterface
{
    use FakerTrait;

    public static array $references;

    public function __construct(ParameterBagInterface $bag)
    {
        self::setFaker(new Faker(bag: $bag));
    }

    public function getDependencies(): array
    {
        return [
            BaseClassifierFixture::class,
        ];
    }

    public function reference(BaseEntity $fixture, ?int $key = null): void
    {
        $name = $fixture::class;
        if (null !== $key) {
            $name .= $key;
        }

        $this->addReference($name, $fixture);
        self::$references[$fixture::class][] = $name;
    }

    protected function randomArrayKey(array $array): mixed
    {
        return (new Randomizer())->pickArrayKeys(array: $array, num: 1)[0];
    }

    /**
     * @return BaseEntity|null
     */
    protected function getEntity(string $fixture, ?int $i = null): ?object
    {
        $key = (static::$references[$fixture][$this->randomArrayKey(static::$references[$fixture])] ?? $i) ?? null;

        return match ($key) {
            null => null,
            default => $this->getReference($key),
        };
    }

    protected function getNode(string $type): object
    {
        return $this->getReference('node' . $type . $this->randomArrayKey(SiteTreeFixture::$references[$type]));
    }

    protected function getImage(): object
    {
        return $this->getReference('wdFile_image');
    }

    protected function getFile(): object
    {
        return $this->getReference('wdFile_text');
    }

    /**
     * @return BackedEnum
     */
    protected function getClassifier(BackedEnum $type): object
    {
        return $this->getReference(($values = BaseClassifierFixture::$references[BaseClassifierFixture::class][$type->name])[$this->randomArrayKey($values)]);
    }
}
