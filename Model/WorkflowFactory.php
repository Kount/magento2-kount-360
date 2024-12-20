<?php
/**
 * Copyright (c) 2024 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Kount360\Model;

use Kount\Kount360\Model\Config\Source\WorkflowMode;

class WorkflowFactory
{
    protected $workflows;

    public function __construct(
        array $workflows = []
    ) {
        $this->workflows = $workflows;
    }

    /**
     * @param string $mode
     * @return \Kount\Kount360\Model\WorkflowInterface
     * @throws \InvalidArgumentException
     */
    public function create($mode)
    {
        if (empty($this->workflows[$mode])) {
            throw new \InvalidArgumentException("{$mode}: isn't allowed as Kount workflow mode");
        }
        return $this->workflows[$mode];
    }
}
