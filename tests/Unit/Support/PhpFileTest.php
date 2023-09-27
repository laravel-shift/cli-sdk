<?php

namespace Tests\Unit\Support;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shift\Cli\Sdk\Support\PhpFile;

class PhpFileTest extends TestCase
{
    #[Test]
    public function addImport_does_not_add_existing()
    {
        \copy('tests/fixtures/php/imports.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::addImport('Illuminate\Support\Arr', 'tests/fixtures/subject.swap');

        $this->assertFalse($actual);
        $this->assertFileEquals('tests/fixtures/php/imports.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function addImport_respects_aliases()
    {
        \copy('tests/fixtures/php/imports-alias.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::addImport('Illuminate\Support\Arr', 'tests/fixtures/subject.swap');

        $this->assertFalse($actual);
        $this->assertFileEquals('tests/fixtures/php/imports-alias.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function addImport_adds_to_top_of_imports()
    {
        \copy('tests/fixtures/php/imports.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::addImport('App\Models\User', 'tests/fixtures/subject.swap');

        $this->assertTrue($actual);
        $this->assertFileEquals('tests/fixtures/php/imports.after.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function addImport_adds_new_import_after_namespace()
    {
        \copy('tests/fixtures/php/imports-namespace.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::addImport('App\Models\User', 'tests/fixtures/subject.swap');

        $this->assertTrue($actual);
        $this->assertFileEquals('tests/fixtures/php/imports-namespace.after.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function addImport_adds_new_import_after_open_tag()
    {
        \copy('tests/fixtures/php/imports-open.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::addImport('App\Models\User', 'tests/fixtures/subject.swap');

        $this->assertTrue($actual);
        $this->assertFileEquals('tests/fixtures/php/imports-open.after.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function removeImport_does_nothing_when_does_not_exist()
    {
        \copy('tests/fixtures/php/imports.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::removeImport('Unknown\Import', 'tests/fixtures/subject.swap');

        $this->assertFalse($actual);
        $this->assertFileEquals('tests/fixtures/php/imports.php', 'tests/fixtures/subject.swap');
    }

    #[Test]
    public function removeImport_handles_alias()
    {
        \copy('tests/fixtures/php/imports-alias.php', 'tests/fixtures/subject.swap');

        $actual = PhpFile::removeImport('Illuminate\Support\Arr', 'tests/fixtures/subject.swap');

        $this->assertTrue($actual);
        $this->assertFileEquals('tests/fixtures/php/imports-alias-remove.after.php', 'tests/fixtures/subject.swap');
    }
}
