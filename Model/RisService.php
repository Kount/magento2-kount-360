<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class RisService
{
    const AUTO_DECLINE = 'DECLINE';
    const AUTO_REVIEW = 'REVIEW';
    const AUTO_ESCALATE = 'E';
    const AUTO_APPROVE = 'APPROVE';
    const AUTH_AUTHORIZED = 'APPROVE';
    const AUTH_DECLINED = 'DECLINED';
    const MACK_YES = 'Y';
    const MACK_NO = 'N';
    const DEFAULT_ANID = '0123456789';

    public function __construct(
        private Ris\Inquiry\Builder $inquiryBuilder,
        private \Kount\Kount360\Model\Ris\Update\Builder $updateBuilder,
        private \Kount\Kount360\Model\ApiClient $apiClient,
        private \Kount\Kount360\Model\Order\Ris $orderRis,
        private \Kount\Kount360\Model\Logger $logger,
        private \Magento\Framework\App\State $state,
        private \Kount\Kount360\Model\Config\Account $configAccount,
        private \Magento\Framework\Serialize\Serializer\Json $serializerJson
    ) {

    }

    /**
     * @return array
     */
    public function getAutos()
    {
        return [self::AUTO_APPROVE, self::AUTO_REVIEW, self::AUTO_ESCALATE, self::AUTO_DECLINE];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $graceful
     * @param string $auth
     * @param string $mack
     * @return bool
     * @throws LocalizedException
     */
    public function inquiryRequest(
        Order $order, $graceful = true, $auth = self::AUTH_AUTHORIZED, $mack = self::MACK_YES
    ) {
        $ris = $this->orderRis->getRis($order);
        if (!empty($ris->getResponse())) {
            $this->logger->info('Skipp second time inquiry request.'); /* Authorize.net calls payment place twice */
            return false;
        }

        $inquiryRequest = $this->inquiryBuilder->build($order, $auth, $mack);
        if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
            try {
                // mode is no longer needed on kount 360, replaced with PATCH and POST requests
                //$inquiryRequest->setMode(\Magento\Framework\DataObject::MODE_P);
            } catch (\Exception $e) {
                $this->logger->warning('Mode doesn\'t mach any of the defined inquiry modes');
                return false;
            }

            if ($order->getShippingAddress() !== null && $order->getShippingAddress()->getTelephone() !== null) {
                $phone = $order->getShippingAddress()->getTelephone();
                $phone = preg_replace("/[^a-zA-Z0-9]+/", "", $phone);
            } else {
                $phone = self::DEFAULT_ANID;
            }

            $inquiryRequest->setAnid($phone);
        }
        $url = $this->configAccount->getKountOrderEndpoint();
        $inquiryRequest = $inquiryRequest->getData();
        $inquiryResponse = $this->apiClient->post('inquiryRequest', $url, $inquiryRequest);

        if (!$this->getTransactionId($inquiryResponse)) {
            $this->logger->warning('No transaction_id in response, skipping Update.');
            return false;
        }

        if (!$graceful && $this->parseResponseForAction($inquiryResponse) === RisService::AUTO_DECLINE) {
            throw new LocalizedException(__('Payment authorization rejection from the processor.'));
        }


        $this->orderRis->updateRis($order, $inquiryResponse);
        return true;
    }

    private function getTransactionId(array $inquiryResponse)
    {
        $transactionId = null;
        if (!isset($inquiryResponse['order']['transactions'])) {
            return $transactionId;
        }

        foreach ($inquiryResponse['order']['transactions'] as $transaction) {
            $transactionId = $transaction['transactionId'];
            break;
        }
        return $transactionId;
    }

    /**
     * @param $response
     * @return string|bool
     */
    protected function parseResponseForAction($response)
    {
        if (!isset($response['order']['riskInquiry']['decision'])) {
            return 'Approve';
        }
        return $response['order']['riskInquiry']['decision'];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     * @throws \Exception
     */
    public function updateRequest(Order $order): bool
    {
        $ris = $this->orderRis->getRis($order);
        if (empty($ris->getTransactionId())) {
            $this->logger->warning('No ris transaction_id in order, skipping Update.');
            return false;
        }

        $updateRequest = $this->updateBuilder->build($order, $ris->getTransactionId());
        $updateRequest = $updateRequest->getData();
        $url = $this->configAccount->getKountOrderEndpoint();
        $this->apiClient->patch('updateOrder', $url . '/' . $ris->getTransactionId(), $updateRequest);

        return true;
    }
}
