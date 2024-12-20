<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model\Ens;

class EventHandlerFactory
{
    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @param array $handlers
     */
    public function __construct(
        array $handlers
    ) {
        $this->handlers = $handlers;
    }

    /**
     * @param string $handlerCode
     * @return \Kount\Kount360\Model\Ens\EventHandlerInterface
     * @throws \InvalidArgumentException
     */
    public function create($handlerCode)
    {
        if (empty($this->handlers[$handlerCode])) {
            throw new \InvalidArgumentException("Handler for {$handlerCode} ENS event isn't configured.");
        }

        return $this->handlers[$handlerCode];
    }
}
