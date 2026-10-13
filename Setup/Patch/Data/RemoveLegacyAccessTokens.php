<?php
/**
 * Copyright (c) 2026 KOUNT, INC.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kount\Kount360\Setup\Patch\Data;

use Kount\Kount360\Model\Config\Authorization;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RemoveLegacyAccessTokens implements DataPatchInterface
{
    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @return $this
     */
    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');

        $connection->delete(
            $table,
            [
                'path IN (?)' => [
                    Authorization::ACCESS_TOKEN_CONFIG_PATH_TEST,
                    Authorization::ACCESS_TOKEN_CONFIG_PATH_PROD,
                ],
            ]
        );

        return $this;
    }

    /**
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}
