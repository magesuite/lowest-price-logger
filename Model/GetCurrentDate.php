<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Model;

class GetCurrentDate
{
    public function execute(): string
    {
        return date('Y-m-d');
    }
}
