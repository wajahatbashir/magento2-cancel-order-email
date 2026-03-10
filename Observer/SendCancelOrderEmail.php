<?php

namespace WB\CancelOrderEmail\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use WB\CancelOrderEmail\Model\Config;
use Psr\Log\LoggerInterface;

class SendCancelOrderEmail implements ObserverInterface
{
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $storeManager;
    protected $config;
    protected $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->transportBuilder  = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager      = $storeManager;
        $this->config            = $config;
        $this->logger            = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        $storeId = $order->getStoreId();

        if (!$this->config->isEnabled($storeId)) {
            return;
        }

        $customerEmail = $order->getCustomerEmail();
        if (!$customerEmail) {
            return;
        }

        $store               = $this->storeManager->getStore($storeId);
        $senderEmail         = $this->config->getSenderEmail($storeId);
        $template            = $this->config->getEmailTemplate($storeId);
        $formattedGrandTotal = strip_tags((string) $order->formatPrice($order->getGrandTotal()));

        $templateVars = [
            'order'                => $order,
            'store'                => $store,
            'store_name'           => $store->getFrontendName(),
            'formatted_grand_total' => $formattedGrandTotal,
        ];

        try {
            $this->inlineTranslation->suspend();

            // Send to customer
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope($senderEmail, $storeId)
                ->addTo($customerEmail, $order->getCustomerName())
                ->getTransport();

            $transport->sendMessage();

            $this->logger->info(sprintf(
                'WB_CancelOrderEmail: Cancellation email sent for order #%s to %s',
                $order->getIncrementId(),
                $customerEmail
            ));

            // Optionally send to admin(s)
            if ($this->config->isAdminNotifyEnabled($storeId)) {
                $adminEmailRaw = $this->config->getAdminEmail($storeId);
                $adminEmails   = array_filter(array_map('trim', explode(',', $adminEmailRaw)));

                foreach ($adminEmails as $adminEmail) {
                    $adminTransport = $this->transportBuilder
                        ->setTemplateIdentifier($template)
                        ->setTemplateOptions([
                            'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeId,
                        ])
                        ->setTemplateVars($templateVars)
                        ->setFromByScope($senderEmail, $storeId)
                        ->addTo($adminEmail)
                        ->getTransport();

                    $adminTransport->sendMessage();

                    $this->logger->info(sprintf(
                        'WB_CancelOrderEmail: Cancellation email sent for order #%s to admin %s',
                        $order->getIncrementId(),
                        $adminEmail
                    ));
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'WB_CancelOrderEmail: Failed to send cancellation email for order #%s. Error: %s',
                $order->getIncrementId(),
                $e->getMessage()
            ));
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
