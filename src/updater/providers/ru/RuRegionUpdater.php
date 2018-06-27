<?php

// Update region data base for russian regions
class RuRegionUpdater implements IUpdater {
    const DATA_URL = 'https://www.rossvyaz.ru/docs/articles/Kody_DEF-9kh.csv';
    const REGION_CODES_PATH = __DIR__.'/../../../lang/ru/region.csv';
    const REGION_ALIASES_PATH = __DIR__.'/regionAliases.csv';
    const REGION_BORDERS_PATH = __DIR__.'/../../../res/regionBorderNumber/ru.csv';
    const RUSSIA_CODE = '7';

    private $regionCodes;
    private $regionAliases;

    public function update()
    {
        $tmpfname = tempnam("/tmp", "defCodes");
        file_put_contents(
            $tmpfname,
            iconv('CP1251', 'UTF-8',
                file_get_contents(self::DATA_URL)
            )
        );

        $result = [];
        $bordersOut = fopen(self::REGION_BORDERS_PATH, "w");
        $handle = fopen($tmpfname, "r");
        fgetcsv($handle, null, ';');
        while ($row = fgetcsv($handle, null, ';')) {
            if (count($row) <= 1) {
                continue;
            }
            list($def, $from, $to,,,$regionHrName) = array_map('trim', $row);
            if ($this->needIgnore($regionHrName)) {
                continue;
            }
            $regionCode = $this->getRegionCode($regionHrName);
            $result[$regionCode][] = self::RUSSIA_CODE.$def.$from.'-'.self::RUSSIA_CODE.$def.$to;
        }
        ksort($result);
        foreach ($result as $regionCode => $borders) {
            array_unshift($borders,$regionCode);
            fputcsv($bordersOut, $borders);
        }
        fclose($bordersOut);
    }

    /**
     * Get associative array $regionName => $code
     *
     * @return array
     */
    private function getRegionCodesByHrName() {
        if (empty($this->regionCodes)) {
            $handle = fopen(self::REGION_CODES_PATH, "r");
            $codes = [];
            while ($row = fgetcsv($handle)) {
                $codes[$this->clearName($row[1])] = $row[0];
            }

            $this->regionCodes = $codes;
        }

        return $this->regionCodes;
    }

    /**
     * Get associative array $regionAlias => $regionMainHrName
     *
     * @return array
     */
    private function getRegionAliases() {
        if (empty($this->regionAliases)) {
            $handle = fopen(self::REGION_ALIASES_PATH, "r");
            $aliases = [];
            while ($row = fgetcsv($handle)) {
                $mainHrName = $this->clearName(array_shift($row));
                foreach ($row as $alias) {
                    $aliases[$alias] = $mainHrName;
                }
            }

            $this->regionAliases = $aliases;
        }

        return $this->regionAliases;
    }

    /**
     * @param string $regionHrName
     * @return string
     * @throws Exception
     */
    private function getRegionCode($regionHrName)
    {
        $regionAliases = $this->getRegionAliases();
        if (!empty($regionAliases[$regionHrName])) {
            $regionHrName = $regionAliases[$regionHrName];
        }

        $clearRegionName = $this->clearName($regionHrName);

        $redionCodes = $this->getRegionCodesByHrName();
        if (empty($redionCodes[$clearRegionName])) {
            throw new Exception('Unknown region name "'.$regionHrName.'"');
        }

        return $redionCodes[$clearRegionName];
    }

    private function clearName($name)
    {
        $name = mb_strtolower($name);
        $chunks = explode('|', $name);
        $name = array_pop($chunks);
        $name = preg_replace('/обл\.|область|край|республика|г\.|автономный округ| ао/u', '', $name);
        return preg_replace('/[^а-я]/u', '', $name);
    }

    /**
     * @param string $regionName
     * @return bool
     */
    private function needIgnore($regionName) {
        return $regionName === 'Российская Федерация';
    }


    private function addStationCodes() {
        $standCodes = [];
        $handle = fopen(__DIR__.'/station_codes.csv', "r");
        while (($data = fgetcsv($handle, 1000000, ";")) !== FALSE) {
            $newCodes = array_map(function($code) {
                return '7'.$code  . '-' . '7'.$code;
            }, explode(',', $data[0]));

            $standCodes[$data[1]] = $newCodes;
        }
        fclose($handle);


        $regions = [];
        $handle = fopen(__DIR__.'/../../../lang/ru/region.csv', "r");
        while (($data = fgetcsv($handle, 1000000, ",")) !== FALSE) {
            $regions[$data[1]] = $data[0];
        }
        fclose($handle);

        $standCodesReg = [];
        foreach ($standCodes as $region => $codes) {
            $standCodesReg[$regions[$region]] = $codes;
        }

        $codeValues = [];
        $handle = fopen(__DIR__.'/../../../res/regionBorderNumber/ru.csv', "r");
        while (($data = fgetcsv($handle, 1000000, ",")) !== FALSE) {
            $codeValues[$data[0]] = $data;
        }
        fclose($handle);

        foreach ($standCodesReg as $regionCode => $codes) {
            $codeValues[$regionCode] = array_merge($codeValues[$regionCode], $codes);
        }

        $handle = fopen(__DIR__.'/../../../res/regionBorderNumber/ru.csv', "w");
        foreach ($codeValues as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}