<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Helper;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Paypal\Model\Config;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    protected $extendedCodeList = [
        Config::METHOD_PAYFLOWPRO => [
            Config::METHOD_PAYFLOWPRO,
            Config::METHOD_PAYMENT_PRO
        ],
        Config::METHOD_WPP_EXPRESS => [
            Config::METHOD_WPS_EXPRESS,
            Config::METHOD_WPP_EXPRESS
        ]
    ];

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $methodFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Payment\Model\Method\Factory $methodFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Method\Factory $methodFactory
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodFactory = $methodFactory;
        parent::__construct($context);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function getMethodModelConfigName($code): string
    {
        return sprintf('%s/%s/model', PaymentHelper::XML_PATH_PAYMENT_METHODS, $code);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function getMethodConfigActivePath($code): string
    {
        return sprintf('%s/%s/active', PaymentHelper::XML_PATH_PAYMENT_METHODS, $code);
    }

    /**
     * @param string $scope
     * @param string $scopeValue
     * @return AbstractMethod[]
     */
    public function getActiveMethods($scope, $scopeValue)
    {
        $res = [];
        $methods = $this->paymentHelper->getPaymentMethods();

        foreach (array_keys($methods) as $code) {
            $model = $this->scopeConfig->getValue($this->getMethodModelConfigName($code), $scope, $scopeValue);
            if (!$model) {
                continue;
            }

            $isActive = $this->isPaymentMethodActive($code, $scope, $scopeValue);
            if (!$isActive) {
                continue;
            }

            /** @var AbstractMethod $methodInstance */
            $methodInstance = $this->methodFactory->create($model);
            $res[] = $methodInstance;
        }

        uasort(
            $res,
            function (MethodInterface $a, MethodInterface $b) {
                if ((int)$a->getConfigData('sort_order') < (int)$b->getConfigData('sort_order')) {
                    return -1;
                }

                if ((int)$a->getConfigData('sort_order') > (int)$b->getConfigData('sort_order')) {
                    return 1;
                }

                return 0;
            }
        );

        return $res;
    }

    /**
     * @param string $code
     * @param string $scope
     * @param string $scopeValue
     * @return bool
     */
    protected function isPaymentMethodActive($code, $scope, $scopeValue): bool
    {
        foreach ($this->getExtendedCodeList($code) as $codeKey) {
            $isActive = $this->scopeConfig->getValue($this->getMethodConfigActivePath($codeKey), $scope, $scopeValue);
            if ($isActive) {
                return true;
            }
        }
        return false;
    }

    /**
     * For PayPal methods support
     *
     * @param string $code
     * @return array
     */
    protected function getExtendedCodeList($code): array
    {
        return isset($this->extendedCodeList[$code]) ? $this->extendedCodeList[$code] : [$code];
    }
}
