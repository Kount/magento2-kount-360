<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */

namespace Kount\Kount360\Model\Queue\Handler;

class Handler
{
    public function __construct(
        private \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        private \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        private \Kount\Kount360\Model\RisService $risService,
        private \Kount\Kount360\Model\Logger $logger
    )
    {
    }

    /**
     * @param $orderIncrementId
     * @return void
     */
    public function execute($orderIncrementId)
    {
        try {
            // Build the search criteria
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('increment_id', $orderIncrementId, 'eq')
                ->setPageSize(1) // We only want one result
                ->create();

            // Search for the order
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();

            // Check if the order exists
            if (empty($orderList)) {
                $this->logger->info('Order #' . $orderIncrementId . ' did not update to Kount. It may not exist, skipping.');
                return;
            }

            // Return the first order (increment ID is unique)
            $salesOrder = reset($orderList);
            $this->risService->updateRequest($salesOrder);

        } catch (\Exception $e) {
            // This order never made it into magento, continue
            $this->logger->info('Kount Exception: Order #' . $orderIncrementId . ' did not update to Kount. It may not exist, skipping.');
            $this->logger->info($e->getMessage());
        }

    }



}
