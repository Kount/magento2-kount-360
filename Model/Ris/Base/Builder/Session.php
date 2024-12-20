<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ris\Base\Builder;

class Session
{
    /**
     * @var \Kount\Kount360\Model\Session
     */
    protected $kountSession;

    /**
     * Session constructor.
     * @param \Kount\Kount360\Model\Session $kountSession
     */
    public function __construct(
        \Kount\Kount360\Model\Session $kountSession
    ) {
        $this->kountSession = $kountSession;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     */
    public function process(\Magento\Framework\DataObject $request)
    {
        $request->setSessionId($this->kountSession->getKountSessionId());
    }
}
