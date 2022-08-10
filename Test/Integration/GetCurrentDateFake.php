<?php

namespace MageSuite\LowestPriceLogger\Test\Integration;

class GetCurrentDateFake extends \MageSuite\LowestPriceLogger\Model\GetCurrentDate
{
    protected $value;

    public function execute()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
