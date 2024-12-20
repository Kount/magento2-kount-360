<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Inquiry;

use Kount\Kount360\Model\RisService;
use Magento\Sales\Model\Order;

class Builder
{
    /**
     * @var \Kount\Kount360\Model\Ris\InquiryFactory
     */
    protected $inquiryFactory;

    /**
     * @var \Kount\Kount360\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Kount\Kount360\Model\Ris\Inquiry\Builder\VersionInfo
     */
    protected $versionBuilder;

    /**
     * @var \Kount\Kount360\Model\Ris\Base\Builder\Session
     */
    protected $sessionBuilder;

    /**
     * @var \Kount\Kount360\Model\Ris\Inquiry\Builder\Order
     */
    protected $orderBuilder;

    /**
     * @var \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface
     */
    protected $paymentBuilder;

    /**
     * @param \Kount\Kount360\Model\Ris\InquiryFactory $inquiryFactory
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Kount\Kount360\Model\Ris\Inquiry\Builder\VersionInfo $versionBuilder
     * @param \Kount\Kount360\Model\Ris\Base\Builder\Session $sessionBuilder
     * @param \Kount\Kount360\Model\Ris\Inquiry\Builder\Order $orderBuilder
     * @param \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface $paymentBuilder
     */
    public function __construct(
        \Kount\Kount360\Model\Ris\InquiryFactory $inquiryFactory,
        \Kount\Kount360\Model\Config\Account $configAccount,
        \Kount\Kount360\Model\Ris\Inquiry\Builder\VersionInfo $versionBuilder,
        \Kount\Kount360\Model\Ris\Base\Builder\Session $sessionBuilder,
        \Kount\Kount360\Model\Ris\Inquiry\Builder\Order $orderBuilder,
        \Kount\Kount360\Model\Ris\Base\Builder\PaymentInterface $paymentBuilder
    ) {
        $this->inquiryFactory = $inquiryFactory;
        $this->configAccount = $configAccount;
        $this->versionBuilder = $versionBuilder;
        $this->sessionBuilder = $sessionBuilder;
        $this->orderBuilder = $orderBuilder;
        $this->paymentBuilder = $paymentBuilder;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $auth
     * @param string $mack
     * @return \Magento\Framework\DataObject
     */
    public function build(Order $order, $auth = RisService::AUTH_AUTHORIZED, $mack = RisService::MACK_YES)
    {
        $inquiry = $this->inquiryFactory->create($order->getStore()->getWebsiteId());
        $this->orderBuilder->process($inquiry, $order);
        return $inquiry;
    }
}
