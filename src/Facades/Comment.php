<?php

namespace Shift\Cli\Sdk\Facades;

use Shift\Cli\Sdk\Support\CommentRepository;

/**
 * @method static void addComment(string $comment, array $paths = [], string $reference = null)
 * @method static \Shift\Cli\Sdk\Models\Comment[] flush()
 *
 * @see \Shift\Cli\Sdk\Support\CommentRepository
 */
class Comment extends Facade
{
    public static function break(): string
    {
        return PHP_EOL . PHP_EOL;
    }

    protected static function getFacadeAccessor()
    {
        return Comment::class;
    }

    protected static function getInstance()
    {
        return new CommentRepository();
    }
}
