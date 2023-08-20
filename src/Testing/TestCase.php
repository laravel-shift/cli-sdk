<?php

namespace Shift\Cli\Sdk\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Shift\Cli\Sdk\Facades\Facade;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Facade::clearResolvedInstances();
    }
}
