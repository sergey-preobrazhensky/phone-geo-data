<?php

// Update region data base for russian regions
class RuRegionUpdater implements IUpdater {
    const DATA_URL = 'https://rossvyaz.gov.ru/upload/gallery/315/63315_8f1c5a1d7c4fd1e0431c427ed3961439e962b01e.csv';
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
            file_get_contents(self::DATA_URL, false, stream_context_create(
                [
                    "ssl"=> [
                        "verify_peer" => false,
                        "verify_peer_name" => false,
                    ]
                ]
            ))
        );

        $result = [];
        $bordersOut = fopen(self::REGION_BORDERS_PATH, "w");
        $handle = fopen($tmpfname, "r");
        fgetcsv($handle, null, ';');
        while ($row = fgetcsv($handle, null, ';')) {
            if (count($row) <= 1) {
                continue;
            }
            list($def, $from, $to,,$operator,$regionHrName) = array_map('trim', $row);
            if ($this->needIgnore($regionHrName)) {
                continue;
            }
            $regionCode = $this->getRegionCode($regionHrName);
            $result[$regionCode][$operator][] = self::RUSSIA_CODE.$def.$from.'-'.self::RUSSIA_CODE.$def.$to;
        }
        ksort($result);
        foreach ($result as $regionCode => $operatorBorders) {
            foreach ($operatorBorders as $operator => $borders) {
                array_unshift($borders,$operator);
                array_unshift($borders,$regionCode);
                fputcsv($bordersOut, $borders);
            }
        }
        fclose($bordersOut);

        $this->addStationCodes();
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

        $regionCodes = $this->getRegionCodesByHrName();
        if (empty($regionCodes[$clearRegionName])) {
            throw new Exception('Unknown region name "'.$regionHrName.'"');
        }

        return $regionCodes[$clearRegionName];
    }

    private function clearName($name)
    {
        $name = mb_strtolower($name);
        $chunks = explode('|', $name);
        $name = array_pop($chunks);
        $chunks = explode('*', $name);
        $name = array_shift($chunks);
        $name = preg_replace('/обл\.|область|край|республика|г\.|автономный округ|ао/u', '', $name);
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

        $handle = fopen(__DIR__.'/../../../res/regionBorderNumber/ru.csv', "a");
        foreach ($standCodesReg as $regionCode => $row) {
            array_unshift($row,'Городской телефон');
            array_unshift($row,$regionCode);
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}