<?php

namespace WB\CancelOrderEmail\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const XML_PATH_ENABLE              = 'wb_cancelorderemail/general/enable';
    const XML_PATH_EMAIL_TEMPLATE      = 'wb_cancelorderemail/general/email_template';
    const XML_PATH_SENDER_EMAIL        = 'wb_cancelorderemail/general/sender_email';
    const XML_PATH_NOTIFY_ADMIN        = 'wb_cancelorderemail/admin_notification/enable';
    const XML_PATH_ADMIN_EMAIL         = 'wb_cancelorderemail/admin_notification/admin_email';

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getEmailTemplate($storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSenderEmail($storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SENDER_EMAIL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isAdminNotifyEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_NOTIFY_ADMIN, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getAdminEmail($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_ADMIN_EMAIL, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
