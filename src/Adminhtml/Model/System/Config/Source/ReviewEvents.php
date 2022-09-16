<?php

/**
 * Copyright Elgentos. All rights reserved.
 * https://www.elgentos.nl
 */

declare(strict_types=1);

namespace Elgentos\Kiyoh\Adminhtml\Model\System\Config\Source;

class ReviewEvents
{
    public function toOptionArray(): array
    {
        return [
            ['value' => '', 'label' => __('')],
            ['value' => 'Shipping', 'label' => __('Shipping')]
        ];
    }
}
