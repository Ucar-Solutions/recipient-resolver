<?php
declare(strict_types=1);

namespace Test\Ucarsolutions\RecipientResolver;

use PHPUnit\Framework\TestCase;
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;

final class YamlRecipientProviderTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'yamlrecips_');
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    private function writeYaml(string $yaml): void
    {
        file_put_contents($this->tmpFile, $yaml);
    }

    public function testProvideParsesValidYamlAndValidatesEmails(): void
    {
        $this->writeYaml(<<<YAML
alerts:
  - alerts@example.com
billing:
  - billing@example.com
  - finance@example.com
YAML
        );
        $provider = new YamlRecipientProvider($this->tmpFile);

        $out = $provider->provide();

        $this->assertSame([
            'alerts' => ['alerts@example.com'],
            'billing' => ['billing@example.com', 'finance@example.com'],
        ], $out);
    }

    public function testProvideSkipsInvalidEmails(): void
    {
        $this->writeYaml(<<<YAML
ops:
  - oncall@example.com
  - "not-an-email"
  - "also@invalid@double"
YAML
        );
        $provider = new YamlRecipientProvider($this->tmpFile);

        $out = $provider->provide();

        $this->assertSame([
            'ops' => ['oncall@example.com'],
        ], $out);
    }

    public function testProvideHandlesEmptyListsAndKeepsDuplicates(): void
    {
        $this->writeYaml(<<<YAML
empty-list: []
dupes:
  - a@example.com
  - a@example.com
YAML
        );
        $provider = new YamlRecipientProvider($this->tmpFile);

        $out = $provider->provide();

        // Leerlisten bleiben leer; Duplikate bleiben (Provider dedupliziert nicht)
        $this->assertSame([
            'empty-list' => [],
            'dupes' => ['a@example.com', 'a@example.com'],
        ], $out);
    }

    public function testProvideWithTopLevelEmptyMap(): void
    {
        // Leere Map statt leerer Datei (Yaml::parse('') würde null liefern)
        $this->writeYaml("{}\n");
        $provider = new YamlRecipientProvider($this->tmpFile);

        $out = $provider->provide();

        $this->assertSame([], $out);
    }
}