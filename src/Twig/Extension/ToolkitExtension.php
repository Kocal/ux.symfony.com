<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ToolkitExtension extends AbstractExtension
{
    public function getFunctions(): iterable
    {
        yield new TwigFunction('toolkit_code_example', [ToolkitRuntime::class, 'codeExample'], ['is_safe' => ['html']]);
    }
}
