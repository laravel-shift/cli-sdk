<?php

namespace Shift\Cli\Sdk\Testing;

use PHPUnit\Framework\Assert;

trait InteractsWithProject
{
    private string $uid;

    private string $cwd;

    private array $structure;

    public static function setUpBeforeClass(): void
    {
        if (!SnapshotState::$purged) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::snapshotsPath()), \RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($files as $file) {
                if (in_array($file->getBasename(), ['.', '..', '.gitignore'])) {
                    continue;
                }

                if ($file->isDir()) {
                    rmdir($file->getPathName());
                } elseif ($file->isFile() || $file->isLink()) {
                    unlink($file->getPathname());
                }
            }

            SnapshotState::$purged = true;
        }

        parent::setUpBeforeClass();
    }

    protected function tearDown(): void
    {
        if (isset($this->cwd)) {
            chdir($this->cwd);
        }

        parent::tearDown();
    }

    public function fakeProject(array $structure): void
    {
        $this->structure = $structure;

        $project = $this->currentSnapshotPath();
        mkdir($project);

        foreach ($this->structure as $src => $fixture) {
            if (!is_dir($project . DIRECTORY_SEPARATOR . dirname($src))) {
                mkdir($project . DIRECTORY_SEPARATOR . dirname($src), recursive: true);
            }

            if (str_starts_with($fixture, 'tests/fixtures/')) {
                Assert::assertFileExists($this->fixturePath($fixture));
                copy($this->fixturePath($fixture), $project . DIRECTORY_SEPARATOR . $src);
            } else {
                file_put_contents($project . DIRECTORY_SEPARATOR . $src, $fixture);
            }
        }

        $this->cwd = getcwd();
        chdir($this->currentSnapshotPath());
    }

    public function assertFileChanges(string $expected, string $actual): void
    {
        if (str_starts_with($expected, 'tests/fixtures/')) {
            Assert::assertFileEquals($this->fixturePath($expected), $actual);

            return;
        }

        Assert::assertStringEqualsFile($actual, $expected);
    }

    public function assertFileNotChanged(string $actual): void
    {
        Assert::assertArrayHasKey($actual, $this->structure, 'Failed asserting original file existed');
        $expected = $this->structure[$actual];

        if (str_starts_with($expected, 'tests/fixtures/')) {
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
        if (!isset($this->uid)) {
            $caller = debug_backtrace(0, 3)[2];
            $this->uid = md5($caller['class'] . '::' . $caller['function'] . '::' . serialize($caller['args']));
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

        $path ??= dirname(__DIR__, 5);

        return $path;
    }
}
