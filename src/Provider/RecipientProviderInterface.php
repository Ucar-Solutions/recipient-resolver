<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Provider;

interface RecipientProviderInterface
{
    /**
     * @return array<string,array<int,string>>
     */
    public function provide(): array;
}