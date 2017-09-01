<?php
use PHPUnit\Framework\TestCase;
use SergeyPreobrazhensky\PhoneGeoData\PhoneGeoDataGetter;

include_once __DIR__.'/../src/autoload.php';
/**
 *
 */
class GeoDataTest extends TestCase
{

    public function testGeoData()
    {
        $phoneGeoDataParser = new PhoneGeoDataGetter('ru');
        $phoneData = $phoneGeoDataParser->parse('+79190010001');
        self::assertEquals('Россия', $phoneData->getCountry());
        self::assertEquals('Владимирская область', $phoneData->getRegion());
        self::assertEquals('+3:00', $phoneData->getTimeZone());
    }

}
