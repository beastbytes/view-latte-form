<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Form\Node;

use Generator;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

class ButtonNode extends StatementNode
{
    use ConfigTrait;

    private ExpressionNode $content;
    private ?ExpressionNode $theme = null;
    protected string $name;

    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = $tag->node = new self;
        $node->name = $tag->name;

        foreach ($tag->parser->parseArguments() as $i => $argument) {
            switch ($i) {
                case 0:
                    $node->content = $argument->value;
                    break;
                case 1:
                    $node->theme = $argument->value;
                    break;
            }
        }

        if (!$tag->parser->isEnd()) {
            $node->config = $tag->parser->parseModifier();
        }

        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo Yiisoft\FormModel\Field::' . $this->name
            . '(%node, %raw'
            . ($this->theme !== null ? '%node' : ', %raw')
            . ') %line;',
            $this->content,
            $this->getConfig(),
            $this->theme,
            $this->position,
        );
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        yield $this->content;

        if ($this->theme !== null) {
            yield $this->theme;
        }
    }
}