<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order\Action;

use Magento\Framework\Exception\LocalizedException;
use Kount\Kount360\Model\Config\Source\DeclineAction;
use Kount\Kount360\Model\Order\ActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Kount\Kount360\Model\Order\Ris as OrderRis;

class Decline implements ActionInterface
{
    public function __construct(
        private \Kount\Kount360\Model\Config\Workflow $configWorkflow,
        private \Magento\Framework\Registry $registry,
        private \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        private \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        private \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        private \Kount\Kount360\Model\Logger $logger,
        private \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order, $isInPaymentWorkflow = false)
    {
        $action = $this->configWorkflow->getDeclineAction();

        $isProcessed = false;

        $isForceCancel = false;
        if (DeclineAction::ACTION_REFUND === $action) {
            $isProcessed = $this->refund($order);
            $isForceCancel = !$isProcessed;
        }

        if (DeclineAction::ACTION_CANCEL === $action || $isForceCancel) {
            try {
                $isProcessed = $this->cancel($order);
            } catch (\Throwable $e) {
                if ($isInPaymentWorkflow) {
                    $this->messageManager->addErrorMessage(__('Your order was declined. Please try again or contact support.'));
                    throw $e;
                }
            }
        }

        if (!$isProcessed) {
            $this->setOrderStatusDecline($order);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function cancel($order)
    {
        $isCanceled = $this->orderCancel($order);
        $orderComment = $isCanceled
            ? __('Order cancelled / voided due to Kount RIS Decline.')
            : __('Failed to cancel order. Cancel attempt due to Kount RIS Decline.');
        $order->addStatusHistoryComment($orderComment);

        return $isCanceled;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function refund($order)
    {
        $isRefunded = $this->orderRefund($order);
        $orderComment = $isRefunded
            ? __('Order refunded due to Kount RIS Decline.')
            : __('Failed to refund order. Refund attempt due to Kount RIS Decline.');
        $order->addStatusHistoryComment($orderComment);

        return $isRefunded;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function setOrderStatusDecline(Order $order)
    {
        $orderState = $order->getHoldBeforeState();
        $orderStatus = $order->getHoldBeforeStatus();
        if ($orderState === Order::STATE_HOLDED && $orderStatus === OrderRis::STATUS_KOUNT_DECLINE) {
            $this->logger->info('Setting order to Kount Decline status/state - already set, skipping');
            return;
        }

        $this->logger->info('Setting order to Kount Decline status/state');

        $order->setHoldBeforeState($order->getState());
        $order->setHoldBeforeStatus($order->getStatus());

        $order->setState(Order::STATE_HOLDED);
        $order->addStatusToHistory(OrderRis::STATUS_KOUNT_DECLINE, __('Order declined from Kount.'), false);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function orderRefund(Order $order)
    {
        try {
            if (!$order->canCreditmemo()) {
                throw new LocalizedException(__('Cant create credit memo for order: %1.', $order->getIncrementId()));
            }

            if (!$order->hasInvoices()) {
                throw new LocalizedException(__('No invoices found for order: %1.', $order->getIncrementId()));
            }

            $invoiceCollection = $order->getInvoiceCollection();

            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            foreach ($invoiceCollection as $invoice) {
                $this->logger->info('Issuing refund / credit memo for invoice: ' . $invoice->getIncrementId());

                $this->creditmemoLoader->setOrderId($order->getId());
                $this->creditmemoLoader->setInvoiceId($invoice->getId());

                $this->registry->unregister('current_creditmemo');
                $creditmemo = $this->creditmemoLoader->load();
                if (!$creditmemo instanceof \Magento\Sales\Model\Order\Creditmemo) {
                    throw new LocalizedException(
                        __('Cannot create creditmemo for invoice: %1.', $invoice->getIncrementId())
                    );
                }

                $creditmemo->addComment(__('Kount Decline'), true);
                $creditmemo->setCustomerNote(__('Kount Decline'));
                $creditmemo->setCustomerNoteNotify(true);

                $this->creditmemoManagement->refund($creditmemo);
                $this->creditmemoManagement->notify($creditmemo->getId());

                $order->load($order->getId());
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Unable to refund Magento order: ' . $order->getIncrementId());
            $this->logger->critical($e);
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function orderCancel(Order $order)
    {
        $this->logger->info('Attempting to cancel Magento order.');

        if ($order->canCancel()) {
            $order->cancel();
            return true;
        }
        $this->logger->error('Unable to cancel Magento order.');
        return false;
    }
}
