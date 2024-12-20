<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Filesystem\DirectoryList;
use Kount\Kount360\Model\Config\Log as ConfigLog;

class Log extends Field
{
    /**
     * Override method to output our custom HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $dir = $this->_filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        if ($dir->isFile('log/' . ConfigLog::FILENAME)) {
            $html = '<a id="' . $element->getHtmlId() . '" href="' . $this->getUrl('kount360/config/log') . '">' . __(
                    'Download'
                ) . '</a>';
        } else {
            $html = '<span id="' . $element->getHtmlId() . '">' . __('File is not generated yet.') . '</a>';
        }
        return $html;
    }
}
