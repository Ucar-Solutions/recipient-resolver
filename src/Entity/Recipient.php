<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Entity;

use JsonSerializable;

final readonly class Recipient implements JsonSerializable
{
    public function __construct(
        private array $recipients,
        private array $carbonCopy,
        private array $blindCarbonCopy,
    )
    {
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getCarbonCopy(): array
    {
        return $this->carbonCopy;
    }

    public function getBlindCarbonCopy(): array
    {
        return $this->blindCarbonCopy;
    }

    public function jsonSerialize(): array
    {
        return [
            'recipient' => $this->getRecipients(),
            'carbonCopy' => $this->getCarbonCopy(),
            'blindCarbonCopy' => $this->getBlindCarbonCopy()
        ];
    }
}