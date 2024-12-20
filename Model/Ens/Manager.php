<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens;

class Manager
{
    /**
     * @var \Kount\Kount360\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Kount\Kount360\Model\Ens\EventHandlerFactory
     */
    protected $eventHandlerFactory;

    /**
     * @var \Kount\Kount360\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Kount\Kount360\Model\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $supportedEvents;

    /**
     * @param \Kount\Kount360\Model\Config\Account $configAccount
     * @param \Kount\Kount360\Model\Ens\EventHandlerFactory $eventHandlerFactory
     * @param \Kount\Kount360\Helper\Data $helperData
     * @param \Kount\Kount360\Model\Logger $logger
     * @param array $supportedEvents
     */
    public function __construct(
        \Kount\Kount360\Model\Config\Account $configAccount,
        \Kount\Kount360\Model\Ens\EventHandlerFactory $eventHandlerFactory,
        \Kount\Kount360\Helper\Data $helperData,
        \Kount\Kount360\Model\Logger $logger,
        array $supportedEvents
    ) {
        $this->configAccount = $configAccount;
        $this->eventHandlerFactory = $eventHandlerFactory;
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->supportedEvents = $supportedEvents;
    }

    public function handleRequest($jsonData)
    {
        if (!is_array($jsonData)) {
            throw new \InvalidArgumentException('Invalid JSON request.');
        }

        if (!isset($jsonData['clientId']) || $jsonData['clientId'] != $this->configAccount->getClientId()) {
            throw new \InvalidArgumentException('Invalid Merchant Id in event JSON.');
        }

        $this->logger->info('Kount extension version: ' . $this->helperData->getModuleVersion());

        $successes = $failures = 0;

        try {
            // Validate and handle the event
            if (!$this->validateFieldName($jsonData)) {
                $successes++;
                $this->logger->info("Site code doesn't match, ignored.");
            } else {
                $this->handleEvent($jsonData);
                $successes++;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $failures++;
        }

        return $this->generateResponse($successes, $failures);
    }

    /**
     * @param $successes
     * @param $failures
     * @return false|string
     */
    public function generateResponse($successes, $failures): string
    {
        return json_encode([
            'successes' => $successes,
            'failures' => $failures
        ]);
    }

    /**
     * @param array $event
     * @return bool
     */
    protected function validateFieldName($event)
    {
        return isset($event['fieldName']);
    }

    /**
     * @param array $event
     */
    protected function handleEvent($event)
    {
        $eventName = $event['eventType'] ?? null;
        if (empty($eventName)) {
            throw new \InvalidArgumentException('Invalid Event name.');
        }

        if (!in_array($eventName, $this->supportedEvents, true)) {
            $this->logger->info("Event {$eventName} received, ignored.");
            return;
        }

        $eventHandler = $this->eventHandlerFactory->create($eventName);
        $eventHandler->process($event);
    }
}
