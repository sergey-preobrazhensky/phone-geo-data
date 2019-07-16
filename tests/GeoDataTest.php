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
    public function testGeoData($phone, $country, $region, $timeZone, $operator)
    {
        $phoneGeoDataParser = new PhoneGeoDataGetter('ru');
        $phoneData = $phoneGeoDataParser->parse($phone);

        self::assertEquals($country, $phoneData->getCountry());
        self::assertEquals($region, $phoneData->getRegion());
        self::assertEquals($timeZone, $phoneData->getTimeZone());
        self::assertEquals($operator, $phoneData->getOperator());
    }

    public function testGeoDataProvider(){
        return [
            ['+79190010001', 'Россия', 'Владимирская область', '+3:00','ПАО "Мобильные ТелеСистемы"'],
            ['+7 919 957-58-12', 'Россия', 'Тюменская область', '+5:00','ПАО "Мобильные ТелеСистемы"'],
            ['7 4922 33-15-43', 'Россия', 'Владимирская область', '+3:00','Городской телефон'],
            ['+7 499 398-10-11', 'Россия', 'Москва', '+3:00','Городской телефон'],
        ];
    }
}
