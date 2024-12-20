<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Order;

class ActionFactory
{
    const DECLINE = 'decline';
    const REVIEW = 'review';
    const RESTORE = 'restore';

    public function __construct(
        protected \Kount\Kount360\Model\Order\Action\Decline $declineAction,
        protected \Kount\Kount360\Model\Order\Action\Review $reviewAction,
        protected \Kount\Kount360\Model\Order\Action\Restore $restoreAction
    ) {
    }

    /**
     * @param string $action
     * @return \Kount\Kount360\Model\Order\ActionInterface
     * @throws \InvalidArgumentException
     */
    public function create($action)
    {
        $property = strtolower($action) . 'Action';
        if (!property_exists($this, $property)) {
            throw new \InvalidArgumentException(
                "Action {$action} is not supported."
            );
        }

        $actionObject = $this->{$property};
        if (!$actionObject instanceof ActionInterface) {
            throw new \InvalidArgumentException(
                get_class($actionObject) . ' must be an instance of ' . \Kount\Kount360\Model\Order\ActionInterface::class
            );
        }

        return $this->{$property};
    }
}
