<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigurationRepositoryTest extends TestCase
{
    #[Test]
    public function it_returns_null_when_no_value_or_default()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository();

        $this->assertNull($repository->get('unknown-key'));
    }

    #[Test]
    public function it_returns_default_when_no_value()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository();

        $this->assertSame('default-value', $repository->get('unknown-key', 'default-value'));
    }

    #[Test]
    public function it_returns_a_default_configuration()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository();
        $repository->setDefaultTasks(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $repository->get('tasks'));
        $this->assertSame([], $repository->get('ignore'));
    }

    #[Test]
    public function it_loads_and_merges_values_from_config_file()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository('tests/fixtures/config/ignore.json');
        $repository->setDefaultTasks(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $repository->get('tasks'));
        $this->assertSame(
            [
                'tests/fixtures/',
                'resources/templates/*.blade.php',
                'example.php',
            ],
            $repository->get('ignore')
        );
    }

    #[Test]
    public function it_replaces_default_values_with_config_file()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository('tests/fixtures/config/empty-tasks.json');

        $this->assertSame([], $repository->get('tasks'));
        $this->assertSame(['example.php'], $repository->get('ignore'));
    }

    #[Test]
    public function it_throws_error_for_invalid_config_file()
    {
        $repository = new \Shift\Cli\Sdk\Support\ConfigurationRepository('tests/fixtures/config/invalid.json');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The configuration file (tests/fixtures/config/invalid.json) contains invalid JSON.');

        $repository->get('tasks');
    }
}
