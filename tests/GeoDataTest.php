<?php
use PHPUnit\Framework\TestCase;
use SergeyPreobrazhensky\PhoneGeoData\PhoneGeoDataGetter;

include_once __DIR__.'/../src/autoload.php';
/**
 *
 */
class GeoDataTest extends TestCase
{
    /**
     * @dataProvider testGeoDataProvider
     */
    public function testGeoData($phone, $country, $region, $timeZone)
    {
        $phoneGeoDataParser = new PhoneGeoDataGetter('ru');
        $phoneData = $phoneGeoDataParser->parse($phone);

        self::assertEquals($country, $phoneData->getCountry());
        self::assertEquals($region, $phoneData->getRegion());
        self::assertEquals($timeZone, $phoneData->getTimeZone());
    }

    public function testGeoDataProvider(){
        return [
            ['+79190010001', 'Россия', 'Владимирская область', '+3:00'],
            ['+7 919 957-58-12', 'Россия', 'Тюменская область', '+5:00'],
            ['7 4922 33-15-43', 'Россия', 'Владимирская область', '+3:00'],
        ];
    }
}
