<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Kount\Kount360\Model\Config\Log as ConfigLog;

class Log extends Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Exception
     */
    public function execute() : \Magento\Framework\App\ResponseInterface
    {
        return $this->fileFactory->create(
            ConfigLog::FILENAME,
            ['type' => 'filename', 'value' => 'log/' . ConfigLog::FILENAME, 'rm' => false],
            DirectoryList::VAR_DIR
        );
    }
}
