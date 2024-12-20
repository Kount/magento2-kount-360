<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Api\Data;

interface RisInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const RIS_ID = 'ris_id';
    const ORDER_ID = 'order_id';
    const SCORE = 'score';
    const RESPONSE = 'response';
    const RULE = 'rule';
    const DESCRIPTION = 'description';
    const TRAN = 'transaction_id';
    const GEOX = 'geox';
    const COUNTRY = 'country';
    const KAPTCHA = 'kaptcha';
    const CARDS = 'cards';
    const EMAILS = 'emails';
    const DEVICES = 'devices';
    const OMNISCORE = 'omniscore';
    const IP_ADDRESS = 'ip_address';
    const IP_CITY = 'ip_city';
    const NETW = 'netw';
    const MOBILE_DEVICE = 'mobile_device';
    const MOBILE_TYPE = 'mobile_type';

    /**
     * @return int
     */
    public function getRisId();

    /**
     * @param int $risId
     * @return $this
     */
    public function setRisId($risId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getScore();

    /**
     * @param int $score
     * @return $this
     */
    public function setScore($score);

    /**
     * @return string
     */
    public function getResponse();

    /**
     * @param string $response
     * @return $this
     */
    public function setResponse($response);

    /**
     * @return string
     */
    public function getRule();

    /**
     * @param string $rule
     * @return $this
     */
    public function setRule($rule);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getGeox();

    /**
     * @param string $geox
     * @return $this
     */
    public function setGeox($geox);

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country);

    /**
     * @return string
     */
    public function getKaptcha();

    /**
     * @param string $kaptcha
     * @return $this
     */
    public function setKaptcha($kaptcha);

    /**
     * @return string
     */
    public function getCards();

    /**
     * @param string $cards
     * @return $this
     */
    public function setCards($cards);

    /**
     * @return string
     */
    public function getEmails();

    /**
     * @param string $emails
     * @return $this
     */
    public function setEmails($emails);

    /**
     * @return string
     */
    public function getDevices();

    /**
     * @param string $devices
     * @return $this
     */
    public function setDevices($devices);

    /**
     * @return string
     */
    public function getOmniscore();

    /**
     * @param string $omniscore
     * @return $this
     */
    public function setOmniscore($omniscore);

    /**
     * @return string
     */
    public function getIpAddress();

    /**
     * @param string $ipAddress
     * @return $this
     */
    public function setIpAddress($ipAddress);

    /**
     * @return string
     */
    public function getIpCity();

    /**
     * @param string $ipCity
     * @return $this
     */
    public function setIpCity($ipCity);

    /**
     * @return string
     */
    public function getNetw();

    /**
     * @param string $netw
     * @return $this
     */
    public function setNetw($netw);

    /**
     * @return string
     */
    public function getMobileDevice();

    /**
     * @param string $mobileDevice
     * @return $this
     */
    public function setMobileDevice($mobileDevice);

    /**
     * @return string
     */
    public function getMobileType();

    /**
     * @param string $mobileType
     * @return $this
     */
    public function setMobileType($mobileType);
}
