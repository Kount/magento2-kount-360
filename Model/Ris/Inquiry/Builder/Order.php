<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry\Builder;

use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class Order
{
    const FIELD_CARRIER = 'CARRIER';
    const FIELD_METHOD = 'METHOD';
    const FIELD_COUPON_CODE = 'COUPON_CODE';
    const FIELD_ACCOUNT_NAME = 'ACCOUNT_NAME';
    const LOCAL_IP = '10.0.0.1';

    public function __construct(
        private \Magento\Framework\App\State $appState,
        private \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        private \Magento\Directory\Helper\Data $directoryHelper,
        private \Magento\Framework\HTTP\Header $httpHeader,
        private \Kount\Kount360\Model\Logger $logger,
        private \Kount\Kount360\Model\Config\Account $configAccount,
        private \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        private \Kount\Kount360\Model\Ris\Base\Builder\Session $kountSession,
        private \Magento\Framework\App\Request\Http $requestHttp,
        private \Kount\Kount360\Helper\Data $kountHelper,
        private \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        private \Kount\Kount360\Model\Ris\Inquiry\Builder\Payment\Type $paymentType,
        private \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
    }

    /**
     * @param DataObject $request
     * @param OrderInterface $order
     * @return void
     */
    public function process(DataObject $request, OrderInterface $order): void
    {
        $this->processGeneralInfo($request, $order);
        $this->processAccountData($request, $order);
        $this->processCart($request, $order);
        $this->processFulfillment($request, $order);
        $this->processOrderTransactions($request, $order);
        $this->processDiscountInfo($request, $order);
        $this->processCustomFields($request, $order);
    }

    public function processUpdate(
        DataObject $request,
        $risTransactionId,
        OrderInterface $order,
        $realTimeDecline = false
    ): void {
        $this->processGeneralInfo($request, $order, true);
        $this->processAccountData($request, $order);
        $this->processOrderTransactions($request, $order, $risTransactionId, $realTimeDecline);
    }

    /**
     * @param DataObject $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $updateRequest
     * @return void
     */
    protected function processGeneralInfo(DataObject $request, OrderInterface $order, bool $updateRequest = false): void
    {
        $request->setData('merchantOrderId', $order->getIncrementId());
        $storeInformation = $this->configAccount->getStoreInformation();
        $store = $order->getStore();
        $request->setData('channel', $store->getBaseUrl() ?? 'MAGENTO');
        if (!$updateRequest) {
            $this->kountSession->process($request);
        }
        $now = $this->getCurrentTime();
        $request->setData('creationDateTime', $now);
        $this->processIpAndUserAgent($request, $order);
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param OrderInterface $order
     * @return void
     */
    protected function processAccountData(DataObject $request, OrderInterface $order): void
    {
        $accountData = [];
        $accountData['id'] = $order->getCustomerId() ? $order->getCustomerEmail() : '';
        $accountData['type'] = (string)$order->getCustomerGroupId();
        $now = $this->getCurrentTime();
        $accountData['creationDateTime'] = $now;
        $accountData['username'] = $order->getCustomerEmail();
        $accountData['accountIsActive'] = true;
        $request->setData('account', $accountData);
    }

    /**
     * @param DataObject $request
     * @param OrderInterface $order
     * @param null $risTransactionId
     * @param bool $realTimeDecline
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processOrderTransactions(
        DataObject $request,
        OrderInterface $order,
        $risTransactionId = null,
        $realTimeDecline = false
    ): void {
        $transactionData = [];
        // Payment Data
        $transactionData['merchantTransactionId'] = (string)($order->getPayment()->getLastTransId() ?? '');
        $transactionData['processor'] = $order->getPayment()->getMethodInstance()->getTitle();
        $transactionData['processorMerchantId'] = '';
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode() ?? '';
        $this->logger->info('Payment Code: ' . $paymentCode);
        $transactionData['payment'] = [
            'type' => $this->paymentType->getPaymentType($paymentCode),
            'paymentToken' => '',
            'bin' => '',
            'last4' => (string)($order->getPayment()->getCcLast4() ?? '')
        ];

        // Totals Data
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $transactionData['subtotal'] = (string)$this->convertAndRoundAmount(
            $order->getBaseSubtotal(),
            $baseCurrencyCode
        );
        $transactionData['currency'] = $this->configAccount->getCurrency();
        $transactionData['orderTotal'] = (string)$this->convertAndRoundAmount(
            $order->getBaseGrandTotal(),
            $baseCurrencyCode
        );

        // Get Addresses
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $transactionData['tax'] = [
            'isTaxable' => $order->getTaxAmount() ? true : false,
            'taxableCountryCode' => ($shippingAddress) ? $shippingAddress->getCountryId(
            ) : $billingAddress->getCountryId(),
            'taxAmount' => (string)$this->convertAndRoundAmount($order->getBaseTaxAmount(), $baseCurrencyCode)
        ];

        // Billing Data
        $transactionData['billedPerson'] = [];
        $transactionData['billedPerson']['name'] = [
            'first' => $billingAddress->getFirstname(),
            'last' => $billingAddress->getLastname()
        ];

        $transactionData['billedPerson']['emailAddress'] = $billingAddress->getEmail();
        $transactionData['billedPerson']['phoneNumber'] = $billingAddress->getTelephone();
        $transactionData['billedPerson']['address'] = [
            'addressType' => 'BILLING',
            'line1' => $billingAddress->getStreetLine(1),
            'line2' => $billingAddress->getStreetLine(2),
            'city' => $billingAddress->getCity(),
            'region' => $billingAddress->getRegion(),
            'countryCode' => $billingAddress->getCountryId(),
            'postalCode' => $billingAddress->getPostcode()
        ];

        if ($this->dataPersistor->get('kount_post_auth_failure')) {
            $realTimeDecline = true;
            $this->dataPersistor->set('kount_post_auth_failure', false);
        }

        $transactionData['transactionStatus'] = $realTimeDecline ?
            'REFUSED' :
            ($order->getPayment()->getMethod() ? 'CAPTURED' : 'PENDING');

        $transactionData['authorizationStatus'] = [
            'authResult' => $realTimeDecline ?
                'DECLINED' :
                ($order->getPayment()->getMethod() ? 'APPROVED' : 'UNKNOWN')
        ];

        if ($risTransactionId) {
            $transactionData['transactionId'] = $risTransactionId;
        }
        $request->setData('transactions', [$transactionData]);
    }

    /**
     * @param float $amount
     * @param string $baseCurrencyCode
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function convertAndRoundAmount($amount, $baseCurrencyCode)
    {
        $currency = $this->configAccount->getCurrency();
        $amount = $currency === $baseCurrencyCode
            ? $amount
            : $this->directoryHelper->currencyConvert($amount, $baseCurrencyCode, $currency);
        return round($amount * 100);
    }

    /**
     * @param DataObject $request
     * @param OrderInterface $order
     * @return void
     */
    protected function processFulfillment(DataObject $request, OrderInterface $order)
    {
        $fulfillmentData = [];
        $fulfillmentData['type'] = 'SHIPPED';
        $fulfillmentData['shipping'] = [];
        $fulfillmentData['shipping']['amount'] = (string)$this->convertAndRoundAmount(
            $order->getBaseShippingAmount(),
            $order->getBaseCurrencyCode()
        );
        $shippingMethod = $order->getShippingMethod(true);
        $fulfillmentData['shipping']['provider'] = $shippingMethod ? ($shippingMethod->getData(
            'carrier_code'
        ) ?? '') : '';
        $fulfillmentData['shipping']['method'] = $shippingMethod && $shippingMethod->getData(
            'method'
        ) ? 'STANDARD' : '';
        $fulfillmentData['recipient'] = $this->processShippingInfo($request, $order);
        $fulfillmentData['store'] = $this->processStore($request, $order);
        $request->setData('fulfillment', [$fulfillmentData]);
    }

    protected function processDiscountInfo($request, $order)
    {
        $promotions = [];

        $appliedRules = $order->getAppliedRuleIds();

        if ($appliedRules) {
            $ruleIds = explode(',', $appliedRules);

            foreach ($ruleIds as $ruleId) {
                try {
                    $rule = $this->ruleRepository->getById($ruleId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }
                if ($rule) {
                    $promotionData = [
                        'id' => $rule->getRuleId(),
                        'description' => $rule->getName(),
                        'status' => 'accepted',
                        'statusReason' => 'Promotion applied successfully.',
                        'discount' => [
                            'percentage' => $this->calculateDiscountPercentage($order, $rule),
                            'amount' => $this->calculateDiscountAmount($order, $rule),
                        ]
                    ];

                    $promotions[] = $promotionData;
                }
            }
        }

        // Set promotions data into the request
        $request->setData('promotions', $promotions);
    }

    protected function calculateDiscountPercentage($order, $rule)
    {
        $discountAmount = (float)$this->calculateDiscountAmount($order, $rule);
        $discountAmount = $discountAmount / 100;
        $orderTotal = $order->getBaseSubtotal();

        return $orderTotal > 0 ? $discountAmount / $orderTotal : 0;
    }

    /**
     * Calculate the discount amount for the given rule.
     */
    protected function calculateDiscountAmount($order, $rule)
    {
        $discountAmount = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getAppliedRuleIds() && in_array($rule->getRuleId(), explode(',', $item->getAppliedRuleIds()))) {
                $discountAmount += $item->getBaseDiscountAmount();
            }
        }

        return (string)$this->convertAndRoundAmount($discountAmount, $order->getBaseCurrencyCode());
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function processStore(DataObject $request, OrderInterface $order): array
    {
        $storeData = [];
        $storeData['id'] = (string)$order->getStoreId();
        array_merge($storeData, $this->configAccount->getStoreInformation());
        return $storeData;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return array
     */
    protected function processShippingInfo(DataObject $request, OrderInterface $order): array
    {
        $shippingInfo = [];
        $shippingInfo['sameAsBilling'] = false;
        $shippingAddress = $order->getShippingAddress();
        if (!empty($shippingAddress)) {
            $shippingInfo['person'] = [];
            $shippingInfo['person']['name'] = [
                'first' => $shippingAddress->getFirstname(),
                'last' => $shippingAddress->getLastname()
            ];

            $shippingInfo['person']['emailAddress'] = $shippingAddress->getEmail();
            $shippingInfo['person']['phoneNumber'] = $shippingAddress->getTelephone();
            $shippingInfo['person']['address'] = [
                'addressType' => 'SHIPPING',
                'line1' => $shippingAddress->getStreetLine(1),
                'line2' => $shippingAddress->getStreetLine(2),
                'city' => $shippingAddress->getCity(),
                'region' => $shippingAddress->getRegion(),
                'countryCode' => $shippingAddress->getCountryId(),
                'postalCode' => $shippingAddress->getPostcode()
            ];
        }
        return $shippingInfo;
    }

    /**
     * @param DataObject $request
     * @param OrderInterface $order
     * @return void
     */
    protected function processCart(DataObject $request, \Magento\Sales\Api\Data\OrderInterface $order): void
    {
        $realOrderItems = [];
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }
            $realOrderItems[] = $orderItem;
        }

        $cart = [];
        /** @var \Magento\Sales\Model\Order\Item $realOrderItem */
        foreach ($realOrderItems as $realOrderItem) {
            $productName = $realOrderItem->getName() ?? $realOrderItem->getSku();
            $cart[] = [
                'id' => $realOrderItem->getQuoteItemId() ?? $realOrderItem->getId(),
                'price' => (string)$this->convertAndRoundAmount(
                    $realOrderItem->getBasePrice(),
                    $order->getBaseCurrencyCode()
                ),
                'description' => ($realOrderItem->getDescription() ? $realOrderItem->getDescription() : $productName),
                'name' => $productName,
                'quantity' => round($realOrderItem->getQtyOrdered()),
                'sku' => $realOrderItem->getSku(),
                'url' => $realOrderItem->getProduct()->getProductUrl(),
                'image' => $realOrderItem->getProduct()->getImage(),
            ];
        }
        $request->setData('items', $cart);
    }

    /**
     * @param DataObject $request
     * @param OrderInterface $order
     * @return void
     */
    protected function processIpAndUserAgent(DataObject $request, OrderInterface $order)
    {
        $ipAddress = $this->getIpAddress($order);
        $ipAddress = $this->isBackend($ipAddress) ? self::LOCAL_IP : $ipAddress;
        $request->setData('userIp', $ipAddress);
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    protected function processCustomFields(DataObject $request, OrderInterface $order): void
    {
        $customFields = [];
        if (!empty($request->getData('promotions'))) {
            $descriptions = [];
            foreach ($request->getData('promotions') as $promotion) {
                $descriptions[] = $promotion['description'];
            }
            $customFields['COUPON_CODE'] = implode(',', $descriptions);
        }

        $shippingMethod = $order->getShippingMethod(true);
        if (is_object($shippingMethod)) {
            $customFields['CARRIER'] = $shippingMethod->getData('carrier_code') ?? '';
            $customFields['METHOD'] = $shippingMethod->getData('method') ?? '';
        } else {
            $customFields['CARRIER'] = '';
            $customFields['METHOD'] = '';
        }
        $customFields['EXT'] = $this->kountHelper->getModuleVersion();
        $store = $order->getStore();
        $customFields['ACCOUNT_NAME'] = $store->getBaseUrl() ?? 'MAGENTO';
        $customFields['PLATFORM'] = 'Adobe Commerce ' . $this->productMetadata->getEdition(
            ) . ':' . $this->productMetadata->getVersion();
        $request->setData('customFields', $customFields);
    }

    /**
     * @param $ipAddress
     * @return bool
     */
    protected function isBackend($ipAddress): bool
    {
        try {
            return (bool)($this->appState->getAreaCode() === Area::AREA_ADMINHTML || empty($ipAddress));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return false;
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    protected function getIpAddress(OrderInterface $order)
    {
        $ipAddress = ($order->getXForwardedFor() ?: ($this->getRequestXForwardedFor() ?: $order->getRemoteIp()));
        if (!$ipAddress) {
            return "";
        }
        if (false !== strpos($ipAddress, ',')) {
            $ipAddress = explode(',', $ipAddress);
            $ipAddress = array_shift($ipAddress);
        }
        return $ipAddress;
    }

    /**
     * @return \Magento\Framework\App\Request\Http
     * @deprecated
     *
     * As temporary fix
     *
     */
    private function getRequestXForwardedFor()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        return $this->requestHttp->getServer('HTTP_X_FORWARDED_FOR');
    }

    private function getCurrentTime()
    {
        return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
    }
}
