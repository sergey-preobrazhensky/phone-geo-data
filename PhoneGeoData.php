<?php

/**
 *
 */
class PhoneGeoData
{
    /** @var string */
    private $countryCode;

    /** @var string */
    private $regionCode;

    /** @var string */
    private $timeZone;

    /** @var array */
    private $hrCountries;

    /** @var array */
    private $hrRegions;

    /** @var string */
    private $lang;

    /**
     * PhoneGeoData constructor.
     * @param string $country
     * @param string $region
     * @param string $timeZone
     */
    public function __construct($countryCode, $regionCode, $timeZone, $lang)
    {
        $this->countryCode = $countryCode;
        $this->regionCode = $regionCode;
        $this->timeZone = $timeZone;
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getRegionCode()
    {
        return $this->regionCode;
    }


    /**
     * @return string
     */
    public function getCountry()
    {
        $countries = $this->getHrCountries();
        return (isset($countries[$this->countryCode])) ? $countries[$this->countryCode] : '';
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        $regions = $this->getHrRegions();
        return (isset($regions[$this->regionCode])) ? $regions[$this->regionCode] : '';
    }

    /**
     * @return string
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @return array
     */
    private function getHrCountries()
    {
        if (! $this->hrCountries) {
            $fp_in = fopen(__DIR__.'/lang/'.$this->lang.'/country.csv', "rb");
            $hrCountries = [];
            while ($row = fgetcsv($fp_in, 4096, ',')) {
                $hrCountries[$row[0]] = $row[1];
            }
            $this->hrCountries = $hrCountries;
        }

        return $this->hrCountries;
    }

    /**
     * @return array
     */
    private function getHrRegions()
    {
        if (! $this->hrRegions) {
            $fp_in = fopen(__DIR__.'/lang/'.$this->lang.'/region.csv', "rb");
            $hrRegions = [];
            while ($row = fgetcsv($fp_in, 4096, ',')) {
                $hrRegions[$row[0]] = $row[1];
            }
            $this->hrRegions = $hrRegions;
        }

        return $this->hrRegions;
    }
}