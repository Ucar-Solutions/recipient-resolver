<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Entity;

final readonly class NullRecipient extends Recipient
{
    public function __construct()
    {
        parent::__construct([], [], []);
    }

}