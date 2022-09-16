<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\Cron;

use Elgentos\Kiyoh\Service\RetrieveKiyohReviews;
use Magento\Framework\Exception\LocalizedException;

class RetrieveReviews
{
    private RetrieveKiyohReviews $service;

    public function __construct(
        RetrieveKiyohReviews $service
    )
    {
        $this->service = $service;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): void {
        $this->service->execute();
    }
}
