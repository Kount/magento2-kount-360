<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Kount\Kount360\Api\Data\RisInterface;
use Magento\Framework\Model\AbstractModel;

class Ris extends AbstractModel implements RisInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Kount\Kount360\Model\ResourceModel\Ris::class);
    }

    /**
     * @return int
     */
    public function getRisId()
    {
        return $this->getData(self::RIS_ID);
    }

    /**
     * @param int $risId
     * @return $this
     */
    public function setRisId($risId)
    {
        $this->setData(self::RIS_ID, $risId);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
        return $this;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->getData(self::SCORE);
    }

    /**
     * @param int $score
     * @return $this
     */
    public function setScore($score)
    {
        $this->setData(self::SCORE, $score);
        return $this;
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->getData(self::RESPONSE);
    }

    /**
     * @param string $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->setData(self::RESPONSE, $response);
        return $this;
    }

    /**
     * @return string
     */
    public function getRule()
    {
        return $this->getData(self::RULE);
    }

    /**
     * @param string $rule
     * @return $this
     */
    public function setRule($rule)
    {
        $this->setData(self::RULE, $rule);
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRAN);
    }

    /**
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(self::TRAN, $transactionId);
        return $this;
    }

    /**
     * @return string
     */
    public function getGeox()
    {
        return $this->getData(self::GEOX);
    }

    /**
     * @param string $geox
     * @return $this
     */
    public function setGeox($geox)
    {
        $this->setData(self::GEOX, $geox);
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->setData(self::COUNTRY, $country);
        return $this;
    }

    /**
     * @return string
     */
    public function getKaptcha()
    {
        return $this->getData(self::KAPTCHA);
    }

    /**
     * @param string $kaptcha
     * @return $this
     */
    public function setKaptcha($kaptcha)
    {
        $this->setData(self::KAPTCHA, $kaptcha);
        return $this;
    }

    /**
     * @return string
     */
    public function getCards()
    {
        return $this->getData(self::CARDS);
    }

    /**
     * @param string $cards
     * @return $this
     */
    public function setCards($cards)
    {
        $this->setData(self::CARDS, $cards);
        return $this;
    }

    /**
     * @return string
     */
    public function getEmails()
    {
        return $this->getData(self::EMAILS);
    }

    /**
     * @param string $emails
     * @return $this
     */
    public function setEmails($emails)
    {
        $this->setData(self::EMAILS, $emails);
        return $this;
    }

    /**
     * @return string
     */
    public function getDevices()
    {
        return $this->getData(self::DEVICES);
    }

    /**
     * @param string $devices
     * @return $this
     */
    public function setDevices($devices)
    {
        $this->setData(self::DEVICES, $devices);
        return $this;
    }

    /**
     * @return string
     */
    public function getOmniscore()
    {
        return $this->getData(self::OMNISCORE);
    }

    /**
     * @param string $omniscore
     * @return $this
     */
    public function setOmniscore($omniscore)
    {
        $this->setData(self::OMNISCORE, $omniscore);
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->getData(self::IP_ADDRESS);
    }

    /**
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress)
    {
        $this->setData(self::IP_ADDRESS, $ipAddress);
        return $this;
    }

    /**
     * @return string
     */
    public function getIpCity()
    {
        return $this->getData(self::IP_CITY);
    }

    /**
     * @param string $ipCity
     * @return $this
     */
    public function setIpCity($ipCity)
    {
        $this->setData(self::IP_CITY, $ipCity);
        return $this;
    }

    /**
     * @return string
     */
    public function getNetw()
    {
        return $this->getData(self::NETW);
    }

    /**
     * @param string $netw
     * @return $this
     */
    public function setNetw($netw)
    {
        $this->setData(self::NETW, $netw);
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileDevice()
    {
        return $this->getData(self::MOBILE_DEVICE);
    }

    /**
     * @param string $mobileDevice
     * @return $this
     */
    public function setMobileDevice($mobileDevice)
    {
        $this->setData(self::MOBILE_DEVICE, $mobileDevice);
        return $this;
    }

    /**
     * @return string
     */
    public function getMobileType()
    {
        return $this->getData(self::MOBILE_TYPE);
    }

    /**
     * @param string $mobileType
     * @return $this
     */
    public function setMobileType($mobileType)
    {
        $this->setData(self::MOBILE_TYPE, $mobileType);
        return $this;
    }
}
