<?php

namespace Shift\Cli\Sdk\Parsers\Finders;

use PhpParser\Node;

class ImportFinder
{
    public function search(Node $node)
    {
        return $node instanceof Node\Stmt\UseUse;
    }

    public function process(array $instances)
    {
        $output = [];

        /** @var Node\Stmt\UseUse $instance */
        foreach ($instances as $instance) {
            $output[$instance->name->toCodeString()] = [
                'line' => ['start' => $instance->getStartLine(), 'end' => $instance->getEndLine()],
                'offset' => ['start' => $instance->getStartFilePos(), 'end' => $instance->getEndFilePos()],
                'fqcn' => $instance->name->toCodeString(),
                'alias' => $instance->alias?->name,
            ];
        }

        return $output;
    }
}
