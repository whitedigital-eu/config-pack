<?php declare(strict_types = 1);

namespace WhiteDigital\Config\DataFixture;

use Composer\InstalledVersions;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use LogicException;
use WhiteDigital\Config\DataFixture\Traits\CommonFixture;
use WhiteDigital\Config\Faker;
use WhiteDigital\Config\Traits;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\SiteTree\DataFixture\SiteTreeFixture;
use WhiteDigital\SiteTree\Entity\SiteTree;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

abstract class AbstractFixture extends Fixture implements DependentFixtureInterface
{
    use CommonFixture;
    use Traits\Common;
    use Traits\FakerTrait;

    public static array $references;

    public function __construct(Faker $faker)
    {
        self::setFaker($faker);
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

    /**
     * @return BaseEntity|null
     */
    protected function getEntity(string $fixture, ?int $i = null): ?object
    {
        $key = (static::$references[$fixture][$i ?? self::randomArrayKey(static::$references[$fixture])] ?? null) ?? null;

        return match ($key) {
            null => null,
            default => $this->getReference($key, $fixture),
        };
    }

    /**
     * @return BaseEntity[]
     */
    protected function getEntityReferences(string $fixture): array
    {
        return static::$references[$fixture] ?? [];
    }

    protected function getNode(string $type): object
    {
        if (!InstalledVersions::isInstalled('whitedigital-eu/site-tree')) {
            throw new LogicException('SiteTree is missing. Try running "composer require whitedigital-eu/site-tree".');
        }

        return $this->getReference('node' . $type . self::randomArrayKey(SiteTreeFixture::$references[$type]), SiteTree::class);
    }

    protected function getImage(): object
    {
        return $this->getReference('wdFile_image', StorageItem::class);
    }

    protected function getFile(): object
    {
        return $this->getReference('wdFile_text', StorageItem::class);
    }
}
