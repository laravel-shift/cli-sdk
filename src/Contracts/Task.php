<?php

namespace Shift\Cli\Sdk\Contracts;

interface Task
{
    public function perform(): int;
}
