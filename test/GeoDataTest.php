<?php
use PHPUnit\Framework\TestCase;
include_once __DIR__.'/../PhoneGeoDataGetter.php';
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
