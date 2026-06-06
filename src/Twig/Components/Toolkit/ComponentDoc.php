<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components\Toolkit;

use App\Enum\ToolkitKitId;
use App\Service\CommonMark\ConverterFactory;
use App\Service\Toolkit\ToolkitService;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\NodeIterator;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;
use Symfony\UX\Toolkit\Recipe\Recipe;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class ComponentDoc
{
    public ToolkitKitId $kitId;
    public Recipe $component;

    private ?string $markdownContent = null;

    public function __construct(
        private readonly ToolkitService $toolkitService,
        private readonly ConverterFactory $converterFactory,
    ) {
    }

    public function getMarkdownContent(): string
    {
        return $this->markdownContent ??= $this->toolkitService->renderRecipeMarkdown($this->kitId, $this->component);
    }

    /**
     * @return list<array{level: int, title: string, id: string}>
     */
    public function getTocItems(): array
    {
        $environment = ($this->converterFactory)()->getEnvironment();
        $document = new MarkdownParser($environment)->parse($this->getMarkdownContent());
        $renderer = new HtmlRenderer($environment);

        $items = [];
        foreach ($document->iterator(NodeIterator::FLAG_BLOCKS_ONLY) as $node) {
            if (!$node instanceof Heading) {
                continue;
            }
            $level = $node->getLevel();
            if ($level < 2 || $level > 3) {
                continue;
            }
            $id = $node->data->get('attributes/id', null);
            if (null === $id) {
                continue;
            }
            $items[] = [
                'level' => $level,
                'title' => (string) $renderer->renderNodes($node->children()),
                'id' => $id,
            ];
        }

        return $items;
    }
}
