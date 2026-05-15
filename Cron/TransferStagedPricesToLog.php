<?php

declare(strict_types=1);

namespace MageSuite\LowestPriceLogger\Cron;

class TransferStagedPricesToLog
{
    public function __construct(
        protected \MageSuite\LowestPriceLogger\Service\TransferStagedPricesToLog $transferStagedPricesToLog
    ) {
    }

    public function execute(): void
    {
        $this->transferStagedPricesToLog->execute();
    }
}
