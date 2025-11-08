<?php
declare(strict_types=1);

namespace Ucarsolutions\RecipientResolver\Provider;

use Symfony\Component\Yaml\Yaml;

final readonly class YamlRecipientProvider implements RecipientProviderInterface
{
    public function __construct(private string $path)
    {
    }

    /**
     * @return array<string,array<int,string>>
     */
    public function provide(): array
    {
        $result = [];
        $yamlContent = Yaml::parse(file_get_contents($this->path));
        foreach ($yamlContent as $list => $recipients) {
            $r = [];
            foreach ($recipients as $recipient) {
                if (!$this->isLikelyEmail($recipient)) {
                    continue;
                }
                $r[] = $recipient;
            }
            $result[$list] = $r;
        }
        return $result;
    }

    private function isLikelyEmail(string $value): bool
    {
        return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
    }
}