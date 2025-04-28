<?php declare(strict_types = 1);

namespace WhiteDigital\Config\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WhiteDigital\Config\DataFixture\Traits\CommonFixture;
use WhiteDigital\Config\Traits;

use function is_array;

class BaseClassifierFixture extends Fixture
{
    use CommonFixture;
    use Traits\Common;

    public static array $references;
    public static int $i = 0;

    public function __construct(private readonly ?string $classifierEntityClass = null)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadFixtures($manager, []);
    }

    protected function loadFixtures(ObjectManager $manager, array $classifiers): void
    {
        foreach ($classifiers as $value => $classifier) {
            $fixture = (new $this->classifierEntityClass())->setValue($value);

            if (is_array($classifier)) {
                $fixture->setData($classifier['data']);
                $classifier = $classifier['classifier'];
            }

            $fixture->setType($classifier);

            $manager->persist($fixture);
            $manager->flush();

            $this->addReference(__CLASS__ . self::$i, $fixture);
            self::$references[self::class][$classifier->name][] = __CLASS__ . self::$i;
            self::$i++;
        }
    }
}
