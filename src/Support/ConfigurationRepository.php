<?php

namespace Shift\Cli\Sdk\Support;

class ConfigurationRepository
{
    private string $path = 'shift-cli.json';

    private ?array $data = null;

    private array $tasks = [];

    private array $options = [];

    public function __construct(string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    public function get($key, $default = null)
    {
        return \data_get($this->data(), $key, $default);
    }

    private function data(): array
    {
        $this->data ??= $this->load();

        return $this->data;
    }

    public function defaults(): array
    {
        return array_merge(['tasks' => $this->tasks, 'ignore' => []], $this->options);
    }

    public function setDefaultTasks(array $tasks, array $options = []): void
    {
        $this->tasks = $tasks;
        $this->options = $options;
    }

    private function load(): array
    {
        if (! file_exists($this->path)) {
            return $this->defaults();
        }

        $configuration = json_decode(file_get_contents($this->path), true);
        if (! is_array($configuration)) {
            throw new \RuntimeException("The configuration file ({$this->path}) contains invalid JSON.");
        }

        return array_replace_recursive($this->defaults(), $configuration);
    }
}
