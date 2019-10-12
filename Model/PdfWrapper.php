<?php

declare(strict_types=1);

namespace JohnRogar\MageEmailPreview\Model;

use Knp\Snappy\Pdf;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class PdfWrapper
 * @package JohnRogar\MageEmailPreview\Model
 */
class PdfWrapper
{

    const CONFIG_WKHTMLTOPDF_PATH = 'general/johnrogar_email_preview/wkhtmltopdf_path';

    private $path = '/usr/local/bin/wkhtmltopdf';

    private $snappy;

    private $scopeConfig;

    /**
     * PdfWrapper constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->snappy = new Pdf();
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $html
     * @return string
     */
    public function toPdf(string $html)
    {
        $path = $this->scopeConfig->getValue(self::CONFIG_WKHTMLTOPDF_PATH);

        if (!$path) {
            $path = $this->path;
        }

        $this->snappy->setBinary($path);
        return $this->snappy->getOutputFromHtml($html);
    }
}
