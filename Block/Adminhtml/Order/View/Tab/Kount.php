<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Block\Adminhtml\Order\View\Tab;

class Kount extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    protected $_template = 'order/view/tab/kount.phtml';

    /**
     * @var \Kount\Kount360\Model\Ris
     */
    protected $ris;

    public function __construct(
        protected \Magento\Backend\Block\Template\Context $context,
        protected \Kount\Kount360\Model\Config\Account $configAccount,
        protected \Kount\Kount360\Model\Order\Ris $orderRis,
        protected \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->initRis();
        return parent::_prepareLayout();
    }

    protected function initRis()
    {
        $this->ris = $this->orderRis->getRis($this->getOrder());
    }

    /**
     * @return int|string
     */
    public function getRisScore()
    {
        return $this->ris->getScore() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisResponse()
    {
        return $this->ris->getResponse() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisDescription()
    {
        return $this->ris->getDescription() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisRules()
    {
        return $this->ris->getRule() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisTransactionId()
    {
        return $this->ris->getTransactionId();
    }

    /**
     * @return string
     */
    public function getAWCUrl()
    {
        return $this->configAccount->getAwcUrl($this->getOrder()->getStore()->getWebsiteId())
        . '/event-analysis/order/' . $this->getRisTransactionId();
    }

    /**
     * @return string
     */
    public function getRisGeox()
    {
        return $this->ris->getGeox() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisCountry()
    {
        return $this->ris->getCountry() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisKaptcha()
    {
        return $this->ris->getKaptcha() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisCards()
    {
        return $this->ris->getCards() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisEmails()
    {
        return $this->ris->getEmails() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getRisDevices()
    {
        return $this->ris->getDevices() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getOmniscore()
    {
        return $this->ris->getOmniscore() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ris->getIpAddress() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getIpCity()
    {
        return $this->ris->getIpCity() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getNetw()
    {
        return $this->ris->getNetw() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getMobileDevice()
    {
        return $this->ris->getMobileDevice() ? : __('N');
    }

    /**
     * @return string
     */
    public function getMobileType()
    {
        return $this->ris->getMobileType() ? : __('N/A');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Kount');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('Kount');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
