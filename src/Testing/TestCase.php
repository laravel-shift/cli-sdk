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

    protected function assertCommentMatching(array $comments, string $content = null, array $paths = null, string $reference = null): void
    {
        if (is_null($content) && is_null($paths) && is_null($reference)) {
            throw new \InvalidArgumentException('At least one of `content`, `paths`, or `reference` arguments must be provided.');
        }

        $matches = \collect($comments)
            ->where(function (\Shift\Cli\Sdk\Models\Comment $comment) use ($reference, $paths, $content) {
                return (is_null($content) || str_contains($comment->content(), $content))
                    && (is_null($paths) || empty(array_diff($comment->paths(), $paths)))
                    && (is_null($reference) || $reference === $comment->reference());
            });

        $this->assertCount(1, $matches, 'Failed to find a comment matching the given criteria.');
    }
}
