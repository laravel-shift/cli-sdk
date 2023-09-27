<?php

namespace Shift\Cli\Sdk\Models;

class Comment
{
    private string $content;

    private ?string $reference;

    private array $paths;

    public function __construct(string $content, array $paths, ?string $reference)
    {
        $this->content = $content;
        $this->paths = $paths;
        $this->reference = $reference;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function hasPaths(): bool
    {
        return \count($this->paths) > 0;
    }

    public function hasReference(): bool
    {
        return $this->reference !== null;
    }

    public function paths(): array
    {
        return \array_map(
            fn ($path) => \str_starts_with($path, \getcwd()) ? \substr($path, \strlen(\getcwd() . DIRECTORY_SEPARATOR)) : $path,
            $this->paths
        );
    }

    public function reference(): ?string
    {
        return $this->reference;
    }
}
