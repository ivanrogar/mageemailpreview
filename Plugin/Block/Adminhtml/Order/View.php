<?php

declare(strict_types=0);

namespace JohnRogar\MageEmailPreview\Plugin\Block\Adminhtml\Order;

use Magento\Backend\Model\UrlInterface;
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Email\Model\Template\Config;

/**
 * Class View
 * @package JohnRogar\MageEmailPreview\Plugin\Block\Adminhtml\Order
 */
class View
{

    private $url;
    private $config;

    /**
     * View constructor.
     * @param UrlInterface $url
     * @param Config $config
     */
    public function __construct(
        UrlInterface $url,
        Config $config
    ) {
        $this->url = $url;
        $this->config = $config;
    }

    /**
     * @param OrderView $subject
     */
    public function beforeSetLayout(OrderView $subject)
    {
        $subject->addButton(
            'johnrogar_email_preview',
            [
                'label' => __('Email Preview'),
                'class' => 'johnrogar-email-preview-button',
                'order-id' => $subject->getOrderId(),
            ]
        );
    }
}
