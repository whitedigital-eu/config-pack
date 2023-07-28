<?php declare(strict_types = 1);

namespace WhiteDigital\Config\Traits;

use DateTime;
use DateTimeZone;
use LogicException;
use WhiteDigital\Config\Faker;
use WhiteDigital\EntityResourceMapper\UTCDateTimeImmutable;

use function method_exists;
use function sprintf;

/**
 * @method DateTime        creditCardExpirationDate($valid = true)
 * @method DateTime        dateTimeAD($max = 'now', $timezone = null)
 * @method DateTime        dateTimeBetween($startDate = '-30 years', $endDate = 'now', $timezone = null)
 * @method DateTime        dateTimeInInterval($date = '-30 years', $interval = '+5 days', $timezone = null)
 * @method DateTime        dateTimeThisCentury($max = 'now', $timezone = null)
 * @method DateTime        dateTimeThisDecade($max = 'now', $timezone = null)
 * @method DateTime        dateTimeThisMonth($max = 'now', $timezone = null)
 * @method DateTime        dateTimeThisYear($max = 'now', $timezone = null)
 * @method array           creditCardDetails($valid = true)
 * @method array           randomElements($array = ['a', 'b', 'c'], $count = 1, $allowDuplicates = false)
 * @method array           rgbColorAsArray()
 * @method array           shuffleArray($array = [])
 * @method array|string    paragraphs($nb = 3, $asText = false)
 * @method array|string    sentences($nb = 3, $asText = false)
 * @method array|string    shuffle($arg = '')
 * @method bool            boolean($chanceOfGettingTrue = 50)
 * @method float           latitude($min = -90, $max = 90)
 * @method float           longitude($min = -180, $max = 180)
 * @method float           randomFloat($nbMaxDecimals = null, $min = 0, $max = null)
 * @method float[]         localCoordinates()
 * @method int             biasedNumberBetween($min = 0, $max = 100, $function = 'sqrt')
 * @method int             imei()
 * @method int             numberBetween($int1 = 0, $int2 = 2147483647)
 * @method int             randomDigit()
 * @method int             randomDigitNot($except)
 * @method int             randomDigitNotNull()
 * @method int             randomDigitNotZero()
 * @method int             randomNumber($nbDigits = null, $strict = false)
 * @method int             unixTime($max = 'now')
 * @method int|string|null randomKey($array = [])
 * @method mixed           passthrough($value)
 * @method mixed           randomElement($array = ['a', 'b', 'c'])
 * @method string          address()
 * @method string          amPm($max = 'now')
 * @method string          asciify($string = ' * * * *')
 * @method string          bloodGroup()
 * @method string          bothify($string = '## ??')
 * @method string          buildingNumber()
 * @method string          century()
 * @method string          city()
 * @method string          citySuffix()
 * @method string          colorName()
 * @method string          company()
 * @method string          companyEmail()
 * @method string          companySuffix()
 * @method string          country()
 * @method string          countryCode()
 * @method string          countryISOAlpha3()
 * @method string          creditCardExpirationDateString($valid = true, $expirationDateFormat = null)
 * @method string          creditCardNumber($type = null, $formatted = false, $separator = '-')
 * @method string          creditCardType()
 * @method string          currencyCode()
 * @method string          date($format = 'Y-m-d', $max = 'now')
 * @method string          dayOfMonth($max = 'now')
 * @method string          dayOfWeek($max = 'now')
 * @method string          domainName()
 * @method string          domainWord()
 * @method string          ean13()
 * @method string          ean8()
 * @method string          email()
 * @method string          emoji()
 * @method string          fileExtension()
 * @method string          firstName($gender = null)
 * @method string          firstNameFemale()
 * @method string          firstNameMale()
 * @method string          freeEmail()
 * @method string          freeEmailDomain()
 * @method string          hexColor()
 * @method string          iban($countryCode = null, $prefix = '', $length = null)
 * @method string          ipv4()
 * @method string          ipv6()
 * @method string          isbn10()
 * @method string          isbn13()
 * @method string          iso8601($max = 'now')
 * @method string          jobTitle()
 * @method string          languageCode()
 * @method string          lastName()
 * @method string          lexify($string = '????')
 * @method string          localIpv4()
 * @method string          locale()
 * @method string          macAddress()
 * @method string          mimeType()
 * @method string          month($max = 'now')
 * @method string          monthName($max = 'now')
 * @method string          name($gender = null)
 * @method string          numerify($string = '###')
 * @method string          paragraph($nbSentences = 3, $variableNbSentences = true)
 * @method string          password($minLength = 6, $maxLength = 20)
 * @method string          phoneNumber()
 * @method string          postcode()
 * @method string          randomAscii()
 * @method string          randomHtml($maxDepth = 4, $maxWidth = 4)
 * @method string          randomLetter()
 * @method string          realText($maxNbChars = 200, $indexSize = 2)
 * @method string          regexify($regex = '')
 * @method string          rgbColor()
 * @method string          rgbCssColor()
 * @method string          rgbaCssColor()
 * @method string          safeColorName()
 * @method string          safeEmail()
 * @method string          safeEmailDomain()
 * @method string          safeHexColor()
 * @method string          semver(bool $preRelease = false, bool $build = false)
 * @method string          sentence($nbWords = 6, $variableNbWords = true)
 * @method string          shuffleString($string = '', $encoding = 'UTF-8')
 * @method string          slug($nbWords = 6, $variableNbWords = true)
 * @method string          streetAddress()
 * @method string          streetName()
 * @method string          streetSuffix()
 * @method string          swiftBicNumber()
 * @method string          text($maxNbChars = 200)
 * @method string          time($format = 'H:i:s', $max = 'now')
 * @method string          timezone($countryCode = null)
 * @method string          title($gender = null)
 * @method string          titleFemale()
 * @method string          titleMale()
 * @method string          tld()
 * @method string          url()
 * @method string          userAgent()
 * @method string          userName()
 * @method string          uuid()
 * @method string          year($max = 'now')
 *
 * This trait simplifies calling of Faker generator by passing through calls to faker factory itself.
 * So, usage is simply self::text() instead of instantiating new factory and calling $this->factory->text()
 *
 * If it is necessary to override any factory method, override it here and remove method annotation from above
 */
trait FakerTrait
{
    protected static ?Faker $faker = null;

    public static function __callStatic(string $name, array $arguments)
    {
        if (null === self::$faker) {
            throw new LogicException(sprintf('%s not set, call setFaker(new Faker()) before using this', Faker::class));
        }

        if (!method_exists(static::class, $name)) {
            return self::$faker::f()->{$name}(...$arguments);
        }

        return self::{$name}(...$arguments);
    }

    public static function setFaker(Faker $faker): void
    {
        self::$faker = $faker;
    }

    public static function words(int $words = 3): string
    {
        return self::$faker::f()->words($words, true);
    }

    public static function randomDecimal(int $decimalPlaces = 2, int $min = 0, int $max = 100): string
    {
        return sprintf("%.{$decimalPlaces}f", self::$faker::f()->randomFloat($decimalPlaces, $min, $max));
    }

    public static function word(): string
    {
        return self::words(1);
    }

    public function dateTime(mixed $max = 'now', ?DateTimeZone $timezone = null): UTCDateTimeImmutable
    {
        return UTCDateTimeImmutable::createFromInterface(self::$faker::f()->dateTime($max, $timezone));
    }
}
