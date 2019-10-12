<?php

declare(strict_types=0);

namespace JohnRogar\MageEmailPreview\Block\Adminhtml\Customer;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class View
 * @package JohnRogar\MageEmailPreview\Block\Adminhtml\Customer
 */
class View implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [
            'type' => 'button',
            'on_click' => '',
            'label' => __('Email Preview'),
            'class' => 'johnrogar-email-preview-button',
            'sort_order' => 40,
        ];

        return $data;
    }
}
