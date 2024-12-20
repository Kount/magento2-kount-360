<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

class Session extends \Magento\Framework\Session\SessionManager
{
    const KOUNT_SESSION_ID = 'kount_session_id';

    /**
     * @return $this
     */
    public function incrementKountSessionId()
    {
        $kountSessionId = substr(hash('sha512', rand(0, 100000) . '-' . microtime()), 0, 32);
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
