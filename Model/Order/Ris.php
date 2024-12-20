<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order;

use Kount\Kount360\Api\Data\RisInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Ris
{
    const STATUS_KOUNT_REVIEW = 'review_kount';
    const STATUS_KOUNT_DECLINE = 'decline_kount';

    /**
     * @param \Kount\Kount360\Api\Data\RisInterfaceFactory $risFactory
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     * @param \Kount\Kount360\Api\RisRepository $risRepository
     * @param \Kount\Kount360\Model\Logger $logger
     */
    public function __construct(
        protected \Kount\Kount360\Api\Data\RisInterfaceFactory $risFactory,
        protected \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        protected \Kount\Kount360\Api\RisRepositoryInterface $risRepository,
        protected \Kount\Kount360\Model\Logger $logger
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
                $ris->setGeox(
                    isset($device['location']['latitude'], $device['location']['longitude'])
                        ? $device['location']['latitude'] . ',' . $device['location']['longitude']
                        : ''
                );
                $ris->setCountry($device['location']['country'] ?? '');
                $ris->setKaptcha($device['tor'] ?? false ? 'Yes' : 'No');
                $ris->setIpAddress($device['id'] ?? '');
                $ris->setIpCity($device['location']['city'] ?? '');
                $ris->setNetw($device['deviceAttributes']['os'] ?? '');
                $ris->setMobileDevice($device['deviceAttributes']['mobileSdkType'] ?? '');
                $ris->setMobileType($device['deviceAttributes']['mobileSdkType'] ?? '');
            } else {
                // Default values for missing device data
                $ris->setGeox('');
                $ris->setCountry('');
                $ris->setKaptcha('No');
                $ris->setIpAddress('');
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

        // Logging updated values
        $this->logger->info('Setting RIS Response to order:');
        $this->logger->info('Response: ' . $ris->getResponse());
        $this->logger->info('Score: ' . $ris->getScore());
        $this->logger->info('Rules: ' . $ris->getRule());
        $this->logger->info('Description: ' . $ris->getDescription());
        $this->logger->info('TransactionId: ' . $ris->getTransactionId());
        $this->logger->info('Geox: ' . $ris->getGeox());
        $this->logger->info('Country: ' . $ris->getCountry());
        $this->logger->info('Kaptcha: ' . $ris->getKaptcha());
        $this->logger->info('Cards: ' . $ris->getCards());
        $this->logger->info('Emails: ' . $ris->getEmails());
        $this->logger->info('Devices: ' . $ris->getDevices());
        $this->logger->info('Omniscore: ' . $ris->getOmniscore());
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

        if (!empty($response['order']['riskInquiry'][0]['policySetExecuted']['policiesExecuted'])) {
            foreach ($response['order']['riskInquiry'][0]['policySetExecuted']['policiesExecuted'] as $policy) {
                $rules .= 'Rule ID ' . ($policy['id'] ?? 'N/A') . ': ' . ($policy['name'] ?? 'Unknown Rule') . "\n";
            }
        } else {
            $rules = 'No Rules';
        }

        return $rules;
    }
}
