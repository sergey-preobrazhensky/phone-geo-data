<?php
namespace SergeyPreobrazhensky\PhoneGeoData;

class PhoneGeoDataGetter {
    const RUSSIA = 'Россия';

    /** @var NumberBorder[] */
    private $countryNumberBorders;

    /** @var NumberBorder[][][][] */
    private $regionNumberBorders;

    /** @var array */
    private $regionTimeZones;

    /** @var array */
    private $countryTimeZones;

    /** @var string */
    private $lang;

    /**
     * PhoneGeoDataGetter constructor.
     * @param string $lang
     */
    public function __construct($lang)
    {
        $this->lang = $lang;
    }


    public function parse($phone) {

        $phone = preg_replace('/[^0-9]*/u', '', (string) $phone);

        if ($countryCode = $this->getCountryCode($phone)) {
            list($regionCode, $operator) = $this->getRegionOperator($phone, $countryCode);
        } else {
            list($regionCode, $operator) = ['', ''];
        }

        $timeZone = $this->getTimeZone($countryCode, $regionCode);

        return new PhoneGeoData($countryCode, $regionCode, $timeZone, $operator, $this->lang);
    }

    /**
     * @param $phone
     * @return string
     */
    private function getCountryCode($phone)
    {
        foreach ($this->getCountryNumberBorders() as $countryCode => $borders) {
            foreach ($borders as $borderItem) {
                if ($borderItem->match($phone)) {
                    return $countryCode;
                }
            }
        }

        return '';
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     * @return string
     */
    private function getTimeZone($countryCode, $regionCode)
    {
        if ($countryCode && $regionCode && $timeZone = $this->getRegionTimeZone($countryCode, $regionCode)) {

            return $timeZone;
        } elseif ($countryCode) {

            return $this->getCountryTimeZone($countryCode);
        }

        return '';
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     * @return string
     */
    private function getRegionTimeZone($countryCode, $regionCode)
    {
        if (!isset($this->regionTimeZones[$countryCode])) {
            $this->regionTimeZones[$countryCode] = [];
            $codesFielName = __DIR__.'/res/regionTimeZone/'.strtolower($countryCode).'.csv';
            if (file_exists($codesFielName)) {
                $fp_in = fopen($codesFielName, "rb");
                $zoneByCode = [];
                while ($row = fgetcsv($fp_in, 4096, ',')) {
                    $zoneByCode[$row[0]] = $row[1];
                }
                $this->regionTimeZones[$countryCode] = $zoneByCode;
            }
        }

        if (isset($this->regionTimeZones[$countryCode][$regionCode])) {
            return $this->regionTimeZones[$countryCode][$regionCode];
        } else {
            return '';
        }
    }

    private function getCountryTimeZone($countryCode)
    {
        if ($this->countryTimeZones === null) {
            $fp_in = fopen(__DIR__.'/res/countryTimeZone.csv', "rb");
            $zoneByCode = [];
            while ($row = fgetcsv($fp_in, 4096, ',')) {
                $zoneByCode[$row[0]] = $row[1];
            }
            $this->countryTimeZones = $zoneByCode;
        }

        if (isset($this->countryTimeZones[$countryCode])) {
            return $this->countryTimeZones[$countryCode];
        } else {
            return '';
        }
    }

    /**
     * @return NumberBorder[][]
     */
    private function getCountryNumberBorders()
    {
        if (!$this->countryNumberBorders) {
            $fp = fopen(__DIR__ . '/res/countryNumberBorder.csv', 'r');
            $countryNumberBorders = [];
            while ($data = fgetcsv($fp)) {
                $countryCode = array_shift($data);
                $borders = [];
                foreach ($data as $borderItem) {
                    $border = explode('-', $borderItem);
                    if (!isset($border[1])) {
                        $border[1] = $border[0];
                    }
                    $borders[] = new NumberBorder($border[0], $border[1]);
                }
                $countryNumberBorders[$countryCode] = $borders;
            }
            $this->countryNumberBorders = $countryNumberBorders;
        }

        return $this->countryNumberBorders;
    }

    /**
     * @param $phone
     * @param $countryCode
     * @return string[]
     */
    private function getRegionOperator($phone, $countryCode)
    {
        foreach ($this->getNumberBorders($countryCode) as $regionCode => $operatorBorders) {
            foreach ($operatorBorders as $operator => $borders) {
                foreach ($borders as $borderItem) {
                    if ($borderItem->match($phone)) {
                        return [$regionCode, $operator];
                    }
                }
            }
        }

        return ['',''];
    }

    /**
     * @param $countryCode
     * @return NumberBorder[][][]
     */
    private function getNumberBorders($countryCode)
    {
        if (empty($countryCode)) {
            return [];
        }
        if (!isset($this->regionNumberBorders[$countryCode])) {
            $this->regionNumberBorders[$countryCode] = [];
            $regionBorderFilePath = __DIR__ . '/res/regionBorderNumber/'.strtolower($countryCode).'.csv';
            if (file_exists($regionBorderFilePath)) {
                $fp = fopen($regionBorderFilePath, 'r');
                $regionNumberBorders = [];
                while ($data = fgetcsv($fp)) {
                    $regionCode = array_shift($data);
                    $operator = array_shift($data);
                    $borders = [];
                    foreach ($data as $borderItem) {
                        $border = explode('-', $borderItem);
                        if (!isset($border[1])) {
                            $border[1] = $border[0];
                        }
                        $borders[] = new NumberBorder($border[0], $border[1]);
                    }
                    if (isset($regionNumberBorders[$regionCode][$operator])) {
                        $borders = array_merge($borders, $regionNumberBorders[$regionCode][$operator]);
                    }
                    $regionNumberBorders[$regionCode][$operator] = $borders;
                }
                $this->regionNumberBorders[$countryCode] = $regionNumberBorders;
            }
        }

        return $this->regionNumberBorders[$countryCode];
    }
}