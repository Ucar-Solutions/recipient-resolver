# Recipient Resolver

Small, zero-magic library to resolve named recipient lists (e.g. `alerts`, `billing`) into email addresses. Simple YAML config in, arrays out. Works great in PHP apps and can be wrapped for CLI/other languages.

## Why?

*   Centralize who gets which emails (alerts, ops, marketing, …)
*   Keep config in version control (YAML)
*   Resolve lists reliably in code (`alerts` → `alerts@example.com`, …)

## Features

*   ✅ Tiny API: `all()` and `resolve($list)`
*   ✅ YAML provider included
*   ✅ Email validation (basic)
*   ✅ Framework-agnostic
*   🔌 Extensible via `RecipientProviderInterface`

## Install

```
composer require ucarsolutions/recipient-resolver
```

## Usage

### 1) YAML config

```
# recipients.yaml
alerts:
  - alerts@example.com
billing:
  - billing@example.com
  - finance@example.com
ops-oncall:
  - oncall@example.com
marketing-de:
  - de.marketing@example.com
```

### 2) PHP

```
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;
use Ucarsolutions\RecipientResolver\Service\RecipientResolver;

$provider = new YamlRecipientProvider(__DIR__ . '/recipients.yaml');
$resolver = new RecipientResolver($provider);

// Get the whole map
$all = $resolver->all(); // array<string, string[]>

// Resolve a single list
$alerts = $resolver->resolve('alerts'); // ['alerts@example.com']
```

## Extensibility

Implement your own source by plugging into the interface:

```
use Ucarsolutions\RecipientResolver\Provider\RecipientProviderInterface;

final class MyDbRecipientProvider implements RecipientProviderInterface
{
    public function provide(): array
    {
        // return ['alerts' => ['a@example.com'], ...];
    }
}
```

Then wire it:

```
$resolver = new RecipientResolver(new MyDbRecipientProvider());
```

## Testing

This repo ships with PHPUnit tests using real files/classes (no mocks).

```
composer install
vendor/bin/phpunit
```

## Roadmap (help welcome!)

*   Optional de-duplication per list
*   Optional “merge providers” (e.g., base YAML + env override)
*   Optional CLI wrapper to print JSON/CSV/lines

## Contributing

Issues and PRs are very welcome. Keep it small, readable, and covered by tests.  
For new providers, add minimal docs + tests.

## License

MIT © Ucar Solutions