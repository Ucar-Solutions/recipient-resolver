<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Service;

use Ucarsolutions\RecipientResolver\Provider\RecipientProviderInterface;

final readonly class RecipientResolver implements RecipientResolverInterface
{
    public function __construct(private RecipientProviderInterface $provider)
    {
    }


    public function all(): array
    {
        return $this->provider->provide();
    }

    public function resolve(string $list): array
    {
        $recipients = $this->provider->provide();
        return $recipients[$list] ?? [];
    }

}
