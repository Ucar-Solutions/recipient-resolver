<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Service;

use Ucarsolutions\RecipientResolver\Entity\Recipient;

interface RecipientResolverInterface
{
    public function all(): array;

    public function resolve(string $list): Recipient;
}