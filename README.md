# config-pack

> **WARNING**  
When upgrading from **v1** to **v2**, it is better to run `composer update` without scripts and plugins,
as v2 does not require `symfony/phpunit-bridge` anymore and uninstalling it may change or delete
test related files, like `.env.test` or `phpunit.xml.dist`:  
> 
>`composer update -nW --no-scripts --no-plugins whitedigital-eu/config-pack`
---

Installation
---

```shell
composer require --dev whitedigital-eu/config-pack
```

Usage
---

### PhpCsFixer:
```shell
vendor/bin/php-cs-fixer --config=vendor/whitedigital-eu/config-pack/.php-cs-fixer.php fix
```
In PhpStorm:  
* point to same config file in settings of PHP CS Fixer
* check "allow risky rules" checkbox
* choose custom ruleset
* point to php-cs-fixer binary

### PhpUnit:
```shell
vendor/bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist tests
```
*or if tests/bootstrap.php differs from one in whitedigital-eu/config-pack:*
```shell
vendor/bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist --bootstrap=tests/bootstrap.php tests
```

### PhpStan
```shell
vendor/bin/phpstan --configuration=vendor/whitedigital-eu/config-pack/phpstan.neon.dist analyse src
```
In PhpStorm:
* point to same config file in settings of PHPStan
* point to your project's autoload, usually `vendor/autoload.php`
* point to phpstan binary

Functional usage (since v2.2)
---

> ConfigPack only works in dev and test environments, make sure you set this config only for dev and test.  
> 
> `/** config/bundles.php */`  
> `WhiteDigital\Config\ConfigPack::class => ['dev' => true, 'test' => true]`

### Faker
This package now comes with `fakerphp/faker` as a dependency thus making usage of fake data
generation easier. If used without `AbstractTestCase` or `AbstractFixture`, you can easily
use faker using `FakerTrait`.

```php
use WhiteDigital\Config\Faker;
use WhiteDigital\Config\Traits\FakerTrait;

class Test 
{
    use FakerTrait;
    
    public function __construct(Faker $faker) 
    {
        self::setFaker($faker);
    }
    
    public function test(): string 
    {
        return self::text();
    }
}
```
As `Faker` is used with this library's configuration, `setFaker` function call is mandatory before using any faker functionality.  
`Faker` is autowired automatically. `FakerTrait` primarily passes through functions from faker factory, most useful methods
are defined as methods in annotation so IDE can see them.  
By default Faker uses `lv_LV` as locale and `2022` as seed. If different values are needed, you can configure them:  
```yaml
config_pack:
    seed: 123
    locale: en_US
```
```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\ConfigPackConfig;

return static function (ConfigPackConfig $config, ContainerConfigurator $container): void {
    if (in_array($container->env(), ['dev', 'test', ], true)) {
        $config
            ->seed(123)
            ->locale('en_US');
    }
};
```

### AbstractTestCase
As most of tests for testing api-platform apis are somewhat similar, `AbstractTestCase` defines a lot of useful functions
to make testing apis a lot easier.  
In overall, with using `AbstractTestCase` usual api test should look like this:  
```php
use WhiteDigital\Config\Test\AbstractTestCase;
use WhiteDigital\Config\Test\Traits;

class CustomerTest extends AbstractTestCase 
{
    use Traits\DeleteItem;
    use Traits\GetCollection;
    use Traits\GetItem;
    use Traits\GetItemNotFound;

    protected static string $iri = '/api/customers';
    
    public function testPostItem(): int
    {
        return self::post([
            'key1' => self::words(),
            'key2' => self::text(),
            'key3' => self::randomDecimal(),
        ])->id;
    }

    #[Depends('testPostItem')]
    public function testPatchItem(int $id): int
    {
        return self::patch($id, [
            'key3' => self::randomDigit(),
        ])->id;
    }
}
```
This tests:  
`GET`: /api/customers/{id}  
`GET`: /api/customer/{not_existing_id}  
`GET`: /api/customers  
`POST`: /api/customers  
`PATCH`: /api/customers/{id}  
`DELETE`: /api/customers/{id}

