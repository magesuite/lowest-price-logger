<?php

namespace MageSuite\LowestPriceLogger\Model;

class GetCurrentDate
{
    public function execute()
    {
        return date('Y-m-d');
    }
}
