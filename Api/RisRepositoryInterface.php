<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Api;

use Kount\Kount360\Api\Data\RisInterface;
interface RisRepositoryInterface
{
    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return \Kount\Kount360\Api\Data\RisInterface
     */
    public function save(RisInterface $ris): RisInterface;

    /**
     * @param \Kount\Kount360\Api\Data\RisInterface $ris
     * @return void
     */
    public function delete(RisInterface $ris): void;

    /**
     * @param int $risId
     * @return void
     */
    public function deleteById(int $risId): void;

    /**
     * @param int $risId
     * @return \Kount\Kount360\Api\Data\RisInterface
     */
    public function getById(int $risId): RisInterface;

    /**
     * @param int $orderId
     * @return \Kount\Kount360\Api\Data\RisInterface
     */
    public function getByOrderId(int $orderId): RisInterface;
}