Tests by default uses authentication, you can configure `login_email` and `login_password` in `config_pack` configuration:  
```yaml
config_pack:
    login_email: test@test.com
    login_password: test
```
```php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\ConfigPackConfig;

return static function (ConfigPackConfig $config, ContainerConfigurator $container): void {
    if (in_array($container->env(), ['dev', 'test', ], true)) {
        $config
            ->loginEmail('test@test.com')
            ->loginPassword('test');
    }
};
```

### AbstractFixture
Similarly to `AbstractTestCase`, `AbstractFixture` defines base functions for doctrine fixtures.
It also uses `FakerTrait` to simplify data generation.  
```php
use Doctrine\Persistence\ObjectManager;
use WhiteDigital\Config\DataFixture\AbstractFixture;

class OneFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $fixture = (new One())
            ->setKey1(self::words())
            ->setKey2(self::rnadomDecimal());
            
        $manager->persist($fixture);
        $manager->flush();
        
        $this->reference($fixture);
    }
}

class TwoFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        for($i = 0; $i < 10; $i++){
            $fixture = (new Two())
                ->setKey1(self::words())
                ->setKey2($this->getEntity(One::class));
                
            $manager->persist($fixture);
            $manager->flush();
            
            $this->reference($fixture, $i);
        }
    }
    
    public function getDependencies() : array
    {
        $dependencies = parent::getDependencies();
        $dependencies[] = OneFixture::class;
        
        return $dependencies;
    }
}
```
`AbstractFixture` defines some useful functions:  

`getEntity` -> `getEntity(Entity::class)`: returns entity object for given class if fixture, where this class
is created, is added to dependencies and dependant fixture defines reference.

`getImage` and `getFile`: coupled with `whitedigital-eu/storage-item-resource` library, these functions return 
entity for `StorageItem` based on need -> image or text file.  

`reference`: set current fixture to references to be used elsewhere where this fixture is a dependency  

`getClassifier`: explicitly returns Classifier if project contains any classifiers and
project Classifier fixture extends `BaseClassifierFixture`.

### BaseClassifierFixture
If your project have any classifier logic, you can extend `BaseClassifierFixture` and it will make classifier creation easier.  
You need to have an entity with at least this structure to make this work:  
```php
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class Classifier
{
    private ?int $id = null;
    private ?string $value = null;
    private ?array $data = [];
    private ?ClassifierType $type = null;
}
```
And you need to have a Backed enum (here called `ClassifierType`).  
Example of `ClassifierType` is something like this:
```php
enum ClassifierType: string
{
    case ONE = 'ONE';
    case TWO = 'TWO';
}
```
With `BaseClassifierFixture` classifiers can be created using two ways:  
simple:
```php
use WhiteDigital\Config\DataFixture\BaseClassifierFixture;

class ClassifierFixture extends BaseClassifierFixture
{
    public function __construct() 
    {
        parent::__construct(Classifier::class);
    }
    
    public function load(ObjectManager $manager): void
    {
        $classifiers = [
            'ONE' => ClassifierType::ONE,
            'TWO' => ClassifierType::TWO;
        ];

        $this->loadFixtures($manager, $classifiers);
    }
}
```
with data:
```php
use WhiteDigital\Config\DataFixture\BaseClassifierFixture;

class ClassifierFixture extends BaseClassifierFixture
{
    public function __construct() 
    {
        parent::__construct(Classifier::class);
    }
    
    public function load(ObjectManager $manager): void
    {
        $classifiers = [
            ['classifier' => ClassifierType::ONE, 'data' => ['data' => 'one']],
            ['classifier' => ClassifierType::TWO, 'data' => ['data' => 'two']],
        ];

        $this->loadFixtures($manager, $classifiers);
    }
}
```
dependant:
```php
use WhiteDigital\Config\DataFixture\BaseClassifierFixture;

class ClassifierFixture extends BaseClassifierFixture
{
    public function __construct() 
    {
        parent::__construct(Classifier::class);
    }
    
    public function load(ObjectManager $manager): void
    {
        $classifiers = [
            'ONE' => ClassifierType::ONE,
        ];

        $this->loadFixtures($manager, $classifiers);

        $dependants = [
            'TWO' => ['classifier' => ClassifierType::TWO, 'data' => ['one_iri' => '/api/classifiers/' . $this->getClassifier(ClassifierType::ONE)->getId(), ], ],
        ];

        $this->loadFixtures($manager, $dependants);
    }
}
```
