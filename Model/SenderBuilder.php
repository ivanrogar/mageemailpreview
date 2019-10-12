<?php

declare(strict_types=0);

namespace JohnRogar\MageEmailPreview\Model;

/**
 * Class SenderBuilder
 * @package JohnRogar\MageEmailPreview\Model
 */
class SenderBuilder extends \Magento\Sales\Model\Order\Email\SenderBuilder
{

    /**
     * @return \Magento\Framework\Mail\MessageInterface
     */
    public function send()
    {
        $this->configureEmailTemplate();

        $this->transportBuilder->addTo(
            $this->identityContainer->getCustomerEmail(),
            $this->identityContainer->getCustomerName()
        );

        $copyTo = $this->identityContainer->getEmailCopyTo();

        if (!empty($copyTo) && $this->identityContainer->getCopyMethod() == 'bcc') {
            foreach ($copyTo as $email) {
                $this->transportBuilder->addBcc($email);
            }
        }

        return $this->transportBuilder->getTransport()->getMessage();
    }
}
