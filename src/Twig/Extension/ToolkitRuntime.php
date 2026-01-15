<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Enum\ToolkitKitId;
use App\Service\Toolkit\ToolkitService;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class ToolkitRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ToolkitService $toolkitService,
    ) {
    }

    public function codeExample(string $kitId, string $recipeName, string $exampleName, array $options = [], bool $preview = true): string
    {
        $kitId = ToolkitKitId::from($kitId);
        $kit = $this->toolkitService->getKit($kitId);
        $recipe = $kit->getRecipe($recipeName);

        $exampleFile = sprintf('%s/examples/%s.html.twig', $recipe->absolutePath, $exampleName);
        if (!file_exists($exampleFile)) {
            throw new \InvalidArgumentException(sprintf('Example "%s" does not exist for recipe "%s" in kit "%s", path "%s" not found.', $exampleName, $recipeName, $kitId->value, $exampleFile));
        }

        $exampleCode = trim(file_get_contents($exampleFile));
        $language = 'twig';
        $options = json_encode($options + ['kit' => $kitId->value]);


        if ($preview) {
            return sprintf(
                <<<'MARKDOWN'
                    ::: tabs

                    :: tab Preview

                    [toolkit-preview %3$s]
                    %1$s
                    [/toolkit-preview]

                    :: tab Code

                    ```%2$s %3$s
                    %1$s
                    ```

                    :::
                    MARKDOWN,
                $exampleCode,
                $language,
                $options,
            );
        }
    }
}
