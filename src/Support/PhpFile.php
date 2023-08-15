<?php

namespace Shift\Cli\Sdk\Support;

use RuntimeException;
use Shift\Cli\Sdk\Models\File;
use Shift\Cli\Sdk\Parsers\Finders\ImportFinder;
use Shift\Cli\Sdk\Parsers\NikicParser;

class PhpFile
{
    public static function imports($path): array
    {
        static $finder;

        $finder ??= new NikicParser(new ImportFinder());

        return $finder->parse(file_get_contents($path));
    }

    public static function findImport(string $fqcn, string $path): ?array
    {
        foreach (self::imports($path) as $import) {
            if ($import['fqcn'] === $fqcn) {
                return $import;
            }
        }

        return null;
    }

    public static function addImport(string $fqcn, string $path): bool
    {
        $import = self::findImport($fqcn, $path);
        if ($import) {
            return false;
        }

        $file = File::fromPath($path);
        $imports = self::imports($path);
        $content = 'use ' . $fqcn . ';' . PHP_EOL;

        if ($imports) {
            $line = current($imports)['line']['start'] - 1;
        } else {
            $line = 1;
            $content = PHP_EOL . $content;

            $tokens = token_get_all($file->contents());

            $index = self::findToken(T_NAMESPACE, $tokens);
            if ($index !== false && str_starts_with($file->line($tokens[$index][2]), 'namespace')) {
                $line = $tokens[$index][2];
            } else {
                $index = self::findToken(T_OPEN_TAG, $tokens);
                if ($index !== false) {
                    $line = $tokens[$index][2];
                }
            }
        }

        $file->insert($line, $content);
        file_put_contents($path, $file->contents());

        return true;
    }

    /**
     * Remove the `use` statement for a class in a PHP file.
     */
    public static function removeImport(string $fqcn, string $path): bool
    {
        $import = self::findImport($fqcn, $path);
        if (! $import) {
            return false;
        }

        $file = File::fromPath($path);
        $file->removeSegment($import['line']['start'], $import['line']['end']);
        file_put_contents($path, $file->contents());

        return true;
    }

    /**
     * Find the position of the closing parenthesis for the given opening parenthesis position
     */
    public static function findClosingParenthesis(int $position, string $contents): int
    {
        $characters = str_split($contents);
        $ignoreUntil = [];
        $comment = false;

        for ($i = $position + 1; $i < count($characters); $i++) {
            if ($comment === false && empty($ignoreUntil) && $characters[$i] === '/' && $characters[$i + 1] === '/') {
                $comment = 'single';

                continue;
            }

            if ($comment === false && empty($ignoreUntil) && $characters[$i] === '/' && $characters[$i + 1] === '*') {
                $comment = 'multi';

                continue;
            }

            if ($comment === 'single') {
                if (preg_match('/\R/', $characters[$i])) {
                    $comment = false;
                }

                continue;
            }

            if ($comment === 'multi') {
                if ($characters[$i] === '/' && $characters[$i - 1] === '*') {
                    $comment = false;
                }

                continue;
            }

            if ($characters[$i] === end($ignoreUntil) && $characters[$i - 1] !== '\\') {
                array_pop($ignoreUntil);

                continue;
            }

            if ($characters[$i] === ')' && empty($ignoreUntil)) {
                return $i;
            }

            $closingCharacter = match ($characters[$i]) {
                "'" => "'",
                '"' => '"',
                '(' => ')',
                '[' => ']',
                '{' => '}',
                default => null,
            };

            if ($closingCharacter && $characters[$i - 1] !== '\\') {
                $ignoreUntil[] = $closingCharacter;
            }
        }

        throw new RuntimeException('Unmatched closing parenthesis: ' . $contents);
    }

    private static function findToken($token, array $tokens, int $offset = 0)
    {
        for ($i = $offset; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === $token) {
                return $i;
            }
        }

        return false;
    }
}
