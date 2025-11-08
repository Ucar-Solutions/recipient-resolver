<?php
declare(strict_types=1);

namespace Test\Ucarsolutions\Service;

use PHPUnit\Framework\TestCase;
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;
use Ucarsolutions\RecipientResolver\Service\RecipientResolver;

final class RecipientResolverTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'yamlrecips_');
        file_put_contents($this->tmpFile, <<<YAML
alerts:
  - alerts@example.com
billing:
  - billing@example.com
  - finance@example.com
empty: []
YAML
        );
    }

    protected function tearDown(): void
    {
        @unlink($this->tmpFile);
    }

    public function testAllReturnsMapFromProvider(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));

        $all = $resolver->all();

        $this->assertArrayHasKey('alerts', $all);
        $this->assertArrayHasKey('billing', $all);
        $this->assertArrayHasKey('empty', $all);
        $this->assertSame(['alerts@example.com'], $all['alerts']);
    }

    public function testResolveKnownKeyReturnsList(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));

        $list = $resolver->resolve('billing');

        $this->assertSame(['billing@example.com', 'finance@example.com'], $list);
    }

    public function testResolveEmptyListKeyReturnsEmptyArray(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));

        $list = $resolver->resolve('empty');

        $this->assertSame([], $list);
    }

    public function testResolveUnknownKeyReturnsEmptyArray(): void
    {
        $resolver = new RecipientResolver(new YamlRecipientProvider($this->tmpFile));

        $list = $resolver->resolve('does-not-exist');

        $this->assertSame([], $list);
    }
}