<?php
declare(strict_types=1);

namespace Test\Ucarsolutions\RecipientResolver;

use PHPUnit\Framework\TestCase;
use Ucarsolutions\RecipientResolver\Entity\Recipient;
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;

final class YamlRecipientProviderTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'yamlrecips_');
        if ($this->tmpFile === false) {
            self::fail('Could not create temporary file for YAML test.');
        }
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
  receiver:
    - alerts@example.com
  cc:
    - cc1@example.com
    - cc2@example.com
  bcc:
    - b1@example.com
billing:
  receiver:
    - billing@example.com
    - finance@example.com
  cc: []
  bcc: []
YAML
        );

        $provider = new YamlRecipientProvider($this->tmpFile);
        $out = $provider->provide();

        self::assertIsArray($out);
        self::assertArrayHasKey('alerts', $out);
        self::assertArrayHasKey('billing', $out);

        self::assertInstanceOf(Recipient::class, $out['alerts']);
        self::assertSame(['alerts@example.com'], $out['alerts']->getReceiver());
        self::assertSame(['cc1@example.com', 'cc2@example.com'], $out['alerts']->getCarbonCopy());
        self::assertSame(['b1@example.com'], $out['alerts']->getBlindCarbonCopy());

        self::assertInstanceOf(Recipient::class, $out['billing']);
        self::assertSame(['billing@example.com', 'finance@example.com'], $out['billing']->getReceiver());
        self::assertSame([], $out['billing']->getCarbonCopy());
        self::assertSame([], $out['billing']->getBlindCarbonCopy());

        // JSON shape (smoke test)
        $json = json_encode($out['alerts'], JSON_THROW_ON_ERROR);
        self::assertIsString($json);
        self::assertStringContainsString('"recipient":["alerts@example.com"]', $json);
        self::assertStringContainsString('"carbonCopy":["cc1@example.com","cc2@example.com"]', $json);
        self::assertStringContainsString('"blindCarbonCopy":["b1@example.com"]', $json);
    }

    public function testProvideSkipsInvalidEmailsAcrossAllLists(): void
    {
        $this->writeYaml(<<<YAML
ops:
  receiver:
    - oncall@example.com
    - "not-an-email"
    - "also@invalid@double"
  cc:
    - "valid.cc@example.com"
    - "bad cc"
  bcc:
    - "also-bad"
    - "valid.bcc@example.com"
YAML
        );

        $provider = new YamlRecipientProvider($this->tmpFile);
        $out = $provider->provide();

        self::assertArrayHasKey('ops', $out);
        $rec = $out['ops'];
        self::assertInstanceOf(Recipient::class, $rec);

        self::assertSame(['oncall@example.com'], $rec->getReceiver());
        self::assertSame(['valid.cc@example.com'], $rec->getCarbonCopy());
        self::assertSame(['valid.bcc@example.com'], $rec->getBlindCarbonCopy());
    }

    public function testProvideHandlesEmptyListsAndKeepsDuplicates(): void
    {
        $this->writeYaml(<<<YAML
empty:
  receiver: []
  cc: []
  bcc: []
dupes:
  receiver:
    - a@example.com
    - a@example.com
  cc:
    - c@example.com
    - c@example.com
  bcc:
    - b@example.com
    - b@example.com
YAML
        );

        $provider = new YamlRecipientProvider($this->tmpFile);
        $out = $provider->provide();

        self::assertArrayHasKey('empty', $out);
        self::assertSame([], $out['empty']->getReceiver());
        self::assertSame([], $out['empty']->getCarbonCopy());
        self::assertSame([], $out['empty']->getBlindCarbonCopy());

        self::assertArrayHasKey('dupes', $out);
        self::assertSame(['a@example.com', 'a@example.com'], $out['dupes']->getReceiver());
        self::assertSame(['c@example.com', 'c@example.com'], $out['dupes']->getCarbonCopy());
        self::assertSame(['b@example.com', 'b@example.com'], $out['dupes']->getBlindCarbonCopy());
    }

    public function testProvideWithTopLevelEmptyMap(): void
    {
        $this->writeYaml("{}\n");

        $provider = new YamlRecipientProvider($this->tmpFile);
        $out = $provider->provide();

        self::assertSame([], $out);
    }
}
