<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order;

use Kount\Kount360\Api\Data\RisInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;


class Ris
{
    const STATUS_KOUNT_REVIEW = 'review_kount';
    const STATUS_KOUNT_DECLINE = 'decline_kount';

    /**
     * @param \Kount\Kount360\Api\Data\RisInterfaceFactory $risFactory
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     * @param \Kount\Kount360\Api\RisRepositoryInterface $risRepository
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Kount\Kount360\Model\Config\Log $configLog
     */
    public function __construct(
        protected \Kount\Kount360\Api\Data\RisInterfaceFactory $risFactory,
        protected \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        protected \Kount\Kount360\Api\RisRepositoryInterface $risRepository,
        protected \Kount\Kount360\Model\Logger $logger,
        protected \Magento\Framework\App\ResourceConnection $resource,
        protected \Kount\Kount360\Model\Config\Log $configLog
    ) {
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Kount\Kount360\Api\Data\RisInterface
     */
    public function getRis(OrderInterface $order)
    {
        $extensionAttributes = $this->retrieveOrderExtensionAttributes($order);
        $ris = $extensionAttributes->getKountRis();

        if (empty($ris)) {
            try {
                $ris = $this->risRepository->getByOrderId((int)$order->getEntityId());
            } catch (NoSuchEntityException $e) {
                $ris = $this->risFactory->create();
                //fallback to kount_ris table
                $this->fallbackToKountRisTable($ris, $order);
            }
            $extensionAttributes->setKountRis($ris);
        }
        return $ris;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderExtensionInterface
     */
    protected function retrieveOrderExtensionAttributes(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->orderExtensionFactory->create();
            $order->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function linkRis(RisInterface $ris, OrderInterface $order): void
    {
        $ris->setOrderId($order->getEntityId());
        $this->risRepository->save($ris);
    }

    public function updateRis(OrderInterface $order, array $response)
    {
        $ris = $this->getRis($order);
        $orderData = $response['order'] ?? []; // Safely access 'order'

        // Ensure 'order' and 'riskInquiry' exist in the response
        if (!empty($orderData['riskInquiry'])) {
            $riskInquiry = $orderData['riskInquiry'];

            $ris->setScore($riskInquiry['omniscore'] ?? null);
            $ris->setResponse($riskInquiry['decision'] ?? ''); // 'decision' maps to response
            $ris->setRule($this->getTriggeredRules($response));
            $ris->setDescription($riskInquiry['segmentExecuted']['segment']['name'] ?? '');
            $ris->setTransactionId($orderData['orderId']);
            //$ris->setTransactionId($orderData['transactions'][0]['transactionId'] ?? '');

            // Handle device data if it exists
            $device = $riskInquiry['device'] ?? null;
            if ($device) {
                $ris->setGeox($device['location']['countryCode'] ?? '');
                $ris->setCountry($device['location']['countryCode'] ?? '');
                $ris->setKaptcha($device['tor'] ?? false ? 'Yes' : 'No');
                
                $ris->setIpAddress(
                    (isset($device['deviceAttributes']['ip'][0]['address'])) 
                        ? $device['deviceAttributes']['ip'][0]['address'] 
                        : 'N/A'
                );
                
                $ris->setIpCity($device['location']['city'] ?? '');
                $ris->setNetw($device['deviceAttributes']['os'] ?? '');
                $ris->setMobileDevice($device['deviceAttributes']['mobileSdkType'] ?? '');
                $ris->setMobileType($device['deviceAttributes']['mobileSdkType'] ?? '');
            } else {
                // Default values for missing device data
                $ris->setGeox('');
                $ris->setCountry('');
                $ris->setKaptcha('No');
                $ris->setIpAddress('N/A');
                $ris->setIpCity('');
                $ris->setNetw('');
                $ris->setMobileDevice('');
                $ris->setMobileType('');
            }

            // Handle persona data
            $persona = $riskInquiry['persona'] ?? [];
            $ris->setCards($persona['uniqueCards'] ?? 0);
            $ris->setEmails($persona['uniqueEmails'] ?? 0);
            $ris->setDevices($persona['uniqueDevices'] ?? 0);

            $ris->setOmniscore($riskInquiry['omniscore'] ?? null);
        }

        if ($order->getEntityId()) {
            $this->linkRis($ris, $order);
        }

        $this->logRisValues($ris);
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return void
     */
    private function logRisValues($ris): void
    {
        $this->logger->info('Setting RIS Response to order:');
        $this->logger->info('Response: ' . $ris->getResponse());
        $this->logger->info('Score: ' . $ris->getScore());
        $this->logger->info('Rules: ' . $ris->getRule());
        $this->logger->info('Description: ' . $ris->getDescription());
        $this->logger->info('TransactionId: ' . $ris->getTransactionId());
        $this->logger->info('Omniscore: ' . $ris->getOmniscore());

        if (!$this->configLog->isRisMetricsEnabled()) {
            return;
        }

        $this->logger->info('Geox: ' . $ris->getGeox());
        $this->logger->info('Country: ' . $ris->getCountry());
        $this->logger->info('Kaptcha: ' . $ris->getKaptcha());
        $this->logger->info('Cards: ' . $ris->getCards());
        $this->logger->info('Emails: ' . $ris->getEmails());
        $this->logger->info('Devices: ' . $ris->getDevices());
        $this->logger->info('IP Address: ' . $ris->getIpAddress());
        $this->logger->info('IP City: ' . $ris->getIpCity());
        $this->logger->info('Network: ' . $ris->getNetw());
        $this->logger->info('Mobile Device: ' . $ris->getMobileDevice());
        $this->logger->info('Mobile Type: ' . $ris->getMobileType());
    }

    /**
     * @param array $response
     * @return string
     */
    protected function getTriggeredRules(array $response)
    {
        $rules = '';

        if (isset($response['order']['riskInquiry']['segmentExecuted']['policiesExecuted'])) {
            foreach ($response['order']['riskInquiry']['segmentExecuted']['policiesExecuted'] as $policy) {
                $rules .= $policy['name'] . "\n";
            }
        } else {
            $rules = 'No Rules';
        }

        return $rules;
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    private function fallbackToKountRisTable(\Kount\Kount360\Api\Data\RisInterface $ris, OrderInterface $order): void
    {
        $connection = $this->resource->getConnection();
        $fullTableName = $this->resource->getTableName('kount_ris');

        if ($connection->isTableExists($fullTableName)) {
            $select = $connection->select()
                ->from($fullTableName)
                ->where('order_id = ?', (int)$order->getEntityId()) // safe binding
                ->limit(1);
            $row = $connection->fetchRow($select);
            if ($row) {
                $ris->setData($row);
                $ris->setData('avoid_link', true);
            }
        }
    }
}
