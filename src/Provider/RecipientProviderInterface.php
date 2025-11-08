<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Provider;

use Ucarsolutions\RecipientResolver\Entity\Recipient;

interface RecipientProviderInterface
{
    /**
     * @return array<string,Recipient>
     */
    public function provide(): array;
}