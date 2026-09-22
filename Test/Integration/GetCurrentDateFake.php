<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Test\Integration;

class GetCurrentDateFake extends \MageSuite\LowestPriceLogger\Model\GetCurrentDate
{
    protected string $value;

    public function execute(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
