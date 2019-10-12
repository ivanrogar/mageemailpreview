<?php

declare(strict_types=0);

namespace JohnRogar\MageEmailPreview\Model\Order\Email;

use JohnRogar\MageEmailPreview\Model\SenderBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Zend\Mime\Message;

/**
 * Class FakeSender
 * @package JohnRogar\MageEmailPreview\Model\Order\Email
 */
class FakeSender extends OrderSender
{

    /**
     * FakeSender constructor.
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \JohnRogar\MageEmailPreview\Model\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->orderResource = $orderResource;
        $this->globalConfig = $globalConfig;
        $this->addressRenderer = $addressRenderer;
        $this->eventManager = $eventManager;
        $this->templateContainer = $templateContainer;
        $this->identityContainer = $identityContainer;
        $this->senderBuilderFactory = $senderBuilderFactory;
        $this->logger = $logger;
        $this->addressRenderer = $addressRenderer;
    }

    /**
     * @param Order $order
     * @param null|string $templateId
     * @return bool|string
     */
    public function preview(Order $order, ?string $templateId)
    {
        $this->identityContainer->setStore($order->getStore());

        if (!$this->identityContainer->isEnabled()) {
            return false;
        }

        $this->prepareTemplate($order);

        if (is_string($templateId)) {
            $this->templateContainer->setTemplateId($templateId);
        }

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        $body = '';

        $response = $sender->send()->getBody();

        if ($response instanceof Message) {
            foreach ($response->getParts() as $part) {
                if ($part->getType() === 'text/html') {
                    $body .= $part->getContent();
                }
            }

            return $body;
        }

        return $response;
    }
}
