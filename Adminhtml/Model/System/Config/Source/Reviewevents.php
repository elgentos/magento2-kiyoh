<?php

namespace Elgentos\Kiyoh\Adminhtml\Model\System\Config\Source;

class Reviewevents
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label'=>__('')],
            ['value' => 'Shipping', 'label'=>__('Shipping')]
        ];
    }
}
