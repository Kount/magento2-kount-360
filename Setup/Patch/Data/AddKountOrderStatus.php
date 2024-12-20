<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;
use Kount\Kount360\Model\Order\Ris as OrderRis;

class AddKountOrderStatus implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function apply(): void
    {
        if(!$this->kountStatusExist()){
            $setup = $this->moduleDataSetup;

            /* Add Kount order statuses */
            $status = [
                ['status' => OrderRis::STATUS_KOUNT_REVIEW , 'label' => __('Review')],
                ['status' => OrderRis::STATUS_KOUNT_DECLINE , 'label' => __('Decline')],
            ];
            $setup->getConnection()->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $status);

            /* Attach Kount statuses to holded order state */
            $states = [
                ['status' => OrderRis::STATUS_KOUNT_REVIEW, 'state' => Order::STATE_HOLDED, 'is_default' => 0],
                ['status' => OrderRis::STATUS_KOUNT_DECLINE, 'state' => Order::STATE_HOLDED, 'is_default' => 0],
            ];
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default'],
                $states
            );
        }

    }

    /**
     * @return bool
     */
    protected function kountStatusExist(): bool
    {
        $setup = $this->moduleDataSetup;
        $connection = $setup->getConnection();
        $salesOrderStatus = $setup->getTable('sales_order_status');
        $selectQuery = $connection->select()->from($salesOrderStatus)->where('status = ?',OrderRis::STATUS_KOUNT_REVIEW);
        $kountStatus = $connection->fetchAll($selectQuery);

        return count($kountStatus) > 0;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
