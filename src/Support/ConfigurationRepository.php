<?php

namespace Shift\Cli\Sdk\Support;

class ConfigurationRepository
{
    private string $path = 'shift-cli.json';

    private ?array $data = null;

    private array $defaults = [];

    public function __construct(string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    public function get($key, $default = null)
    {
        return data_get($this->data(), $key, $default);
    }

    private function data(): array
    {
        $this->data ??= $this->load();

        return $this->data;
    }

    private function defaults(): array
    {
        return $this->defaults;
    }

    public function setDefaults(array $defaults): void
    {
        $this->defaults = $defaults;
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

        return array_replace($this->defaults(), $configuration);
    }
}
