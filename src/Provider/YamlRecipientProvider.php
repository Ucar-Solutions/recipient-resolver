<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Provider;

use Symfony\Component\Yaml\Yaml;
use Ucarsolutions\RecipientResolver\Entity\Recipient;

final readonly class YamlRecipientProvider implements RecipientProviderInterface
{
    public function __construct(private string $path)
    {
    }

    /**
     * @return array<string,Recipient>
     */
    public function provide(): array
    {
        $result = [];
        $yamlContent = Yaml::parseFile($this->path);

        foreach ($yamlContent as $list => $config) {
            $recipients = [];
            $carbonCopy = [];
            $blindCarbonCopy = [];
            foreach ($config['receiver'] ?? [] as $recipient) {
                if ($this->isLikelyEmail($recipient)) {
                    $recipients[] = $recipient;
                }
            }
            foreach ($config['cc'] ?? [] as $cc) {
                if ($this->isLikelyEmail($cc)) {
                    $carbonCopy[] = $cc;
                }
            }
            foreach ($config['bcc'] ?? [] as $bcc) {
                if ($this->isLikelyEmail($bcc)) {
                    $blindCarbonCopy[] = $bcc;
                }
            }


            $result[$list] = new Recipient(
                receiver: $recipients,
                carbonCopy: $carbonCopy,
                blindCarbonCopy: $blindCarbonCopy
            );
        }

        return $result;
    }

    private function isLikelyEmail(string $value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}