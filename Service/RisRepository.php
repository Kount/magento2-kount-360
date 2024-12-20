<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Kount\Kount360\Service;

use Kount\Kount360\Api\Data\RisInterface;
use Kount\Kount360\Api\RisRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RisRepository implements RisRepositoryInterface
{
    /**
     * @param \Kount\Kount360\Api\Data\RisInterfaceFactory $risInterfaceFactory
     * @param \Kount\Kount360\Model\ResourceModel\Ris $risResource
     */
    public function __construct(
        private \Kount\Kount360\Api\Data\RisInterfaceFactory $risInterfaceFactory,
        private \Kount\Kount360\Model\ResourceModel\Ris $risResource,
    ) {
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return \Kount\Kount360\Api\Data\RisInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(RisInterface $ris): RisInterface
    {
        try {
            $this->risResource->save($ris);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save RIS Record: %1', $exception->getMessage()));
        }
        return $ris;
    }

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(RisInterface $ris): void
    {
        try {
            $this->risResource->delete($ris);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the RIS Record: %1', $e->getMessage()));
        }
    }

    /**
     * @param int $risId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $risId): void
    {
        $this->delete($this->getById($risId));
    }

    /**
     * @param int $risId
     * @return \Kount\Kount360\Api\Data\RisInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $risId): RisInterface
    {
        $ris = $this->risInterfaceFactory->create();
        $ris->load($risId);
        if (!$ris->getId()) {
            throw new NoSuchEntityException(
                __('The RIS record with the order ID "%1" doesn\'t exist.', $risId)
            );
        }
        return $ris;
    }

    /**
     * @param int $orderId
     * @return \Kount\Kount360\Api\Data\RisInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId(int $orderId): RisInterface
    {
        $ris = $this->risInterfaceFactory->create();
        $this->risResource->load($ris, $orderId, 'order_id'); // Load by 'order_id'
        if (!$ris->getId()) {
            throw new NoSuchEntityException(
                __('The RIS record with the order ID "%1" doesn\'t exist.', $orderId)
            );
        }
        return $ris;
    }

}
