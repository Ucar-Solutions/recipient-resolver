# Recipient Resolver

Small, zero-magic library to resolve **named recipient lists** (e.g. `alerts`, `billing`) into a structured `Recipient` object with **To / CC / BCC**. Simple YAML config in, **objects out**. Works great in PHP apps and can be wrapped for CLI/other languages.

## Why?

*   Centralize who gets which emails (alerts, ops, marketing, …)
*   Keep config in version control (YAML)
*   Resolve lists reliably in code (`alerts` → `Recipient(to: ["alerts@example.com"])`)

## Features

*   ✅ Tiny API: `all()` and `resolve($list)`
*   ✅ YAML provider included
*   ✅ `Recipient` value object (TO/CC/BCC) + `NullRecipient` fallback
*   ✅ JSON-serializable recipients
*   ✅ Framework-agnostic, strict types
*   🔌 Extensible via `RecipientProviderInterface`

## Install

```bash
composer require ucarsolutions/recipient-resolver
```

## Usage

### 1) YAML config (with TO/CC/BCC)

```yaml
# recipients.yaml
alerts:
  receiver:
    - alerts@example.com
  cc:
    - cc.alerts@example.com
  bcc: []

billing:
  receiver:
    - billing@example.com
    - finance@example.com
  cc: []
  bcc:
    - billing.bcc@example.com

marketing-de:
  receiver:
    - de.marketing@example.com
```

> **Schema:** Each top-level key is a _list name_. Under it, use arrays `receiver`, `cc`, and `bcc`. Missing keys are treated as empty lists. Invalid emails are skipped.

### 2) PHP

```php
<?php
use Ucarsolutions\RecipientResolver\Provider\YamlRecipientProvider;
use Ucarsolutions\RecipientResolver\Service\RecipientResolver;
use Ucarsolutions\RecipientResolver\Entity\Recipient;
use Ucarsolutions\RecipientResolver\Entity\NullRecipient;

$provider = new YamlRecipientProvider(__DIR__ . '/recipients.yaml');
$resolver = new RecipientResolver($provider);

// Get the whole map
/** @var array<string, Recipient> $all */
$all = $resolver->all();

// Resolve a single list
$alerts = $resolver->resolve('alerts'); // Recipient

$to  = $alerts->getReceiver();        // ['alerts@example.com']
$cc  = $alerts->getCarbonCopy();      // ['cc.alerts@example.com']
$bcc = $alerts->getBlindCarbonCopy(); // []

// Unknown list → NullRecipient (safe no-op)
$unknown = $resolver->resolve('does-not-exist'); // NullRecipient
$unknown->getReceiver(); // []

// JSON (e.g., for logging)
$json = json_encode($alerts, JSON_THROW_ON_ERROR);
// {"recipient":["alerts@example.com"],"carbonCopy":["cc.alerts@example.com"],"blindCarbonCopy":[]}
```

## Extensibility

Implement your own source by plugging into the interface:

```php
<?php
use Ucarsolutions\RecipientResolver\Entity\Recipient;
use Ucarsolutions\RecipientResolver\Provider\RecipientProviderInterface;

/**
 * @return array<string, Recipient>
 */
final class MyDbRecipientProvider implements RecipientProviderInterface
{
    public function provide(): array
    {
        // Build Recipient objects from your data source
        return [
            'alerts' => new Recipient(
                receiver: ['a@example.com'],
                carbonCopy: [],
                blindCarbonCopy: [],
            ),
        ];
    }
}
```

Then wire it:

```php
<?php
use Ucarsolutions\RecipientResolver\Service\RecipientResolver;

$resolver = new RecipientResolver(new MyDbRecipientProvider());
```

## Behavior & Guarantees

*   **Validation:** Emails are validated with `filter_var(..., FILTER_VALIDATE_EMAIL)`. Invalid entries are skipped.
*   **Empties:** Missing sections (`receiver`/`cc`/`bcc`) → treated as empty arrays.
*   **Duplicates:** Preserved (provider does not deduplicate).
*   **Unknown lists:** `resolve()` returns `NullRecipient` (all arrays empty).

## Testing

This repo ships with PHPUnit tests using real files/classes (no mocks).

```bash
composer install
vendor/bin/phpunit
```

## Roadmap help welcome!

*   Optional de-duplication per list
*   Optional “merge providers” (e.g., base YAML + env override)
*   Optional CLI wrapper to print JSON/CSV/lines

## Contributing

Issues and PRs are very welcome. Keep it small, readable, and covered by tests.  
For new providers, add minimal docs + tests.

## License

MIT © Ucar Solutions