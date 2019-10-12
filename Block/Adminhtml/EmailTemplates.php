<?php

declare(strict_types=1);

namespace JohnRogar\MageEmailPreview\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Email\Model\Template\Config;
use Magento\Framework\App\State;

/**
 * Class EmailTemplates
 * @package JohnRogar\MageEmailPreview\Block\Adminhtml
 */
class EmailTemplates extends Template
{

    private $config;

    private $state;

    private $checkDeveloperMode;

    /**
     * EmailTemplates constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param bool $checkDeveloperMode
     * @param array $data
     * @SuppressWarnings(BooleanArgumentFlag)
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        bool $checkDeveloperMode = false,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->checkDeveloperMode = $checkDeveloperMode;
        $this->state = $context->getAppState();
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        $templates = [];

        $type = $this->getData('preview_template_type');

        foreach ($this->config->getAvailableTemplates() as $template) {
            $label = $template['label'];

            if (!is_string($label)) {
                $label = $label->__toString();
            }

            if (stripos($template['value'], $type) !== false) {
                $templates[$template['value']] = $label;
            }
        }

        return $templates;
    }

    /**
     * @return string
     */
    public function getPreviewUrl()
    {
        $type = (string)$this->getData('preview_template_type');

        // yes, of course we could've let Magento build the url with params
        switch ($type) {
            case 'order':
                return $this
                        ->getUrl('johnrogar_email_preview/order/preview/index')
                    . '?order_id=' . $this->getRequest()->getParam('order_id');
            case 'customer':
                return $this
                        ->getUrl('johnrogar_email_preview/customer/preview/index')
                    . '?customer_id=' . $this->getRequest()->getParam('id');
        }

        return null;
    }

    /**
     * @return bool
     */
    public function shouldActivate()
    {
        if (!$this->checkDeveloperMode) {
            return true;
        }

        return $this->state->getMode() === State::MODE_DEVELOPER;
    }
}
