<?php

declare(strict_types=1);

namespace JohnRogar\MageEmailPreview\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use JohnRogar\MageEmailPreview\Model\Order\Email\FakeSender;
use JohnRogar\MageEmailPreview\Model\PdfWrapper;

/**
 * Class Index
 * @package JohnRogar\MageEmailPreview\Controller\Adminhtml\Order
 * @SuppressWarnings(ElseExpression)
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Preview extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    protected $wrapper;

    protected $defaultLocale = 'hr_HR';

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param FakeSender $orderSender
     * @param PdfWrapper $wrapper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        FakeSender $orderSender,
        PdfWrapper $wrapper
    ) {
        $locale = ($context->getRequest()->getParam('locale')) ?: $this->defaultLocale;

        $context->getLocaleResolver()->setLocale($locale);
        $context->getLocaleResolver()->setDefaultLocale($locale);

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $context->getMessageManager();
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->wrapper = $wrapper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $order = null;

        if ($orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $e) {
            }
        }

        if (!$order) {
            $this->messageManager->addErrorMessage('Order not found');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $templateId = ($this->getRequest()->getParam('template_id')) ?: null;

        $html = $this->orderSender->preview($order, $templateId);

        $result->setContents($html);

        if ((int) $this->getRequest()->getParam('convert_to_pdf') === 1) {
            $result->setHeader('Content-Type', 'application/pdf');
            $result->setHeader('Content-Disposition', 'inline; filename="preview.pdf"');
            $result->setContents($this->wrapper->toPdf($html));
        }

        return $result;
    }
}
