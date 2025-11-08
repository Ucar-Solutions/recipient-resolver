<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Entity;

use JsonSerializable;

readonly class Recipient implements JsonSerializable
{
    public function __construct(
        private array $receiver,
        private array $carbonCopy,
        private array $blindCarbonCopy,
    )
    {
    }

    public function getReceiver(): array
    {
        return $this->receiver;
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
            'recipient' => $this->getReceiver(),
            'carbonCopy' => $this->getCarbonCopy(),
            'blindCarbonCopy' => $this->getBlindCarbonCopy()
        ];
    }
}