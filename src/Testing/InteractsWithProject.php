<?php

namespace Shift\Cli\Sdk\Testing;

use Illuminate\Support\Str;
use PHPUnit\Framework\Assert;

trait InteractsWithProject
{
    private string $uid;

    private string $cwd;

    private array $structure;

    public static function setUpBeforeClass(): void
    {
        if (! SnapshotState::$purged) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::snapshotsPath()), \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                if (\in_array($file->getBasename(), ['.', '..', '.gitignore'])) {
                    continue;
                }

                if ($file->isDir()) {
                    \rmdir($file->getPathName());
                } elseif ($file->isFile() || $file->isLink()) {
                    \unlink($file->getPathname());
                }
            }

            SnapshotState::$purged = true;
        }

        parent::setUpBeforeClass();
    }

    protected function tearDown(): void
    {
        if (isset($this->cwd)) {
            \chdir($this->cwd);
        }

        parent::tearDown();
    }

    public function fakeClass(string $fqcn): string
    {
        $namespace = Str::beforeLast($fqcn, '\\');
        $class = Str::afterLast($fqcn, '\\');

        return <<<EOT
<?php

namespace $namespace;

class $class
{
    // ...
}
EOT;

    }

    public function fakeProject(array $structure, array $autoload = []): void
    {
        $this->structure = $structure;

        $project = $this->currentSnapshotPath();
        \mkdir($project);

        if (! empty($autoload)) {
            $classmap = [];
            foreach ($autoload as $fqcn => $fixture) {
                $fake = \preg_replace('/\W+/', '_', Str::before($fqcn, '.php')) . '.php';
                $structure['vendor/fakes/' . $fake] = $fixture;
                $classmap[$fqcn] = $fake;
            }

            $structure = \array_merge($structure, [
                'composer.json' => $this->composerStub(),
                'vendor/autoload.php' => $this->autoloaderStub(),
                'vendor/fake-classmap.json' => \json_encode($classmap, JSON_PRETTY_PRINT),
            ]);
        }

        foreach ($structure as $src => $fixture) {
            if (! \is_dir($project . DIRECTORY_SEPARATOR . \dirname($src))) {
                \mkdir($project . DIRECTORY_SEPARATOR . \dirname($src), recursive: true);
            }

            if (\str_starts_with($fixture, 'tests/fixtures/')) {
                Assert::assertFileExists($this->fixturePath($fixture));
                \copy($this->fixturePath($fixture), $project . DIRECTORY_SEPARATOR . $src);
            } else {
                \file_put_contents($project . DIRECTORY_SEPARATOR . $src, $fixture);
            }
        }

        $this->cwd = \getcwd();
        \chdir($this->currentSnapshotPath());
    }

    public function assertFileChanges(string $expected, string $actual): void
    {
        if (\str_starts_with($expected, 'tests/fixtures/')) {
            Assert::assertFileEquals($this->fixturePath($expected), $actual);

            return;
        }

        Assert::assertStringEqualsFile($actual, $expected);
    }

    public function assertFileNotChanged(string $actual): void
    {
        Assert::assertArrayHasKey($actual, $this->structure, 'Failed asserting original file existed');
        $expected = $this->structure[$actual];

        if (\str_starts_with($expected, 'tests/fixtures/')) {
            Assert::assertFileEquals($this->fixturePath($expected), $actual, 'Failed asserting there were no file changes');

            return;
        }

        Assert::assertStringEqualsFile($actual, $expected, 'Failed asserting there were no file changes');
    }

    public function assertFileMoved(string $expected, string $original): void
    {
        Assert::assertFileDoesNotExist($original);
        Assert::assertFileExists($expected);
    }

    public function assertFileRemoved(string $original)
    {
        Assert::assertFileDoesNotExist($original);
    }

    private function fixturePath(string $fixture)
    {
        return self::basePath() . DIRECTORY_SEPARATOR . $fixture;
    }

    private function currentSnapshotPath(): string
    {
        if (! isset($this->uid)) {
            $caller = \debug_backtrace(0, 3)[2];
            $this->uid = \md5($caller['class'] . '::' . $caller['function'] . '::' . \serialize($caller['args']));
        }

        return self::snapshotsPath() . DIRECTORY_SEPARATOR . $this->uid;
    }

    private static function snapshotsPath(): string
    {
        return self::basePath() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'snapshots';
    }

    private static function basePath(): string
    {
        static $path;

        $path ??= \dirname(__DIR__, 5);

        return $path;
    }

    private function autoloaderStub(): string
    {
        return <<<'EOT'
<?php
spl_autoload_register(function ($class) {
    static $classes;

    $classes ??= json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'fake-classmap.json'), true);

    if (isset($classes[$class])) {
        require __DIR__ . DIRECTORY_SEPARATOR . 'fakes' . DIRECTORY_SEPARATOR . $classes[$class];
    }

    $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . str_replace(['App\\', '\\'], ['app/', DIRECTORY_SEPARATOR], $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
}, prepend: true);
EOT;
    }

    private function composerStub(): string
    {
        return \json_encode(
            [
                'autoload' => [
                    'psr-4' => [
                        'App\\' => 'app/',
                    ],
                ],
            ],
            JSON_PRETTY_PRINT
        );
    }
}
