<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens\EventHandler;

use Kount\Kount360\Model\Ens\EventHandlerInterface;
use Kount\Kount360\Model\RisService;
use Kount\Kount360\Model\Order\ActionFactory as OrderActionFactory;

class StatusEdit extends EventHandlerOrder implements EventHandlerInterface
{
    const EVENT_NAME = 'Order.StatusChange';

    /**
     * @param \Kount\Kount360\Model\Order\ActionFactory $orderActionFactory
     * @param \Kount\Kount360\Model\Order\Ris $orderRis
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Kount\Kount360\Model\Logger $logger
     * @param \Kount\Kount360\Model\RisService $risService
     */
    public function __construct(
        protected \Kount\Kount360\Model\Order\ActionFactory $orderActionFactory,
        protected \Kount\Kount360\Model\Order\Ris $orderRis,
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        protected \Kount\Kount360\Model\Logger $logger,
        protected RisService $risService
    ) {
        parent::__construct($orderRepository, $searchCriteriaBuilder);
    }

    /**
     * @param \Magento\Framework\Simplexml\Element $event
     * @return void
     */
    public function process($event): void
    {
        $orderId = $event['merchantOrderId'];
        $transactionId  = $event['kountOrderId'];
        $oldValue = $event['oldValue'];
        $newValue = $event['newValue'];
        $this->logger->info('ENS Event Details');
        $this->logger->info('Name: ' . self::EVENT_NAME);
        $this->logger->info('order_number: ' . $orderId);
        $this->logger->info('transaction_id: ' . $transactionId);
        $this->logger->info('old_value: ' . $oldValue);
        $this->logger->info('new_value: ' . $newValue);

        $order = $this->loadOrder($orderId);
        $ris = $this->orderRis->getRis($order);

        $this->validateTransactionId($ris, $transactionId);
        $this->validateStatus($oldValue);
        $this->validateStatus($newValue);
        $this->updateRisResponse($order, $ris, $newValue);
        $this->updateOrderStatus($order, $ris, $oldValue, $newValue);
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @param int $transactionId
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    protected function validateTransactionId($ris, $transactionId): bool
    {
        if (empty($transactionId)) {
            throw new \InvalidArgumentException('Invalid Transaction ID.');
        }

        if (empty($ris->getTransactionId())) {
            throw new \InvalidArgumentException('Invalid Order Transaction ID.');
        }

        if ($ris->getTransactionId() !== $transactionId) {
            throw new \InvalidArgumentException(
                'Transaction ID does not match order,
                event must be for discarded version of order!'
            );
        }
        return true;
    }

    /**
     * @param string $status
     * @return bool
     */
    protected function validateStatus($status): bool
    {
        if (empty($status) || !in_array($status, $this->risService->getAutos(), true)) {
            throw new \InvalidArgumentException('Invalid status.');
        }
        return true;
    }

    /**
     * @param string $oldStatus
     * @param \Magento\Sales\Model\Order $order
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return bool
     */
    protected function isAllowedAction($oldStatus, $order, $ris): bool
    {
        if (in_array($oldStatus, [RisService::AUTO_REVIEW, RisService::AUTO_ESCALATE], true)
            && $ris->getResponse() !== $oldStatus
            && $this->isOrderPreHalt($order)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function isOrderPreHalt($order): bool
    {
        if ($order->getHoldBeforeState() != null) {
            return true;
        }
        $this->logger->info('Pre-hold order state / status not preserved.');
        return false;
    }

    /**
     * @param $order
     * @param $ris
     * @param $status
     * @return void
     */
    protected function updateRisResponse($order, $ris, $status): void
    {
        $ris->setResponse($status);
        $order->addStatusHistoryComment(__('Kount ENS Notification: Modify status of an order by agent to ' . $status));
        $this->orderRepository->save($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @param string $oldStatus
     * @param string $newStatus
     */
    protected function updateOrderStatus($order, $ris, $oldStatus, $newStatus): void
    {
        if (!$this->isAllowedAction($oldStatus, $order, $ris)) {
            return;
        }

        switch ($newStatus) {
            case RisService::AUTO_APPROVE:
                $this->approveOrder($order);
                break;
            case RisService::AUTO_DECLINE:
                $this->declineOrder($order);
                break;
            default:
                $this->logger->info("New status {$newStatus}, does not change order status.");
                break;
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function approveOrder($order): void
    {
        $this->logger->info(
            'Kount status transitioned from review to allow. Order: '
            . $order->getIncrementId()
        );

        $this->orderActionFactory->create(OrderActionFactory::RESTORE)->process($order);
        $this->orderRepository->save($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
    */
    protected function declineOrder($order): void
    {
        $this->logger->info(
            'Kount status transitioned from review to decline. Order: '
            . $order->getIncrementId()
        );

        $this->orderActionFactory->create(OrderActionFactory::RESTORE)->process($order);
        $this->orderRepository->save($order);
        $this->orderActionFactory->create(OrderActionFactory::DECLINE)->process($order);
        $this->orderRepository->save($order);
    }
}
