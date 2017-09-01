<?php
namespace SergeyPreobrazhensky\PhoneGeoData;
/**
 *
 */
class NumberBorder
{
    /** @var  int */
    private $min;

    /** @var  int */
    private $max;

    /**
     * NumberBorder constructor.
     * @param int $min
     * @param int $max
     */
    public function __construct($min, $max)
    {
        $this->min = (int) $min;
        $this->max = (int) $max;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public function match($phone)
    {
        $minChunk = (int)substr($phone, 0, strlen((string)$this->min));
        $maxChunk = (int)substr($phone, 0, strlen((string)$this->max));

        return ($minChunk >= $this->min && $maxChunk <= $this->max);
    }
}