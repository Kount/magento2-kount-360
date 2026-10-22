<?php
/**
 * Copyright (c) 2025 KOUNT, INC.
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
        if (!is_string($handlerCode) || !isset($this->handlers[$handlerCode])) {
            throw new \InvalidArgumentException('Handler for the requested ENS event isn\'t configured.');
        }

        $handler = $this->handlers[$handlerCode];

        if (!$handler instanceof EventHandlerInterface) {
            throw new \InvalidArgumentException("Configured ENS handler for {$handlerCode} is not a valid handler.");
        }

        return $handler;
    }
}
