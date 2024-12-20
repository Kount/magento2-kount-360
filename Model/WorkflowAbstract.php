<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Kount\Kount360\Model\Order\ActionFactory as OrderActionFactory;

abstract class WorkflowAbstract implements WorkflowInterface
{
    public function __construct(
        protected \Kount\Kount360\Model\Config\Workflow $configWorkflow,
        protected \Kount\Kount360\Model\RisService $risService,
        protected \Kount\Kount360\Model\Order\ActionFactory $orderActionFactory,
        protected \Kount\Kount360\Model\Order\Ris $orderRis,
        protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        protected \Magento\Framework\MessageQueue\PublisherInterface $publisher,
        protected \Kount\Kount360\Model\Logger $logger,
    ) {

    }


    protected function updaterOrderStatus($order, $inPaymentWorkflow = false)
    {
        $kountRisResponse = $this->orderRis->getRis($order)->getResponse();
        switch ($kountRisResponse) {
            case RisService::AUTO_DECLINE:
                $this->orderActionFactory->create(OrderActionFactory::DECLINE)->process($order, $inPaymentWorkflow);
                break;
            case RisService::AUTO_REVIEW:
            case RisService::AUTO_ESCALATE:
                $this->orderActionFactory->create(OrderActionFactory::REVIEW)->process($order);
                break;
        }

        $this->orderRepository->save($order);
    }
}
