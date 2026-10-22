<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
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
        $htmlId = $this->escapeHtmlAttr($element->getHtmlId());
        if ($dir->isFile('log/' . ConfigLog::FILENAME)) {
            $html = '<a id="' . $htmlId . '" href="' . $this->escapeUrl($this->getUrl('kount360/config/log')) . '">'
                . $this->escapeHtml(__('Download')) . '</a>';
        } else {
            $html = '<span id="' . $htmlId . '">' . $this->escapeHtml(__('File is not generated yet.')) . '</span>';
        }
        return $html;
    }
}
