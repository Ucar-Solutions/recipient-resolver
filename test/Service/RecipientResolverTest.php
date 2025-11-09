<?php
declare(strict_types=1);

namespace Test\Ucarsolutions\Service;

use PHPUnit\Framework\TestCase;
use Ucarsolutions\RecipientResolver\Entity\NullRecipient;
use Ucarsolutions\RecipientResolver\Entity\Recipient;
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;
use Ucarsolutions\RecipientResolver\Service\RecipientResolver;

final class RecipientResolverTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'yamlrecips_');
        if ($this->tmpFile === false) {
            self::fail('Could not create temporary file for YAML test.');
        }

        file_put_contents($this->tmpFile, <<<YAML
alerts:
  receiver:
    - alerts@example.com
  cc:
    - alerts.cc@example.com
  bcc: []
billing:
  receiver:
    - billing@example.com
    - finance@example.com
  cc: []
  bcc:
    - billing.bcc@example.com
empty:
  receiver: []
  cc: []
  bcc: []
YAML);
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testAllReturnsMapFromProviderWithRecipientObjects(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));
        $all = $resolver->all();

        self::assertIsArray($all);
        self::assertArrayHasKey('alerts', $all);
        self::assertArrayHasKey('billing', $all);
        self::assertArrayHasKey('empty', $all);

        self::assertInstanceOf(Recipient::class, $all['alerts']);
        self::assertSame(['alerts@example.com'], $all['alerts']->getReceiver());
        self::assertSame(['alerts.cc@example.com'], $all['alerts']->getCarbonCopy());
        self::assertSame([], $all['alerts']->getBlindCarbonCopy());
    }

    public function testResolveKnownKeyReturnsRecipient(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));
        $recipient = $resolver->resolve('billing');

        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertSame(['billing@example.com', 'finance@example.com'], $recipient->getReceiver());
        self::assertSame([], $recipient->getCarbonCopy());
        self::assertSame(['billing.bcc@example.com'], $recipient->getBlindCarbonCopy());

        // JSON shape check (smoke)
        $json = json_encode($recipient, JSON_THROW_ON_ERROR);
        self::assertStringContainsString('"recipient":["billing@example.com","finance@example.com"]', $json);
        self::assertStringContainsString('"blindCarbonCopy":["billing.bcc@example.com"]', $json);
    }

    public function testResolveEmptyListKeyReturnsRecipientWithEmptyArrays(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));
        $recipient = $resolver->resolve('empty');

        self::assertInstanceOf(Recipient::class, $recipient);
        self::assertSame([], $recipient->getReceiver());
        self::assertSame([], $recipient->getCarbonCopy());
        self::assertSame([], $recipient->getBlindCarbonCopy());
    }

    public function testResolveUnknownKeyReturnsNullRecipient(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));
        $recipient = $resolver->resolve('does-not-exist');

        self::assertInstanceOf(NullRecipient::class, $recipient);
        self::assertSame([], $recipient->getReceiver());
        self::assertSame([], $recipient->getCarbonCopy());
        self::assertSame([], $recipient->getBlindCarbonCopy());
    }
}
