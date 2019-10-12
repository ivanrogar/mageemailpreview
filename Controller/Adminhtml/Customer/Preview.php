<?php

declare(strict_types=1);

namespace JohnRogar\MageEmailPreview\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceModelFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\CustomerFactory as CustomerFactory;
use Zend\Mime\Message;
use JohnRogar\MageEmailPreview\Model\PdfWrapper;

/**
 * Class Index
 * @package JohnRogar\MageEmailPreview\Controller\Adminhtml\Customer
 * @SuppressWarnings(ElseExpression)
 * @SuppressWarnings(CouplingBetweenObjects)
 * @SuppressWarnings(LongVariable)
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

    protected $customerFactory;

    protected $customerResourceModelFactory;

    protected $wrapper;

    protected $transportBuilder;

    protected $storeManager;

    protected $scopeConfig;

    protected $defaultLocale = 'en_US';

    /**
     * Preview constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerResourceModelFactory $customerResourceFactory
     * @param PdfWrapper $wrapper
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerFactory $customerFactory,
        CustomerResourceModelFactory $customerResourceFactory,
        PdfWrapper $wrapper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $locale = ($context->getRequest()->getParam('locale')) ?: $this->defaultLocale;

        $context->getLocaleResolver()->setLocale($locale);
        $context->getLocaleResolver()->setDefaultLocale($locale);

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->messageManager = $context->getMessageManager();
        $this->customerFactory = $customerFactory;
        $this->customerResourceModelFactory = $customerResourceFactory;
        $this->wrapper = $wrapper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('template_id');
        $params = $this->getParameters();

        $rendered = $this->renderTemplate($templateId, Customer::XML_PATH_REGISTER_EMAIL_IDENTITY, $params);

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $result->setContents($rendered);

        if ((int) $this->getRequest()->getParam('convert_to_pdf') === 1) {
            $result->setHeader('Content-Type', 'application/pdf');
            $result->setHeader('Content-Disposition', 'inline; filename="preview.pdf"');
            $result->setContents($this->wrapper->toPdf($rendered));
        }

        return $result;
    }

    /**
     * @param $templateIdentifier
     * @param $sender
     * @param array $templateParams
     * @return string
     */
    private function renderTemplate($templateIdentifier, $sender, array $templateParams = [])
    {
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $templateIdentifier
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 1]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, 1)
        )->addTo(
            'johnrogar@johnrogar.xxx',
            'Preview'
        )->getTransport();

        $response = $transport->getMessage()->getBody();

        $body = '';

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

    /**
     * @return Customer
     * @throws LocalizedException
     */
    private function getCustomer()
    {
        $customer = $this->customerFactory->create();

        /**
         * @var CustomerResourceModel $resourceModel
         */
        $resourceModel = $this->customerResourceModelFactory->create();

        $resourceModel->load($customer, (int) $this->getRequest()->getParam('customer_id'));

        if (!$customer->getId()) {
            throw new LocalizedException(__('Invalid customer'));
        }

        return $customer;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore()
    {
        return $this->storeManager->getStore(1);
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getParameters()
    {
        return [
            'customer' => $this->getCustomer(),
            'back_url' => '',
            'store' => $this->getStore(),
        ];
    }
}
