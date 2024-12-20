<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Config\Source;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        protected \Kount\Kount360\Model\Config\Backend\Scope $configScope,
        protected \Kount\Kount360\Helper\Payment $paymentHelper
    ) {
    }

    /**
     * @return array[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray(): array
    {
        $paymentMethods = $this->paymentHelper->getActiveMethods(
            $this->configScope->getScope(),
            $this->configScope->getScopeValue()
        );

        $options = [
            ['value' => '', 'label' => __('None')]
        ];

        /** @var \Magento\Payment\Model\Method\AbstractMethod $method */
        foreach ($paymentMethods as $method) {
            $options[] = [
                'value' => $method->getCode(),
                'label' => $method->getTitle() . " ({$method->getCode()})"
            ];
        }

        return $options;
    }
}
