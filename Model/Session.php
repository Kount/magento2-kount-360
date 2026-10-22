<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

class Session extends \Magento\Framework\Session\SessionManager
{
    const KOUNT_SESSION_ID = 'kount_session_id';

    /**
     * @return $this
     * @throws \Exception
     */
    public function incrementKountSessionId()
    {
        $kountSessionId = bin2hex(random_bytes(16));
        $this->storage->setData(self::KOUNT_SESSION_ID, $kountSessionId);

        return $this;
    }

    /**
     * @return string
     */
    public function getKountSessionId()
    {
        if (empty($this->storage->getData(self::KOUNT_SESSION_ID))) {
            $this->incrementKountSessionId();
        }

        return $this->storage->getData(self::KOUNT_SESSION_ID);
    }
}
